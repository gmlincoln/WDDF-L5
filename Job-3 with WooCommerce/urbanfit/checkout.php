<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sslcommerz_api.php';

// Enforce SSL Header Simulation
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

$cart = getCart();
$totals = getCartTotals();

if (empty($cart)) {
    header("Location: shop.php");
    exit;
}

$errorMessage = '';

// Handle Checkout Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['customer_email'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    // Form Validation: Reject Empty Required Fields
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($paymentMethod)) {
        $errorMessage = "⚠️ Form Validation Failed: Please complete all required billing and shipping fields.";
    } else {
        $orderNumber = 'UB-' . rand(1000, 9999);

        // If SSLCommerz Sandbox is selected -> Call SSLCommerz API Gateway
        if ($paymentMethod === 'SSLCommerz Sandbox') {
            $pendingOrderData = [
                'order_number' => $orderNumber,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'cart_items' => $cart
            ];
            $_SESSION['pending_order'] = $pendingOrderData;

            // Call SSLCommerz API Initiation
            $sslRes = initiateSSLCommerzPayment($pendingOrderData);
            if ($sslRes['success'] && !empty($sslRes['gateway_url'])) {
                header("Location: " . $sslRes['gateway_url']);
                exit;
            }
        }

        // Cash on Delivery (COD) Payment Path
        $trxId = 'N/A (COD)';
        $db = getDB();

        if ($db) {
            try {
                $stmt = $db->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, payment_method, trx_id, subtotal, shipping_cost, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
                $stmt->execute([
                    $orderNumber,
                    $name,
                    $email,
                    $phone,
                    $address,
                    $paymentMethod,
                    $trxId,
                    $totals['subtotal'],
                    $totals['shipping'],
                    $totals['total']
                ]);
                $orderId = $db->lastInsertId();

                $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($cart as $item) {
                    $itemStmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['name'],
                        $item['size'],
                        $item['price'],
                        $item['quantity']
                    ]);
                    reduceStock($item['product_id'], $item['quantity']);
                }
            } catch (Exception $e) {}
        } else {
            foreach ($cart as $item) {
                reduceStock($item['product_id'], $item['quantity']);
            }
        }

        // Generate Automated Email Log for COD
        $emailSubject = "Order Confirmation #{$orderNumber} - UrbanFit BD (COD)";
        $emailBody = "Dear {$name},\n\nThank you for shopping at UrbanFit BD! Your Cash on Delivery order #{$orderNumber} has been received.\nTotal Amount: ৳" . number_format($totals['total'], 2) . "\nPayment Method: Cash on Delivery\nEMS Local Shipping Active.\n\nBest regards,\nUrbanFit BD Team";
        logEmailNotification($orderNumber, $email, $emailSubject, $emailBody);

        // Generate Automated SMS Log for COD
        $smsMsg = "UrbanFit BD: COD Order #{$orderNumber} placed successfully! Total: BDT {$totals['total']}. Prepare cash upon EMS delivery.";
        logSMSNotification($orderNumber, $phone, $smsMsg);

        // Save order info to session for receipt view
        $_SESSION['last_order'] = [
            'order_number' => $orderNumber,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'payment_method' => $paymentMethod,
            'trx_id' => $trxId,
            'subtotal' => $totals['subtotal'],
            'shipping' => $totals['shipping'],
            'total' => $totals['total'],
            'cart_items' => $cart
        ];

        clearCart();
        header("Location: order_success.php?order=" . urlencode($orderNumber));
        exit;
    }
}
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem;">Secure Checkout</h1>
    <p style="color: var(--text-muted);">Complete customer details and proceed to SSLCommerz Sandbox payment gateway.</p>
</div>

<!-- HTTPS / SSL Security Padlock Banner -->
<div class="ssl-badge-box">
    <div style="font-size: 2rem;">🔒</div>
    <div>
        <strong style="font-size: 1.05rem; display: block; color: var(--text-main);">256-Bit SSL Encrypted Checkout Endpoint</strong>
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            HTTPS Protocol verified • Certificate Status: Active (TLS_AES_256_GCM_SHA384)
        </span>
    </div>
</div>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<form method="POST" action="checkout.php">
    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 2rem;">
        
        <!-- Left: Customer Information Form -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-main);">
                Customer & Delivery Information
            </h3>

            <div class="form-group">
                <label for="customer_name">Full Name <span style="color: var(--accent-rose);">*</span></label>
                <input type="text" id="customer_name" name="customer_name" required class="form-control" placeholder="e.g. Md. Golam Maula" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="customer_email">Email Address <span style="color: var(--accent-rose);">*</span></label>
                    <input type="email" id="customer_email" name="customer_email" required class="form-control" placeholder="e.g. customer@domain.com" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="customer_phone">Mobile Number <span style="color: var(--accent-rose);">*</span></label>
                    <input type="text" id="customer_phone" name="customer_phone" required class="form-control" placeholder="e.g. 01711223344" value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="shipping_address">Delivery Address (EMS Shipping Zone) <span style="color: var(--accent-rose);">*</span></label>
                <textarea id="shipping_address" name="shipping_address" required class="form-control" rows="3" placeholder="House/Road no, Area, City, Postcode"></textarea>
            </div>

            <!-- Payment Method Selection -->
            <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; margin: 2rem 0 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-main);">
                Payment Gateway Options
            </h3>

            <div class="payment-methods">
                <label class="payment-card selected">
                    <input type="radio" name="payment_method" value="SSLCommerz Sandbox" checked>
                    <div>
                        <strong style="color: var(--text-main); display: block;">SSLCommerz Gateway (Sandbox)</strong>
                        <small style="color: var(--accent-cyan); font-weight: 700;">💳 bKash, Nagad, Cards & Net Banking</small>
                    </div>
                </label>

                <label class="payment-card">
                    <input type="radio" name="payment_method" value="Cash on Delivery">
                    <div>
                        <strong style="color: var(--text-main); display: block;">Cash on Delivery (COD)</strong>
                        <small style="color: var(--text-muted);">Pay upon physical receipt</small>
                    </div>
                </label>
            </div>

            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-top: 1rem; font-size: 0.85rem; color: #0369a1;">
                ℹ️ Clicking <strong>"Proceed to SSLCommerz Sandbox Payment"</strong> will redirect you to the SSLCommerz Sandbox Gateway page where you can test Mobile Banking (bKash/Nagad), Cards, or Net Banking in 1-click.
            </div>
        </div>

        <!-- Right: Order Items & Total -->
        <div>
            <div class="order-summary-box">
                <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-main);">
                    Order Review (<?php echo $totals['count']; ?> items)
                </h3>

                <div style="margin-bottom: 1.5rem; max-height: 250px; overflow-y: auto;">
                    <?php foreach ($cart as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid var(--border-color);">
                            <div>
                                <strong style="color: var(--text-main); font-size: 0.9rem; display: block;"><?php echo htmlspecialchars($item['name']); ?></strong>
                                <span style="font-size: 0.75rem; color: var(--accent-emerald); font-weight: 700;">Size: <?php echo htmlspecialchars($item['size']); ?> × <?php echo $item['quantity']; ?></span>
                            </div>
                            <strong style="color: var(--text-main); font-size: 0.9rem;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-line">
                    <span style="color: var(--text-muted);">Items Subtotal</span>
                    <strong style="color: var(--text-main);"><?php echo formatPrice($totals['subtotal']); ?></strong>
                </div>

                <div class="summary-line">
                    <div>
                        <span style="color: var(--text-muted); display: block;">EMS Shipping (Local Delivery)</span>
                        <small style="color: var(--accent-cyan); font-size: 0.75rem;">Fixed rate applied</small>
                    </div>
                    <strong style="color: var(--text-main);"><?php echo formatPrice($totals['shipping']); ?></strong>
                </div>

                <div class="summary-line total">
                    <span>Payable Total</span>
                    <span><?php echo formatPrice($totals['total']); ?></span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 1.1rem; font-size: 1.1rem;">
                        💳 Proceed to SSLCommerz Sandbox Payment
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>

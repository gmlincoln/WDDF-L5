<?php
require_once __DIR__ . '/config.php';

$pendingOrder = $_SESSION['pending_order'] ?? null;

if (!$pendingOrder) {
    header("Location: cart.php");
    exit;
}

$errorMessage = '';

// Handle SSLCommerz Sandbox Payment Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_sandbox_payment') {
    $selectedChannel = trim($_POST['sandbox_channel'] ?? 'Visa / Mastercard Card');
    $trxId = 'SSLC-SBX-' . rand(100000, 999999);
    $orderNumber = $pendingOrder['order_number'];
    
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, payment_method, trx_id, subtotal, shipping_cost, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
            $stmt->execute([
                $orderNumber,
                $pendingOrder['name'],
                $pendingOrder['email'],
                $pendingOrder['phone'],
                $pendingOrder['address'],
                "SSLCommerz Sandbox ({$selectedChannel})",
                $trxId,
                $pendingOrder['subtotal'],
                $pendingOrder['shipping'],
                $pendingOrder['total']
            ]);
            $orderId = $db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($pendingOrder['cart_items'] as $item) {
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
        foreach ($pendingOrder['cart_items'] as $item) {
            reduceStock($item['product_id'], $item['quantity']);
        }
    }

    // Email Log
    $emailSubject = "Order Confirmation #{$orderNumber} - UrbanFit BD (SSLCommerz)";
    $emailBody = "Dear {$pendingOrder['name']},\n\nPayment of ৳" . number_format($pendingOrder['total'], 2) . " via SSLCommerz Sandbox ({$selectedChannel}) was successful!\nOrder #{$orderNumber} is confirmed.\nTrxID: {$trxId}\n\nBest regards,\nUrbanFit BD Team";
    logEmailNotification($orderNumber, $pendingOrder['email'], $emailSubject, $emailBody);

    // SMS Log
    $smsMsg = "UrbanFit BD: Payment ৳{$pendingOrder['total']} successful via SSLCommerz ({$selectedChannel}). Order #{$orderNumber}. TrxID: {$trxId}.";
    logSMSNotification($orderNumber, $pendingOrder['phone'], $smsMsg);

    // Set last order for receipt view
    $_SESSION['last_order'] = [
        'order_number' => $orderNumber,
        'name' => $pendingOrder['name'],
        'email' => $pendingOrder['email'],
        'phone' => $pendingOrder['phone'],
        'address' => $pendingOrder['address'],
        'payment_method' => "SSLCommerz Sandbox ({$selectedChannel})",
        'trx_id' => $trxId,
        'subtotal' => $pendingOrder['subtotal'],
        'shipping' => $pendingOrder['shipping'],
        'total' => $pendingOrder['total'],
        'cart_items' => $pendingOrder['cart_items']
    ];

    unset($_SESSION['pending_order']);
    clearCart();
    header("Location: order_success.php?order=" . urlencode($orderNumber));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSLCommerz Payment Gateway Demo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
        }

        body {
            background-color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* SSLCommerz Mobile Frame / Modal Container */
        .sslc-demo-frame {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Header Header Top Action Icons */
        .sslc-top-bar {
            padding: 1.25rem 1.5rem 0.75rem;
            text-align: center;
            background: #ffffff;
            position: relative;
        }

        .lang-picker {
            position: absolute;
            top: 1rem;
            right: 1.25rem;
            font-size: 0.8rem;
            color: #0084c7;
            font-weight: 700;
            cursor: pointer;
        }

        .merchant-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
        }

        .sslc-actions-row {
            display: flex;
            justify-content: center;
            gap: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.75rem;
            color: #0084c7;
            font-weight: 600;
            cursor: pointer;
            position: relative;
        }

        .action-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.3rem;
            position: relative;
            color: #475569;
        }

        .badge-count {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #0084c7;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tabs Bar */
        .sslc-tabs-bar {
            display: flex;
            background: #0084c7;
        }

        .sslc-nav-tab {
            flex: 1;
            padding: 0.9rem 0.5rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #ffffff;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: background 0.2s ease;
        }

        .sslc-nav-tab.active {
            background: #005b8a;
        }

        /* Main Form Body */
        .sslc-body {
            padding: 1.5rem;
            background: #ffffff;
            flex: 1;
        }

        .logos-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .brand-pill {
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .pill-visa { background: #1a1f71; color: #ffffff; }
        .pill-mc { background: #eb001b; color: #ffffff; }
        .pill-amex { background: #006fcf; color: #ffffff; }

        /* Form Inputs */
        .card-number-group {
            display: flex;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .card-input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #334155;
            outline: none;
        }

        .card-input:focus {
            border-color: #0084c7;
        }

        .card-number-group input {
            border: none;
            border-radius: 0;
        }

        .emi-btn {
            background: #e2e8f0;
            color: #64748b;
            border: none;
            border-left: 1px solid #cbd5e1;
            padding: 0 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .row-two-inputs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .cvc-wrap {
            position: relative;
            width: 100%;
        }

        .cvc-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            opacity: 0.6;
        }

        .checkbox-container {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            background: #fafafa;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.4rem;
        }

        .help-circle {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1px solid #0084c7;
            color: #0084c7;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .terms-text {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .terms-text a {
            color: #0084c7;
            text-decoration: none;
        }

        /* Sticky Pay Button Bar */
        .sslc-pay-bar {
            background: #cbd5e1;
            padding: 1rem;
            text-align: center;
        }

        .btn-pay-submit {
            width: 100%;
            background: linear-gradient(135deg, #0084c7, #006699);
            color: #ffffff;
            border: none;
            padding: 1rem;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 132, 199, 0.3);
            transition: all 0.2s ease;
        }

        .btn-pay-submit:hover {
            background: linear-gradient(135deg, #006699, #004b73);
        }

        /* Wallet Cards */
        .wallet-options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .wallet-card {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .wallet-card.selected {
            border-color: #0084c7;
            background: #f0f9ff;
        }
    </style>
</head>
<body>

    <form method="POST" action="sslcommerz_sandbox.php" class="sslc-demo-frame" id="sslc-form">
        <input type="hidden" name="action" value="process_sandbox_payment">
        <input type="hidden" name="sandbox_channel" id="sandbox-channel-input" value="Visa / Mastercard Card">

        <!-- Top Header Bar -->
        <div class="sslc-top-bar">
            <span class="lang-picker">🌐 EN</span>
            <div class="merchant-title"><?php echo STORE_NAME; ?></div>

            <div class="sslc-actions-row">
                <div class="action-item">
                    <div class="action-icon-circle">🎧</div>
                    <span>Support</span>
                </div>
                <div class="action-item">
                    <div class="action-icon-circle">❓</div>
                    <span>FAQ</span>
                </div>
                <div class="action-item">
                    <div class="action-icon-circle">
                        🎁
                        <span class="badge-count">3</span>
                    </div>
                    <span>Offers</span>
                </div>
                <div class="action-item">
                    <div class="action-icon-circle">🚪</div>
                    <span>Login</span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs Bar -->
        <div class="sslc-tabs-bar">
            <div class="sslc-nav-tab active" onclick="switchNavTab('cards', this)">CARDS</div>
            <div class="sslc-nav-tab" onclick="switchNavTab('mobile', this)">MOBILE BANKING</div>
            <div class="sslc-nav-tab" onclick="switchNavTab('net', this)">NET BANKING</div>
        </div>

        <!-- Form Body -->
        <div class="sslc-body">
            
            <!-- CARDS TAB PANEL -->
            <div id="panel-cards" class="tab-panel-box">
                <div class="logos-header">
                    <span class="brand-pill pill-visa">VISA</span>
                    <span class="brand-pill pill-mc">MC</span>
                    <span class="brand-pill pill-amex">AMEX</span>
                    <span style="font-size: 0.85rem; color: #0084c7; font-weight: 600; margin-left: 0.25rem;">Other Cards</span>
                </div>

                <!-- Card Number Input + EMI Dropdown -->
                <div class="card-number-group">
                    <input type="text" class="card-input" placeholder="Enter Card Number" value="4000 0000 0000 0002" required>
                    <button type="button" class="emi-btn">Avail EMI ▾</button>
                </div>

                <!-- MM/YY + CVC/CVV Row -->
                <div class="row-two-inputs">
                    <input type="text" class="card-input" placeholder="MM/YY" value="12/28" required style="flex: 1;">
                    <div class="cvc-wrap" style="flex: 1;">
                        <input type="password" class="card-input" placeholder="CVC/CVV" value="123" required>
                        <span class="cvc-icon">💳</span>
                    </div>
                </div>

                <!-- Card Holder Name -->
                <div style="margin-bottom: 1.25rem;">
                    <input type="text" class="card-input" placeholder="Card Holder Name" value="<?php echo htmlspecialchars($pendingOrder['name']); ?>" required>
                </div>

                <!-- Remember Checkbox Box -->
                <div class="checkbox-container">
                    <div class="checkbox-row">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" checked>
                            <span>Save card & remember me</span>
                        </label>
                        <span class="help-circle">?</span>
                    </div>
                    <div class="terms-text">
                        By checking this box you agree to the <a href="#">Terms of Service</a>
                    </div>
                </div>
            </div>

            <!-- MOBILE BANKING TAB PANEL -->
            <div id="panel-mobile" class="tab-panel-box" style="display: none;">
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">Select Mobile Banking Provider:</p>
                
                <div class="wallet-options-grid">
                    <div class="wallet-card selected" onclick="selectWallet(this, 'bKash Mobile Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">🌸</span>
                        <strong style="font-size: 0.9rem; color: #e2136e;">bKash</strong>
                    </div>

                    <div class="wallet-card" onclick="selectWallet(this, 'Nagad Mobile Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">🟠</span>
                        <strong style="font-size: 0.9rem; color: #f7931e;">Nagad</strong>
                    </div>

                    <div class="wallet-card" onclick="selectWallet(this, 'Rocket Mobile Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">🚀</span>
                        <strong style="font-size: 0.9rem; color: #8c3494;">Rocket</strong>
                    </div>

                    <div class="wallet-card" onclick="selectWallet(this, 'Upay Mobile Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">⚡</span>
                        <strong style="font-size: 0.9rem; color: #0084ff;">Upay</strong>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: #64748b; font-weight: 700;">Account Mobile Number</label>
                    <input type="text" class="card-input" value="<?php echo htmlspecialchars($pendingOrder['phone']); ?>">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.8rem; color: #64748b; font-weight: 700;">PIN / Verification Code</label>
                    <input type="password" class="card-input" value="12345">
                </div>
            </div>

            <!-- NET BANKING TAB PANEL -->
            <div id="panel-net" class="tab-panel-box" style="display: none;">
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">Select Net Banking Partner Bank:</p>

                <div class="wallet-options-grid">
                    <div class="wallet-card selected" onclick="selectWallet(this, 'DBBL Nexus Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">🏦</span>
                        <strong style="font-size: 0.9rem; color: #008853;">DBBL Nexus</strong>
                    </div>

                    <div class="wallet-card" onclick="selectWallet(this, 'City Touch Sandbox')">
                        <span style="font-size: 1.8rem; display: block;">🏦</span>
                        <strong style="font-size: 0.9rem; color: #004b87;">City Touch</strong>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.8rem; color: #64748b; font-weight: 700;">User ID / Account Number</label>
                    <input type="text" class="card-input" value="ACC-9988223311">
                </div>
            </div>

        </div>

        <!-- Bottom Sticky Pay Bar -->
        <div class="sslc-pay-bar">
            <button type="submit" class="btn-pay-submit">
                <span>👆</span> PAY <?php echo number_format($pendingOrder['total'], 0); ?> BDT
            </button>
            <div style="margin-top: 0.75rem;">
                <a href="cart.php" style="font-size: 0.8rem; color: #e11d48; font-weight: 600; text-decoration: none;">
                    Cancel & Return to Shopping Cart
                </a>
            </div>
        </div>
    </form>

    <script>
        function switchNavTab(tabName, el) {
            document.querySelectorAll('.sslc-nav-tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');

            document.querySelectorAll('.tab-panel-box').forEach(p => p.style.display = 'none');
            document.getElementById('panel-' + tabName).style.display = 'block';

            if (tabName === 'cards') {
                document.getElementById('sandbox-channel-input').value = 'Visa / Mastercard Card';
            }
        }

        function selectWallet(cardEl, channelName) {
            document.querySelectorAll('.wallet-card').forEach(c => c.classList.remove('selected'));
            cardEl.classList.add('selected');
            document.getElementById('sandbox-channel-input').value = channelName;
        }
    </script>
</body>
</html>

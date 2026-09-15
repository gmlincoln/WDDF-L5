<?php
require_once __DIR__ . '/header.php';

$isAdminLoggedIn = $_SESSION['admin_logged'] ?? false;
$loginError = '';

// Handle Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === 'admin' && $pass === 'admin123') {
        $_SESSION['admin_logged'] = true;
        $isAdminLoggedIn = true;
    } else {
        $loginError = 'Invalid admin username or password!';
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged']);
    header("Location: admin.php");
    exit;
}

// Handle Stock Update Action
if ($isAdminLoggedIn && isset($_POST['update_stock'])) {
    $pId = (int)$_POST['product_id'];
    $newStock = max(0, (int)$_POST['new_stock']);
    
    $db = getDB();
    if ($db) {
        $stmt = $db->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $pId]);
    }
    header("Location: admin.php?updated=1");
    exit;
}

$products = getAllProducts();

// Fetch DB Orders if available
$orders = [];
$smsLogs = $_SESSION['sms_logs'] ?? [];
$emailLogs = $_SESSION['email_logs'] ?? [];

$db = getDB();
if ($db) {
    try {
        $ordersStmt = $db->query("SELECT * FROM orders ORDER BY id DESC");
        $orders = $ordersStmt->fetchAll();

        $smsStmt = $db->query("SELECT * FROM sms_logs ORDER BY id DESC");
        $dbSms = $smsStmt->fetchAll();
        if (!empty($dbSms)) $smsLogs = $dbSms;

        $emailStmt = $db->query("SELECT * FROM email_logs ORDER BY id DESC");
        $dbEmail = $emailStmt->fetchAll();
        if (!empty($dbEmail)) $emailLogs = $dbEmail;
    } catch (Exception $e) {}
}
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800;">UrbanFit BD Admin Panel</h1>
        <p style="color: var(--text-muted);">Manage product inventory, track customer orders, and audit SMS/Email notification logs.</p>
    </div>
    <?php if ($isAdminLoggedIn): ?>
        <a href="admin.php?logout=1" class="btn-secondary" style="color: var(--accent-rose);">Logout Admin</a>
    <?php endif; ?>
</div>

<?php if (!$isAdminLoggedIn): ?>
    <!-- Admin Login Screen -->
    <div style="max-width: 450px; margin: 2rem auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔑</div>
            <h2 style="font-family: var(--font-heading); font-weight: 800; color: var(--text-main);">Admin Authentication</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Default Credentials: Username: <code>admin</code> | Password: <code>admin123</code></p>
        </div>

        <?php if ($loginError): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form method="POST" action="admin.php">
            <input type="hidden" name="admin_login" value="1">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="username" required class="form-control" placeholder="admin">
            </div>

            <div class="form-group">
                <label>Admin Password</label>
                <input type="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                Authenticate & Access Dashboard →
            </button>
        </form>
    </div>

<?php else: ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">✓ Product stock updated successfully!</div>
    <?php endif; ?>

    <!-- Product Stock Management Table -->
    <div class="admin-card">
        <h3 style="font-family: var(--font-heading); font-weight: 800; margin-bottom: 1rem; color: var(--text-main);">
            📦 Inventory & Stock Management (8 Products)
        </h3>
        
        <table class="cart-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>SKU</th>
                    <th>Current Stock</th>
                    <th>Stock Status</th>
                    <th>Modify Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($p['name']); ?></strong></td>
                        <td><span style="color: var(--accent-cyan); font-size: 0.85rem;"><?php echo htmlspecialchars($p['category'] ?? $p['category_name'] ?? ''); ?></span></td>
                        <td><?php echo formatPrice($p['price']); ?></td>
                        <td><code><?php echo htmlspecialchars($p['sku']); ?></code></td>
                        <td><strong style="font-size: 1.1rem; color: var(--text-main);"><?php echo $p['stock']; ?></strong></td>
                        <td>
                            <?php if ((int)$p['stock'] > 0): ?>
                                <span class="badge-stock in-stock" style="position: relative; top: 0; right: 0;">In Stock</span>
                            <?php else: ?>
                                <span class="badge-stock out-of-stock" style="position: relative; top: 0; right: 0;">Stock = 0 (Blocked)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="admin.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="update_stock" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number" name="new_stock" value="<?php echo $p['stock']; ?>" min="0" class="form-control" style="width: 70px; padding: 0.3rem 0.5rem; font-size: 0.85rem;">
                                <button type="submit" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Orders History -->
    <div class="admin-card">
        <h3 style="font-family: var(--font-heading); font-weight: 800; margin-bottom: 1rem; color: var(--text-main);">
            🛍️ Placed Customer Orders
        </h3>

        <?php if (empty($orders)): ?>
            <p style="color: var(--text-muted);">No orders recorded in database yet. Perform a checkout test to populate orders.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Payment Path</th>
                        <th>TrxID</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong style="color: var(--accent-emerald);"><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($o['customer_name']); ?><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($o['customer_email']); ?></small></td>
                            <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
                            <td><span style="color: var(--accent-amber); font-weight: 700;"><?php echo htmlspecialchars($o['payment_method']); ?></span></td>
                            <td><code><?php echo htmlspecialchars($o['trx_id'] ?: 'N/A (COD)'); ?></code></td>
                            <td><strong style="color: var(--text-main);"><?php echo formatPrice($o['total_amount']); ?></strong></td>
                            <td><span class="badge-stock in-stock" style="position: relative; top: 0; right: 0;"><?php echo htmlspecialchars($o['status']); ?></span></td>
                            <td><small style="color: var(--text-muted);"><?php echo htmlspecialchars($o['created_at']); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Notification Logs Audit Table -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-card">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--accent-emerald);">
                📩 Email Notification Audit Logs
            </h3>
            <?php if (empty($emailLogs)): ?>
                <p style="color: var(--text-muted); font-size: 0.85rem;">No email logs generated yet.</p>
            <?php else: ?>
                <div class="log-box" style="max-height: 200px;">
                    <?php foreach ($emailLogs as $log): ?>
                        [EMAIL SENT] Order #<?php echo htmlspecialchars($log['order_number'] ?? ''); ?> to <?php echo htmlspecialchars($log['recipient_email'] ?? ''); ?><br>
                        Subject: <?php echo htmlspecialchars($log['subject'] ?? ''); ?><br>
                        Time: <?php echo htmlspecialchars($log['sent_at'] ?? ''); ?><br>
                        ------------------------------------------------<br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--accent-cyan);">
                📱 SMS Notification Audit Logs
            </h3>
            <?php if (empty($smsLogs)): ?>
                <p style="color: var(--text-muted); font-size: 0.85rem;">No SMS logs generated yet.</p>
            <?php else: ?>
                <div class="log-box" style="max-height: 200px; color: #a7f3d0;">
                    <?php foreach ($smsLogs as $log): ?>
                        [SMS DISPATCHED] Order #<?php echo htmlspecialchars($log['order_number'] ?? ''); ?> to <?php echo htmlspecialchars($log['phone_number'] ?? ''); ?><br>
                        Message: <?php echo htmlspecialchars($log['message'] ?? ''); ?><br>
                        Time: <?php echo htmlspecialchars($log['sent_at'] ?? ''); ?><br>
                        ------------------------------------------------<br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>

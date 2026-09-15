<?php
/**
 * SSLCommerz Demo Payment Gateway Portal for UrbanFit BD
 * Visually matches the real SSLCommerz payment iframe UI
 */
require_once __DIR__ . '/wp-load.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_POST['wc_order_id']) ? intval($_POST['wc_order_id']) : 0);
$wc_order = $order_id ? wc_get_order($order_id) : null;

if (!$wc_order) {
    wp_redirect(get_permalink(wc_get_page_id('cart')) ?: site_url('/'));
    exit;
}

$customer_name  = $wc_order->get_formatted_billing_full_name();
$customer_phone = $wc_order->get_billing_phone();
$total_amount   = $wc_order->get_total();
$order_number   = $wc_order->get_order_number();

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_sandbox_payment') {
    $selectedChannel = trim($_POST['sandbox_channel'] ?? 'Visa/Mastercard');
    $trxId = 'SSLC-SBX-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $wc_order->update_meta_data('_sslcommerz_channel', $selectedChannel);
    $wc_order->update_meta_data('_sslcommerz_trxid', $trxId);
    $wc_order->update_status('processing', sprintf('Payment received via SSLCommerz Demo (%s). TrxID: %s', $selectedChannel, $trxId));
    wc_reduce_stock_levels($wc_order->get_id());

    if (function_exists('WC') && WC()->cart) {
        WC()->cart->empty_cart();
    }

    $timestamp = date('Y-m-d H:i:s');
    $smsMsg = sprintf("[%s] SMS SENT to %s | Order #%s | Total: %s BDT | SSLCommerz Demo (%s) | Status: processing\n",
        $timestamp, $customer_phone, $order_number, $total_amount, $selectedChannel);
    file_put_contents(ABSPATH . 'sms_log.txt', $smsMsg, FILE_APPEND);

    wp_redirect($wc_order->get_checkout_order_received_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSLCommerz Secure Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #1a2744;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .ssl-wrapper {
            width: 100%;
            max-width: 360px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        /* TOP BAR */
        .ssl-topbar {
            background: #fff;
            padding: 12px 16px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eaecef;
        }
        .ssl-logo { display: flex; align-items: center; gap: 7px; }
        .ssl-logo-icon {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, #0070f3, #00bcd4);
            border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 9px; font-weight: 800; letter-spacing: -0.3px;
        }
        .ssl-logo-text { font-size: 13px; font-weight: 700; color: #1a2744; }
        .ssl-logo-text span { color: #0070f3; }
        .ssl-lock { font-size: 18px; }
        /* MERCHANT */
        .ssl-merchant {
            text-align: center;
            padding: 12px 16px 8px;
            border-bottom: 1px solid #eaecef;
        }
        .ssl-merchant h2 { font-size: 18px; font-weight: 700; color: #1a2744; margin-bottom: 3px; }
        .ssl-merchant p { font-size: 12px; color: #6b7280; }
        /* ICONS ROW */
        .ssl-icons-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 14px;
            padding: 8px 16px;
            background: #fff;
            border-bottom: 1px solid #eaecef;
        }
        .ssl-icon-item { display: flex; flex-direction: column; align-items: center; gap: 2px; cursor: pointer; text-decoration: none; }
        .ssl-icon-circle {
            width: 28px; height: 28px;
            border: 1.5px solid #d1d5db;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #6b7280;
            position: relative;
        }
        .ssl-icon-circle .badge {
            position: absolute; top: -4px; right: -4px;
            background: #ef4444; color: #fff;
            border-radius: 50%; width: 13px; height: 13px;
            font-size: 7px; display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .ssl-icon-label { font-size: 9px; color: #6b7280; font-weight: 500; }
        /* TABS */
        .ssl-tabs { display: flex; background: #1a2744; }
        .ssl-tab {
            flex: 1; padding: 11px 4px;
            text-align: center; font-size: 10px; font-weight: 700;
            color: rgba(255,255,255,0.55); cursor: pointer;
            letter-spacing: 0.3px; text-transform: uppercase;
            border-bottom: 3px solid transparent;
            transition: all 0.2s; user-select: none;
        }
        .ssl-tab.active { color: #fff; border-bottom-color: #0070f3; background: rgba(0,112,243,0.12); }
        .ssl-tab:hover:not(.active) { color: rgba(255,255,255,0.85); background: rgba(255,255,255,0.05); }
        /* BODY */
        .ssl-body { padding: 16px; background: #fff; flex: 1; }
        .panel { display: none; }
        .panel.active { display: block; }
        /* Card brands */
        .card-brands { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
        .brand-logo { height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 3px; font-size: 9px; font-weight: 800; padding: 0 6px; }
        .brand-visa { background: #1a1f71; color: #fff; font-style: italic; font-size: 12px; }
        .mc-circles { display: flex; }
        .mc-c { width: 18px; height: 18px; border-radius: 50%; opacity: 0.9; }
        .mc-c.red { background: #eb001b; }
        .mc-c.orange { background: #f79e1b; margin-left: -7px; }
        .brand-amex { background: #006fcf; color: #fff; font-size: 8px; }
        .brand-other { color: #0070f3; font-size: 11px; font-weight: 600; margin-left: 2px; cursor: pointer; }
        /* Card input with EMI */
        .input-emi {
            display: flex; border: 1px solid #d1d5db;
            border-radius: 5px; overflow: hidden; margin-bottom: 10px;
        }
        .input-emi input {
            flex: 1; border: none; padding: 11px 12px;
            font-size: 14px; color: #374151; outline: none; font-family: inherit;
        }
        .emi-btn {
            background: #f3f4f6; border: none; border-left: 1px solid #d1d5db;
            padding: 0 10px; font-size: 10px; font-weight: 600; color: #374151;
            cursor: pointer; white-space: nowrap;
        }
        .emi-btn:hover { background: #e5e7eb; }
        .ssl-input {
            width: 100%; border: 1px solid #d1d5db; border-radius: 5px;
            padding: 11px 12px; font-size: 14px; color: #374151; outline: none;
            font-family: inherit; transition: border-color 0.2s; background: #fff;
        }
        .ssl-input:focus { border-color: #0070f3; box-shadow: 0 0 0 3px rgba(0,112,243,0.1); }
        .ssl-input::placeholder { color: #9ca3af; }
        .two-col { display: flex; gap: 10px; margin-bottom: 10px; }
        .two-col .ssl-input { flex: 1; }
        .cvv-wrap { position: relative; flex: 1; }
        .cvv-wrap .ssl-input { padding-right: 34px; }
        .cvv-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }
        .save-row {
            display: flex; align-items: flex-start; gap: 8px;
            margin-top: 10px; padding: 10px;
            background: #f9fafb; border-radius: 5px; border: 1px solid #e5e7eb;
        }
        .save-row input[type='checkbox'] { width: 14px; height: 14px; margin-top: 2px; accent-color: #0070f3; flex-shrink: 0; }
        .save-text { font-size: 10px; color: #6b7280; line-height: 1.4; }
        .save-text a { color: #0070f3; text-decoration: none; }
        .help-icon {
            margin-left: auto; width: 17px; height: 17px; border-radius: 50%;
            border: 1.5px solid #9ca3af; display: flex; align-items: center; justify-content: center;
            font-size: 9px; color: #9ca3af; cursor: pointer; flex-shrink: 0;
        }
        /* Wallets */
        .wallet-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px; }
        .wallet-item {
            border: 2px solid #e5e7eb; border-radius: 7px; padding: 10px 4px;
            text-align: center; cursor: pointer; transition: all 0.18s;
        }
        .wallet-item:hover { border-color: #0070f3; background: #f0f7ff; }
        .wallet-item.selected { border-color: #0070f3; background: #eff6ff; }
        .w-emoji { font-size: 22px; display: block; margin-bottom: 3px; }
        .w-name { font-size: 9px; font-weight: 700; }
        .w-bkash { color: #e2136e; } .w-nagad { color: #f7931e; } .w-rocket { color: #8b3cf7; }
        .w-upay { color: #0070f3; } .w-tap { color: #22c55e; } .w-mcash { color: #dc2626; }
        .field-label { font-size: 10px; font-weight: 600; color: #374151; margin-bottom: 5px; }
        /* Banks */
        .bank-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px; }
        .bank-item {
            border: 2px solid #e5e7eb; border-radius: 7px; padding: 10px 4px;
            text-align: center; cursor: pointer; transition: all 0.18s;
        }
        .bank-item:hover { border-color: #0070f3; background: #f0f7ff; }
        .bank-item.selected { border-color: #0070f3; background: #eff6ff; }
        .b-emoji { font-size: 20px; display: block; margin-bottom: 3px; }
        .b-name { font-size: 9px; font-weight: 700; color: #374151; }
        /* PAY BAR */
        .ssl-pay-bar { background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .btn-pay {
            width: 100%; background: linear-gradient(90deg, #1a2744 0%, #0070f3 100%);
            color: #fff; border: none; padding: 16px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            letter-spacing: 0.4px; display: flex; align-items: center;
            justify-content: center; gap: 8px; transition: opacity 0.2s;
            font-family: inherit;
        }
        .btn-pay:hover { opacity: 0.92; }
        .btn-pay:active { transform: scale(0.99); }
        .ssl-footer {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 7px 14px;
        }
        .ssl-cancel { font-size: 11px; color: #ef4444; font-weight: 600; text-decoration: none; }
        .ssl-cancel:hover { text-decoration: underline; }
        .ssl-powered { font-size: 9px; color: #9ca3af; text-align: right; }
        .ssl-powered strong { color: #1a2744; }
        /* Loading */
        .ssl-loading {
            display: none; position: fixed; inset: 0;
            background: rgba(26,39,68,0.88);
            align-items: center; justify-content: center;
            z-index: 9999; flex-direction: column; gap: 14px;
        }
        .ssl-loading.show { display: flex; }
        .spinner {
            width: 44px; height: 44px;
            border: 4px solid rgba(255,255,255,0.2);
            border-top-color: #0070f3; border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { color: #fff; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="ssl-loading" id="ssl-loading">
        <div class="spinner"></div>
        <div class="loading-text">Processing Secure Payment&#8230;</div>
    </div>

    <form method="POST" action="sslcommerz_sandbox.php" class="ssl-wrapper" id="ssl-form" onsubmit="handleSubmit(event)">
        <input type="hidden" name="action" value="process_sandbox_payment">
        <input type="hidden" name="wc_order_id" value="<?php echo esc_attr($order_id); ?>">
        <input type="hidden" name="sandbox_channel" id="sandbox_channel" value="Visa/Mastercard">

        <!-- TOP BAR -->
        <div class="ssl-topbar">
            <div class="ssl-logo">
                <div class="ssl-logo-icon">SSL</div>
                <div class="ssl-logo-text">SSL<span>Commerz</span></div>
            </div>
            <div class="ssl-lock">&#128274;</div>
        </div>

        <!-- MERCHANT -->
        <div class="ssl-merchant">
            <h2>Demo</h2>
            <p>Order #<?php echo esc_html($order_number); ?> &nbsp;&bull;&nbsp; <strong>&#2547;<?php echo number_format((float)$total_amount, 0); ?> BDT</strong></p>
        </div>

        <!-- SUPPORT ICONS -->
        <div class="ssl-icons-row">
            <a href="#" class="ssl-icon-item" onclick="return false">
                <div class="ssl-icon-circle">&#127911;</div>
                <span class="ssl-icon-label">Support</span>
            </a>
            <a href="#" class="ssl-icon-item" onclick="return false">
                <div class="ssl-icon-circle">?</div>
                <span class="ssl-icon-label">FAQ</span>
            </a>
            <a href="#" class="ssl-icon-item" onclick="return false">
                <div class="ssl-icon-circle">&#127873;<span class="badge">3</span></div>
                <span class="ssl-icon-label">Offers</span>
            </a>
            <a href="#" class="ssl-icon-item" onclick="return false">
                <div class="ssl-icon-circle">&#128100;</div>
                <span class="ssl-icon-label">Login</span>
            </a>
        </div>

        <!-- TABS -->
        <div class="ssl-tabs">
            <div class="ssl-tab active" onclick="switchTab('cards',this)">CARDS</div>
            <div class="ssl-tab" onclick="switchTab('mobile',this)">MOBILE BANKING</div>
            <div class="ssl-tab" onclick="switchTab('net',this)">NET BANKING</div>
        </div>

        <!-- BODY -->
        <div class="ssl-body">

            <!-- CARDS -->
            <div id="panel-cards" class="panel active">
                <div class="card-brands">
                    <div class="brand-logo brand-visa">VISA</div>
                    <div class="mc-circles"><div class="mc-c red"></div><div class="mc-c orange"></div></div>
                    <div class="brand-logo brand-amex">AMEX</div>
                    <span class="brand-other">Other Cards</span>
                </div>
                <div class="input-emi">
                    <input type="text" placeholder="Enter Card Number" value="4000 0000 0000 0002" maxlength="19" oninput="fmtCard(this)">
                    <button type="button" class="emi-btn">Avail EMI &#9660;</button>
                </div>
                <div class="two-col">
                    <input type="text" class="ssl-input" placeholder="MM/YY" value="12/28" maxlength="5" id="card-exp">
                    <div class="cvv-wrap">
                        <input type="password" class="ssl-input" placeholder="CVC/CVV" value="123" maxlength="4">
                        <span class="cvv-icon">&#128179;</span>
                    </div>
                </div>
                <input type="text" class="ssl-input" placeholder="Card Holder Name" value="<?php echo htmlspecialchars($customer_name); ?>" style="margin-bottom:10px">
                <div class="save-row">
                    <input type="checkbox" id="save-card">
                    <label class="save-text" for="save-card">Save card &amp; remember me<br>
                        <small>By checking this box you agree to the <a href="#">Terms of Service</a></small></label>
                    <div class="help-icon">?</div>
                </div>
            </div>

            <!-- MOBILE BANKING -->
            <div id="panel-mobile" class="panel">
                <div class="wallet-grid">
                    <div class="wallet-item selected" onclick="selWallet(this,'bKash')"><span class="w-emoji">&#128151;</span><span class="w-name w-bkash">bKash</span></div>
                    <div class="wallet-item" onclick="selWallet(this,'Nagad')"><span class="w-emoji">&#128992;</span><span class="w-name w-nagad">Nagad</span></div>
                    <div class="wallet-item" onclick="selWallet(this,'Rocket')"><span class="w-emoji">&#128640;</span><span class="w-name w-rocket">Rocket</span></div>
                    <div class="wallet-item" onclick="selWallet(this,'Upay')"><span class="w-emoji">&#9889;</span><span class="w-name w-upay">Upay</span></div>
                    <div class="wallet-item" onclick="selWallet(this,'TapnPay')"><span class="w-emoji">&#128241;</span><span class="w-name w-tap">TapnPay</span></div>
                    <div class="wallet-item" onclick="selWallet(this,'mCash')"><span class="w-emoji">&#128181;</span><span class="w-name w-mcash">mCash</span></div>
                </div>
                <div class="field-label">Mobile Number (linked to wallet)</div>
                <input type="text" class="ssl-input" value="<?php echo htmlspecialchars($customer_phone); ?>" placeholder="01XXXXXXXXX" maxlength="11">
            </div>

            <!-- NET BANKING -->
            <div id="panel-net" class="panel">
                <div class="bank-grid">
                    <div class="bank-item selected" onclick="selBank(this,'DBBL Nexus')"><span class="b-emoji">&#127981;</span><span class="b-name">DBBL Nexus</span></div>
                    <div class="bank-item" onclick="selBank(this,'City Touch')"><span class="b-emoji">&#127981;</span><span class="b-name">City Touch</span></div>
                    <div class="bank-item" onclick="selBank(this,'Islami Bank')"><span class="b-emoji">&#128332;</span><span class="b-name">Islami Bank</span></div>
                    <div class="bank-item" onclick="selBank(this,'BRAC Bank')"><span class="b-emoji">&#127981;</span><span class="b-name">BRAC Bank</span></div>
                    <div class="bank-item" onclick="selBank(this,'Mutual Trust')"><span class="b-emoji">&#127981;</span><span class="b-name">Mutual Trust</span></div>
                    <div class="bank-item" onclick="selBank(this,'AB Bank')"><span class="b-emoji">&#127981;</span><span class="b-name">AB Bank</span></div>
                </div>
                <p style="font-size:11px;color:#6b7280;text-align:center">You will be redirected to your bank&#8217;s portal to complete payment.</p>
            </div>

        </div>

        <!-- PAY BAR -->
        <div class="ssl-pay-bar">
            <button type="submit" class="btn-pay">
                &#128274; &nbsp;PAY &#2547;<?php echo number_format((float)$total_amount, 0); ?> BDT
            </button>
            <div class="ssl-footer">
                <a href="<?php echo esc_url(get_permalink(wc_get_page_id('cart'))); ?>" class="ssl-cancel">&#10005; Cancel</a>
                <div class="ssl-powered">Secured by <strong>SSLCommerz</strong><br>Sandbox Demo</div>
            </div>
        </div>
    </form>

    <script>
        function switchTab(n,el) {
            document.querySelectorAll('.ssl-tab').forEach(t=>t.classList.remove('active'));
            document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('panel-'+n).classList.add('active');
            var d={cards:'Visa/Mastercard',mobile:'bKash',net:'DBBL Nexus'};
            document.getElementById('sandbox_channel').value=d[n]||'Visa/Mastercard';
        }
        function selWallet(el,ch) {
            el.closest('.wallet-grid').querySelectorAll('.wallet-item').forEach(w=>w.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('sandbox_channel').value=ch;
        }
        function selBank(el,ch) {
            el.closest('.bank-grid').querySelectorAll('.bank-item').forEach(b=>b.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('sandbox_channel').value=ch;
        }
        function fmtCard(inp) {
            var v=inp.value.replace(/\s/g,'').replace(/\D/g,'');
            var p=[];
            for(var i=0;i<v.length;i+=4) p.push(v.substring(i,i+4));
            inp.value=p.join(' ');
        }
        document.getElementById('card-exp').addEventListener('input',function(){
            var v=this.value.replace(/\D/g,'');
            if(v.length>=2) v=v.substring(0,2)+'/'+v.substring(2);
            this.value=v.substring(0,5);
        });
        function handleSubmit(e) {
            e.preventDefault();
            document.getElementById('ssl-loading').classList.add('show');
            setTimeout(function(){ document.getElementById('ssl-form').submit(); }, 1800);
        }
    </script>
</body>
</html>

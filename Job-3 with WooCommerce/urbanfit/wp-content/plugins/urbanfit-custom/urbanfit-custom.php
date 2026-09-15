<?php
/**
 * Plugin Name: UrbanFit BD Store Enhancements
 * Description: Custom WooCommerce extensions for UrbanFit BD: SSLCommerz Online Payment Gateway, Mobile Banking gateway, EMS Shipping method, SMS order notification logger, mandatory size validation, and storefront design enhancements.
 * Version: 1.2.0
 * Author: Md. Golam Maula
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Force WooCommerce Options on Plugin Activation
add_action('init', function () {
    update_option('woocommerce_currency', 'BDT');
    update_option('woocommerce_currency_pos', 'left_space');
    update_option('woocommerce_force_ssl_checkout', 'no');
    update_option('woocommerce_coming_soon', 'no');
});

// 2. Out of Stock Add-To-Cart Guard
add_filter('woocommerce_add_to_cart_validation', 'urbanfit_validate_stock_before_cart', 10, 3);
function urbanfit_validate_stock_before_cart($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    if ($product && (!$product->is_in_stock() || $product->get_stock_quantity() <= 0)) {
        wc_add_notice(__('You cannot add this item to cart because it is out of stock.', 'urbanfit'), 'error');
        return false;
    }
    return $passed;
}

// 3. Register Custom Shipping Method: EMS (Local Delivery)
add_action('woocommerce_shipping_init', 'urbanfit_ems_shipping_init');
function urbanfit_ems_shipping_init() {
    if (!class_exists('WC_Shipping_EMS')) {
        class WC_Shipping_EMS extends WC_Shipping_Method {
            public function __construct() {
                $this->id                 = 'ems_shipping';
                $this->method_title       = __('EMS (Local Delivery)', 'urbanfit');
                $this->method_description = __('Express Mail Service local shipping method for Bangladesh.', 'urbanfit');
                $this->enabled            = "yes";
                $this->title              = "EMS (Local Delivery)";
                $this->init();
            }

            public function init() {
                $this->init_form_fields();
                $this->init_settings();
                $this->title = $this->get_option('title', 'EMS (Local Delivery)');
            }

            public function calculate_shipping($package = array()) {
                $rate = array(
                    'id'       => $this->id,
                    'label'    => $this->title,
                    'cost'     => 100, // 100 BDT Flat Rate
                    'calc_tax' => 'per_item'
                );
                $this->add_rate($rate);
            }
        }
    }
}

add_filter('woocommerce_shipping_methods', 'urbanfit_add_ems_shipping');
function urbanfit_add_ems_shipping($methods) {
    $methods['ems_shipping'] = 'WC_Shipping_EMS';
    return $methods;
}

// 4. Register Custom Payment Gateways (Mobile Banking & SSLCommerz Sandbox)
add_action('plugins_loaded', 'urbanfit_payment_gateways_init', 11);
function urbanfit_payment_gateways_init() {
    if (!class_exists('WC_Payment_Gateway')) return;

    // Mobile Banking Gateway (bKash / Nagad)
    class WC_Gateway_Mobile_Banking extends WC_Payment_Gateway {
        public function __construct() {
            $this->id                 = 'mobile_banking';
            $this->icon               = ''; 
            $this->has_fields         = true;
            $this->method_title       = __('Mobile Banking (bKash / Nagad)', 'urbanfit');
            $this->method_description = __('Accept mobile banking payments with Transaction ID verification.', 'urbanfit');

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = __('Mobile Banking (bKash / Nagad)', 'urbanfit');
            $this->description = __('Send payment to Merchant Personal No: 01700000000. Enter your Mobile Number and Transaction ID below.', 'urbanfit');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable Mobile Banking Payment Gateway',
                    'default' => 'yes'
                )
            );
        }

        public function payment_fields() {
            echo '<fieldset id="wc-' . esc_attr($this->id) . '-form" class="wc-credit-card-form wc-payment-form" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">';
            echo '<p style="font-size: 13px; color: #475569; margin-bottom: 10px;">' . esc_html($this->description) . '</p>';
            echo '<p class="form-row form-row-first">
                    <label style="font-weight: 600; font-size: 13px;">Sender Mobile Number <span class="required">*</span></label>
                    <input type="text" class="input-text" name="mobile_banking_number" placeholder="e.g. 017XXXXXXXX" required style="border-radius: 6px;" />
                  </p>';
            echo '<p class="form-row form-row-last">
                    <label style="font-weight: 600; font-size: 13px;">Transaction ID (TrxID) <span class="required">*</span></label>
                    <input type="text" class="input-text" name="mobile_banking_trxid" placeholder="e.g. TRX88776655" required style="border-radius: 6px;" />
                  </p>';
            echo '<div class="clear"></div></fieldset>';
        }

        public function validate_fields() {
            if (empty($_POST['mobile_banking_number'])) {
                wc_add_notice(__('Please enter your Mobile Banking sender number.', 'urbanfit'), 'error');
                return false;
            }
            if (empty($_POST['mobile_banking_trxid'])) {
                wc_add_notice(__('Please enter your Transaction ID (TrxID).', 'urbanfit'), 'error');
                return false;
            }
            return true;
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            if (isset($_POST['mobile_banking_number'])) {
                $order->update_meta_data('_mobile_banking_number', sanitize_text_field($_POST['mobile_banking_number']));
            }
            if (isset($_POST['mobile_banking_trxid'])) {
                $order->update_meta_data('_mobile_banking_trxid', sanitize_text_field($_POST['mobile_banking_trxid']));
            }

            $order->update_status('processing', __('Payment received via Mobile Banking. TrxID: ' . sanitize_text_field($_POST['mobile_banking_trxid']), 'urbanfit'));
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }
    }

    // SSLCommerz Official Payment Gateway (calls real SSLCommerz API)
    class WC_Gateway_SSLCommerz extends WC_Payment_Gateway {

        // SSLCommerz Sandbox Credentials (replace with live credentials for production)
        const STORE_ID       = 'testbox';
        const STORE_PASSWORD = 'qwerty';
        const SANDBOX_URL    = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
        const IS_SANDBOX     = true;

        public function __construct() {
            $this->id                 = 'sslcommerz';
            $this->icon               = 'https://securepay.sslcommerz.com/public/image/SSLCommerz-Pay-With-logo-All-Size-03.png';
            $this->has_fields         = false;
            $this->method_title       = __('SSLCommerz Online Payment', 'urbanfit');
            $this->method_description = __('Accept Visa, Mastercard, AMEX, bKash, Nagad, Rocket & Net Banking via official SSLCommerz gateway.', 'urbanfit');

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->get_option('title', __('SSLCommerz — Cards / bKash / Nagad / Rocket / Net Banking', 'urbanfit'));
            $this->description = $this->get_option('description', __('Pay securely via SSLCommerz. You will be redirected to the official SSLCommerz payment page.', 'urbanfit'));

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // IPN / callback listeners
            add_action('woocommerce_api_wc_gateway_sslcommerz', array($this, 'handle_ipn'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable SSLCommerz Payment Gateway',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'   => 'Title',
                    'type'    => 'text',
                    'default' => 'SSLCommerz — Cards / bKash / Nagad / Rocket / Net Banking',
                ),
                'description' => array(
                    'title'   => 'Description',
                    'type'    => 'textarea',
                    'default' => 'Pay securely via SSLCommerz. You will be redirected to the official SSLCommerz payment page.',
                ),
            );
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            // Build SSLCommerz POST parameters
            $post_data = array(
                // Store credentials
                'store_id'       => self::STORE_ID,
                'store_passwd'   => self::STORE_PASSWORD,

                // Transaction info
                'total_amount'   => $order->get_total(),
                'currency'       => 'BDT',
                'tran_id'        => 'URBANFIT-' . $order_id . '-' . time(),

                // Return / notify URLs
                'success_url'    => WC()->api_request_url('WC_Gateway_SSLCommerz') . '?order_id=' . $order_id . '&status=success',
                'fail_url'       => WC()->api_request_url('WC_Gateway_SSLCommerz') . '?order_id=' . $order_id . '&status=fail',
                'cancel_url'     => WC()->api_request_url('WC_Gateway_SSLCommerz') . '?order_id=' . $order_id . '&status=cancel',
                'ipn_url'        => WC()->api_request_url('WC_Gateway_SSLCommerz') . '?order_id=' . $order_id . '&status=ipn',

                // Customer info
                'cus_name'       => $order->get_formatted_billing_full_name(),
                'cus_email'      => $order->get_billing_email(),
                'cus_add1'       => $order->get_billing_address_1(),
                'cus_add2'       => $order->get_billing_address_2(),
                'cus_city'       => $order->get_billing_city(),
                'cus_state'      => $order->get_billing_state(),
                'cus_postcode'   => $order->get_billing_postcode(),
                'cus_country'    => $order->get_billing_country() ?: 'Bangladesh',
                'cus_phone'      => $order->get_billing_phone(),
                'cus_fax'        => $order->get_billing_phone(),

                // Shipping info (use billing as fallback)
                'ship_name'      => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ?: $order->get_formatted_billing_full_name(),
                'ship_add1'      => $order->get_shipping_address_1() ?: $order->get_billing_address_1(),
                'ship_add2'      => $order->get_shipping_address_2() ?: $order->get_billing_address_2(),
                'ship_city'      => $order->get_shipping_city() ?: $order->get_billing_city(),
                'ship_state'     => $order->get_shipping_state() ?: $order->get_billing_state(),
                'ship_postcode'  => $order->get_shipping_postcode() ?: $order->get_billing_postcode(),
                'ship_country'   => $order->get_shipping_country() ?: 'Bangladesh',

                // Product info
                'product_name'   => 'UrbanFit BD Order #' . $order->get_order_number(),
                'product_category' => 'Fashion / Apparel',
                'product_profile'  => 'general',

                // API version
                'version'        => 'SSLCZV3X1',
            );

            $order->update_status('pending', __('Awaiting SSLCommerz payment.', 'urbanfit'));

            // Call SSLCommerz API
            $response = wp_remote_post(self::SANDBOX_URL, array(
                'body'    => $post_data,
                'timeout' => 30,
                'sslverify' => false, // needed for localhost / sandbox HTTP
            ));

            if (is_wp_error($response)) {
                wc_add_notice(__('SSLCommerz connection error: ' . $response->get_error_message(), 'urbanfit'), 'error');
                return array('result' => 'fail');
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (empty($data) || $data['status'] !== 'SUCCESS' || empty($data['GatewayPageURL'])) {
                $msg = isset($data['failedreason']) ? $data['failedreason'] : 'Unknown SSLCommerz error.';
                wc_add_notice(__('SSLCommerz Error: ' . $msg, 'urbanfit'), 'error');
                return array('result' => 'fail');
            }

            // Redirect to real SSLCommerz hosted payment page
            return array(
                'result'   => 'success',
                'redirect' => $data['GatewayPageURL'],
            );
        }

        // Handle success / fail / cancel / IPN callbacks from SSLCommerz
        public function handle_ipn() {
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
            $status   = isset($_GET['status'])   ? sanitize_text_field($_GET['status']) : '';
            $order    = $order_id ? wc_get_order($order_id) : null;

            if (!$order) {
                wp_redirect(wc_get_page_url('shop'));
                exit;
            }

            if ($status === 'success') {
                $val_id  = sanitize_text_field($_POST['val_id'] ?? $_GET['val_id'] ?? '');
                $tran_id = sanitize_text_field($_POST['tran_id'] ?? '');

                // Mark order as processing
                $order->payment_complete($tran_id);
                $order->update_meta_data('_sslcommerz_val_id',  $val_id);
                $order->update_meta_data('_sslcommerz_tran_id', $tran_id);
                $order->add_order_note(sprintf(__('SSLCommerz payment SUCCESS. TranID: %s | ValID: %s', 'urbanfit'), $tran_id, $val_id));
                $order->save();

                if (function_exists('WC') && WC()->cart) {
                    WC()->cart->empty_cart();
                }

                // SMS log
                $smsMsg = sprintf("[%s] Order #%s | SSLCommerz SUCCESS | TranID: %s | Amount: %s BDT\n",
                    date('Y-m-d H:i:s'), $order->get_order_number(), $tran_id, $order->get_total());
                file_put_contents(ABSPATH . 'sms_log.txt', $smsMsg, FILE_APPEND);

                wp_redirect($order->get_checkout_order_received_url());
                exit;

            } elseif ($status === 'fail') {
                $order->update_status('failed', __('SSLCommerz payment failed.', 'urbanfit'));
                wc_add_notice(__('Payment failed via SSLCommerz. Please try again.', 'urbanfit'), 'error');
                wp_redirect(wc_get_checkout_url());
                exit;

            } elseif ($status === 'cancel') {
                $order->update_status('cancelled', __('Customer cancelled SSLCommerz payment.', 'urbanfit'));
                wc_add_notice(__('Payment cancelled. Your order has been cancelled.', 'urbanfit'), 'notice');
                wp_redirect(wc_get_checkout_url());
                exit;

            } elseif ($status === 'ipn') {
                // Background IPN — just mark paid if not already
                if (!$order->is_paid()) {
                    $tran_id = sanitize_text_field($_POST['tran_id'] ?? '');
                    $order->payment_complete($tran_id);
                    $order->save();
                }
                http_response_code(200);
                exit('OK');
            }

            wp_redirect(wc_get_page_url('shop'));
            exit;
        }
    }
}

add_filter('woocommerce_payment_gateways', 'urbanfit_add_all_payment_gateways');
function urbanfit_add_all_payment_gateways($gateways) {
    $gateways[] = 'WC_Gateway_SSLCommerz';
    return $gateways;
}

// Make SSLCommerz default selected payment gateway on Checkout
add_filter('woocommerce_chosen_payment_method', 'urbanfit_default_to_sslcommerz');
function urbanfit_default_to_sslcommerz($default) {
    return 'sslcommerz';
}

// 4. Force Product Size Variation Dropdown Order: S, M, L, XL
add_filter('woocommerce_dropdown_variation_attribute_options_args', 'urbanfit_sort_size_attribute_args');
function urbanfit_sort_size_attribute_args($args) {
    if (isset($args['attribute']) && stristr($args['attribute'], 'size')) {
        $args['orderby'] = 'menu_order';
        $args['order']   = 'ASC';
    }
    return $args;
}

add_filter('woocommerce_dropdown_variation_attribute_options_html', 'urbanfit_sort_size_dropdown_html', 10, 2);
function urbanfit_sort_size_dropdown_html($html, $args) {
    if (!isset($args['attribute']) || !stristr($args['attribute'], 'size')) {
        return $html;
    }

    $desired_order = array('s', 'm', 'l', 'xl', 'one-size');

    if (preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>(.*?)<\/option>/i', $html, $matches, PREG_SET_ORDER)) {
        $placeholder = '';
        $options     = array();

        foreach ($matches as $match) {
            $val  = $match[1];
            $text = $match[2];
            if ($val === '') {
                $placeholder = $match[0];
            } else {
                $slug = strtolower(trim($val));
                $pos  = array_search($slug, $desired_order);
                if ($pos === false) $pos = 999;
                $options[] = array('pos' => $pos, 'html' => $match[0]);
            }
        }

        usort($options, function($a, $b) {
            return $a['pos'] - $b['pos'];
        });

        $select_name = esc_attr($args['attribute'] ?? 'attribute_pa_size');
        $id          = esc_attr($args['id'] ?? $args['attribute']);
        $class       = esc_attr($args['class'] ?? '');

        $new_html  = '<select id="' . $id . '" class="' . $class . '" name="' . $select_name . '" data-attribute_name="attribute_' . esc_attr($args['attribute']) . '" data-show_option_none="yes">';
        if ($placeholder) {
            $new_html .= $placeholder;
        } else {
            $new_html .= '<option value="">Choose an option</option>';
        }
        foreach ($options as $opt) {
            $new_html .= $opt['html'];
        }
        $new_html .= '</select>';
        return $new_html;
    }

    return $html;
}

// 5. Order SMS Notification Logger
add_action('woocommerce_thankyou', 'urbanfit_log_sms_notification', 10, 1);
function urbanfit_log_sms_notification($order_id) {
    if (!$order_id) return;
    $order = wc_get_order($order_id);
    if (!$order) return;

    if ($order->get_meta('_sms_logged')) return;

    $phone = $order->get_billing_phone();
    $total = $order->get_total();
    $payment_method = $order->get_payment_method_title();
    $timestamp = date('Y-m-d H:i:s');

    $sms_message = sprintf(
        "[%s] SMS SENT to %s | Order #%d Confirmed | Total: %s BDT | Payment: %s | Status: %s",
        $timestamp,
        $phone,
        $order_id,
        $total,
        $payment_method,
        $order->get_status()
    );

    $log_path = ABSPATH . 'sms_log.txt';
    file_put_contents($log_path, $sms_message . PHP_EOL, FILE_APPEND);

    $order->update_meta_data('_sms_logged', 'yes');
    $order->save();
}

// 6. Display Mobile Banking & SSLCommerz Details on Thank You Page
add_action('woocommerce_thankyou', 'urbanfit_display_payment_meta_details', 20);
function urbanfit_display_payment_meta_details($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $mb_number = $order->get_meta('_mobile_banking_number');
    $mb_trxid  = $order->get_meta('_mobile_banking_trxid');

    $sslc_channel = $order->get_meta('_sslcommerz_channel');
    $sslc_trxid   = $order->get_meta('_sslcommerz_trxid');

    if ($mb_number && $mb_trxid) {
        echo '<section class="woocommerce-order-details" style="background: #f8fafc; padding: 18px; margin-top: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">';
        echo '<h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">' . __('Mobile Banking Payment Details', 'urbanfit') . '</h3>';
        echo '<p style="margin: 4px 0;"><strong>Sender Phone:</strong> ' . esc_html($mb_number) . '</p>';
        echo '<p style="margin: 4px 0;"><strong>Transaction ID (TrxID):</strong> <span style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-family: monospace;">' . esc_html($mb_trxid) . '</span></p>';
        echo '</section>';
    }

    if ($sslc_trxid) {
        echo '<section class="woocommerce-order-details" style="background: #f0f9ff; padding: 18px; margin-top: 20px; border-radius: 8px; border: 1px solid #bae6fd;">';
        echo '<h3 style="font-size: 16px; font-weight: 700; color: #0369a1;">' . __('SSLCommerz Payment Confirmation', 'urbanfit') . '</h3>';
        echo '<p style="margin: 4px 0;"><strong>Payment Gateway:</strong> SSLCommerz Sandbox (' . esc_html($sslc_channel) . ')</p>';
        echo '<p style="margin: 4px 0;"><strong>Transaction ID:</strong> <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-weight: 700;">' . esc_html($sslc_trxid) . '</span></p>';
        echo '</section>';
    }
}

// 7. User Account Nav — placed in parallel alignment with search box in header
add_action('wp_footer', 'urbanfit_navbar_account_inject');
add_action('wp_head',   'urbanfit_navbar_account_styles');

function urbanfit_navbar_account_inject() {
    $account_url  = wc_get_page_permalink('myaccount');
    $is_logged_in = is_user_logged_in();

    ob_start();
    if ($is_logged_in) {
        $user         = wp_get_current_user();
        $display_name = esc_html($user->display_name);
        $avatar       = get_avatar($user->ID, 30, '', '', array('class' => 'urbanfit-avatar'));
        $orders_url   = wc_get_account_endpoint_url('orders');
        $logout_url   = wp_logout_url(home_url('/'));
        ?>
        <div class="urbanfit-account-nav logged-in" id="urbanfit-acct-nav">
            <button class="urbanfit-acct-trigger" onclick="document.getElementById('urbanfit-acct-dropdown').classList.toggle('open')" aria-label="My Account">
                <?php echo $avatar; ?>
                <span class="acct-name"><?php echo $display_name; ?></span>
                <svg class="acct-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="urbanfit-acct-dropdown" id="urbanfit-acct-dropdown">
                <div class="acct-dd-header"><span class="acct-dd-name">&#128075; Hello, <?php echo $display_name; ?></span></div>
                <a href="<?php echo esc_url($account_url); ?>" class="acct-dd-item"><span>&#128100;</span> My Account</a>
                <a href="<?php echo esc_url($orders_url); ?>" class="acct-dd-item"><span>&#128230;</span> My Orders</a>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="acct-dd-item"><span>&#9881;</span> Account Settings</a>
                <div class="acct-dd-divider"></div>
                <a href="<?php echo esc_url($logout_url); ?>" class="acct-dd-item acct-dd-logout"><span>&#128682;</span> Log Out</a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="urbanfit-account-nav logged-out" id="urbanfit-acct-nav">
            <a href="<?php echo esc_url($account_url); ?>" class="urbanfit-acct-btn" id="urbanfit-login-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Login</span>
            </a>
            <a href="<?php echo esc_url($account_url); ?>?action=register" class="urbanfit-acct-btn urbanfit-register-btn" id="urbanfit-register-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                <span>Register</span>
            </a>
        </div>
        <?php
    }
    $html = trim(ob_get_clean());
    $html_js = json_encode($html);
    ?>
    <script>
    (function() {
        var html = <?php echo $html_js; ?>;
        var searchWidget = document.querySelector('.site-header .site-search, .site-header .widget_product_search');
        var siteHeaderCol = document.querySelector('.site-header > .col-full');
        
        var div = document.createElement('div');
        div.innerHTML = html;
        var node = div.firstElementChild || div.firstChild;

        if (searchWidget && searchWidget.parentNode) {
            // Insert right after the search widget in the top header row
            searchWidget.parentNode.insertBefore(node, searchWidget.nextSibling);
        } else if (siteHeaderCol) {
            siteHeaderCol.appendChild(node);
        }

        // Close dropdown on outside click
        document.addEventListener('click', function(e) {
            var nav = document.getElementById('urbanfit-acct-nav');
            var dd  = document.getElementById('urbanfit-acct-dropdown');
            if (nav && dd && !nav.contains(e.target)) dd.classList.remove('open');
        });
    })();
    </script>
    <?php
}

function urbanfit_navbar_account_styles() {
    ?>
    <style id="urbanfit-account-nav-styles">
        /* ── Header Top Row Parallel Flex Layout ── */
        .site-header {
            padding-top: 14px !important;
            padding-bottom: 0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }
        .site-header > .col-full {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 16px !important;
            float: none !important;
            padding-top: 0 !important;
            padding-bottom: 14px !important;
            margin-bottom: 0 !important;
            width: 100% !important;
            max-width: 1200px !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .site-header > .col-full::before,
        .site-header > .col-full::after {
            display: none !important;
        }

        /* ── Site Branding / Logo ── */
        .site-header .site-branding {
            float: none !important;
            width: auto !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
        }
        .site-header .site-branding .site-title {
            margin: 0 !important;
            font-size: 25px !important;
            font-weight: 800 !important;
            letter-spacing: -0.5px !important;
            line-height: 1.2 !important;
        }
        .site-header .site-branding .site-title a {
            color: #0f172a !important;
            text-decoration: none !important;
        }

        /* ── Product Search Box (Parallel Row) ── */
        .site-header .site-search {
            float: none !important;
            width: auto !important;
            max-width: 380px !important;
            flex: 1 1 280px !important;
            margin: 0 0 0 auto !important;
            display: flex !important;
            align-items: center !important;
        }
        .site-header .site-search .widget_product_search {
            margin: 0 !important;
            width: 100% !important;
        }
        .site-header .site-search form.woocommerce-product-search {
            margin: 0 !important;
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
        }
        .site-header .site-search input[type="search"] {
            width: 100% !important;
            height: 40px !important;
            padding: 8px 14px 8px 38px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background: #f8fafc !important;
            font-size: 13.5px !important;
            color: #0f172a !important;
            box-sizing: border-box !important;
            outline: none !important;
            transition: all 0.2s ease !important;
            margin: 0 !important;
        }
        .site-header .site-search input[type="search"]:focus {
            background: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08) !important;
        }
        .site-header .site-search button[type="submit"] {
            display: none !important;
        }

        /* ── Account Nav (Login + Register) Parallel with Search ── */
        .urbanfit-account-nav {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            position: relative !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
            height: 40px !important;
        }

        /* ── Logged-out buttons ── */
        .urbanfit-acct-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 0 14px !important;
            height: 40px !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }
        #urbanfit-login-btn {
            color: #0f172a !important;
            border: 1.5px solid #cbd5e1 !important;
            background: #ffffff !important;
        }
        #urbanfit-login-btn:hover {
            background: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        #urbanfit-register-btn {
            background: #0f172a !important;
            color: #ffffff !important;
            border: 1.5px solid #0f172a !important;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.12) !important;
        }
        #urbanfit-register-btn:hover {
            background: #1e293b !important;
            border-color: #1e293b !important;
            color: #ffffff !important;
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.18) !important;
        }

        /* ── Logged-in trigger ── */
        .urbanfit-acct-trigger {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #0f172a !important;
            border-radius: 8px !important;
            padding: 4px 12px 4px 6px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            font-family: inherit !important;
            height: 40px !important;
            box-sizing: border-box !important;
        }
        .urbanfit-acct-trigger:hover {
            background: #f1f5f9 !important;
            border-color: #94a3b8 !important;
        }
        .urbanfit-avatar {
            border-radius: 50% !important;
            width: 28px !important;
            height: 28px !important;
            object-fit: cover !important;
            display: block !important;
            border: 2px solid #e2e8f0 !important;
        }
        .acct-name {
            max-width: 100px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .acct-chevron { opacity: 0.5; flex-shrink: 0; transition: transform 0.2s; }

        /* ── Dropdown ── */
        .urbanfit-acct-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.06);
            min-width: 200px;
            z-index: 99999;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            animation: acctFadeIn 0.18s ease;
        }
        .urbanfit-acct-dropdown.open { display: block !important; }
        @keyframes acctFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .acct-dd-header {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            padding: 12px 16px;
        }
        .acct-dd-name { color: #fff; font-size: 13px; font-weight: 700; }
        .acct-dd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; font-size: 13px; font-weight: 500;
            color: #374151 !important; text-decoration: none !important;
            transition: background 0.15s;
        }
        .acct-dd-item:hover { background: #f1f5f9 !important; color: #0f172a !important; }
        .acct-dd-item span { font-size: 15px; line-height: 1; flex-shrink: 0; }
        .acct-dd-divider { height: 1px; background: #e2e8f0; margin: 4px 0; }
        .acct-dd-logout { color: #dc2626 !important; }
        .acct-dd-logout:hover { background: #fff1f2 !important; }

        /* ── Row 2: Navigation & Cart ── */
        .storefront-primary-navigation {
            background: #ffffff !important;
            padding: 0 !important;
        }
        .storefront-primary-navigation > .col-full {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            float: none !important;
            width: 100% !important;
            max-width: 1200px !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .storefront-primary-navigation > .col-full::before,
        .storefront-primary-navigation > .col-full::after {
            display: none !important;
        }
        .main-navigation {
            float: none !important;
            width: auto !important;
            margin: 0 !important;
        }
        .site-header-cart {
            float: none !important;
            margin: 0 0 0 auto !important;
            display: inline-flex !important;
            align-items: center !important;
        }
    </style>
    <?php
}


// 8. Hero Banner for UrbanFit BD Shop Front Page
add_action('woocommerce_before_main_content', 'urbanfit_shop_hero_banner', 5);
function urbanfit_shop_hero_banner() {
    if (is_shop() || is_front_page()) {
        echo '<div class="urbanfit-hero-banner" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 40px 25px; border-radius: 12px; margin-bottom: 25px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.12);">';
        echo '<span style="background: #e11d48; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; letter-spacing: 1px;">New Season 2026</span>';
        echo '<h1 style="color: #ffffff; font-size: 32px; font-weight: 800; margin: 12px 0 8px 0; letter-spacing: -0.5px;">UrbanFit BD — Modern Apparel &amp; Accessories</h1>';
        echo '<p style="color: #cbd5e1; font-size: 15px; max-width: 620px; margin: 0 auto 18px auto;">Discover Bangladeshi Men\'s fashion, Women\'s clothing, and accessories. Fast local delivery via EMS across Bangladesh.</p>';
        echo '<div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">';
        echo '<a href="?product_cat=mens-fashion" style="background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.25);">Men\'s Fashion</a>';
        echo '<a href="?product_cat=womens-fashion" style="background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.25);">Women\'s Fashion</a>';
        echo '<a href="?product_cat=accessories" style="background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.25);">Accessories</a>';
        echo '</div>';
        echo '</div>';
    }
}

// 9. Remove duplicate catalog sorting / result count at bottom of homepage/shop
remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_after_shop_loop', 'storefront_sorting', 10);
remove_action('woocommerce_after_shop_loop', 'storefront_sorting', 20);
remove_action('woocommerce_after_shop_loop', 'storefront_sorting', 30);

// 9. High Contrast Button Visibility & Global No-Underline Styles
add_action('wp_head', 'urbanfit_custom_button_and_notice_styles');
function urbanfit_custom_button_and_notice_styles() {
    ?>
    <style id="urbanfit-button-visibility-styles">
        /* Globally strip underline from all buttons and button links */
        a.button,
        button,
        input[type="submit"],
        .woocommerce a.button,
        .woocommerce button.button,
        .woocommerce a.checkout-button,
        .woocommerce button.button.alt,
        .woocommerce #respond input#submit,
        .woocommerce-message a.button,
        .woocommerce-info a.button,
        .woocommerce-error a.button {
            text-decoration: none !important;
        }

        a.button:hover,
        button:hover,
        input[type="submit"]:hover,
        .woocommerce a.button:hover,
        .woocommerce button.button:hover,
        .woocommerce a.checkout-button:hover,
        .woocommerce button.button.alt:hover,
        .woocommerce-message a.button:hover,
        .woocommerce-info a.button:hover {
            text-decoration: none !important;
        }

        /* Fix Notice Banner Styling & Icon Overlap Prevention */
        .woocommerce-info,
        .woocommerce-message,
        .woocommerce-error {
            background: #f0f9ff !important;
            border: 1px solid #bae6fd !important;
            border-left: 4px solid #0284c7 !important;
            color: #0369a1 !important;
            padding: 14px 20px 14px 52px !important;
            border-radius: 8px !important;
            position: relative !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin-bottom: 20px !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
            line-height: 1.5 !important;
        }

        /* Checkout Coupon Form & Toggle Prominence */
        .woocommerce-form-coupon-toggle {
            margin-bottom: 20px !important;
        }
        .woocommerce-form-coupon-toggle .woocommerce-info {
            background: #f0f9ff !important;
            border: 1.5px solid #0284c7 !important;
            border-left: 5px solid #0284c7 !important;
            color: #0369a1 !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1) !important;
        }
        form.checkout_coupon {
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 20px !important;
            margin-bottom: 25px !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06) !important;
        }
        form.checkout_coupon p {
            color: #334155 !important;
            font-size: 14px !important;
            margin-bottom: 12px !important;
        }
        form.checkout_coupon .form-row-first input.input-text {
            border: 1.5px solid #94a3b8 !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
        }
        form.checkout_coupon .form-row-last button.button {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-weight: 700 !important;
            padding: 11px 20px !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2) !important;
            margin: 0 !important;
        }
        form.checkout_coupon .form-row-last button.button:hover {
            background: #0284c7 !important;
            color: #ffffff !important;
        }

        .woocommerce-message {
            background: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            border-left: 4px solid #10b981 !important;
            color: #065f46 !important;
        }

        .woocommerce-error {
            background: #fff1f2 !important;
            border: 1px solid #fecdd3 !important;
            border-left: 4px solid #f43f5e !important;
            color: #9f1239 !important;
        }

        /* Fix pseudo icon position so it NEVER overlaps text */
        .woocommerce-info::before,
        .woocommerce-message::before,
        .woocommerce-error::before {
            position: absolute !important;
            left: 18px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            line-height: 1 !important;
            font-size: 18px !important;
            display: inline-block !important;
            float: none !important;
        }

        /* Links inside notice banners (e.g. "Click here to enter your code") */
        .woocommerce-info a:not(.button),
        .woocommerce-message a:not(.button),
        .woocommerce-error a:not(.button) {
            color: #0284c7 !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            margin-left: 4px !important;
        }

        .woocommerce-info a:not(.button):hover,
        .woocommerce-message a:not(.button):hover,
        .woocommerce-error a:not(.button):hover {
            text-decoration: underline !important;
            color: #0369a1 !important;
        }

        .woocommerce-message a.button,
        .woocommerce-info a.button,
        .woocommerce-error a.button {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            border-radius: 6px !important;
            padding: 8px 18px !important;
            text-decoration: none !important;
            border: none !important;
            display: inline-block !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2) !important;
            float: right !important;
            margin-top: -4px !important;
            line-height: 1.4 !important;
        }

        .woocommerce-message a.button:hover,
        .woocommerce-info a.button:hover,
        .woocommerce-error a.button:hover {
            background: #2563eb !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }

        /* Proceed to Checkout Button Styling - High Contrast, No Underline */
        .woocommerce-cart a.checkout-button,
        .woocommerce button.button.alt,
        .woocommerce a.button.alt {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            padding: 14px 24px !important;
            text-decoration: none !important;
            display: block !important;
            text-align: center !important;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2) !important;
            transition: background 0.2s ease !important;
        }

        .woocommerce-cart a.checkout-button:hover,
        .woocommerce button.button.alt:hover,
        .woocommerce a.button.alt:hover {
            background: #2563eb !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }

        /* High Contrast Form Controls, Inputs & Labels Visibility Fix */
        .woocommerce form .form-row input.input-text,
        .woocommerce form .form-row select,
        .woocommerce form .form-row textarea,
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            box-shadow: none !important;
        }

        .woocommerce form .form-row input.input-text:focus,
        .woocommerce form .form-row select:focus,
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #0284c7 !important;
            outline: 2px solid rgba(2, 132, 199, 0.2) !important;
        }

        .woocommerce form .form-row label,
        .woocommerce label {
            color: #0f172a !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            margin-bottom: 6px !important;
            display: inline-block !important;
        }

        .woocommerce table.shop_table th {
            color: #0f172a !important;
            font-weight: 800 !important;
            background: #f1f5f9 !important;
        }

        .woocommerce table.shop_table td {
            color: #1e293b !important;
            font-size: 14px !important;
        }

        .woocommerce-checkout #payment {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 18px !important;
        }

        .woocommerce-checkout #payment ul.payment_methods li label {
            color: #0f172a !important;
            font-weight: 700 !important;
            font-size: 15px !important;
        }

        .woocommerce-checkout #payment div.payment_box {
            background: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
        }
        /* Hide Bottom Catalog Sorting / Result Count */
        .woocommerce-after-shop-loop,
        .site-main > .storefront-sorting:nth-of-type(2),
        .storefront-sorting:last-of-type,
        .storefront-sorting:last-child {
            display: none !important;
        }

        /* ── Universal Uniform Product Card Grid (Shop, Homepage, Categories, Cart Cross-sells) ── */
        .woocommerce ul.products,
        .woocommerce-page ul.products,
        .site-main ul.products,
        .storefront-product-section ul.products,
        .cross-sells ul.products,
        .related.products ul.products,
        .upsells.products ul.products,
        ul.products {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
            gap: 24px !important;
            margin: 0 0 35px 0 !important;
            padding: 0 !important;
            list-style: none !important;
            float: none !important;
            clear: both !important;
            width: 100% !important;
        }
        .woocommerce ul.products::before,
        .woocommerce ul.products::after,
        ul.products::before,
        ul.products::after {
            display: none !important;
            content: none !important;
        }

        /* ── Individual Product Card: Same Height & Width ── */
        .woocommerce ul.products li.product,
        .woocommerce-page ul.products li.product,
        .site-main ul.products li.product,
        .storefront-product-section ul.products li.product,
        .cross-sells ul.products li.product,
        .related.products ul.products li.product,
        .upsells.products ul.products li.product,
        ul.products li.product {
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            height: 100% !important;
            min-height: 430px !important;
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 16px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
            border: 1px solid #e2e8f0 !important;
            text-align: center !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
            clear: none !important;
            box-sizing: border-box !important;
            position: relative !important;
        }
        .woocommerce ul.products li.product::before,
        .woocommerce ul.products li.product::after,
        ul.products li.product::before,
        ul.products li.product::after {
            display: none !important;
            content: none !important;
        }

        /* Card Hover Elevation */
        .woocommerce ul.products li.product:hover,
        ul.products li.product:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.09) !important;
            border-color: #cbd5e1 !important;
        }

        /* ── Card Link Wrapper ── */
        .woocommerce ul.products li.product a.woocommerce-LoopProduct-link,
        .woocommerce-page ul.products li.product a.woocommerce-LoopProduct-link,
        ul.products li.product a.woocommerce-LoopProduct-link,
        ul.products li.product > a:first-of-type {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 0 auto !important;
            text-decoration: none !important;
            margin: 0 0 10px 0 !important;
            padding: 0 !important;
        }

        /* ── Product Image: Exact Uniform Height & Aspect Ratio ── */
        .woocommerce ul.products li.product img,
        .woocommerce ul.products li.product a img,
        .woocommerce-page ul.products li.product img,
        .site-main ul.products li.product img,
        ul.products li.product img {
            width: 100% !important;
            height: 260px !important;
            min-height: 260px !important;
            max-height: 260px !important;
            object-fit: cover !important;
            object-position: center !important;
            border-radius: 8px !important;
            margin: 0 0 12px 0 !important;
            display: block !important;
            background-color: #f8fafc !important;
            transition: transform 0.3s ease !important;
        }
        .woocommerce ul.products li.product:hover img,
        ul.products li.product:hover img {
            transform: scale(1.02) !important;
        }

        /* ── Product Title: Uniform 2-Line Height ── */
        .woocommerce ul.products li.product .woocommerce-loop-product__title,
        .woocommerce-page ul.products li.product .woocommerce-loop-product__title,
        ul.products li.product .woocommerce-loop-product__title,
        ul.products li.product h2,
        ul.products li.product h3 {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 0 8px 0 !important;
            padding: 0 !important;
            min-height: 42px !important;
            max-height: 42px !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            line-height: 1.35 !important;
            text-align: center !important;
        }

        /* ── Price ── */
        .woocommerce ul.products li.product .price,
        .woocommerce-page ul.products li.product .price,
        ul.products li.product .price {
            font-size: 16px !important;
            font-weight: 800 !important;
            color: #0284c7 !important;
            margin: 0 0 14px 0 !important;
            text-align: center !important;
            display: block !important;
            line-height: 1.2 !important;
        }

        /* ── Action Buttons: Bottom-Aligned & Uniform Height ── */
        .woocommerce ul.products li.product .button,
        .woocommerce ul.products li.product a.button,
        .woocommerce-page ul.products li.product .button,
        .woocommerce a.button.add_to_cart_button,
        .woocommerce a.button.product_type_variable,
        ul.products li.product .button {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            padding: 0 16px !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            text-decoration: none !important;
            margin-top: auto !important;
            width: 100% !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12) !important;
            border: none !important;
            letter-spacing: 0.2px !important;
            box-sizing: border-box !important;
        }
        .woocommerce ul.products li.product .button:hover,
        .woocommerce ul.products li.product a.button:hover,
        .woocommerce a.button.add_to_cart_button:hover,
        .woocommerce a.button.product_type_variable:hover,
        ul.products li.product .button:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            text-decoration: none !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.38) !important;
        }

        /* ── WooCommerce Gutenberg Block Grids (Cart page, Home blocks, etc.) ── */
        .wc-block-grid__products,
        ul.wc-block-grid__products {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
            gap: 24px !important;
            padding: 0 !important;
            margin: 25px 0 40px 0 !important;
            list-style: none !important;
            float: none !important;
            width: 100% !important;
        }
        .wc-block-grid__products::before,
        .wc-block-grid__products::after {
            display: none !important;
            content: none !important;
        }

        .wc-block-grid__product,
        li.wc-block-grid__product {
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            height: 100% !important;
            min-height: 430px !important;
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 16px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
            border: 1px solid #e2e8f0 !important;
            text-align: center !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            position: relative !important;
            float: none !important;
        }

        .wc-block-grid__product:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.09) !important;
            border-color: #cbd5e1 !important;
        }

        .wc-block-grid__product-link {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 0 auto !important;
            text-decoration: none !important;
            margin: 0 0 10px 0 !important;
            padding: 0 !important;
            color: inherit !important;
        }

        .wc-block-grid__product-image {
            width: 100% !important;
            height: 260px !important;
            min-height: 260px !important;
            max-height: 260px !important;
            margin: 0 0 12px 0 !important;
            overflow: hidden !important;
            border-radius: 8px !important;
            display: block !important;
        }

        .wc-block-grid__product-image img,
        .wc-block-grid__product img {
            width: 100% !important;
            height: 260px !important;
            min-height: 260px !important;
            max-height: 260px !important;
            object-fit: cover !important;
            object-position: center !important;
            border-radius: 8px !important;
            display: block !important;
            margin: 0 !important;
            background-color: #f8fafc !important;
            transition: transform 0.3s ease !important;
        }

        .wc-block-grid__product:hover img {
            transform: scale(1.02) !important;
        }

        .wc-block-grid__product-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 0 8px 0 !important;
            padding: 0 !important;
            min-height: 42px !important;
            max-height: 42px !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            line-height: 1.35 !important;
            text-align: center !important;
            text-decoration: none !important;
        }

        .wc-block-grid__product-price {
            font-size: 16px !important;
            font-weight: 800 !important;
            color: #0284c7 !important;
            margin: 0 0 14px 0 !important;
            text-align: center !important;
            display: block !important;
            line-height: 1.2 !important;
        }

        .wc-block-grid__product-add-to-cart {
            margin-top: auto !important;
            width: 100% !important;
        }

        .wc-block-grid__product-add-to-cart a,
        .wc-block-grid__product-add-to-cart button,
        .wc-block-grid__product .wp-block-button__link,
        .wc-block-grid__product-add-to-cart .wp-block-button__link {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            padding: 0 16px !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            text-decoration: none !important;
            width: 100% !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12) !important;
            border: none !important;
            letter-spacing: 0.2px !important;
            box-sizing: border-box !important;
        }

        .wc-block-grid__product-add-to-cart a:hover,
        .wc-block-grid__product-add-to-cart button:hover,
        .wc-block-grid__product .wp-block-button__link:hover,
        .wc-block-grid__product-add-to-cart .wp-block-button__link:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            text-decoration: none !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.38) !important;
        }
    </style>
    <?php
}




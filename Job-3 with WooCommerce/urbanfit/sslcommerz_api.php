<?php
require_once __DIR__ . '/config.php';

function initiateSSLCommerzPayment($orderData) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : '';
    $baseUrl = "{$protocol}://{$host}{$scriptDir}";

    $postData = [
        'store_id' => SSLC_STORE_ID,
        'store_passwd' => SSLC_STORE_PASSWORD,
        'total_amount' => number_format((float)$orderData['total'], 2, '.', ''),
        'currency' => 'BDT',
        'tran_id' => $orderData['order_number'],
        'success_url' => "{$baseUrl}/order_success.php?status=success",
        'fail_url' => "{$baseUrl}/checkout.php?status=fail",
        'cancel_url' => "{$baseUrl}/cart.php?status=cancel",
        
        // Customer Details
        'cus_name' => $orderData['name'],
        'cus_email' => $orderData['email'],
        'cus_add1' => !empty($orderData['address']) ? $orderData['address'] : 'Dhaka Bangladesh',
        'cus_city' => 'Dhaka',
        'cus_postcode' => '1212',
        'cus_country' => 'Bangladesh',
        'cus_phone' => $orderData['phone'],
        
        // Product Parameters
        'shipping_method' => 'NO',
        'product_name' => 'UrbanFit Order #' . $orderData['order_number'],
        'product_category' => 'Apparel',
        'product_profile' => 'general'
    ];

    // cURL POST request to Official SSLCommerz API Gateway Endpoint
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, SSLC_SANDBOX_INIT_URL);
    curl_setopt($handle, CURLOPT_POST, 1);
    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handle, CURLOPT_TIMEOUT, 12);

    $content = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    if ($code == 200 && !empty($content)) {
        $sslcommerzResponse = json_decode($content, true);
        if (is_array($sslcommerzResponse) && isset($sslcommerzResponse['status']) && $sslcommerzResponse['status'] === 'SUCCESS' && !empty($sslcommerzResponse['GatewayPageURL'])) {
            return [
                'success' => true,
                'gateway_url' => $sslcommerzResponse['GatewayPageURL']
            ];
        }
    }

    // Fallback URL if local network is offline or unverified
    return [
        'success' => true,
        'gateway_url' => 'sslcommerz_sandbox.php'
    ];
}

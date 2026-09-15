<?php
$ch = curl_init('https://sandbox.sslcommerz.com/gwprocess/v4/api.php');
$data = [
    'store_id' => 'testbox',
    'store_passwd' => 'qwerty',
    'total_amount' => '1570.00',
    'currency' => 'BDT',
    'tran_id' => 'UB-TEST-' . rand(1000, 9999),
    'success_url' => 'http://localhost:8000/order_success.php',
    'fail_url' => 'http://localhost:8000/checkout.php',
    'cancel_url' => 'http://localhost:8000/cart.php',
    'cus_name' => 'Md. Golam Maula',
    'cus_email' => 'customer@domain.com',
    'cus_add1' => 'Banani Dhaka',
    'cus_city' => 'Dhaka',
    'cus_postcode' => '1212',
    'cus_country' => 'Bangladesh',
    'cus_phone' => '01711223344',
    'shipping_method' => 'NO',
    'product_name' => 'UrbanFit Fashion Order',
    'product_category' => 'Apparel',
    'product_profile' => 'general'
];

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP CODE: " . $info['http_code'] . "\n";
echo "RESPONSE:\n" . $response;

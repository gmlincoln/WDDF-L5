<?php
require_once __DIR__ . '/sslcommerz_api.php';

$order = [
    'order_number' => 'UB-' . rand(1000, 9999),
    'total' => 1570,
    'name' => 'Md. Golam Maula',
    'email' => 'customer@domain.com',
    'phone' => '01711223344',
    'address' => 'Banani, Dhaka',
    'cart_items' => [['id' => 1]]
];

$res = initiateSSLCommerzPayment($order);
print_r($res);

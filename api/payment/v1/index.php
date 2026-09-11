<?php
/**
 * EIMBox - DBBL Rocket Bill Payment API Router
 * Dispatches requests to the respective endpoint script based on PATH_INFO or REQUEST_URI
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$endpoint = basename($path);

// Strip .php if present
$endpoint = preg_replace('/\.php$/', '', $endpoint);

switch (strtolower($endpoint)) {
    case 'paymentvalidation':
        require __DIR__ . '/paymentValidation.php';
        break;

    case 'paymentconfirmation':
        require __DIR__ . '/paymentConfirmation.php';
        break;

    case 'getpaymentstatus':
        require __DIR__ . '/getPaymentStatus.php';
        break;

    default:
        require_once __DIR__ . '/bootstrap.php';
        send_rocket_response('04', 'Invalid Endpoint. Supported: paymentValidation, paymentConfirmation, getPaymentStatus');
        break;
}

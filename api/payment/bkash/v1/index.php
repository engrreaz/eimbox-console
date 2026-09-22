<?php
/**
 * EIMBox - bKash Pay Bill API Router
 * Dispatches incoming requests to the corresponding script
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$endpoint = basename($path);

// Strip .php if present
$endpoint = preg_replace('/\.php$/', '', $endpoint);

switch (strtolower($endpoint)) {
    case 'checkbill':
    case 'check_bill':
        require __DIR__ . '/CheckBill.php';
        break;

    case 'billpayment':
    case 'bill_payment':
    case 'payment':
        require __DIR__ . '/BillPayment.php';
        break;

    case 'recheckpayment':
    case 'recheck_payment':
    case 'recheck':
        require __DIR__ . '/RecheckPayment.php';
        break;

    default:
        require_once __DIR__ . '/bootstrap.php';
        send_bkash_response('202', 'Invalid Endpoint. Supported: CheckBill, BillPayment, RecheckPayment');
        break;
}

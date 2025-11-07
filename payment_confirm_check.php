<?php
require_once 'inc/init.php';
include_once 'inc/customer_auth.php';

$strJsonFileContents = file_get_contents("bkash/config.json");
$array               = json_decode($strJsonFileContents, true);

if (empty($_SESSION['pending_order'])) {
    header("Location: " . BASEURL . "/checkout");
    exit;
} else {

    if (isset($_GET['paymentID']) && isset($_GET['status'])) {
        $paymentID = $_GET['paymentID'];
        $status    = $_GET['status'];

        if ($status == 'success') {

            $clientToken = $_SESSION['token'];

            $post_token = [
                'paymentID' => $paymentID,
            ];
            $url       = curl_init($array['executeURL']);
            $posttoken = json_encode($post_token);

            $header = [
                'Content-Type:application/json',
                'Authorization:' . $clientToken,
                'X-APP-Key:' . $array['app_key'],
            ];

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $resultdata = curl_exec($url);
            curl_close($url);

            if (! empty($resultdata)) {
                $obj = json_decode($resultdata);

                $statusCode    = $obj->statusCode;
                $statusMessage = $obj->statusMessage;

                if ($statusCode == '0000') {

                    // pgw return value
                    $paymentID             = $obj->paymentID;
                    $trxID                 = $obj->trxID;
                    $transactionStatus     = $obj->transactionStatus;
                    $amount                = round($obj->amount, 2);
                    $currency              = $obj->currency;
                    $intent                = $obj->intent;
                    $paymentExecuteTime    = $obj->paymentExecuteTime;
                    $merchantInvoiceNumber = $obj->merchantInvoiceNumber;
                    $payerType             = $obj->payerType;
                    $payerReference        = $obj->payerReference;
                    $customerMsisdn        = $obj->customerMsisdn;
                    $payerAccount          = $obj->payerAccount;
                    $maxRefundableAmount   = $obj->maxRefundableAmount;
                    $statusCode            = $obj->statusCode;
                    $statusMessage         = $obj->statusMessage;

                    // order summary from session

                    $paymentFrom    = trim($_SESSION['pending_order']['payment_method']);
                    $category_id    = (int) trim($_SESSION['pending_order']['category_id']);
                    $product_id     = (int) trim($_SESSION['pending_order']['product_id']);
                    $quantity       = (int) trim($_SESSION['pending_order']['quantity'] ?? 1);
                    $couponDiscount = 0;
                    $discount       = 0;
                    $subTotal       = 0;
                    $grandTotal     = 0;
                    $today          = date('Y-m-d');
                    $coupon_code    = null;

                    $errors = [];

                    $product = singleProductInfoById($product_id, 0);

                    if ($product === false) {
                        $errors[] = "Product not found.";
                    }

                    $price            = $product['price'];
                    $product_discount = $product['discount'];
                    $type             = $product['discount_type'];
                    $isPreorder       = (int) $product['is_preorder'];

                    $status = ($isPreorder === 1 ? 0 : 1);

                    if ($type === 'percent') {
                        $price_after_product_discount = round($price - ($price * $product_discount / 100), 2);
                        $discount                     = round($price * ($product_discount / 100), 2);
                    } else {
                        $price_after_product_discount = round(($price - $product_discount), 2);
                        $discount                     = round($product_discount, 2);
                    }

                    $itemCount = itemCountByProductId($product['id'], 0, 0);

                    if ($isPreorder !== 1) {
                        if ($quantity > $itemCount) {
                            $errors[] = "Order Quantity is bigger than stock quantity";
                        }
                    }

                    $final_price = $price_after_product_discount;

                    // if coupon code found on session

                    if (! empty($_SESSION['pending_order']['coupon_code'])) {

                        $coupon_code = $_SESSION['pending_order']['coupon_code'];

                        $coupon = couponInfoByCouponCode($coupon_code, 0);

                        if (! $coupon) {
                            $errors[] = 'Coupon not found.';
                        } elseif ($coupon['status'] != 1) {
                            $errors[] = 'This coupon is not active.';
                        } elseif ($today < $coupon['start_date']) {
                            $errors[] = 'This coupon has not started yet.';
                        } elseif ($today > $coupon['end_date']) {
                            $errors[] = 'This coupon has expired.';
                        } elseif ((int) $category_id !== (int) $coupon['category_id']) {
                            $errors[] = 'This coupon is not applicable for this category.';
                        } else {

                            $isValid = true;

                            if (! empty($coupon['product_id']) && (int) $coupon['product_id'] !== (int) $product_id) {
                                $isValid  = false;
                                $errors[] = 'This coupon is not applicable for this product.';
                            }

                            if ($isValid) {
                                $coupon_discount_type   = ($coupon['discount_type'] == 'percent' ? 'percent' : 'fixed');
                                $coupon_discount_amount = round($coupon['discount_amount'], 2);

                                if ($coupon_discount_type === 'percent') {
                                    $final_price    = round($price_after_product_discount - ($price_after_product_discount * $coupon_discount_amount / 100), 2);
                                    $couponDiscount = round(($price_after_product_discount * $coupon_discount_amount / 100), 2);
                                } else {
                                    $final_price    = round(($price_after_product_discount - $coupon_discount_amount), 2);
                                    $couponDiscount = round($coupon_discount_amount, 2);
                                }

                                if ($final_price <= 0) {
                                    $errors[] = 'Invalid Product price';
                                }
                            }
                        }
                    }
                    // end coupon code found on session

                    $subTotal       = round(($price * $quantity), 2);
                    $discount       = round(($discount * $quantity), 2);
                    $couponDiscount = round(($couponDiscount * $quantity), 2);
                    $grandTotal     = round(($subTotal - $discount - $couponDiscount), 2);

                    $customer_id   = $_SESSION['pending_order']['customer_id'];
                    $customer_name = $customer['name'];
                    $public_id     = $customer['public_id'];

                    $isReferral = false;

                    if (! empty($customer['referred_by'])) {

                        $referred_code = trim($customer['referred_by']);
                        $referral      = customerInfoByReferralCode($referred_code);

                        if ($referral) {
                            if ($referral['referral_program'] === 1) {
                                $referred_id              = $referral['id'];
                                $referral_bonus           = round($grandTotal * ($referral['referred_bonus'] / 100), 2);
                                $referral_current_balance = $referral['referral_balance'];
                                $referral_new_balance     = $referral_current_balance + $referral_bonus;
                                $bonus_status             = 1;
                                $isReferral               = true;
                            }
                        }
                    }

                    if (empty($errors)) {

                        try {

                            $config->begin_transaction();

                            // payment gateway return data store

                             $stmt = $config->prepare('INSERT INTO payment_gateway_history (customer_id, payment_id, trx_id, transaction_status, amount, currency, intent, payment_execute_time, merchant_invoice_number, payer_type, payer_reference, customer_msisdn, payer_account, max_refundable_amount, status_code, status_message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $stmt->bind_param('isssdssssssssdsss', $customer_id, $paymentID, $trxID, $transactionStatus, $amount, $currency, $intent, $paymentExecuteTime, $merchantInvoiceNumber, $payerType, $payerReference, $customerMsisdn, $payerAccount, $maxRefundableAmount, $statusCode, $statusMessage, $current_date);

                            if (! $stmt->execute()) {
                                throw new Exception($stmt->error);
                            }

                            // Order Table
                            $stmt1 = $config->prepare('INSERT INTO product_sell (customer_id, invoice_id, product_id, unit_price, quantity, sub_total, product_discount, coupon_code, coupon_discount, total, payment_method, status, created_at, site_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $stmt1->bind_param('isididdsddsisi', $customer_id, $merchantInvoiceNumber, $product_id, $price, $quantity, $subTotal, $discount, $coupon_code, $couponDiscount, $grandTotal, $paymentFrom, $status, $current_date, $site_id);

                            if (! $stmt1->execute()) {
                                throw new Exception($stmt1->error);
                            }

                            // referral bonus
                            if ($isReferral) {

                                $stmt3 = $config->prepare("UPDATE customers SET referral_balance = ?, updated_at = ? WHERE referral_code = ?");
                                $stmt3->bind_param('dss', $referral_new_balance, $current_date, $referred_code);

                                if (! $stmt3->execute()) {
                                    throw new Exception($stmt3->error);
                                }

                                $stmt4 = $config->prepare("INSERT INTO referral_bonus_history (referred_id, customer_id, invoice_id, bonus_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt4->bind_param('iisdis', $referred_id, $customer_id, $merchantInvoiceNumber, $referral_bonus, $bonus_status, $current_date);

                                if (! $stmt4->execute()) {
                                    throw new Exception($stmt4->error);
                                }
                            }

                            // if product is not pre order

                            if ($isPreorder !== 1) {

                                $product_items = productItemByProductIdLimit($product_id, 0, 0, $quantity);

                                if ($product_items !== false) {

                                    while ($item = mysqli_fetch_assoc($product_items)) {

                                        $item_id     = $item['id'];
                                        $sell_status = 1;

                                        $stmt5 = $config->prepare("UPDATE product_items SET invoice_id = ?, sell_status = ?, updated_at = ? WHERE id = ?");
                                        $stmt5->bind_param('sisi', $merchantInvoiceNumber, $sell_status, $current_date, $item_id);

                                        if (! $stmt5->execute()) {
                                            throw new Exception($stmt5->error);
                                        }
                                    }
                                }
                            }

                            $config->commit();

                            $n_title   = "New Order";
                            $n_message = 'Your order #' . $invoice . " has been successfully placed.";
                            $n_link    = "orders";

                            storeNotificaton($n_title, $n_message, $n_link, $current_date, $customer_id, 'customer');

                            if ($isReferral) {

                                $n_title   = "Referral Bonus";
                                $n_message = 'You got ' . CURRENCY . ' ' . number_format($referral_bonus, 2) . " bonus from from the purchase of " . $customer_name;
                                $n_link    = "referral-bonus";

                                storeNotificaton($n_title, $n_message, $n_link, $current_date, $referred_id, 'customer');

                            }

                            $subject  = "Order Confirmation";
                            $bodyText = '
                <body>
                <div class="container">
                    <div class="header">Order Confirmation</div>
                    <div class="content">
                        <p>Hello ' . $customer_name . '</p>
                        <p> Your order invoice ID#' . $invoice . ' has been successfully placed. Enjoy your purchase. </p>
                        <p>Thank you.</p>

                        <h3>Order Details</h3>
                        <table width="100%" border="1">
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                            <tr>
                                <th>' . $product['product_name'] . '</th>
                                <th>' . $quantity . '</th>
                                <th>' . round($product['price'], 2) . '</th>
                                <th>' . round(($product['price'] * $quantity), 2) . '</th>
                            </tr>
                            <tr>
                                <td colspan="3" class="total">Sales Discount</td>
                                <td class="total">' . round(($discount * $quantity), 2) . '</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="total">Coupon Discount</td>
                                <td class="total">' . round(($discount * $quantity), 2) . '</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="total">Grand Total</td>
                                <td class="total">' . round($grandTotal, 2) . '</td>
                            </tr>
                        </table>

                    </div>';

                            $sendEmail = emailSend($customer['email'], $subject, $bodyText, "Order Confirmation", $website['site_name']);

                            unset($_SESSION['pending_order']);

                            $config->close();
                            header('location:' . BASEURL . '/order-placed/' . $merchantInvoiceNumber);

                        } catch (Exception $e) {
                            $config->rollback();

                            $_SESSION['flash_message'] = [
                                'status' => 'error',
                                'text'   => "❌ Transaction failed and rolled back: " . $e->getMessage(),
                            ];
                            $config->close();
                            header("location:" . BASEURL . "/checkout");
                            exit;
                        }

                    } else {

                            $_SESSION['flash_message'] = [
                                'status' => 'error',
                                'text'   => $errors,
                            ];

                            header("location:" . BASEURL . "/checkout");
                            exit;

                    }

                } else {
                    $_SESSION['flash_message'] = [
                        'status' => 'error',
                        'text'   => $statusCode . '-' .$statusMessage,
                    ];

                    header("location:" . BASEURL . "/checkout");
                    exit;
                }
            }

        } else {

            $_SESSION['flash_message'] = [

                'status' => 'error',
                'text'   => $status
            ];
            
            header("location:" . BASEURL . "/checkout");
            exit;
        }

    } else {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text'   => 'unknown error'
        ];

        header("location:" . BASEURL . "/checkout");
        exit;
    }
}

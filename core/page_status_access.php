<?php
// require_once 'config.php';
// require_once 'db.php';
$access_grant = true;  // default false hobe
$proceed_grant = false;



if ($access_grant == false && $page_status >= $page_status_grant)
    $access_grant = true;

// page grand check
if($access_grant == false) {

}

// accessibility quilifying check
// module use, page use, activity , time spent, user since + avg use time, 
// daily avg use


$invoice_id = 1;
$stmt = $conn->prepare("SELECT * FROM billing_invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

// echo '<pre>'.print_r($invoice).'</pre>';
?>
<!-- Check access table that permit or not (table need create);
check activity time()
check score. -->

<?php



/*

// Grant API Response:

{
  "statusCode": "0000",
  "statusMessage": "Successful",
  "id_token": "eyJraWQiOiJvTVJzNU9ZY0wrUnRXQ2o3ZEJtdlc5VDBEcytrckw5M1NzY0VqUzlERXVzPSIsImFsZyI6IlJTMjU2In0.eyJzdWIiOiJlODNlMDkwMC1jY2ZmLTQzYTctODhiNy0wNjE5NDJkMTVmOTYiLCJhdWQiOiI2cDdhcWVzZmljZTAxazltNWdxZTJhMGlhaCIsImV2ZW50X2lkIjoiYTQ1N2FmMDAtODRiYi00ZTg2LTg5YWItMTg1NWJhNjdkMjFiIiwidG9rZW5fdXNlIjoiaWQiLCJhdXRoX3RpbWUiOjE3NjIwNzIxNDEsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC5hcC1zb3V0aGVhc3QtMS5hbWF6b25hd3MuY29tXC9hcC1zb3V0aGVhc3QtMV9yYTNuUFkzSlMiLCJjb2duaXRvOnVzZXJuYW1lIjoic2FuZGJveFRva2VuaXplZFVzZXIwMiIsImV4cCI6MTc2MjA3NTc0MSwiaWF0IjoxNzYyMDcyMTQxfQ.TpWodBYv8ibUA9xfEWas0bQy1ay9-ZRG5vY2jcy98neqQ-msbum0GKjB671MQ6JveMuuH4SQF4x0z9Hf-m3MX6K9MuNzdCmpfPiEizet42T3EBfGCnuGOu5Zcbd5mFGK1iDyFqEBwQEABdblUXuhZAJjiD9jcOtNwxe-0F8MfY4O2AA4gbpZ9la1cEtTyqnv5XMoJ_5OHB-U4iW7wBozWQd4LJUKza6oTdKq1k1wp5UoU4BYBkkOmwGs4MiBkRaZU6d-i_oIoMtxFwjMG2sgF1omKMwpAYW7ts3rJKNlOG8QV-lJdztpaJkV5YYV1cFYGSwcIKZKX_ajjsS0hNUQ5g",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "eyJjdHkiOiJKV1QiLCJlbmMiOiJBMjU2R0NNIiwiYWxnIjoiUlNBLU9BRVAifQ.h0FuFfGFMXoEO3DFWc1_dTG-czgFrW-_8bh-1HrWPbSyH6AYV_qyTBwXX0wfdJV4Q-3z7lb7e1aW12ggX9YLFBOG2wJpzDUn9YwFhu_ZG9oBzTZ-lvSRR0FfNCHuWCwSF0xbZiEC52DFyvPvbLKJYalCib_3Z81W4FKexQACPEgLbeGlnTqbw-ROAtYr8ZkIxFfjyaom8hx_pzVLmWPgbdufRgk7A7z6SsHtOkbch20z8irnbunlq7SE_ZyKY1vYPVjqc_Zb4_VCdoW6SDT_-oqoYsCBbIRYCQ-mG4j1bIeS4SUJDjLUA6PugfOsoLrD2imTuTO9nxf-Pjv8WfBsbw.wSJAdboxuFeP3Srq.VXlb6rBQpEuTzTdac4TEWf6VhFc1Vyxsds0XjaM238AvnM1kUv6xRs2YXauym1oknubZyLnWLutn4Brc2Hy_CfBGvzZA3R_HnjsodBgwIXnpowO-hKJD9BVotTEpKN9xfyl2H_38UTGQP7BH8TJq7HqUhakNaECow_Zqxsi2ffmxb2hI3Mkefzj8dLg6PSwACQA94_1DcGTwd8XMVxXL6XyGDR4rNcavBHpZ1neUoXuK3a9ZCFuY-crXPLnTGE2N8poK1qhA_pRfKNS1Q6au2sGUnPaoxWXHdHiUxWr757lBNtfHuqlbMWeKj8zHFhX_yaaSA6CmC-DSsKtuuDCYK6KMsf58DlySqb8A67tM7M5ueEcPM2ewfUJrH5QFYn821TPedgEBJa55LwFPhgaDhLmgNw8Vc9Fo-55RVgE6etxcMgiCuhqqiOtNjvJ4Ekkna91IayryrQ41m4i8zbuL0RIhWzbJ4M_UQelmz1VcBkTrvJ06uzAtDCFJcTb9Ds_t3XShpG10XO9Qr6Z724uqxW8E3l0OdOctD__wyahIrgAokP7-LEk9-w2tnQJU3rbIkMyaZoaeve4k7esmfJuGVbsOfB3DrYgPFIRA0Dt8iroTpITJvnCo8jIxQ0wOEkkYOWbEWZI7_Dn1_nxYhKXJI3CG3tmM7HmNMuzMXgJ5sE64JVcPn38pVdMJ4QkrkMW1xvfEesFtdRZAlr1SWTk8z6lSXP_g-46Sy-Xg5TUnKztMqFDQgq4qjDuyUjLJlSW6851osg6lj623DKOrregrBt-4Lj1KGZz7qwFHwZMGYvy47rr0czw3OWyoJ4s7N_AlsdsoKuZ2_QyX3Jwd7CWQqgOH5Jfy2JHrtDiBYStXcBIji3uhLCP7Hc2SYMaSbbd35sFNKCPrJxC_VZncLk2q354FNURlwQMsRaMJN-hrx6glBiaS8PHUzBjz1bA0qO-0IFNcjWouNi-i5uz2E1i57d4_YvMBf5sWpDhDRW43cXjAtntpsdFyreZZK46sycngAi6AmDkSb4LJhwojDvoigjWNtAK_Kl_mUblWDT_FnxBWr0TGI5T-jDJXEjcID-UNZcAcf4tY0yCWHevSTDnvy1vGhjep_dvMW-dhAvBILH3lHa9Y3ZRC7eE9_b_zUfHyeaVOREWalIFZ2lHxPc0Bpi8hRF0nY_Mhvg81TfuZDUDkOSocYhKn9LV964_aIOuPcWvLu_B_LqdkeGIgF23aET2rlbs_ebgnAslWt6HpXuLfCYGnEX5ZgfeCGKRGx38tFH_VvROFT2QI3QBbcOLUfFBlKOhQ8jZU5VU_9lF0hW8HUSPb7Yw.x8r7vV59sHtojfv60NITkQ"
}

// Create API response: 

{
  "paymentID": "TR0011mqN0nOK1762072142166",
  "bkashURL": "https://sandbox.payment.bkash.com/?paymentId=TR0011mqN0nOK1762072142166&hash=Rb!oeT7T_4rvI2Qdy2)djiW6Y6xzFdkq*ebB1(MDKDQMeEt*ED1P)sGp2Ea0x8IIo0S6yhEoEhUNjQ3V.qmC3R6Ug-NTNeM9rcP11762072142167&mode=0011&apiVersion=v1.2.0-beta/",
  "callbackURL": "https://dingee-ecoresort.com/wc-api/bkash_payment_process?orderId=2606&invoiceID=bfw_6907164d9d12c_2606",
  "successCallbackURL": "https://dingee-ecoresort.com/wc-api/bkash_payment_process?orderId=2606&invoiceID=bfw_6907164d9d12c_2606&paymentID=TR0011mqN0nOK1762072142166&status=success&signature=k3YOrG8qy6",
  "failureCallbackURL": "https://dingee-ecoresort.com/wc-api/bkash_payment_process?orderId=2606&invoiceID=bfw_6907164d9d12c_2606&paymentID=TR0011mqN0nOK1762072142166&status=failure&signature=k3YOrG8qy6",
  "cancelledCallbackURL": "https://dingee-ecoresort.com/wc-api/bkash_payment_process?orderId=2606&invoiceID=bfw_6907164d9d12c_2606&paymentID=TR0011mqN0nOK1762072142166&status=cancel&signature=k3YOrG8qy6",
  "amount": "599.00",
  "intent": "sale",
  "currency": "BDT",
  "paymentCreateTime": "2025-11-02T14:29:02:166 GMT+0600",
  "transactionStatus": "Initiated",
  "merchantInvoiceNumber": "bfw_6907164d9d12c_2606",
  "statusCode": "0000",
  "statusMessage": "Successful"
}

// Execute API response:

{
  "paymentID": "TR0011mqN0nOK1762072142166",
  "trxID": "CK250NROQ5",
  "transactionStatus": "Completed",
  "amount": "599.00",
  "currency": "BDT",
  "intent": "sale",
  "paymentExecuteTime": "2025-11-02T14:30:12:378 GMT+0600",
  "merchantInvoiceNumber": "bfw_6907164d9d12c_2606",
  "payerType": "Customer",
  "payerReference": "bKash_6907164d9d12a_2",
  "customerMsisdn": "01619777283",
  "payerAccount": "01619777283",
  "maxRefundableAmount": "599.00",
  "statusCode": "0000",
  "statusMessage": "Successful"
}
  */
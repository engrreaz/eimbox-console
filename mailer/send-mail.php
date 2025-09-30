<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Composer autoload
include_once('core/core-val.php');

// $mail_type = 'forgot-password';
// $mail_to = $email;
// $mail_name = 'EIMBox User';
// $mail_subject = 'Reset EIMBox Password';
// $mail_attach = '';
// $msg_success = "Reset Password mail has been send to your mail $mail_to";

echo MAIL_SECRET;
echo $resetLink;

// --------------------------------------------------------------------
$htmlTemplate = file_get_contents('mailer/mail-templete-' . $mail_type . '.php'); // অথবা সরাসরি স্ট্রিং হিসেবে.

if ($mail_type == 'forgot-password') {
  $html = str_replace('%NAME%', htmlspecialchars($mail_to, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
  $html = str_replace('%RESET_LINK%', htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'), $html);
  $mail_alt = '';
} else if ($mail_type == 'otp') {
  $otp = $token;

  $otp = str_pad(preg_replace('/\D/', '', $otp), 6, '0', STR_PAD_LEFT);
  $digits = str_split($otp);
  $html = str_replace('%OTP%', htmlspecialchars($otp, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
  for ($i = 0; $i < 6; $i++) {
    $html = str_replace('%D' . ($i + 1) . '%', htmlspecialchars($digits[$i], ENT_QUOTES, 'UTF-8'), $html);
  }
  $html = str_replace('%NAME%', htmlspecialchars($to_name, ENT_QUOTES, 'UTF-8'), $html);
  $mail_alt = '';
}




// ********************************************************************
$to_mail = $mail_to;
$to_name = $mail_name;
$mail_body = $html;
$pht = $mail_attach;
$success_message = $msg_success;
// *********************************************************************************************************
$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = MAIL_FROM;
  $mail->Password = MAIL_SECRET;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port = 465;


  $mail->setFrom(MAIL_FROM, MAIL_NAME);
  $mail->addAddress($to_mail, $to_name);
  $mail->addReplyTo(MAIL_ADMIN, NAME_ADMIN);
  $mail->addBCC(MAIL_CC, NAME_CC);

  if (file_exists($pht)) {
    $mail->addAttachment($pht);
  }

  $mail->isHTML(true);
  $mail->Subject = $mail_subject;
  $mail->Body = $mail_body;
  $mail->AltBody = $mail_alt;

  $mail->send();
  echo $success_message;
} catch (Exception $e) {
  echo "Mail could not be sent. Error: {$mail->ErrorInfo}";
}
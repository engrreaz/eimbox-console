<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Composer autoload
include_once('../core/core-val.php');

// ********************************************************************
// $to_mail = 'engrreaz@gmail.com';
// $to_name = 'Engr. Reazul Hoque';
// $mail_subject = 'Test Email with PHPMailer';
// $mail_body = '<h1>Hello</h1><p>This is a <b>test mail</b> with attachment.</p>';
// $mail_alt = 'This is the plain text version of the email';


// ********************************************************************
$to_mail = $_POST['to_mail'] ?? '';
$to_name = $_POST['to_name'] ?? '';
$mail_subject = $_POST['mail_subject'] ?? '';
$mail_body = $_POST['mail_body'] ?? '';
$mail_alt = $_POST['mail_alt'] ?? 'This is the plain text version of the email';
$pht = $_POST['mail_doc'] ?? '';
$success_message = $_POST['mail_success'] ?? '';
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
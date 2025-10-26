<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load core variables
include_once(dirname(__DIR__) . '/core/core-val.php');

try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = MAIL_FROM;
  $mail->Password = MAIL_SECRET;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port = 465;
  $mail->SMTPSecure = 'ssl';


  $mail->setFrom(MAIL_FROM, MAIL_NAME);
  $mail->addAddress($mail_to, $mail_name);
  $mail->addReplyTo(MAIL_ADMIN, NAME_ADMIN);
  $mail->addBCC(MAIL_CC, NAME_CC);

  if ($mail_attach && file_exists($mail_attach)) {
    $mail->addAttachment($mail_attach);
  }

  // Load template
  $template_path = dirname(__DIR__) . "/mailer/mail-templete-$mail_type.php";
  if (!file_exists($template_path)) {
    throw new Exception("Mail template not found: $template_path");
  }
  $htmlTemplate = file_get_contents($template_path);

  if ($mail_type === 'new-ins') {

    $html = str_replace('%NAME%', htmlspecialchars($mail_name, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
    $html = str_replace('%ACTIVATION_LINK%', htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'), $html);
    $html = str_replace('%INSTITUTE_NAME%', htmlspecialchars($mail_name, ENT_QUOTES, 'UTF-8'), $html);
    $mail_alt = '';

  } else if ($mail_type === 'otp') {

    $html = str_replace('%NAME%', htmlspecialchars($mail_name, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
    $html = str_replace('%ACTIVATION_LINK%', htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'), $html);
    $html = str_replace('%INSTITUTE_NAME%', htmlspecialchars($mail_name, ENT_QUOTES, 'UTF-8'), $html);

    $digits = str_split($token); // ['4','8','2','1','5','9']
    // প্লেসহোল্ডারগুলো একে একে রিপ্লেস করা
    for ($i = 0; $i < 6; $i++) {
      $html = str_replace('%D' . ($i + 1) . '%', $digits[$i], $html);
    }

    // পুরো OTP প্লেসহোল্ডারে বসানো
    $html = str_replace('%OTP%', $token, $html);

    $mail_alt = '';

  } else if ($mail_type == 'forgot-password') {

    $html = str_replace('%NAME%', htmlspecialchars($mail_to, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
    $html = str_replace('%RESET_LINK%', htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'), $html);
    $mail_alt = '';

  } else if ($mail_type == 'new-user') {


    $html = str_replace('%EMAIL%', htmlspecialchars($mail_to, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
    $html = str_replace('%ACTIVATION_LINK%', htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'), $html);
    $html = str_replace(' %INSTITUTE_NAME%', htmlspecialchars($scname, ENT_QUOTES, 'UTF-8'), $html);

    $mail_alt = '';

  }

  $mail->isHTML(true);
  $mail->Subject = $mail_subject;
  $mail->Body = $html;
  $mail->AltBody = $mail_alt;

  $mail->send();

} catch (Exception $e) {
  throw new Exception("Mail could not be sent. Error: {$mail->ErrorInfo}");
}
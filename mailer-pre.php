<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';


$mailto = $_POST['mailcnt'];

$replyto = $_POST['replyto'];
$cc = $_POST['cc'];
$bcc = $_POST['bcc'];

$att = $_POST['att'];

$sub = $_POST['sub'];
$body = $_POST['body'];
$bodyalt = $_POST['bodyalt'];


////////////////////////////////////////////////////////////////////////////////////////////////

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'thisisengrreaz@gmail.com';
    $mail->Password = 'yqfzkzimwasohexq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('thisisengrreaz@gmail.com', 'EIMBox');

    for ($x = 1; $x <= $mailto; $x++) {
        $person = trim($_POST['person_' . $x]);
        $mail->addAddress($person);
    }
    // প্রথম খণ্ডের সূচনা অধ্যায় ("কৃত্রিম বুদ্ধিমত্তা কী এবং কেন")

    // $mail->addAddress('engrreaz@gmail.com');
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('masterlabibshahriar@gmail.com');
    // $mail->addBCC('masterlabibshahriar@gmail.com');

    if ($replyto != '') {
        $mail->addReplyTo($replyto);
    }
    if ($cc != '') {
        $mail->addCC($cc);
    }
    if ($bcc != '') {
        $mail->addBCC($bcc);
    }



    // $aa= 'backup/103187/Table-103187-12_08_2024_11_21_46.ebd';
    for ($y = 1; $y <= $att; $y++) {
        $a = trim($_POST['fl_' . $y]);
        // echo trim($a);
        // $a = $att_ . $y;
        $mail->addAttachment($a);
        // if($aa == $a) {
        //     echo 'String Same';
        // } else {
        //     echo 'Different<br>';
        //     echo $a . '<br>' . $aa . '<br>' . strlen($a) . '/' . strlen($aa);
        // }
    }

    // $mail->addAttachment($a);
    //Attachments
    // $mail->addAttachment('backup/103187/Table-103187-12_08_2024_11_21_46.ebd');
    // $mail->addAttachment('backup/103187/pack.txt');

    $mail->isHTML(true);
    $mail->Subject = $sub;
    $mail->Body = $body;
    $mail->AltBody = $bodyalt;

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
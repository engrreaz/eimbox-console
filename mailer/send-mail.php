<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load core variables
include_once(dirname(__DIR__) . '/core/core-val.php');

try {
    $mail = new PHPMailer(true);

    // === SMTP Config ===
    $mail->isSMTP();
    $mail->Host = 'mail.eimbox.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_FROM;   // উদাহরণ: noreply@eimbox.com
    $mail->Password = MAIL_SECRET; // ইমেইল পাসওয়ার্ড
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';
    $mail->Port = 465;

    // SSL সার্টিফিকেট যদি self-signed হয় (শুধু টেস্টের জন্য)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // === Sender & Receiver ===
    $mail->setFrom(MAIL_FROM, MAIL_NAME);
    $mail->addAddress($mail_to, $mail_name);
    // $mail->addReplyTo(MAIL_ADMIN, NAME_ADMIN);
    // $mail->addBCC(MAIL_CC, NAME_CC);

    if (!empty($mail_attach) && file_exists($mail_attach)) {
        $mail->addAttachment($mail_attach);
    }

    // === Load Template ===
    $template_path = dirname(__DIR__) . "/mailer/mail-templete-$mail_type.php";
    if (!file_exists($template_path)) {
        throw new Exception("Mail template not found: $template_path");
    }

    $htmlTemplate = file_get_contents($template_path);
    $html = $htmlTemplate; // default assign

    // === Replace Variables by Type ===
    if ($mail_type === 'new-ins') {
        $html = str_replace(
            ['%NAME%', '%ACTIVATION_LINK%', '%INSTITUTE_NAME%'],
            [htmlspecialchars($mail_name), htmlspecialchars($resetLink), htmlspecialchars($mail_name)],
            $htmlTemplate
        );
    } else if ($mail_type === 'otp') {
        $html = str_replace(
            ['%NAME%', '%ACTIVATION_LINK%', '%INSTITUTE_NAME%'],
            [htmlspecialchars($mail_name), htmlspecialchars($resetLink), htmlspecialchars($mail_name)],
            $htmlTemplate
        );

        $digits = str_split($token);
        for ($i = 0; $i < 6; $i++) {
            $html = str_replace('%D' . ($i + 1) . '%', $digits[$i], $html);
        }
        $html = str_replace('%OTP%', $token, $html);
    } else if ($mail_type == 'forgot-password') {
        $html = str_replace(
            ['%NAME%', '%RESET_LINK%'],
            [htmlspecialchars($mail_to), htmlspecialchars($resetLink)],
            $htmlTemplate
        );
    } else if ($mail_type == 'new-user') {
        $html = str_replace(
            ['%EMAIL%', '%ACTIVATION_LINK%', '%INSTITUTE_NAME%'],
            [htmlspecialchars($mail_to), htmlspecialchars($resetLink), htmlspecialchars($scname)],
            $htmlTemplate
        );
    }

    // === Final Mail ===
    $mail->isHTML(true);
    $mail->Subject = $mail_subject;
    $mail->Body = $html;
    $mail->AltBody = strip_tags($html);

    $mail->send();

} catch (Exception $e) {
    throw new Exception("Mail could not be sent. Error: {$mail->ErrorInfo}");
}

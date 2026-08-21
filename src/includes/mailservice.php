<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

require_once __DIR__ . '/../config.php';


function sendSystemEmail($toEmail, $toName, $subject, $htmlBody, $altBody = '', $debug = false) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug  = $debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF; //Verbose output only when asked (test script)
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = SMTP_HOST;                              //Hostname, not IP — TLS cert is issued for smtp.gmail.com
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = SMTP_USER;                              //SMTP username
        $mail->Password   = SMTP_PASS;                              //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable explicit TLS encryption
        $mail->Port       = SMTP_PORT;                              //TCP port to connect to; 587 as we use `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        //Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        //Content
        $mail->isHTML(true);                                         //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

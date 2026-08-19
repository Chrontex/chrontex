<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php'; 

require_once __DIR__ . '/../config.php'; 


function sendSystemEmail($toEmail, $toName, $subject, $htmlBody, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = SMTP_HOST;                              //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = SMTP_USER;                              //SMTP username
        $mail->Password   = SMTP_PASS;                              //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
        $mail->Port       = SMTP_PORT;                              //TCP port to connect to; 587 as we use `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

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
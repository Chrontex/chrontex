<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

require_once __DIR__ . '/../config.php';

function send_verification_email($to_email, $to_name, $token, $debug = false) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug  = $debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF; //Verbose output only when asked (test script)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Timeout    = 10;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $verify_link = SITE_URL . "/verify.php?token=" . urlencode($token);

        $subject      = "Verify your Chrontex account";
        $heading      = "Welcome to Chrontex!";
        $intro        = "Thanks for registering, {$to_name}. Click the button below to verify your email and activate your account.";
        $button_text  = "Verify My Email";
        $expiry_note  = "This link expires in 24 hours.";
        $footer_note  = "If you didn't create this account, you can safely ignore this email.";

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = "
        <div style='font-family:Inter,Arial,sans-serif;background:#03110B;padding:40px 20px;'>
          <div style='max-width:420px;margin:0 auto;background:#0B1F16;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:32px;'>
            <h2 style='color:#ffffff;font-family:Poppins,Arial,sans-serif;margin-top:0;'>{$heading}</h2>
            <p style='color:#c9d1cb;font-size:15px;line-height:1.5;'>{$intro}</p>
            <div style='text-align:center;margin:28px 0;'>
              <a href='{$verify_link}' style='background:#16A34A;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;'>{$button_text}</a>
            </div>
            <p style='color:#8a9990;font-size:13px;'>Button not working? Paste this link into your browser:<br>
              <a href='{$verify_link}' style='color:#16A34A;word-break:break-all;'>{$verify_link}</a>
            </p>
            <p style='color:#65766E;font-size:12px;margin-top:24px;'>{$expiry_note}<br>{$footer_note}</p>
          </div>
        </div>";

        $mail->AltBody =
            "{$heading}\n\n{$intro}\n\n{$verify_link}\n\n{$expiry_note}\n{$footer_note}";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Verification email failed for {$to_email}: " . $mail->ErrorInfo);
        return false;
    }
}
?>

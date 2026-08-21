<?php
// SMTP smoke test for the mail service. Run from the repo root:
//   php tests/send-test-mail.php recipient@example.com
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../src/includes/mailservice.php';

$to = $argv[1] ?? '';
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php tests/send-test-mail.php recipient@example.com\n");
    exit(1);
}

$sentAt = date('Y-m-d H:i:s T');
$smtpTarget = SMTP_HOST . ':' . SMTP_PORT;
$html = <<<HTML
<h2>Chrontex mail service test</h2>
<p>This message was sent by <code>tests/send-test-mail.php</code> at {$sentAt}.</p>
<p>If you can read this, SMTP via <strong>{$smtpTarget}</strong> (STARTTLS) is working.</p>
HTML;

echo "Sending test email to {$to} via " . SMTP_HOST . ":" . SMTP_PORT . " as " . FROM_EMAIL . " ...\n\n";

$ok = sendSystemEmail($to, 'Test Recipient', 'Chrontex SMTP test — ' . $sentAt, $html, '', true);

echo $ok
    ? "\nOK: test email sent to {$to}. Check the inbox (and spam folder).\n"
    : "\nFAILED: see the SMTP debug output above for the reason.\n";
exit($ok ? 0 : 1);

<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailservice.php';
require_once __DIR__ . '/../includes/turnstile.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !turnstile_verify($_POST['cf-turnstile-response'] ?? null, 'resend', $_SERVER['REMOTE_ADDR'] ?? null)) {
    $message = 'Captcha verification failed. Please try again.';
    $message_type = 'danger';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare('SELECT user_id, full_name, status FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = 'No account found with that email.';
        $message_type = 'danger';
    } else {
        $user = $result->fetch_assoc();
        if ($user['status'] !== 'unverified') {
            $message = 'This account is already verified. You can log in.';
            $message_type = 'success';
        } else {
            $token = bin2hex(random_bytes(32));
            $update = $conn->prepare(
                "UPDATE users SET verification_token=?, verification_expires=DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE user_id=?"
            );
            $update->bind_param('si', $token, $user['user_id']);
            $update->execute();
            $update->close();

            if (send_verification_email($email, $user['full_name'], $token)) {
                $message = 'A new verification email has been sent. Check your inbox.';
                $message_type = 'success';
            } else {
                $message = "Couldn't send the email right now. Please try again shortly.";
                $message_type = 'danger';
            }
        }
    }
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 mt-3">
            <h2 class="mb-3">Resend Verification Email</h2>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" action="resend_verification.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <?php turnstile_field('resend'); ?>
                <button type="submit" class="btn btn-primary w-100">Send New Link</button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="<?php echo BASE_URL; ?>/login.php">Back to login</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

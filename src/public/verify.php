<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$token = $_GET['token'] ?? '';
$success = false;
$message = '';

if ($token === '') {
    $message = 'No verification token was provided.';
} else {
    $stmt = $conn->prepare(
        "SELECT user_id FROM users
         WHERE verification_token = ? AND verification_expires > NOW() AND status = 'unverified'"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $update = $conn->prepare(
            "UPDATE users SET status='active', verification_token=NULL, verification_expires=NULL WHERE user_id=?"
        );
        $update->bind_param('i', $user['user_id']);
        $update->execute();
        $update->close();
        $success = true;
        $message = 'Your email has been verified. You can now log in.';
    } else {
        $message = "This link is invalid, expired, or has already been used. "
                 . "If you've already verified, you can log in below — "
                 . "otherwise request a new link.";
    }
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 mt-3 text-center">
            <h2 class="mb-3">Email Verification</h2>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>

            <?php if ($success): ?>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-primary w-100">Go to Login</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-primary w-100 mb-2">Go to Login</a>
                <a href="<?php echo BASE_URL; ?>/resend_verification.php" class="btn btn-outline-secondary w-100">Request a New Link</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

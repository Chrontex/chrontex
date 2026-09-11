<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/turnstile.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!turnstile_verify($_POST['cf-turnstile-response'] ?? null, 'login', $_SERVER['REMOTE_ADDR'] ?? null)) {
        $error = 'Captcha verification failed. Please try again.';
    } else {
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare('SELECT user_id, full_name, password, role, status FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] === 'blocked') {
                $error = 'Your account has been blocked. Contact support.';
            } elseif ($user['status'] === 'unverified') {
                $error = 'Please verify your email before logging in. '
                       . "<a href='" . BASE_URL . "/resend_verification.php'>Resend verification email</a>";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                $destination = $user['role'] === 'admin' ? '/admin/index.php' : '/student/index.php';
                header('Location: ' . BASE_URL . $destination);
                exit;
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 mt-3">
            <h2 class="mb-3">Login</h2>

            <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                <div class="alert alert-success">Registration successful. Check your email to verify your account before logging in.</div>
            <?php elseif (isset($_GET['registered']) && $_GET['registered'] === 'mailfail'): ?>
                <div class="alert alert-warning">Account created, but the verification email couldn't be sent. Use "Resend verification email" below once you see the login error, or contact support.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/login.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <?php turnstile_field('login'); ?>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <p class="text-center mt-3 mb-1">
                Don't have an account? <a href="<?php echo BASE_URL; ?>/student/register.php">Register here</a>
            </p>
            <p class="text-center mb-0">
                <a href="<?php echo BASE_URL; ?>/resend_verification.php">Resend verification email</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

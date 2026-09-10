<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/mailservice.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $contact   = trim($_POST['contact_number']);

    $allowed_domains = ['nsbm.ac.lk', 'students.nsbm.ac.lk'];
    $email_lower = strtolower($email);
    $has_allowed_domain = false;
    foreach ($allowed_domains as $domain) {
        if (str_ends_with($email_lower, '@' . $domain)) {
            $has_allowed_domain = true;
            break;
        }
    }

    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } elseif (!$has_allowed_domain) {
        $errors[] = 'Only NSBM email addresses (@nsbm.ac.lk or @students.nsbm.ac.lk) are allowed.';
    }
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $token  = bin2hex(random_bytes(32));

        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password, contact_number, role, verification_token, verification_expires)
             VALUES (?, ?, ?, ?, 'student', ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))"
        );
        $stmt->bind_param('sssss', $full_name, $email, $hashed, $contact, $token);
        $stmt->execute();
        $stmt->close();

        $sent_ok = send_verification_email($email, $full_name, $token);

        header('Location: ' . BASE_URL . '/login.php?registered=' . ($sent_ok ? '1' : 'mailfail'));
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 mt-3">
            <h2 class="mb-3">Create a Student Account</h2>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <div class="form-text">Use your NSBM email — @nsbm.ac.lk or @students.nsbm.ac.lk.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="text-center mt-3 mb-0">
                Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

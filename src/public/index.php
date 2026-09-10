<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row text-center align-items-center" style="min-height: 60vh;">
    <div class="col-12">
        <h1 class="display-4">Welcome to Chrontex</h1>
        <p class="lead">Plan, manage, and join university events — all in one place.</p>
        <div class="mt-4">
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo BASE_URL; ?>/<?php echo $role === 'admin' ? 'admin' : 'student'; ?>/index.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-primary btn-lg me-2">Login</a>
                <a href="<?php echo BASE_URL; ?>/student/register.php" class="btn btn-outline-secondary btn-lg">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

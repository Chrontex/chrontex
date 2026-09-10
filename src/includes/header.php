<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;        // 'admin' | 'student'
$fullName   = $_SESSION['full_name'] ?? '';

$currentPath = $_SERVER['SCRIPT_NAME'];

function navLinkClass($path, $current) {
    return 'nav-link' . ($path === $current ? ' active' : '');
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chrontex - Event Planning</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg site-navbar mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/index.php">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="var(--brand-green, #16A34A)" stroke-width="2"/>
                    <path d="M12 7v5l3.5 2" stroke="var(--brand-green, #16A34A)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Chrontex
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                <?php if (!$isLoggedIn): ?>

                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/index.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/student/register.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/student/register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/login.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/login.php">Login</a>
                    </li>

                <?php elseif ($role === 'admin'): ?>

                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/admin/index.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/admin/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/admin/categories.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/admin/categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/admin/events.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/admin/events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/admin/announcements.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/admin/announcements.php">Announcements</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/admin/registrations.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/admin/registrations.php">Registrations</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <span class="navbar-text">Hi, <?php echo htmlspecialchars($fullName); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                    </li>

                <?php elseif ($role === 'student'): ?>

                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/student/index.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/student/index.php">Browse Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/student/schedule.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/student/schedule.php">My Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo navLinkClass(BASE_URL.'/student/announcements.php', $currentPath); ?>" href="<?php echo BASE_URL; ?>/student/announcements.php">Announcements</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <span class="navbar-text">Hi, <?php echo htmlspecialchars($fullName); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                    </li>

                <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>
    <div class="container flex-grow-1 pb-5">

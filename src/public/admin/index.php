<?php
require_once __DIR__ . '/../auth_check.php';
check_role('admin');

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-1">Admin Dashboard</h1>
        <p class="text-secondary mb-4">Manage categories, events, announcements and registrations.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 text-center">
            <h5>Categories</h5>
            <p class="text-secondary">Create and manage event categories.</p>
            <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn btn-primary mt-auto">Manage Categories</a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 text-center">
            <h5>Events</h5>
            <p class="text-secondary">Create, edit and delete events.</p>
            <a href="<?php echo BASE_URL; ?>/admin/events.php" class="btn btn-primary mt-auto">Manage Events</a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 text-center">
            <h5>Announcements</h5>
            <p class="text-secondary">Post general or event-specific announcements.</p>
            <a href="<?php echo BASE_URL; ?>/admin/announcements.php" class="btn btn-primary mt-auto">Manage Announcements</a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 text-center">
            <h5>Registrations</h5>
            <p class="text-secondary">View participant lists per event.</p>
            <a href="<?php echo BASE_URL; ?>/admin/registrations.php" class="btn btn-primary mt-auto">View Registrations</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

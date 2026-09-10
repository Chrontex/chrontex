<?php
require_once __DIR__ . '/../auth_check.php';
check_role('student');
require_once __DIR__ . '/../../includes/db.php';

$announcements = $conn->query(
    "SELECT a.announcement_id, a.title, a.message, a.created_at, e.title AS event_title
     FROM announcements a
     LEFT JOIN events e ON a.event_id = e.event_id
     WHERE a.is_active = 1
     ORDER BY a.created_at DESC"
);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Announcements</h1>
    </div>
</div>

<?php if ($announcements->num_rows === 0): ?>
    <div class="alert alert-secondary">There are no announcements right now.</div>
<?php endif; ?>

<div class="row g-4">
    <?php while ($a = $announcements->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0"><?php echo htmlspecialchars($a['title']); ?></h5>
                    <?php if ($a['event_title']): ?>
                        <span class="badge text-bg-primary"><?php echo htmlspecialchars($a['event_title']); ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-secondary mb-2"><?php echo nl2br(htmlspecialchars($a['message'])); ?></p>
                <p class="small text-secondary mb-0"><?php echo htmlspecialchars($a['created_at']); ?></p>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

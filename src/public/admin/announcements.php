<?php
require_once __DIR__ . '/../auth_check.php';
check_role('admin');
require_once __DIR__ . '/../../includes/db.php';

$errors = [];
$success = '';
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $announcement_id = (int) $_POST['announcement_id'];
        $stmt = $conn->prepare('UPDATE announcements SET is_active = NOT is_active WHERE announcement_id = ?');
        $stmt->bind_param('i', $announcement_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Announcement updated.';
    } elseif ($action === 'delete') {
        $announcement_id = (int) $_POST['announcement_id'];
        $stmt = $conn->prepare('DELETE FROM announcements WHERE announcement_id = ?');
        $stmt->bind_param('i', $announcement_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Announcement deleted.';
    } else {
        $title           = trim($_POST['title']);
        $message         = trim($_POST['message']);
        $event_id_posted = (int) $_POST['event_id'];
        $announcement_id = (int) ($_POST['announcement_id'] ?? 0);

        if ($title === '') $errors[] = 'Title is required.';
        if ($message === '') $errors[] = 'Message is required.';

        if (empty($errors)) {
            $posted_by = (int) $_SESSION['user_id'];
            $event_id  = $event_id_posted > 0 ? $event_id_posted : null;

            if ($action === 'edit' && $announcement_id > 0) {
                $stmt = $conn->prepare('UPDATE announcements SET title = ?, message = ?, event_id = ? WHERE announcement_id = ?');
                $stmt->bind_param('ssii', $title, $message, $event_id, $announcement_id);
                $stmt->execute();
                $stmt->close();
                $success = 'Announcement updated.';
            } else {
                $stmt = $conn->prepare('INSERT INTO announcements (title, message, event_id, posted_by) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssii', $title, $message, $event_id, $posted_by);
                $stmt->execute();
                $stmt->close();
                $success = 'Announcement posted.';
            }
        } else {
            $editing = [
                'announcement_id' => $announcement_id, 'title' => $title,
                'message' => $message, 'event_id' => $event_id_posted,
            ];
        }
    }
}

if ($editing === null && isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT announcement_id, title, message, event_id FROM announcements WHERE announcement_id = ?');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editing = $result->fetch_assoc();
    }
    $stmt->close();
}

$events = $conn->query('SELECT event_id, title FROM events ORDER BY event_date DESC');

$announcements = $conn->query(
    'SELECT a.announcement_id, a.title, a.message, a.is_active, a.created_at, e.title AS event_title
     FROM announcements a
     LEFT JOIN events e ON a.event_id = e.event_id
     ORDER BY a.created_at DESC'
);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Manage Announcements</h1>
    </div>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="mb-3"><?php echo $editing ? 'Edit Announcement' : 'New Announcement'; ?></h5>
            <form method="POST" action="announcements.php">
                <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'add'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="announcement_id" value="<?php echo (int) $editing['announcement_id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" required><?php echo htmlspecialchars($editing['message'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Related Event</label>
                    <select name="event_id" class="form-select">
                        <option value="0">-- General announcement --</option>
                        <?php
                        $events->data_seek(0);
                        while ($ev = $events->fetch_assoc()):
                            $selected = (isset($editing['event_id']) && (int) $editing['event_id'] === (int) $ev['event_id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo (int) $ev['event_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($ev['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100"><?php echo $editing ? 'Update Announcement' : 'Post Announcement'; ?></button>
                <?php if ($editing): ?>
                    <a href="announcements.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-3">All Announcements</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($announcements->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-secondary">No announcements yet.</td></tr>
                        <?php endif; ?>
                        <?php while ($a = $announcements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['title']); ?></td>
                                <td><?php echo $a['event_title'] ? htmlspecialchars($a['event_title']) : '<span class="text-secondary">General</span>'; ?></td>
                                <td>
                                    <?php if ($a['is_active']): ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="announcements.php?edit=<?php echo (int) $a['announcement_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="announcements.php" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="announcement_id" value="<?php echo (int) $a['announcement_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <?php echo $a['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="announcements.php" class="d-inline"
                                          onsubmit="return confirm('Delete this announcement? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="announcement_id" value="<?php echo (int) $a['announcement_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

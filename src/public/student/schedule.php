<?php
require_once __DIR__ . '/../auth_check.php';
check_role('student');
require_once __DIR__ . '/../../includes/db.php';

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $event_id = (int) $_POST['event_id'];

    $stmt = $conn->prepare("UPDATE registrations SET status = 'cancelled' WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $event_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: ' . BASE_URL . '/student/schedule.php?msg=cancelled');
    exit;
}

$msg = $_GET['msg'] ?? '';

$stmt = $conn->prepare(
    "SELECT e.event_id, e.title, e.event_date, e.start_time, e.venue,
            c.category_name, r.registered_at
     FROM registrations r
     JOIN events e     ON r.event_id = e.event_id
     JOIN categories c ON e.category_id = c.category_id
     WHERE r.user_id = ? AND r.status = 'registered'
     ORDER BY e.event_date"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$upcoming = [];
$past = [];
$today = date('Y-m-d');
while ($row = $result->fetch_assoc()) {
    if ($row['event_date'] >= $today) {
        $upcoming[] = $row;
    } else {
        $past[] = $row;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">My Schedule</h1>
    </div>
</div>

<?php if ($msg === 'cancelled'): ?>
    <div class="alert alert-success">Registration cancelled.</div>
<?php endif; ?>

<h4 class="mb-3">Upcoming</h4>
<div class="table-responsive mb-5">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Event</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($upcoming)): ?>
                <tr><td colspan="5" class="text-center text-secondary">No upcoming events. <a href="index.php">Browse events</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($upcoming as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_date']); ?> <?php echo substr($row['start_time'], 0, 5); ?></td>
                    <td><?php echo htmlspecialchars($row['venue']); ?></td>
                    <td class="text-end">
                        <form method="POST" action="schedule.php" class="d-inline"
                              onsubmit="return confirm('Cancel your registration for this event?');">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="event_id" value="<?php echo (int) $row['event_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h4 class="mb-3">Past</h4>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Event</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($past)): ?>
                <tr><td colspan="4" class="text-center text-secondary">No past events yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($past as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_date']); ?> <?php echo substr($row['start_time'], 0, 5); ?></td>
                    <td><?php echo htmlspecialchars($row['venue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

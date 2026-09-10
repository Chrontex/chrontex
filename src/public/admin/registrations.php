<?php
require_once __DIR__ . '/../auth_check.php';
check_role('admin');
require_once __DIR__ . '/../../includes/db.php';

$events = $conn->query('SELECT event_id, title, event_date FROM events ORDER BY event_date DESC');

$selected_event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$event = null;
$capacity_info = null;
$participants = null;

if ($selected_event_id > 0) {
    $stmt = $conn->prepare('SELECT event_id, title, event_date, capacity FROM events WHERE event_id = ?');
    $stmt->bind_param('i', $selected_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();

    if ($event) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS taken FROM registrations WHERE event_id = ? AND status = 'registered'");
        $stmt->bind_param('i', $selected_event_id);
        $stmt->execute();
        $capacity_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare(
            "SELECT u.full_name, u.email, u.contact_number, r.registered_at
             FROM registrations r
             JOIN users u ON r.user_id = u.user_id
             WHERE r.event_id = ? AND r.status = 'registered'
             ORDER BY r.registered_at"
        );
        $stmt->bind_param('i', $selected_event_id);
        $stmt->execute();
        $participants = $stmt->get_result();
        $stmt->close();
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 no-print">
        <h1 class="mb-0">Registrations</h1>
        <?php if ($event): ?>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">Print Participant List</button>
        <?php endif; ?>
    </div>
</div>

<div class="card p-4 mb-4 no-print">
    <form method="GET" action="registrations.php" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label">Select Event</label>
            <select name="event_id" class="form-select" onchange="this.form.submit()">
                <option value="0">-- Choose an event --</option>
                <?php while ($ev = $events->fetch_assoc()): ?>
                    <option value="<?php echo (int) $ev['event_id']; ?>" <?php echo $selected_event_id === (int) $ev['event_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ev['title']) . ' (' . htmlspecialchars($ev['event_date']) . ')'; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">View</button>
        </div>
    </form>
</div>

<?php if ($selected_event_id > 0 && !$event): ?>
    <div class="alert alert-danger">Event not found.</div>
<?php elseif ($event): ?>
    <div class="card p-4">
        <h4 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h4>
        <p class="text-secondary">
            <?php echo htmlspecialchars($event['event_date']); ?> &middot;
            <?php echo (int) $capacity_info['taken']; ?> / <?php echo (int) $event['capacity']; ?> registered
        </p>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants->num_rows === 0): ?>
                        <tr><td colspan="4" class="text-center text-secondary">No one has registered for this event yet.</td></tr>
                    <?php endif; ?>
                    <?php while ($p = $participants->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                            <td><?php echo htmlspecialchars($p['contact_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['registered_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

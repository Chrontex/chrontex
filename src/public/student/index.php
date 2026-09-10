<?php
require_once __DIR__ . '/../auth_check.php';
check_role('student');
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $event_id = (int) $_POST['event_id'];
    $user_id  = (int) $_SESSION['user_id'];
    $msg      = 'registered';

    $stmt = $conn->prepare("SELECT registration_id FROM registrations WHERE event_id = ? AND user_id = ? AND status = 'registered'");
    $stmt->bind_param('ii', $event_id, $user_id);
    $stmt->execute();
    $already = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($already) {
        $msg = 'already';
    } else {
        $stmt = $conn->prepare(
            "SELECT e.event_date, e.capacity,
                    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.status = 'registered') AS taken
             FROM events e WHERE e.event_id = ?"
        );
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            $msg = 'error';
        } elseif ($event['event_date'] < date('Y-m-d')) {
            $msg = 'past';
        } elseif ((int) $event['taken'] >= (int) $event['capacity']) {
            $msg = 'full';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO registrations (event_id, user_id) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE status = 'registered', registered_at = CURRENT_TIMESTAMP"
            );
            $stmt->bind_param('ii', $event_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $msg = 'registered';
        }
    }

    $category_carry = (int) ($_POST['category'] ?? 0);
    $q_carry         = $_POST['q'] ?? '';
    header('Location: ' . BASE_URL . '/student/index.php?category=' . $category_carry . '&q=' . urlencode($q_carry) . '&msg=' . $msg);
    exit;
}

$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$q           = isset($_GET['q']) ? trim($_GET['q']) : '';
$msg         = $_GET['msg'] ?? '';

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name');

$stmt = $conn->prepare(
    "SELECT e.event_id, e.title, e.description, e.event_date, e.start_time,
            e.venue, e.capacity, c.category_name,
            (SELECT COUNT(*) FROM registrations r
             WHERE r.event_id = e.event_id AND r.status = 'registered') AS taken
     FROM events e
     JOIN categories c ON e.category_id = c.category_id
     WHERE e.event_date >= CURDATE() AND e.status = 'published'
       AND (? = 0 OR e.category_id = ?)
       AND (e.title LIKE CONCAT('%', ?, '%') OR e.description LIKE CONCAT('%', ?, '%'))
     ORDER BY e.event_date, e.start_time"
);
$stmt->bind_param('iiss', $category_id, $category_id, $q, $q);
$stmt->execute();
$events = $stmt->get_result();
$stmt->close();

$registered_ids = [];
$stmt = $conn->prepare("SELECT event_id FROM registrations WHERE user_id = ? AND status = 'registered'");
$user_id = (int) $_SESSION['user_id'];
$stmt->bind_param('i', $user_id);
$stmt->execute();
$reg_result = $stmt->get_result();
while ($row = $reg_result->fetch_assoc()) {
    $registered_ids[] = (int) $row['event_id'];
}
$stmt->close();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Browse Upcoming Events</h1>
    </div>
</div>

<?php if ($msg === 'registered'): ?>
    <div class="alert alert-success">You're registered for this event.</div>
<?php elseif ($msg === 'already'): ?>
    <div class="alert alert-info">You're already registered for this event.</div>
<?php elseif ($msg === 'full'): ?>
    <div class="alert alert-warning">Sorry, this event is full.</div>
<?php elseif ($msg === 'past'): ?>
    <div class="alert alert-warning">This event has already taken place.</div>
<?php elseif ($msg === 'error'): ?>
    <div class="alert alert-danger">That event could not be found.</div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <form method="GET" action="index.php" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="0">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo (int) $cat['category_id']; ?>" <?php echo $category_id === (int) $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-control" placeholder="Search by title or description"
                   value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<?php if ($events->num_rows === 0): ?>
    <div class="alert alert-secondary">No upcoming events match your search.</div>
<?php endif; ?>

<div class="row g-4">
    <?php while ($ev = $events->fetch_assoc()): ?>
        <?php
        $spots_left     = (int) $ev['capacity'] - (int) $ev['taken'];
        $is_full        = $spots_left <= 0;
        $is_registered  = in_array((int) $ev['event_id'], $registered_ids, true);
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4">
                <span class="badge text-bg-primary mb-2 align-self-start"><?php echo htmlspecialchars($ev['category_name']); ?></span>
                <h5><?php echo htmlspecialchars($ev['title']); ?></h5>
                <p class="text-secondary small mb-2"><?php echo htmlspecialchars($ev['description']); ?></p>
                <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($ev['event_date']); ?> at <?php echo substr($ev['start_time'], 0, 5); ?></p>
                <p class="mb-3"><strong>Venue:</strong> <?php echo htmlspecialchars($ev['venue']); ?></p>
                <p class="mb-3">
                    <?php if ($is_full): ?>
                        <span class="badge text-bg-danger">Event full</span>
                    <?php else: ?>
                        <span class="badge text-bg-success"><?php echo $spots_left; ?> spot<?php echo $spots_left === 1 ? '' : 's'; ?> remaining</span>
                    <?php endif; ?>
                </p>

                <form method="POST" action="index.php" class="mt-auto">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="event_id" value="<?php echo (int) $ev['event_id']; ?>">
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                    <?php if ($is_registered): ?>
                        <button type="button" class="btn btn-secondary w-100" disabled>Already Registered</button>
                    <?php elseif ($is_full): ?>
                        <button type="button" class="btn btn-secondary w-100" disabled>Event Full</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

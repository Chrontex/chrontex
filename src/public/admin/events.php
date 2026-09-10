<?php
require_once __DIR__ . '/../auth_check.php';
check_role('admin');
require_once __DIR__ . '/../../includes/db.php';

$errors = [];
$success = '';
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $event_id = (int) $_POST['event_id'];
        $stmt = $conn->prepare('DELETE FROM events WHERE event_id = ?');
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Event deleted.';
    } else {
        $title       = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category_id = (int) $_POST['category_id'];
        $event_date  = trim($_POST['event_date']);
        $start_time  = trim($_POST['start_time']);
        $end_time    = trim($_POST['end_time']);
        $venue       = trim($_POST['venue']);
        $capacity    = trim($_POST['capacity']);
        $event_id    = (int) ($_POST['event_id'] ?? 0);

        if ($title === '') $errors[] = 'Title is required.';
        if ($category_id <= 0) $errors[] = 'Please choose a category.';
        if ($venue === '') $errors[] = 'Venue is required.';

        $today = date('Y-m-d');
        if ($event_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
            $errors[] = 'A valid event date is required.';
        } elseif ($event_date < $today) {
            $errors[] = 'Event date cannot be in the past.';
        }

        if ($start_time === '') $errors[] = 'Start time is required.';
        if ($end_time !== '' && $start_time !== '' && $end_time <= $start_time) {
            $errors[] = 'End time must be after start time.';
        }

        if (!ctype_digit($capacity) || (int) $capacity <= 0) {
            $errors[] = 'Capacity must be a positive whole number.';
        } else {
            $capacity = (int) $capacity;
        }

        if (empty($errors)) {
            $end_time_value = $end_time !== '' ? $end_time : null;

            if ($action === 'edit' && $event_id > 0) {
                $stmt = $conn->prepare(
                    'UPDATE events SET title = ?, description = ?, category_id = ?, event_date = ?,
                     start_time = ?, end_time = ?, venue = ?, capacity = ?
                     WHERE event_id = ?'
                );
                $stmt->bind_param('ssisssii', $title, $description, $category_id, $event_date, $start_time, $end_time_value, $venue, $capacity, $event_id);
                $stmt->execute();
                $stmt->close();
                $success = 'Event updated.';
            } else {
                $created_by = (int) $_SESSION['user_id'];
                $stmt = $conn->prepare(
                    'INSERT INTO events (title, description, category_id, event_date, start_time, end_time, venue, capacity, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('ssisssiii', $title, $description, $category_id, $event_date, $start_time, $end_time_value, $venue, $capacity, $created_by);
                $stmt->execute();
                $stmt->close();
                $success = 'Event added.';
            }
        } else {
            $editing = [
                'event_id' => $event_id, 'title' => $title, 'description' => $description,
                'category_id' => $category_id, 'event_date' => $event_date, 'start_time' => $start_time,
                'end_time' => $end_time, 'venue' => $venue, 'capacity' => $capacity,
            ];
        }
    }
}

if ($editing === null && isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare(
        'SELECT event_id, title, description, category_id, event_date, start_time, end_time, venue, capacity
         FROM events WHERE event_id = ?'
    );
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editing = $result->fetch_assoc();
    }
    $stmt->close();
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name');

$events = $conn->query(
    'SELECT e.event_id, e.title, e.event_date, e.start_time, e.venue, e.capacity, e.status, c.category_name
     FROM events e
     JOIN categories c ON e.category_id = c.category_id
     ORDER BY e.event_date DESC'
);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Manage Events</h1>
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
            <h5 class="mb-3"><?php echo $editing ? 'Edit Event' : 'Add Event'; ?></h5>
            <form method="POST" action="events.php">
                <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'add'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="event_id" value="<?php echo (int) $editing['event_id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select a category --</option>
                        <?php
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()):
                            $selected = (isset($editing['category_id']) && (int) $editing['category_id'] === (int) $cat['category_id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo (int) $cat['category_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-control" min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo htmlspecialchars($editing['event_date'] ?? ''); ?>" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control"
                               value="<?php echo htmlspecialchars($editing['start_time'] ?? ''); ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control"
                               value="<?php echo htmlspecialchars($editing['end_time'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Venue</label>
                    <input type="text" name="venue" class="form-control"
                           value="<?php echo htmlspecialchars($editing['venue'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1"
                           value="<?php echo htmlspecialchars((string) ($editing['capacity'] ?? 50)); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary w-100"><?php echo $editing ? 'Update Event' : 'Add Event'; ?></button>
                <?php if ($editing): ?>
                    <a href="events.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-3">All Events</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center text-secondary">No events yet.</td></tr>
                        <?php endif; ?>
                        <?php while ($ev = $events->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ev['title']); ?></td>
                                <td><?php echo htmlspecialchars($ev['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($ev['event_date']); ?><br>
                                    <small class="text-secondary"><?php echo substr($ev['start_time'], 0, 5); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($ev['venue']); ?></td>
                                <td><?php echo (int) $ev['capacity']; ?></td>
                                <td class="text-end">
                                    <a href="events.php?edit=<?php echo (int) $ev['event_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="events.php" class="d-inline"
                                          onsubmit="return confirm('Delete this event? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id" value="<?php echo (int) $ev['event_id']; ?>">
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

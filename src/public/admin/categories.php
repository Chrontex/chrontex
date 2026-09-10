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
        $category_id = (int) $_POST['category_id'];
        try {
            $stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?');
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $stmt->close();
            $success = 'Category deleted.';
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                $errors[] = 'This category is in use by existing events.';
            } else {
                $errors[] = 'Could not delete this category.';
            }
        }
    } else {
        $category_name = trim($_POST['category_name']);
        $description   = trim($_POST['description']);
        $category_id   = (int) ($_POST['category_id'] ?? 0);

        if ($category_name === '') {
            $errors[] = 'Category name is required.';
        }

        if (empty($errors)) {
            try {
                if ($action === 'edit' && $category_id > 0) {
                    $stmt = $conn->prepare('UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?');
                    $stmt->bind_param('ssi', $category_name, $description, $category_id);
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Category updated.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO categories (category_name, description) VALUES (?, ?)');
                    $stmt->bind_param('ss', $category_name, $description);
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Category added.';
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $errors[] = 'A category with that name already exists.';
                } else {
                    $errors[] = 'Could not save this category.';
                }
                if ($action === 'edit') {
                    $editing = ['category_id' => $category_id, 'category_name' => $category_name, 'description' => $description];
                }
            }
        } else if ($action === 'edit') {
            $editing = ['category_id' => $category_id, 'category_name' => $category_name, 'description' => $description];
        }
    }
}

if ($editing === null && isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT category_id, category_name, description FROM categories WHERE category_id = ?');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editing = $result->fetch_assoc();
    }
    $stmt->close();
}

$categories = $conn->query('SELECT category_id, category_name, description, created_at FROM categories ORDER BY category_name');

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Manage Categories</h1>
    </div>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="mb-3"><?php echo $editing ? 'Edit Category' : 'Add Category'; ?></h5>
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'add'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="category_id" value="<?php echo (int) $editing['category_id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control"
                           value="<?php echo htmlspecialchars($editing['category_name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100"><?php echo $editing ? 'Update Category' : 'Add Category'; ?></button>
                <?php if ($editing): ?>
                    <a href="categories.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="mb-3">All Categories</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categories->num_rows === 0): ?>
                            <tr><td colspan="3" class="text-center text-secondary">No categories yet.</td></tr>
                        <?php endif; ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                <td class="text-end">
                                    <a href="categories.php?edit=<?php echo (int) $cat['category_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="categories.php" class="d-inline"
                                          onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo (int) $cat['category_id']; ?>">
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

<?php

require_once "../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: manage_categories.php");
    exit;
}


/* GET CATEGORY */

$stmt = $pdo->prepare("
    SELECT *
    FROM categories
    WHERE category_id = ?
");

$stmt->execute([$id]);

$category = $stmt->fetch();

if (!$category) {

    die("Category not found.");

}


/* UPDATE */

if (isset($_POST["update_category"])) {

    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    if ($category_name == "") {

        $error = "Category name is required.";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE categories
                SET category_name = ?,
                    description = ?
                WHERE category_id = ?
            ");

            $stmt->execute([
                $category_name,
                $description,
                $id
            ]);

            header("Location: manage_categories.php");
            exit;

        } catch (PDOException $e) {

            if ($e->errorInfo[1] == 1062) {

                $error = "This category name already exists.";

            } else {

                $error = "Unable to update category.";

            }

        }

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - EventHub</title>
    <style>
        :root {
            --ink: #222;
            --muted: #667085;
            --canvas: #f4f6f9;
            --line: #d9dee7;
            --blue: #007bff;
            --blue-dark: #0056b3;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: var(--canvas);
            color: var(--ink);
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 18px 5%;
            background: var(--ink);
            color: white;
        }

        .navbar strong {
            font-size: 1.1rem;
        }

        .navbar div {
            display: flex;
            gap: 20px;
        }

        .navbar a {
            color: #f8fafc;
            text-decoration: none;
            font-size: .95rem;
        }

        .navbar a:hover,
        .back-link:hover {
            color: #8ec5ff;
        }

        .container {
            width: 90%;
            max-width: 700px;
            margin: 34px auto 56px;
        }

        .page-heading {
            margin-bottom: 22px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: clamp(1.8rem, 4vw, 2.35rem);
        }

        .page-heading p {
            margin: 0;
            color: var(--muted);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: var(--blue-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .form-panel {
            background: white;
            padding: clamp(22px, 4vw, 34px);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 20px;
        }

        label {
            font-size: .9rem;
            font-weight: 700;
        }

        input,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 5px;
            background: #fff;
            color: var(--ink);
            font: inherit;
            transition: border-color .15s, box-shadow .15s;
        }

        textarea {
            resize: vertical;
            min-height: 130px;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, .15);
        }

        .alert {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
            background: #f8d7da;
            color: #721c24;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #edf0f4;
        }

        button {
            border: 0;
            border-radius: 5px;
            padding: 11px 18px;
            background: var(--blue);
            color: white;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: var(--blue-dark);
        }

        .cancel-link {
            color: var(--muted);
            text-decoration: none;
        }

        .cancel-link:hover {
            color: var(--ink);
        }

        @media (max-width: 620px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .navbar div {
                flex-wrap: wrap;
                gap: 12px 18px;
            }

            .container {
                width: calc(100% - 32px);
                margin-top: 26px;
            }
        }
    </style>

</head>

<body>

<nav class="navbar">
    <strong>EventHub Admin</strong>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_categories.php">Categories</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-heading">
        <h1>Edit Category</h1>
        <p>Refine the name and description used to organize your events.</p>
    </div>

    <a class="back-link" href="manage_categories.php">&larr; Back to Categories</a>

    <div class="form-panel">
        <?php if (isset($error)): ?>
            <div class="alert" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label for="category_name">Category Name</label>
                <input
                    type="text"
                    id="category_name"
                    name="category_name"
                    value="<?= htmlspecialchars($category["category_name"]) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($category["description"]) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_category">Update Category</button>
                <a class="cancel-link" href="manage_categories.php">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>

</html>

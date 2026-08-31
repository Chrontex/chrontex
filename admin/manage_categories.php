<?php

require_once "../config/database.php";

$message = "";
$error = "";

/* ADD CATEGORY */

if (isset($_POST["add_category"])) {

    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    if ($category_name == "") {

        $error = "Category name is required.";

    } else {

        try {

            $sql = "
                INSERT INTO categories
                (category_name, description)
                VALUES (?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $category_name,
                $description
            ]);

            $message = "Category added successfully.";

        } catch (PDOException $e) {

            if ($e->errorInfo[1] == 1062) {

                $error = "This category already exists.";

            } else {

                $error = "Unable to add category.";

            }

        }

    }
}


/* GET CATEGORIES */

$stmt = $pdo->query("
    SELECT
        category_id,
        category_name,
        description
    FROM categories
    ORDER BY category_name
");

$categories = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Categories</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: white;
        }

        .success {
            background: #d4edda;
            padding: 10px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            padding: 10px;
            margin-bottom: 15px;
        }

        a {
            margin-right: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Manage Categories</h1>


    <p>
        <a href="dashboard.php">← Back to Dashboard</a>
    </p>

    <?php if ($message): ?>

        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- ADD CATEGORY -->

    <div class="box">

        <h2>Add Category</h2>

        <form method="POST">

            <label>Category Name</label>

            <input
                type="text"
                name="category_name"
                required
            >

            <label>Description</label>

            <textarea
                name="description"
                rows="4"
            ></textarea>

            <button
                type="submit"
                name="add_category"
            >
                Add Category
            </button>

        </form>

    </div>


    <!-- CATEGORY LIST -->

    <div class="box">

        <h2>Existing Categories</h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Actions</th>

            </tr>

            <?php if (count($categories) > 0): ?>

                <?php foreach ($categories as $category): ?>

                    <tr>

                        <td>
                            <?= $category["category_id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category["category_name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category["description"]) ?>
                        </td>

                        <td>

                            <a href="edit_category.php?id=<?= $category["category_id"] ?>">
                                Edit
                            </a>

                            <a
                                href="delete_category.php?id=<?= $category["category_id"] ?>"
                                onclick="return confirm('Are you sure you want to delete this category?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="4">
                        No categories found.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>

</html>
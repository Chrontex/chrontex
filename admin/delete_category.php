<?php

require_once "../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {

    header("Location: manage_categories.php");
    exit;

}

try {

    $stmt = $pdo->prepare("
        DELETE FROM categories
        WHERE category_id = ?
    ");

    $stmt->execute([$id]);

    header("Location: manage_categories.php");
    exit;

} catch (PDOException $e) {

    if ($e->getCode() == "23000") {

        die("
            <h2>Cannot Delete Category</h2>
            <p>This category is in use by existing events.</p>
            <a href='manage_categories.php'>Back to Categories</a>
        ");

    }

    die("Unable to delete category.");

}

?>
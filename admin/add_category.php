<?php

require_once "../config/database.php";

$category_name = "";
$description = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$category_name = trim($_POST["category_name"] ?? "");
	$description = trim($_POST["description"] ?? "");

	if ($category_name === "") {

		$error = "Category name is required.";

	} else {

		try {

			$stmt = $pdo->prepare("
				INSERT INTO categories (category_name, description)
				VALUES (?, ?)
			");

			$stmt->execute([$category_name, $description]);

			header("Location: manage_categories.php");
			exit;

		} catch (PDOException $e) {

			if ($e->errorInfo[1] == 1062) {

				$error = "This category already exists.";

			} else {

				$error = "Unable to add category.";

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
	<title>Add Category</title>

	<style>

		body {
			font-family: Arial, sans-serif;
			background: #f4f6f9;
			margin: 0;
		}

		.container {
			width: 90%;
			max-width: 700px;
			margin: 30px auto;
		}

		.box {
			background: white;
			padding: 25px;
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

		.error {
			background: #f8d7da;
			padding: 10px;
			margin-bottom: 15px;
		}

	</style>

</head>

<body>

<div class="container">

	<h1>Add Category</h1>

	<p>
		<a href="manage_categories.php">← Back to Categories</a>
	</p>

	<div class="box">

		<?php if ($error): ?>
			<div class="error"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<form method="POST">

			<label for="category_name">Category Name</label>
			<input
				id="category_name"
				type="text"
				name="category_name"
				value="<?= htmlspecialchars($category_name) ?>"
				required
				autofocus
			>

			<label for="description">Description</label>
			<textarea
				id="description"
				name="description"
				rows="5"
			><?= htmlspecialchars($description) ?></textarea>

			<button type="submit">Add Category</button>

		</form>

	</div>

</div>

</body>

</html>

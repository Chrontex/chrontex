<?php

session_start();

if (!isset($_SESSION["user_id"])) {
	$_SESSION["user_id"] = 1;
}

require_once "../config/database.php";

$created_by = filter_var(
	$_SESSION["user_id"] ?? null,
	FILTER_VALIDATE_INT
);

if (!$created_by || $created_by < 1) {

	header("Location: ../login.php");
	exit;

}

$event_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$event_id || $event_id < 1) {

	header("Location: manage_events.php");
	exit;

}

$categories = $pdo->query("
	SELECT category_id, category_name
	FROM categories
	ORDER BY category_name
")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {

	die("Event not found.");

}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$title = trim($_POST["title"] ?? "");
	$description = trim($_POST["description"] ?? "");
	$category_id = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);
	$event_date = $_POST["event_date"] ?? "";
	$start_time = $_POST["start_time"] ?? "";
	$end_time = $_POST["end_time"] ?? "";
	$venue = trim($_POST["venue"] ?? "");
	$capacity = filter_input(INPUT_POST, "capacity", FILTER_VALIDATE_INT);
	$status = $_POST["status"] ?? "";

	if ($title === "") {
		$error = "Event title is required.";
	} elseif (!$category_id) {
		$error = "Please select a category.";
	} elseif ($event_date === "") {
		$error = "Event date is required.";
	} elseif ($event_date < date("Y-m-d") && $event_date !== $event["event_date"]) {
		$error = "Event date cannot be moved into the past.";
	} elseif ($start_time === "" || $end_time === "") {
		$error = "Start and end times are required.";
	} elseif ($end_time <= $start_time) {
		$error = "End time must be after start time.";
	} elseif ($venue === "") {
		$error = "Venue is required.";
	} elseif ($capacity === false || $capacity < 1) {
		$error = "Capacity must be at least 1.";
	} elseif (!in_array($status, ["published", "cancelled"], true)) {
		$error = "Invalid event status.";
	}

	if ($error === "") {

		try {

			$stmt = $pdo->prepare("
				UPDATE events
				SET title = ?,
					description = ?,
					category_id = ?,
					event_date = ?,
					start_time = ?,
					end_time = ?,
					venue = ?,
					capacity = ?,
					status = ?,
					created_by = ?
				WHERE event_id = ?
			");

			$stmt->execute([
				$title,
				$description,
				$category_id,
				$event_date,
				$start_time,
				$end_time,
				$venue,
				$capacity,
				$status,
				$created_by,
				$event_id
			]);

			header("Location: manage_events.php?success=updated");
			exit;

		} catch (PDOException $e) {

			$error = "Unable to update event. Please try again.";

		}

	}

	$event = array_merge($event, [
		"title" => $title,
		"description" => $description,
		"category_id" => $category_id,
		"event_date" => $event_date,
		"start_time" => $start_time,
		"end_time" => $end_time,
		"venue" => $venue,
		"capacity" => $capacity,
		"status" => $status
	]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Event - EventHub</title>
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
			max-width: 900px;
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

		.form-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 20px;
		}

		.field {
			display: flex;
			flex-direction: column;
			gap: 7px;
		}

		.field.full-width {
			grid-column: 1 / -1;
		}

		label {
			font-size: .9rem;
			font-weight: 700;
		}

		input,
		select,
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
		select:focus,
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

			.form-grid {
				grid-template-columns: 1fr;
				gap: 17px;
			}

			.field.full-width {
				grid-column: auto;
			}
		}
	</style>
</head>

<body>

<nav class="navbar">
	<strong>EventHub Admin</strong>
	<div>
		<a href="dashboard.php">Dashboard</a>
		<a href="manage_events.php">Events</a>
		<a href="../logout.php">Logout</a>
	</div>
</nav>

<div class="container">

	<div class="page-heading">
		<h1>Edit Event</h1>
		<p>Update the details and availability for this event.</p>
	</div>

	<a class="back-link" href="manage_events.php">&larr; Back to Events</a>

	<div class="form-panel">
		<?php if ($error !== ""): ?>
			<div class="alert" role="alert"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<form method="POST">
			<div class="form-grid">

				<div class="field full-width">
					<label for="title">Event Title</label>
					<input type="text" id="title" name="title" maxlength="150" value="<?= htmlspecialchars($event["title"]) ?>" required>
				</div>

				<div class="field full-width">
					<label for="description">Description</label>
					<textarea id="description" name="description" rows="6"><?= htmlspecialchars($event["description"] ?? "") ?></textarea>
				</div>

				<div class="field">
					<label for="category_id">Category</label>
					<select id="category_id" name="category_id" required>
						<option value="">-- Select Category --</option>
						<?php foreach ($categories as $category): ?>
							<option value="<?= $category["category_id"] ?>" <?= ((int)$event["category_id"] === (int)$category["category_id"]) ? "selected" : "" ?>>
								<?= htmlspecialchars($category["category_name"]) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="field">
					<label for="event_date">Event Date</label>
					<input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event["event_date"]) ?>" required>
				</div>

				<div class="field">
					<label for="start_time">Start Time</label>
					<input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars($event["start_time"]) ?>" required>
				</div>

				<div class="field">
					<label for="end_time">End Time</label>
					<input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars($event["end_time"]) ?>" required>
				</div>

				<div class="field">
					<label for="venue">Venue</label>
					<input type="text" id="venue" name="venue" maxlength="120" value="<?= htmlspecialchars($event["venue"]) ?>" required>
				</div>

				<div class="field">
					<label for="capacity">Capacity</label>
					<input type="number" id="capacity" name="capacity" min="1" value="<?= htmlspecialchars($event["capacity"]) ?>" required>
				</div>

				<div class="field">
					<label for="status">Status</label>
					<select id="status" name="status">
						<option value="published" <?= $event["status"] === "published" ? "selected" : "" ?>>Published</option>
						<option value="cancelled" <?= $event["status"] === "cancelled" ? "selected" : "" ?>>Cancelled</option>
					</select>
				</div>
			</div>

			<div class="form-actions">
				<button type="submit">Save Changes</button>
				<a class="cancel-link" href="manage_events.php">Cancel</a>
			</div>

		</form>
	</div>

</div>

</body>

</html>

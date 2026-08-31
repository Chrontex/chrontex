<?php

require_once "../config/database.php";

$event_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$event_id || $event_id < 1) {

	header("Location: manage_events.php");
	exit;

}

try {

	$stmt = $pdo->prepare("
		DELETE FROM events
		WHERE event_id = ?
	");

	$stmt->execute([$event_id]);

	header("Location: manage_events.php");
	exit;

} catch (PDOException $e) {

	if ($e->getCode() === "23000") {

		die("
			<h2>Cannot Delete Event</h2>
			<p>This event cannot be deleted because it has related records.</p>
			<a href='manage_events.php'>Back to Events</a>
		");

	}

	die("Unable to delete event.");

}

?>

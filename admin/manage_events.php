<?php

require_once "../config/database.php";


/*
    GET ALL EVENTS
*/

$sql = "
    SELECT
        e.event_id,
        e.title,
        e.event_date,
        e.start_time,
        e.end_time,
        e.venue,
        e.capacity,
        e.status,
        c.category_name
    FROM events e
    JOIN categories c
        ON e.category_id = c.category_id
    ORDER BY e.event_date DESC

";

$stmt = $pdo->query($sql);

$events = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Events</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .button {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background: #0056b3;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
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

        .edit {
            color: blue;
            margin-right: 10px;
        }

        .delete {
            color: red;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Manage Events</h1>

        <a
            href="add_event.php"
            class="button"
        >
            + Add Event
        </a>

    </div>

    <p>
        <a href="dashboard.php">
            ← Back to Dashboard
        </a>
    </p>

    <div class="box">

        <table>

            <tr>

                <th>Title</th>

                <th>Category</th>

                <th>Date</th>

                <th>Start Time</th>

                <th>End Time</th>

                <th>Venue</th>

                <th>Capacity</th>

                <th>Status</th>

                <th>Actions</th>

            </tr>

            <?php if (count($events) > 0): ?>

                <?php foreach ($events as $event): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($event["title"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["category_name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["event_date"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["start_time"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["end_time"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["venue"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["capacity"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event["status"]) ?>
                        </td>

                        <td>

                            <a
                                class="edit"
                                href="edit_event.php?id=<?= $event["event_id"] ?>"
                            >
                                Edit
                            </a>

                            <a
                                class="delete"
                                href="delete_event.php?id=<?= $event["event_id"] ?>"
                                onclick="return confirm('Are you sure you want to delete this event?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="9">
                        No events found.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>

</html>
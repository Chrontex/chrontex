<?php

require_once "../config/database.php";
//require_once "../auth_check.php";
//check_role("admin");
$_SESSION['user_id'] = '1'; // Set the user role to admin for testing purposes
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f6f9;
        }

        .header {
            background: #222;
            color: white;
            padding: 20px;
        }

        .container {
            padding: 30px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-decoration: none;
            color: #222;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card:hover {
            transform: translateY(-3px);
        }

    </style>

</head>

<body>

    <div class="header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="container">

        <h2>Welcome, Administrator</h2>

        <div class="menu">

            <a class="card" href="manage_categories.php">
                <h3>Categories</h3>
                <p>Manage event categories.</p>
            </a>

            <a class="card" href="manage_events.php">
                <h3>Events</h3>
                <p>Create and manage events.</p>
            </a>

            <a class="card" href="announcement.php">
                <h3>Announcements</h3>
                <p>Manage announcements.</p>
            </a>

            <a class="card" href="manage_registrations.php">
                <h3>Registrations</h3>
                <p>View event registrations.</p>
            </a>

        </div>

    </div>

</body>

</html>
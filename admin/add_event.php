<?php

//require_once "../auth_check.php";
//check_role("admin");

require_once "../config/database.php";

$error = "";
$success = "";




$stmt = $pdo->query("
    SELECT category_id, category_name
    FROM categories
    ORDER BY category_name
");

$categories = $stmt->fetchAll();




if (isset($_POST["add_event"])) {
    $_SESSION['user_id'] = '1';
    // Get form data
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = $_POST["category_id"] ?? "";
    $event_date = $_POST["event_date"] ?? "";
    $start_time = $_POST["start_time"] ?? "";
    $end_time = $_POST["end_time"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $capacity = $_POST["capacity"] ?? "";
    $status = $_POST["status"] ?? "published";


    

    if ($title === "") {

        $error = "Event title is required.";

    } elseif ($category_id === "") {

        $error = "Please select a category.";

    } elseif ($event_date === "") {

        $error = "Event date is required.";

    } elseif ($start_time === "") {

        $error = "Start time is required.";

    } elseif ($end_time === "") {

        $error = "End time is required.";

    } elseif ($venue === "") {

        $error = "Venue is required.";

    } elseif ($capacity === "" || !is_numeric($capacity)) {

        $error = "Please enter a valid capacity.";

    } elseif ((int)$capacity < 1) {

        $error = "Capacity must be at least 1.";

    }


    

    if ($error === "") {

        $today = date("Y-m-d");

        if ($event_date < $today) {

            $error = "Event date cannot be in the past.";

        }
    }


    

    if ($error === "") {

        if ($end_time <= $start_time) {

            $error = "End time must be after start time.";

        }
    }


    

    if ($error === "") {

        if (
            $status !== "published"
            && $status !== "cancelled"
        ) {

            $error = "Invalid event status.";

        }
    }


    

    $image_path = null;

    if (
        $error === ""
        && isset($_FILES["image"])
        && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "gif",
                "webp"
            ];

            $file_name = $_FILES["image"]["name"];

            $file_extension = strtolower(
                pathinfo(
                    $file_name,
                    PATHINFO_EXTENSION
                )
            );

            if (!in_array(
                $file_extension,
                $allowed_extensions
            )) {

                $error =
                    "Invalid image type. "
                    . "Allowed: JPG, JPEG, PNG, GIF, WEBP.";

            } else {

                

                $max_size = 5 * 1024 * 1024; // 5 MB

                if (
                    $_FILES["image"]["size"]
                    > $max_size
                ) {

                    $error =
                        "Image must be smaller than 5 MB.";

                } else {

                    

                    $upload_directory =
                        "../uploads/events/";

                    if (!is_dir($upload_directory)) {

                        mkdir(
                            $upload_directory,
                            0777,
                            true
                        );
                    }


                    

                    $new_file_name =
                        uniqid("event_", true)
                        . "."
                        . $file_extension;

                    $destination =
                        $upload_directory
                        . $new_file_name;


                    

                    if (
                        move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $destination
                        )
                    ) {

                        $image_path =
                            "uploads/events/"
                            . $new_file_name;

                    } else {

                        $error =
                            "Unable to save uploaded image.";

                    }
                }
            }
        }
    }


    

    if ($error === "") {

        try {

            $sql = "
                INSERT INTO events
                (
                    title,
                    description,
                    category_id,
                    event_date,
                    start_time,
                    end_time,
                    venue,
                    capacity,
                    image_path,
                    status,
                    created_by
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $title,
                $description,
                $category_id,
                $event_date,
                $start_time,
                $end_time,
                $venue,
                (int)$capacity,
                $image_path,
                $status,
                $_SESSION["user_id"]
                
            ]);


            

            header(
                "Location: manage_events.php?success=created"
            );

            exit;

        } catch (PDOException $e) {

            $error =
                "Unable to create event. "
                . "Please try again.". $e->getMessage();

        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Event - EventHub</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>




<nav class="navbar">

    <strong>EventHub Admin</strong>

    <div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="manage_events.php">
            Events
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </div>

</nav>




<div class="container">

    <h1>Create New Event</h1>

    <p>
        <a href="manage_events.php">
            ← Back to Events
        </a>
    </p>


    

    <?php if ($error !== ""): ?>

        <div class="alert error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    

    <form
        method="POST"
        enctype="multipart/form-data"
    >


        

        <label for="title">
            Event Title
        </label>

        <input
            type="text"
            id="title"
            name="title"
            maxlength="150"
            value="<?= htmlspecialchars(
                $_POST["title"] ?? ""
            ) ?>"
            required
        >


        

        <label for="description">
            Description
        </label>

        <textarea
            id="description"
            name="description"
            rows="6"
        ><?= htmlspecialchars(
            $_POST["description"] ?? ""
        ) ?></textarea>


        

        <label for="category_id">
            Category
        </label>

        <select
            id="category_id"
            name="category_id"
            required
        >

            <option value="">
                -- Select Category --
            </option>

            <?php foreach ($categories as $category): ?>

                <option
                    value="<?= $category["category_id"] ?>"
                    <?= (
                        ($_POST["category_id"] ?? "")
                        == $category["category_id"]
                    )
                        ? "selected"
                        : ""
                    ?>
                >

                    <?= htmlspecialchars(
                        $category["category_name"]
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>


        

        <label for="event_date">
            Event Date
        </label>

        <input
            type="date"
            id="event_date"
            name="event_date"
            min="<?= date("Y-m-d") ?>"
            value="<?= htmlspecialchars(
                $_POST["event_date"] ?? ""
            ) ?>"
            required
        >

        <small>
            The event date cannot be in the past.
        </small>


        

        <label for="start_time">
            Start Time
        </label>

        <input
            type="time"
            id="start_time"
            name="start_time"
            value="<?= htmlspecialchars(
                $_POST["start_time"] ?? ""
            ) ?>"
            required
        >


        

        <label for="end_time">
            End Time
        </label>

        <input
            type="time"
            id="end_time"
            name="end_time"
            value="<?= htmlspecialchars(
                $_POST["end_time"] ?? ""
            ) ?>"
            required
        >


        

        <label for="venue">
            Venue
        </label>

        <input
            type="text"
            id="venue"
            name="venue"
            maxlength="120"
            value="<?= htmlspecialchars(
                $_POST["venue"] ?? ""
            ) ?>"
            required
        >


        

        <label for="capacity">
            Capacity
        </label>

        <input
            type="number"
            id="capacity"
            name="capacity"
            min="1"
            value="<?= htmlspecialchars(
                $_POST["capacity"] ?? "50"
            ) ?>"
            required
        >


        

        <label for="image">
            Event Image
        </label>

        <input
            type="file"
            id="image"
            name="image"
            accept=".jpg,.jpeg,.png,.gif,.webp"
        >

        <small>
            Maximum size: 5 MB.
            Allowed formats: JPG, JPEG, PNG, GIF, WEBP.
        </small>


        

        <label for="status">
            Status
        </label>

        <select
            id="status"
            name="status"
        >

            <option
                value="published"
                <?= (
                    ($_POST["status"] ?? "published")
                    === "published"
                )
                    ? "selected"
                    : ""
                ?>
            >
                Published
            </option>

            <option
                value="cancelled"
                <?= (
                    ($_POST["status"] ?? "")
                    === "cancelled"
                )
                    ? "selected"
                    : ""
                ?>
            >
                Cancelled
            </option>

        </select>


        <!-- BUTTONS -->

        <br>

        <button
            type="submit"
            name="add_event"
            class="btn"
        >
            Create Event
        </button>

        <a
            href="manage_events.php"
            class="btn"
        >
            Cancel
        </a>

    </form>

</div>


</body>

</html>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit();
}
include 'C:\xampp\htdocs\Edu_platform\db.php';

// Get course ID from URL
if (!isset($_GET['id'])) {
    echo "No course ID provided.";
    exit();
}
$course_id = intval($_GET['id']);  // Secure with intval
$teacher_id = $_SESSION['user_id'];

// Fetch existing course data
$sql = "SELECT * FROM courses WHERE id = $course_id AND teacher_id = $teacher_id";
$result = mysqli_query($conn, $sql);
if (!$course = mysqli_fetch_assoc($result)) {
    echo "Course not found or you don't own it.";
    exit();
}

// Handle form submission for update
if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $base_mark = intval($_POST['base_mark']);

    // Handle image (keep old if no new upload)
    $image = $course['image'];
    if ($_FILES['image']['name']) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
            // Success
        } else {
            echo "Image upload failed.";
        }
    }

    // Update SQL
    $sql = "UPDATE courses SET 
            name = '$name', 
            description = '$description', 
            base_mark = $base_mark, 
            image = '$image' 
            WHERE id = $course_id AND teacher_id = $teacher_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: courses.php");
        exit();
    } else {
        echo "Error updating: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #555;
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form textarea {
            resize: vertical;
        }

        form input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .current-image {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        a.back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        a.back-link:hover {
            color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Course</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Course Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($course['name']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>

            <label for="base_mark">Base Mark:</label>
            <input type="number" name="base_mark" id="base_mark" value="<?php echo $course['base_mark']; ?>" required>

            <label>Current Image:</label>
            <?php if ($course['image']) { ?>
                <img class="current-image" src="<?php echo $course['image']; ?>" width="150" alt="Course Image">
            <?php } else { echo "<p>No image uploaded.</p>"; } ?>

            <label for="image">Upload New Image (optional):</label>
            <input type="file" name="image" id="image">

            <input type="submit" name="update" value="Update Course">
        </form>

        <a class="back-link" href="courses.php">&larr; Back to Courses</a>
    </div>
</body>
</html>

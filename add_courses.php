<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}
include 'C:\xampp\htdocs\Edu_platform\db.php';
$teacher_id = $_SESSION['user_id'];

// Handle Add
if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $base_mark = intval($_POST['base_mark']);
    $image = '';

    // Check for duplicate course
    $check_sql = "SELECT id FROM courses WHERE name = '$name' AND teacher_id = $teacher_id";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Course '$name' already exists!";
    } else {
        if ($_FILES['image']['name']) {
            $image = 'uploads/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
        }
        $sql = "INSERT INTO courses (name, description, base_mark, image, teacher_id, is_deleted) VALUES ('$name', '$desc', $base_mark, '$image', $teacher_id, 0)";
        if (mysqli_query($conn, $sql)) {
            header("Location: courses.php");
            exit;
        } else {
            $error = "Error adding course: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Edu Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f0, #e0e7e0);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        h2 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .form-label {
            color: #2e7d32;
            font-weight: bold;
        }
        .form-control {
            border-color: #a9a9a9;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .btn-custom {
            background: #2e7d32;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background: #1b5e20;
            transform: translateY(-2px);
        }
        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Add Course</h2>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Course Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="base_mark" class="form-label">Base Mark:</label>
                    <input type="number" class="form-control" id="base_mark" name="base_mark" value="100" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Course Image:</label>
                    <input type="file" class="form-control" id="image" name="image">
                </div>
                <button type="submit" name="add" class="btn btn-custom">Add Course</button>
            </form>
            <a href="dashboard.php" class="btn btn-custom mt-3">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
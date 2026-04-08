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
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    // Check for duplicate email
    $check_sql = "SELECT id FROM students WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Student with email '$email' already exists!";
    } else {
        $sql = "INSERT INTO students (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            header("Location: students.php");
            exit;
        } else {
            $error = "Error adding student: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Edu Platform</title>
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
            <h2>Add Student</h2>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="add" class="btn btn-custom">Add Student</button>
            </form>
            <a href="dashboard.php" class="btn btn-custom mt-3">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
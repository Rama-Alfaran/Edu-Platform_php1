<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'C:\xampp\htdocs\Edu_platform\db.php';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Determine the correct table based on role
$table = ($role == 'Teacher') ? 'teachers' : 'students';
$sql = "SELECT first_name FROM $table WHERE id = $user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Edu Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0e7e0, #f5f5f0);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: #2e7d32;
            color: #fff;
            padding-top: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #1b5e20;
            text-decoration: none;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .profile-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        .profile-card h2 {
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        .options-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        .options-card h3 {
            color: #555;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .btn-custom {
            flex: 1 1 200px;
            padding: 12px;
            color: #fff;
            background: #2e7d32;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1em;
            min-width: 180px;
            display: inline-block;
        }
        .btn-custom:hover {
            background: #1b5e20;
            transform: translateY(-2px);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
                padding: 10px;
            }
            .btn-custom {
                flex: 1 1 100%;
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center mb-4">Edu Platform</h4>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
            <?php if ($role == 'Teacher') { ?>
                <a class="nav-link" href="courses.php">Manage Courses</a>
                <a class="nav-link" href="add_courses.php">Add Course</a>
                <a class="nav-link" href="students.php">Manage Students</a>
                <a class="nav-link" href="add_students.php">Add Student</a>
            <?php } else { ?>
                <a class="nav-link" href="my_courses.php">My Courses</a>
            <?php } ?>
            <a class="nav-link" href="chat.php">Join Chat</a>
            <a href="logout.php" class="btn-logout mt-auto" style="width: 100%; text-align: center; padding: 10px;">Logout</a>
        </nav>
    </div>
    <div class="content">
        <div class="profile-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>, to your <?php echo ($role == 'Teacher') ? 'Teacher' : 'Student'; ?> Dashboard!</h2>
        </div>
        <div class="options-card">
            <?php if ($role == 'Teacher') { ?>
                <h3>Teacher Options</h3>
                <div class="btn-group">
                    <a href="courses.php" class="btn btn-custom">Manage Courses</a>
                    <a href="add_courses.php" class="btn btn-custom">Add Course</a>
                    <a href="students.php" class="btn btn-custom">Manage Students</a>
                    <a href="add_students.php" class="btn btn-custom">Add Student</a>
                </div>
            <?php } else { ?>
                <h3>Student Options</h3>
                <div class="btn-group">
                    <a href="my_courses.php" class="btn btn-custom">View My Courses and Marks</a>
                </div>
            <?php } ?>
            <h3 class="mt-4">General</h3>
            <div class="btn-group">
                <a href="chat.php" class="btn btn-custom">Join Chat</a>
            </div>
        </div>
    </div>
</body>
</html>
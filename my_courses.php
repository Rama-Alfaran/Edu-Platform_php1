<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}
include 'C:\xampp\htdocs\Edu_platform\db.php';
$student_id = $_SESSION['user_id'];

// Query enrollments with course details
$sql = "SELECT c.id, c.name, c.description, c.base_mark, c.image, e.mark 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = $student_id";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Edu Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f0; /* Beige background */
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        h2 {
            color: #4a704a; /* Dark green heading */
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        table th {
            background-color: #4a704a; /* Dark green header */
            color: #fff;
            text-align: center;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9; /* Light gray rows */
        }
        table tr:hover {
            background-color: #e8f5e9; /* Light green hover */
        }
        table img {
            border-radius: 6px;
            max-width: 60px;
            height: auto;
        }
        .btn-custom {
            display: inline-block;
            padding: 10px 20px;
            background: #2e7d32; /* Green button */
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-custom:hover {
            background: #1b5e20; /* Darker green on hover */
        }
        @media (max-width: 768px) {
            .card {
                margin: 20px 0;
                padding: 15px;
            }
            table {
                font-size: 0.9em;
            }
            table img {
                max-width: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>My Enrolled Courses and Marks</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Base Mark</th>
                        <th>My Mark</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td style="text-align: center;"><?php echo $row['base_mark']; ?></td>
                                <td style="text-align: center;"><?php echo $row['mark'] ?? '-'; ?></td>
                                <td style="text-align: center;">
                                    <?php if ($row['image']): ?>
                                        <img src="<?php echo $row['image']; ?>" width="60" alt="Course Image">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center;">No courses enrolled yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn-custom">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
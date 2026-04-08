<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}
include 'C:\xampp\htdocs\Edu_platform\db.php';
$teacher_id = $_SESSION['user_id'];

// Handle Search (Server-side filtering)
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT s.first_name, s.last_name, c.name, e.mark 
        FROM enrollments e 
        JOIN students s ON e.student_id = s.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE c.teacher_id = $teacher_id";
if (!empty($search_query)) {
    $sql .= " AND (CONCAT(s.first_name, ' ', s.last_name) LIKE '%$search_query%' OR c.name LIKE '%$search_query%')";
}
$enrollments_result = mysqli_query($conn, $sql);
$enrollments = mysqli_fetch_all($enrollments_result, MYSQLI_ASSOC);

// If AJAX request, return JSON
if (isset($_GET['ajax'])) {
    echo json_encode($enrollments);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Edu Platform</title>
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
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        h3 {
            color: #555;
            margin: 25px 0 15px;
            font-size: 1.3em;
        }
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #a9a9a9;
            border-radius: 6px;
            font-size: 1em;
        }
        .search-bar button {
            padding: 10px 15px;
            font-size: 1em;
        }
        .btn-custom {
            background: #2e7d32;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-custom:hover {
            background: #1b5e20;
        }
        .btn-clear {
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-clear:hover {
            background: #5a6268;
            transform: translateY(-2px);
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
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        table th {
            background: #2e7d32;
            color: #fff;
            text-align: center;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tr:hover {
            background: #e8f5e9;
        }
        .no-results {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
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
            .card { margin: 20px 0; padding: 15px; }
            table { font-size: 0.9em; }
            .search-bar { flex-direction: column; gap: 5px; }
            .search-bar input { font-size: 0.9em; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center mb-4">Edu Platform</h4>
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
            <a class="nav-link" href="courses.php">Manage Courses</a>
            <a class="nav-link" href="add_course.php">Add Course</a>
            <a class="nav-link active" href="students.php">Manage Students</a>
            <a class="nav-link" href="add_student.php">Add Student</a>
            <a class="nav-link" href="chat.php">Join Chat</a>
            <a href="logout.php" class="btn-logout mt-auto" style="width: 100%; text-align: center; padding: 10px;">Logout</a>
        </nav>
    </div>
    <div class="content">
        <div class="card">
            <h2>Manage Students</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by student name or course name..." onkeyup="debounce(searchStudents, 300)(this.value)">
                <button type="button" class="btn btn-clear" onclick="clearSearch()">Clear</button>
            </div>
        </div>

        <div class="card">
            <h3>Enrolled Students and Marks</h3>
            <table class="table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course Name</th>
                        <th>Mark</th>
                    </tr>
                </thead>
                <tbody id="studentsTbody">
                    <?php foreach ($enrollments as $enrollment) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['name']); ?></td>
                            <td style="text-align: center;"><?php echo $enrollment['mark'] ?? '-'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn-custom mt-3">&larr; Back to Dashboard</a>
        </div>
    </div>

    <script>
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        function searchStudents(query) {
            fetch(`students.php?search=${encodeURIComponent(query)}&ajax=1`)
                .then(response => response.json())
                .then(data => populateTable(data))
                .catch(error => console.error('Error:', error));
        }

        function populateTable(data) {
            const tbody = document.getElementById('studentsTbody');
            tbody.innerHTML = ''; // Clear existing rows

            if (data.length === 0) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.innerHTML = '<td colspan="3" class="no-results">No results found</td>';
                tbody.appendChild(noResultsRow);
            } else {
                data.forEach(enrollment => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${enrollment.first_name} ${enrollment.last_name}</td>
                        <td>${enrollment.name}</td>
                        <td style="text-align: center;">${enrollment.mark ?? '-'}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchStudents(''); // Fetch all enrollments
        }
    </script>
</body>
</html>
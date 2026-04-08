<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}
include 'C:\xampp\htdocs\Edu_platform\db.php';
$teacher_id = $_SESSION['user_id'];

// Handle Delete
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    $id = intval($_GET['delete']);
    $sql = "UPDATE courses SET is_deleted = 1 WHERE id = $id AND teacher_id = $teacher_id";
    mysqli_query($conn, $sql);
    if (isset($_GET['ajax'])) {
        // Return updated data if AJAX
        header('Location: courses.php?ajax=1');
        exit;
    } else {
        header('Location: courses.php');
        exit;
    }
}

// Handle Search (Server-side filtering)
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT * FROM courses WHERE teacher_id = $teacher_id AND is_deleted = 0";
if (!empty($search_query)) {
    $sql .= " AND (name LIKE '%$search_query%' OR description LIKE '%$search_query%')";
}
$courses_result = mysqli_query($conn, $sql);
$courses = mysqli_fetch_all($courses_result, MYSQLI_ASSOC);

// If AJAX request, return JSON
if (isset($_GET['ajax'])) {
    echo json_encode($courses);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Edu Platform</title>
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
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background: #1b5e20;
            transform: translateY(-2px);
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
        .btn-delete {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-right: 5px;
        }
        .btn-delete:hover {
            background: #a71d2a;
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
            background: #2e7d32;
            color: #fff;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        table img {
            border-radius: 6px;
            max-width: 50px;
            height: auto;
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
            table img { max-width: 40px; }
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
            <a class="nav-link active" href="courses.php">Manage Courses</a>
            <a class="nav-link" href="add_course.php">Add Course</a>
            <a class="nav-link" href="students.php">Manage Students</a>
            <a class="nav-link" href="add_student.php">Add Student</a>
            <a class="nav-link" href="chat.php">Join Chat</a>
            <a href="logout.php" class="btn-logout mt-auto" style="width: 100%; text-align: center; padding: 10px;">Logout</a>
        </nav>
    </div>
    <div class="content">
        <div class="card">
            <h2>Manage Courses</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by name or description..." onkeyup="debounce(searchCourses, 300)(this.value)">
                <button type="button" class="btn btn-clear" onclick="clearSearch()">Clear</button>
            </div>
        </div>

        <div class="card">
            <h3>All Courses</h3>
            <table class="table" id="coursesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Base Mark</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="coursesTbody">
                    <?php foreach ($courses as $course) { ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['name']); ?></td>
                            <td><?php echo htmlspecialchars($course['description']); ?></td>
                            <td><?php echo $course['base_mark']; ?></td>
                            <td>
                                <?php if ($course['image']) { ?>
                                    <img src="<?php echo $course['image']; ?>" width="50">
                                <?php } else { echo "-"; } ?>
                            </td>
                            <td>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-custom">Edit</a>
                                <button class="btn btn-delete" onclick="confirmDelete(<?php echo $course['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-custom mt-3">&larr; Back to Dashboard</a>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to soft delete this course? This action can be reversed.")) {
                window.location.href = "courses.php?delete=" + id + "&confirm=1";
            }
        }

        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        function searchCourses(query) {
            fetch(`courses.php?search=${encodeURIComponent(query)}&ajax=1`)
                .then(response => response.json())
                .then(data => populateTable(data))
                .catch(error => console.error('Error:', error));
        }

        function populateTable(data) {
            const tbody = document.getElementById('coursesTbody');
            tbody.innerHTML = ''; // Clear existing rows

            if (data.length === 0) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.innerHTML = '<td colspan="6" class="no-results">No results found</td>';
                tbody.appendChild(noResultsRow);
            } else {
                data.forEach(course => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${course.id}</td>
                        <td>${course.name}</td>
                        <td>${course.description}</td>
                        <td>${course.base_mark}</td>
                        <td>${course.image ? `<img src="${course.image}" width="50">` : '-'}</td>
                        <td>
                            <a href="edit_course.php?id=${course.id}" class="btn btn-custom">Edit</a>
                            <button class="btn btn-delete" onclick="confirmDelete(${course.id})">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchCourses(''); // Fetch all courses
        }
    </script>
</body>
</html>
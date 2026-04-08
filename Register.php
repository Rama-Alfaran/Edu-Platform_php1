<?php
include 'db.php';

if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $hobbies = implode(',', $_POST['hobbies'] ?? []);
    $birthdate = $_POST['birthdate'];
    $role = $_POST['role'];

    // Determine the table based on role
    $table = ($role == 'Teacher') ? 'teachers' : 'students';
    $other_table = ($role == 'Teacher') ? 'students' : 'teachers';

    // Validate birthdate based on role
    $current_date = new DateTime('now', new DateTimeZone('Asia/Riyadh')); // +03 timezone
    $birthdate_obj = new DateTime($birthdate);
    $age = $current_date->diff($birthdate_obj)->y;

    $date_error = '';
    if ($role == 'Student') {
        $min_date = new DateTime('2000-09-24'); // 25 years ago
        $max_date = new DateTime('2020-09-24'); // 5 years ago
        if ($birthdate_obj < $min_date || $birthdate_obj > $max_date) {
            $date_error = "Students must be between 5 and 25 years old (birthdate between September 24, 2000, and September 24, 2020).";
        }
    } elseif ($role == 'Teacher') {
        $min_date = new DateTime('1960-09-24'); // 65 years ago
        $max_date = new DateTime('2002-09-24'); // 18 years ago
        if ($birthdate_obj < $min_date || $birthdate_obj > $max_date) {
            $date_error = "Teachers must be between 18 and 65 years old (birthdate between September 24, 1960, and September 24, 2007).";
        }
    }

    // Check if email already exists in the target table or the other table
    $check_sql = "SELECT email FROM $table WHERE email = ? UNION SELECT email FROM $other_table WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "ss", $email, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Email '$email' is already registered in the system.";
        } else {
            // Proceed with registration only if date is valid
            if (empty($date_error)) {
                $insert_sql = "INSERT INTO $table (first_name, last_name, email, password, gender, hobbies, birthdate) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "sssssss", $first_name, $last_name, $email, $password, $gender, $hobbies, $birthdate);
                    if (mysqli_stmt_execute($insert_stmt)) {
                        header("Location: index.php?success=1");
                        exit();
                    } else {
                        $error = "Error during registration: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($insert_stmt);
                } else {
                    $error = "Prepare failed: " . mysqli_error($conn);
                }
            } else {
                $error = $date_error; // Set date error if validation fails
            }
        }
        mysqli_stmt_close($check_stmt);
    } else {
        $error = "Check query failed: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Edu Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #4a704a;
            margin-bottom: 20px;
        }
        .form-label {
            color: #4a704a;
            font-weight: bold;
        }
        .form-control {
            border-color: #a9a9a9;
            border-radius: 6px;
        }
        .form-check-label {
            color: #555;
        }
        .btn-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }
        .btn-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }
        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 15px;
        }
        @media (max-width: 480px) {
            .register-container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "<div class='success'>Registration successful! You can now <a href='index.php' style='color: #2e7d32;'>login</a>.</div>";
        }
        if (isset($error)) {
            echo "<div class='error'>$error</div>";
        }
        ?>
        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="gender" value="Male" required>
                    <label class="form-check-label">Male</label>
                </div>
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="gender" value="Female">
                    <label class="form-check-label">Female</label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Hobbies</label>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="hobbies[]" value="Reading">
                    <label class="form-check-label">Reading</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="hobbies[]" value="Writing">
                    <label class="form-check-label">Writing</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="hobbies[]" value="Sports">
                    <label class="form-check-label">Sports</label>
                </div>
            </div>
            <div class="mb-3">
                <label for="birthdate" class="form-label">Birthdate</label>
                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role" required onchange="updateDateLimits()">
                    <option value="" disabled selected>Select role</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Student">Student</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>

    <script>
        function updateDateLimits() {
            const role = document.getElementById('role').value;
            const birthdateInput = document.getElementById('birthdate');
            const currentDate = new Date();
            const minDate = new Date(currentDate);
            const maxDate = new Date(currentDate);

            if (role === 'Student') {
                minDate.setFullYear(currentDate.getFullYear() - 25); // 25 years ago
                maxDate.setFullYear(currentDate.getFullYear() - 6);  // 6 years ago
            } else if (role === 'Teacher') {
                minDate.setFullYear(currentDate.getFullYear() - 65); // 65 years ago
                maxDate.setFullYear(currentDate.getFullYear() - 21); // 21 years ago
            }

            // Format dates as YYYY-MM-DD
            const minDateStr = minDate.toISOString().split('T')[0];
            const maxDateStr = maxDate.toISOString().split('T')[0];

            birthdateInput.setAttribute('min', minDateStr);
            birthdateInput.setAttribute('max', maxDateStr);

            // Reset birthdate if outside new limits
            const currentBirthdate = new Date(birthdateInput.value);
            if (currentBirthdate < minDate || currentBirthdate > maxDate) {
                birthdateInput.value = ''; // Clear invalid date
            }
        }

        // Initialize date limits when the page loads
        window.onload = function() {
            updateDateLimits();
        };
    </script>
</body>
</html>
<?php
session_start();
include 'db.php';

$error = '';
if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Check teachers table
    $sql_teacher = "SELECT * FROM teachers WHERE email = '$email'";
    $result_teacher = mysqli_query($conn, $sql_teacher);
    if ($user = mysqli_fetch_assoc($result_teacher)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'Teacher';
            $_SESSION['first_name'] = $user['first_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        // Check students table
        $sql_student = "SELECT * FROM students WHERE email = '$email'";
        $result_student = mysqli_query($conn, $sql_student);
        if ($user = mysqli_fetch_assoc($result_student)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'Student';
                $_SESSION['first_name'] = $user['first_name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Edu Platform</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ece5dd;
            font-family: "Segoe UI", Arial, sans-serif;
        }
        .login-container {
            background: #ffffff;
            padding: 35px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 360px;
            width: 90%;
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.4s ease-in-out;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
            color: #2a2a2a;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-weight: 600;
            font-size: 0.9em;
            color: #3b3b3b;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cfd1cf;
            border-radius: 8px;
            font-size: 0.95em;
            background: #fafafa;
            outline: none;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #25d366;
            background: #fff;
            box-shadow: 0 0 4px rgba(37, 211, 102, 0.3);
        }
        .error {
            color: #d9534f;
            text-align: center;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background-color: #25d366;
            color: white;
            font-size: 1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease-in-out;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #1ebd5c;
            transform: scale(1.02);
        }
        .register-link, .forgot-link {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
        }
        .register-link a, .forgot-link a {
            color: #25d366;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover, .forgot-link a:hover {
            text-decoration: underline;
        }
        .success {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 15px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "<div class='success'>Registration successful! You can now log in.</div>";
        }
        if ($error) {
            echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
        }
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <input type="submit" name="submit" value="Login">
        </form>
        <div class="register-link">
            Don’t have an account? <a href="register.php">Register here</a>
        </div>
        <div class="forgot-link">
            <a href="reset_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
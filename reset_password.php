<?php
include 'db.php';

require 'vendor/autoload.php'; // Include PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if email exists in teachers table
    $sql_teachers = "SELECT id, first_name FROM teachers WHERE email = ?";
    $stmt_teachers = mysqli_prepare($conn, $sql_teachers);
    if ($stmt_teachers) {
        mysqli_stmt_bind_param($stmt_teachers, "s", $email);
        mysqli_stmt_execute($stmt_teachers);
        $result_teachers = mysqli_stmt_get_result($stmt_teachers);
        $user = mysqli_fetch_assoc($result_teachers);

        if ($user) {
            $table = 'teachers';
        } else {
            // Check if email exists in students table
            $sql_students = "SELECT id, first_name FROM students WHERE email = ?";
            $stmt_students = mysqli_prepare($conn, $sql_students);
            if ($stmt_students) {
                mysqli_stmt_bind_param($stmt_students, "s", $email);
                mysqli_stmt_execute($stmt_students);
                $result_students = mysqli_stmt_get_result($stmt_students);
                $user = mysqli_fetch_assoc($result_students);
                $table = 'students';
            } else {
                $error = "Query preparation failed for students: " . mysqli_error($conn);
                mysqli_stmt_close($stmt_teachers);
                exit;
            }
            mysqli_stmt_close($stmt_students);
        }
        mysqli_stmt_close($stmt_teachers);

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

            // Update the user's table with the token and expiration
            $update_sql = "UPDATE $table SET reset_token = ?, reset_expires = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ssi", $token, $expires, $user['id']);
                if (mysqli_stmt_execute($update_stmt)) {
                    // Send email using PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
                        $mail->SMTPAuth = true;
                        $mail->Username = 'your_email@gmail.com'; // Replace with your email
                        $mail->Password = 'your_app_password'; // Replace with your App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Recipients
                        $mail->setFrom('no-reply@yourdomain.com', 'Edu Platform');
                        $mail->addAddress($email, $user['first_name']);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $reset_link = "http://localhost/Edu_platform/reset_password_form.php?token=" . $token;
                        $mail->Body = "Hello {$user['first_name']},<br><br>Click <a href='$reset_link'>this link</a> to reset your password. This link will expire in 1 hour.<br><br>Best,<br>Edu Platform Team";
                        $mail->AltBody = "Hello {$user['first_name']},\n\nClick this link to reset your password: $reset_link. This link will expire in 1 hour.\n\nBest,\nEdu Platform Team";

                        $mail->send();
                        $success = "A password reset link has been sent to $email. Please check your inbox.";
                    } catch (Exception $e) {
                        $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "Error updating token: " . mysqli_error($conn);
                }
                mysqli_stmt_close($update_stmt);
            } else {
                $error = "Prepare failed: " . mysqli_error($conn);
            }
        } else {
            $error = "Email not found in the system.";
        }
    } else {
        $error = "Query preparation failed for teachers: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Edu Platform</title>
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
        .reset-container {
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
            .reset-container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="reset_password.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
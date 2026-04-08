<?php
// Start session
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Clear session data
$_SESSION = array();

// Destroy session
if (session_destroy()) {
    // Set default message
    $logout_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : "You have been successfully logged out.";
} else {
    $logout_message = "Logout failed. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="refresh" content="5;url=index.php">
    <title>Logged Out - Edu Platform</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #ece5dd 0%, #f5f7fa 100%);
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .logout-container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }
        h2 {
            color: #2a2a2a;
            margin-bottom: 1rem;
            font-size: 1.8em;
        }
        p {
            color: #555;
            font-size: 1em;
            margin-bottom: 1.5rem;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #25d366;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        .countdown {
            font-size: 0.9em;
            color: #666;
            margin-top: 0.5rem;
        }
        a {
            display: inline-block;
            margin-top: 1rem;
            text-decoration: none;
            color: white;
            background-color: #25d366;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        a:hover {
            background-color: #1ebd5c;
            transform: translateY(-2px);
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 480px) {
            .logout-container {
                padding: 1.5rem;
            }
            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container" role="alert" aria-live="assertive">
        <h2>Logged Out</h2>
        <p><?php echo $logout_message; ?></p>
        <div class="spinner"></div>
        <p class="countdown">Redirecting in <span id="countdown">5</span> seconds...</p>
        <a href="index.php" aria-label="Return to login page">Go to Login</a>
    </div>

    <script>
        // Countdown timer
        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        const timer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
            }
        }, 1000);
    </script>
</body>
</html>
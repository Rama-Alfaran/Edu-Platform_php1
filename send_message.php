<?php
session_start();
include 'C:\xampp\htdocs\Edu_platform\db.php';
require 'C:\xampp\htdocs\Edu_platform\vendor\autoload.php';
use Pusher\Pusher;

$pusher = new Pusher(
    'a3121da9952c46769d39',
    '1dd848766028449f0575',
    '2050933',
    ['cluster' => 'ap2']
);

$message = $_POST['message'] ?? '';
$recipient_id = $_POST['recipient'] ?? '';
$channel = $_POST['channel'] ?? '';
$sender_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'unknown';

if (!$recipient_id || !is_numeric($recipient_id) || (int)$recipient_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Valid recipient ID required']);
    exit;
}
if (!$message) {
    echo json_encode(['status' => 'error', 'message' => 'No message provided']);
    exit;
}

if (!$channel) {
    $ids = [(int)$sender_id, (int)$recipient_id];
    sort($ids);
    $channel = 'private-chat-' + implode('-', $ids);
}
error_log('Received channel from client: ' . $channel);

$recipient_role = ($role === 'Teacher') ? 'Student' : 'Teacher';
$recipient_table = ($role === 'Teacher') ? 'students' : 'teachers';
$recipient_name = getUserName((int)$recipient_id, $recipient_table);

$timestamp = date('Y-m-d H:i:s', strtotime('now +03')); // Adjust for +03 timezone
$stmt = mysqli_prepare($conn, "INSERT INTO chat_messages (sender_id, recipient_id, message, channel, sender_role, recipient_role, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "iisssss", $sender_id, $recipient_id, $message, $channel, $role, $recipient_role, $timestamp);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
    } else {
        error_log("Insert failed: " . mysqli_error($conn));
        echo json_encode(['status' => 'error', 'message' => 'DB insert failed']);
        exit;
    }
} else {
    error_log("Prepare failed: " . mysqli_error($conn));
    echo json_encode(['status' => 'error', 'message' => 'DB prepare failed']);
    exit;
}

$data = [
    'sender' => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest',
    'recipient' => $recipient_name ?: 'Unknown',
    'message' => $message,
    'timestamp' => $timestamp
];

try {
    error_log('Attempting to trigger channel: ' . $channel . ' with data: ' . json_encode($data));
    $result = $pusher->trigger($channel, 'new-message', $data);
    if ($result) {
        error_log('Pusher trigger succeeded for channel: ' . $channel);
        echo json_encode(['status' => 'success', 'channel' => $channel]);
    } else {
        error_log('Pusher trigger failed for channel: ' . $channel);
        echo json_encode(['status' => 'error', 'message' => 'Trigger failed']);
    }
} catch (Exception $e) {
    error_log('Pusher error: ' . $e->getMessage() . ' for channel: ' . $channel);
    echo json_encode(['status' => 'error', 'message' => 'Pusher error: ' . $e->getMessage()]);
}

function getUserName($userId, $table) {
    global $conn;
    if ($conn) {
        $sql = "SELECT first_name FROM $table WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $user ? $user['first_name'] : 'Unknown';
        }
    }
    return 'Unknown';
}
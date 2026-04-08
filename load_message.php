<?php
session_start();
include 'C:\xampp\htdocs\Edu_platform\db.php';

$channel = $_GET['channel'] ?? '';
$messages = [];

if ($channel && $conn) {
    $stmt = mysqli_prepare($conn, "SELECT sender_id, recipient_id, message, timestamp, sender_role, recipient_role FROM chat_messages WHERE channel = ? ORDER BY timestamp ASC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $channel);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $sender_table = ($row['sender_role'] === 'Teacher') ? 'teachers' : 'students';
                $recipient_table = ($row['recipient_role'] === 'Teacher') ? 'teachers' : 'students';

                $sender_name = getUserName($row['sender_id'], $sender_table);
                $recipient_name = $row['recipient_id'] ? getUserName($row['recipient_id'], $recipient_table) : 'Unknown';

                $messages[] = [
                    'sender' => $sender_name,
                    'recipient' => $recipient_name,
                    'message' => $row['message'],
                    'timestamp' => $row['timestamp']
                ];
            }
        } else {
            error_log("Query execution failed: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed: " . mysqli_error($conn));
    }
} else {
    error_log("Invalid channel or no DB connection: " . $channel);
}

header('Content-Type: application/json');
echo json_encode($messages);

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
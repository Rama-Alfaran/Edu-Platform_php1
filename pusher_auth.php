<?php
session_start();
include __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use Pusher\Pusher;

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// جلب البيانات من POST
$channel_name = $_POST['channel_name'] ?? '';
$socket_id = $_POST['socket_id'] ?? '';

if (empty($channel_name) || empty($socket_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing channel_name or socket_id']);
    exit;
}

// إعداد Pusher
$pusher = new Pusher(
    'a3121da9952c46769d39',  // App Key
    '1dd848766028449f0575',  // App Secret
    '2050933',               // App ID
    ['cluster' => 'ap2', 'useTLS' => true]
);

// تحقق أن المستخدم ضمن القناة
if (strpos($channel_name, 'private-chat-') === 0) {
    $ids = explode('-', substr($channel_name, strlen('private-chat-')));
    $ids = array_filter($ids, 'is_numeric');

    if (in_array($_SESSION['user_id'], $ids)) {
        header('Content-Type: application/json');
        echo $pusher->authorizeChannel($channel_name, $socket_id);
        exit;
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid channel']);
exit;

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'C:\xampp\htdocs\Edu_platform\db.php';

$currentUser = $_SESSION['first_name'] ?? 'Guest';
$currentUserId = $_SESSION['user_id'] ?? 0;

if ($currentUserId === 0) {
    die('User not logged in. Please log in first.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edu Platform Chat</title>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: "Segoe UI", sans-serif;
    background: #e5ddd5;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
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

.chat-container {
    width: 420px;
    max-width: 95%;
    background: #ffffff;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.chat-header {
    background: #075e54;
    color: #fff;
    padding: 12px 16px;
    font-size: 1.2em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.back-button {
    display: flex;
    align-items: center;
}

.back-button a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: transparent;
    border: none;
    color: #fff;
    text-decoration: none;
    transition: transform 0.2s ease, background 0.3s;
}

.back-button a:hover {
    transform: scale(1.1);
    background: #054c44;
    border-radius: 50%;
}

.back-button a svg {
    width: 20px;
    height: 20px;
    fill: #fff; /* WCAG AA compliant with #075e54 (contrast ratio ~4.54:1) */
}

.chat-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #e5ddd5;
    scroll-behavior: smooth;
}

.message-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeIn 0.3s forwards;
}

.message-wrapper.sent {
    justify-content: flex-end;
}

.message {
    max-width: 70%;
    padding: 10px 14px;
    border-radius: 20px;
    font-size: 0.95em;
    line-height: 1.3;
    word-wrap: break-word;
    position: relative;
}

.message.received {
    background: #ffffff;
    border-top-left-radius: 0;
}

.message.sent {
    background: #dcf8c6;
    border-top-right-radius: 0;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #bbb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #fff;
    flex-shrink: 0;
}

.timestamp {
    font-size: 0.7em;
    color: #888;
    margin-top: 4px;
    text-align: right;
}

.status {
    font-size: 0.7em;
    color: #444;
    margin-left: 4px;
}

.typing-indicator {
    font-size: 0.85em;
    color: #444;
    font-style: italic;
    margin-left: 50px;
    display: none;
}

.chat-input {
    display: flex;
    gap: 8px;
    padding: 12px;
    background: #f0f0f0;
    border-top: 1px solid #ccc;
}

.chat-input input[type="text"] {
    flex: 1;
    padding: 10px 14px;
    border-radius: 25px;
    border: 1px solid #ccc;
    font-size: 0.95em;
    outline: none;
}

.chat-input input[type="text"]:focus {
    border-color: #075e54;
    box-shadow: 0 0 5px rgba(7,94,84,0.3);
}

.chat-input button {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: none;
    background: #075e54;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background 0.2s;
}

.chat-input button:hover {
    background: #054c44;
}

.chat-input button svg {
    width: 22px;
    height: 22px;
    fill: white;
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media(max-width: 500px) {
    .chat-container {
        width: 95%;
    }
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .content {
        margin-left: 0;
        padding: 10px;
    }
    .chat-header {
        padding: 10px 12px;
        font-size: 1.1em;
    }
    .back-button a {
        width: 25px;
        height: 25px;
    }
    .back-button a:hover {
        transform: scale(1.1);
    }
    .back-button a svg {
        width: 18px;
        height: 18px;
    }
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
        <a class="nav-link" href="students.php">Manage Students</a>
        <a class="nav-link" href="add_student.php">Add Student</a>
        <a class="nav-link active" href="chat.php">Join Chat</a>
        <a href="logout.php" class="btn-logout mt-auto" style="width: 100%; text-align: center; padding: 10px;">Logout</a>
    </nav>
</div>
<div class="content">
    <div class="chat-container">
        <div class="chat-header">
            <div class="back-button">
                <a href="dashboard.php" aria-label="Back to Dashboard" title="Back to Dashboard">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </a>
            </div>
            Edu Platform Chat
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="typing-indicator" id="typingIndicator">Someone is typing...</div>
        <div class="chat-input">
            <input type="text" id="recipient" placeholder="Recipient ID" style="max-width: 90px;">
            <input type="text" id="message" placeholder="Type a message..." autocomplete="off">
            <button onclick="sendMessage()">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
const currentUser = <?php echo json_encode($currentUser); ?>;
const currentUserId = <?php echo json_encode($currentUserId); ?>;

const pusher = new Pusher('a3121da9952c46769d39', {
    cluster: 'ap2',
    authEndpoint: 'pusher_auth.php',
    forceTLS: true
});

const chatMessagesDiv = document.getElementById('chatMessages');
const recipientInput = document.getElementById('recipient');
const messageInput = document.getElementById('message');
const typingIndicator = document.getElementById('typingIndicator');

let currentChannelName = null;
let currentChannel = null;
let typingTimeout;

// Helper: Get channel name
function getChannelName(recipientId) {
    recipientId = parseInt(recipientId);
    if (!recipientId) return null;
    const ids = [parseInt(currentUserId), recipientId].sort((a, b) => a - b);
    return 'private-chat-' + ids.join('-');
}

// Subscribe to channel
recipientInput.addEventListener('input', function() {
    const recipientId = recipientInput.value.trim();
    const newChannelName = getChannelName(recipientId);
    if (newChannelName && newChannelName !== currentChannelName) {
        currentChannelName = newChannelName;
        subscribeToChannel(currentChannelName);
    }
});

function subscribeToChannel(channelName) {
    if (currentChannel) {
        pusher.unsubscribe(currentChannel.name);
        currentChannel.unbind_all();
    }
    currentChannel = pusher.subscribe(channelName);
    currentChannel.bind('pusher:subscription_succeeded', () => loadMessages(channelName));
    currentChannel.bind('new-message', displayMessage);
    currentChannel.bind('typing', showTypingIndicator);
}

// Display message
function displayMessage(data) {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-wrapper ' + (data.sender === currentUser ? 'sent' : 'received');

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.textContent = data.sender.charAt(0).toUpperCase();

    const msgDiv = document.createElement('div');
    msgDiv.className = 'message ' + (data.sender === currentUser ? 'sent' : 'received');
    msgDiv.innerHTML = `<strong>${data.sender}</strong>: ${data.message} 
                        <div class="timestamp">${data.timestamp}</div>
                        <span class="status">${data.status || ''}</span>`;

    if (data.sender === currentUser) {
        wrapper.appendChild(msgDiv);
        wrapper.appendChild(avatar);
    } else {
        wrapper.appendChild(avatar);
        wrapper.appendChild(msgDiv);
    }

    chatMessagesDiv.appendChild(wrapper);
    chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
}

// Typing indicator
function showTypingIndicator(data) {
    if (data.sender !== currentUser) {
        typingIndicator.style.display = 'block';
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => typingIndicator.style.display = 'none', 1500);
    }
}

// Load messages
function loadMessages(channelName) {
    chatMessagesDiv.innerHTML = '';
    fetch(`load_message.php?channel=${encodeURIComponent(channelName)}`)
        .then(res => res.json())
        .then(messages => messages.forEach(displayMessage))
        .catch(err => console.error('Error loading messages:', err));
}

// Send message
function sendMessage() {
    const recipientId = recipientInput.value.trim();
    const message = messageInput.value.trim();
    if (!recipientId || !message) return alert('Please enter recipient and message.');

    const channelName = getChannelName(recipientId);
    fetch('send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `recipient=${encodeURIComponent(recipientId)}&message=${encodeURIComponent(message)}&channel=${encodeURIComponent(channelName)}`
    }).then(res => res.json())
    .then(resp => {
        if (resp.status === 'error') alert(resp.message);
        else messageInput.value = '';
    }).catch(err => console.error('Send message failed:', err));
}

// Send typing event
messageInput.addEventListener('input', function() {
    if (currentChannel) currentChannel.trigger('client-typing', {sender: currentUser});
});

messageInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
});
</script>
</body>
</html>
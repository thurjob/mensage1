<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];

// Get messages between users
$messages = getMessages($user_id, $other_user_id);

// Format messages for JSON response
$formatted_messages = [];
foreach ($messages as $message) {
    $formatted_messages[] = [
        'id' => $message['id'],
        'sender_id' => $message['sender_id'],
        'receiver_id' => $message['receiver_id'],
        'message' => htmlspecialchars($message['message']),
        'is_read' => $message['is_read'],
        'created_at' => $message['created_at'],
        'time_ago' => timeAgo($message['created_at']),
        'username' => $message['username'],
        'profile_pic' => $message['profile_pic'] ? $message['profile_pic'] : 'assets/images/default-avatar.png',
        'is_verified' => $message['is_verified']
    ];
}

echo json_encode([
    'success' => true,
    'messages' => $formatted_messages
]);
?>


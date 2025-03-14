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

// Check if required parameters are provided
if (!isset($_POST['post_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_POST['user_id'];
$message = isset($_POST['message']) ? $_POST['message'] : null;

// Check if post exists
$post = getPostById($post_id);
if (!$post) {
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}

// Check if user exists
$user = getUserById($user_id);
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Share post
$result = sharePost($_SESSION['user_id'], $post_id, $user_id, $message);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Post shared successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to share post']);
}
?>


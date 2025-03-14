<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if post_id is provided
if (!isset($_POST['post_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing post ID']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Like/unlike the post
$result = likePost($user_id, $post_id);

// Check if post is now liked
$is_liked = hasLikedPost($user_id, $post_id);

// Get updated like count
$likes = getLikes($post_id);

// Helper function to check if user has liked a post
function hasLikedPost($user_id, $post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    
    return $stmt->rowCount() > 0;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => $result,
    'liked' => $is_liked,
    'likes' => $likes
]);


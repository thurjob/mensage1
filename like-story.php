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

// Check if story ID is provided
if (!isset($_POST['story_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing story ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$story_id = $_POST['story_id'];

// Check if story exists
$story = getStoryById($story_id);
if (!$story) {
    echo json_encode(['success' => false, 'error' => 'Story not found']);
    exit;
}

// Toggle like
$is_liked = isStoryLiked($user_id, $story_id);
$result = likeStory($user_id, $story_id);

if ($result) {
    // Get updated like count
    $likes = getStoryLikes($story_id);
    
    echo json_encode([
        'success' => true,
        'liked' => !$is_liked, // Toggle the previous state
        'likes' => $likes
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to like/unlike story']);
}
?>

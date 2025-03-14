<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check if query parameter is provided
if (!isset($_GET['q']) || empty($_GET['q'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$query = '%' . $_GET['q'] . '%';

// Search for users
$stmt = $pdo->prepare("
    SELECT id, username, full_name, profile_pic, is_verified 
    FROM users 
    WHERE (username LIKE ? OR full_name LIKE ?) 
    AND id != ? 
    ORDER BY 
        CASE WHEN username = ? THEN 0
            WHEN username LIKE ? THEN 1
            WHEN full_name LIKE ? THEN 2
            ELSE 3
        END,
        username ASC
    LIMIT 10
");
$stmt->execute([$query, $query, $_SESSION['user_id'], trim($_GET['q']), trim($_GET['q']) . '%', trim($_GET['q']) . '%']);
$users = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($users);


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

// Check if dark_mode parameter is provided
if (!isset($_POST['dark_mode'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$dark_mode = $_POST['dark_mode'] === '1' ? 1 : 0;
$result = updateDarkModePreference($_SESSION['user_id'], $dark_mode);

header('Content-Type: application/json');
echo json_encode(['success' => $result]);


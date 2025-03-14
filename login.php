<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if (loginUser($username, $password)) {
            // Redirect to home page
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-logo">Monogram</h1>
                <p class="auth-subtitle">Log in to see photos and videos from your friends.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="auth-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username or Email" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </div>
                
                <div class="auth-separator">
                    <span>OR</span>
                </div>
                
                <div class="auth-options">
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
            </form>
        </div>
        
        <div class="auth-card auth-signup">
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>


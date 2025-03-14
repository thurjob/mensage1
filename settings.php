<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
$settings = getUserSettings($user_id);
$dark_mode = $settings['dark_mode'];

$success_message = '';
$error_message = '';

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $bio = trim($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if username is changed and already exists
    if ($username !== $user['username']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error_message = 'Username already exists';
        }
    }
    
    // Check if email is changed and already exists
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error_message = 'Email already exists';
        }
    }
    
    // Process profile picture upload
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
        $uploaded_pic = uploadImage($_FILES['profile_pic'], 'uploads/profiles/');
        
        if ($uploaded_pic) {
            $profile_pic = $uploaded_pic;
        } else {
            $error_message = 'Failed to upload profile picture';
        }
    }
    
    // Process password change
    if (!empty($current_password) && !empty($new_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'New password must be at least 6 characters';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }
    }
    
    // Update profile if no errors
    if (empty($error_message)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, full_name = ?, bio = ?, profile_pic = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$username, $email, $full_name, $bio, $profile_pic, $user_id]);
        
        if ($result) {
            // Update session username if changed
            if ($username !== $user['username']) {
                $_SESSION['username'] = $username;
            }
            
            $success_message = 'Profile updated successfully';
            
            // Refresh user data
            $user = getUserById($user_id);
        } else {
            $error_message = 'Failed to update profile';
        }
    }
}

// Process dark mode toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_dark_mode'])) {
    $new_dark_mode = $dark_mode ? 0 : 1;
    
    if (updateDarkModePreference($user_id, $new_dark_mode)) {
        $dark_mode = $new_dark_mode;
        $settings['dark_mode'] = $dark_mode;
        $success_message = 'Theme preference updated';
    } else {
        $error_message = 'Failed to update theme preference';
    }
}

// Process account privacy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_privacy'])) {
    // Implement privacy settings update here
    $success_message = 'Privacy settings updated';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="settings-container">
            <h1 class="page-title">Settings</h1>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="settings-tabs">
                <button class="settings-tab active" data-tab="profile">Edit Profile</button>
                <button class="settings-tab" data-tab="password">Change Password</button>
                <button class="settings-tab" data-tab="theme">Theme</button>
                <button class="settings-tab" data-tab="privacy">Privacy</button>
            </div>
            
            <div class="settings-content">
                <form action="settings.php" method="POST" enctype="multipart/form-data" class="settings-form">
                    <!-- Profile Tab -->
                    <div class="settings-tab-content active" id="profile-tab">
                        <div class="profile-pic-upload">
                            <div class="current-profile-pic">
                                <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $user['username']; ?>" id="profile-pic-preview">
                            </div>
                            
                            <div class="profile-pic-input">
                                <label for="profile_pic" class="btn btn-outline">Change Profile Picture</label>
                                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo $user['bio']; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                    
                    <!-- Password Tab -->
                    <div class="settings-tab-content" id="password-tab">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">Change Password</button>
                        </div>
                    </div>
                    
                    <!-- Theme Tab -->
                    <div class="settings-tab-content" id="theme-tab">
                        <div class="theme-option">
                            <div class="theme-info">
                                <h3>Dark Mode</h3>
                                <p>Switch between light and dark themes</p>
                            </div>
                            
                            <div class="theme-toggle">
                                <button type="submit" name="toggle_dark_mode" class="toggle-switch <?php echo $dark_mode ? 'active' : ''; ?>">
                                    <span class="toggle-slider"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Privacy Tab -->
                    <div class="settings-tab-content" id="privacy-tab">
                        <div class="privacy-option">
                            <div class="privacy-info">
                                <h3>Private Account</h3>
                                <p>When your account is private, only people you approve can see your photos and videos</p>
                            </div>
                            
                            <div class="privacy-toggle">
                                <button type="button" class="toggle-switch">
                                    <span class="toggle-slider"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="privacy-option">
                            <div class="privacy-info">
                                <h3>Activity Status</h3>
                                <p>Allow accounts you follow and anyone you message to see when you were last active</p>
                            </div>
                            
                            <div class="privacy-toggle">
                                <button type="button" class="toggle-switch active">
                                    <span class="toggle-slider"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_privacy" class="btn btn-primary">Save Privacy Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Tab switching functionality
    const tabs = document.querySelectorAll('.settings-tab');
    const tabContents = document.querySelectorAll('.settings-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all tab contents
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Show the corresponding tab content
            const tabId = this.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Profile picture preview
    const profilePicInput = document.getElementById('profile_pic');
    const profilePicPreview = document.getElementById('profile-pic-preview');
    
    profilePicInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                profilePicPreview.src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Toggle switches
    const toggleSwitches = document.querySelectorAll('.toggle-switch');
    
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('click', function() {
            if (!this.getAttribute('name')) {
                this.classList.toggle('active');
            }
        });
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

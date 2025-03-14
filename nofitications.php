<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Mark all notifications as read
markNotificationsAsRead($_SESSION['user_id']);

// Get notifications
$notifications = getNotifications($_SESSION['user_id'], 50); // Get up to 50 notifications

// Get dark mode preference
$settings = getUserSettings($_SESSION['user_id']);
$dark_mode = $settings['dark_mode'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="notifications-container">
            <h1 class="page-title">Notifications</h1>
            
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <h2>No Notifications Yet</h2>
                    <p>When you have notifications, they'll appear here.</p>
                </div>
            <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item">
                            <div class="notification-avatar">
                                <img src="<?php echo $notification['profile_pic'] ? $notification['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $notification['username']; ?>">
                            </div>
                            
                            <div class="notification-content">
                                <div class="notification-text">
                                    <a href="profile.php?username=<?php echo $notification['username']; ?>" class="notification-username">
                                        <?php echo $notification['username']; ?>
                                        <?php if ($notification['is_verified']): ?>
                                            <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <?php endif; ?>
                                    </a>
                                    
                                    <?php
                                    switch ($notification['type']) {
                                        case 'like':
                                            echo 'liked your post.';
                                            break;
                                        case 'comment':
                                            echo 'commented on your post.';
                                            break;
                                        case 'follow':
                                            echo 'started following you.';
                                            break;
                                        case 'mention':
                                            echo 'mentioned you in a comment.';
                                            break;
                                        case 'story_like':
                                            echo 'liked your story.';
                                            break;
                                        case 'story_reply':
                                            echo 'replied to your story: "' . htmlspecialchars(substr($notification['message'], 0, 50)) . (strlen($notification['message']) > 50 ? '...' : '') . '"';
                                            break;
                                        case 'share':
                                            echo 'shared a post with you.';
                                            break;
                                        default:
                                            echo 'interacted with your content.';
                                    }
                                    ?>
                                </div>
                                
                                <span class="notification-time"><?php echo timeAgo($notification['created_at']); ?></span>
                            </div>
                            
                            <?php if ($notification['post_id'] || $notification['story_id']): ?>
                                <div class="notification-media">
                                    <?php if ($notification['post_id'] && $notification['post_image']): ?>
                                        <a href="post.php?id=<?php echo $notification['post_id']; ?>">
                                            <img src="<?php echo $notification['post_image']; ?>" alt="Post">
                                        </a>
                                    <?php elseif ($notification['story_id'] && $notification['story_media']): ?>
                                        <a href="view-story.php?id=<?php echo $notification['story_id']; ?>">
                                            <img src="<?php echo $notification['story_media']; ?>" alt="Story">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

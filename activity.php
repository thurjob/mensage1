<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's activity (likes, comments, follows)
$user_id = $_SESSION['user_id'];

// Get recent likes
$recent_likes = getRecentUserLikes($user_id, 10);

// Get recent comments
$recent_comments = getRecentUserComments($user_id, 10);

// Get recent follows
$recent_follows = getRecentUserFollows($user_id, 10);

// Get dark mode preference
$settings = getUserSettings($user_id);
$dark_mode = $settings['dark_mode'];

// Helper function to get recent likes by user
function getRecentUserLikes($user_id, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT l.*, p.image_path, p.caption, u.username, u.profile_pic, u.is_verified
        FROM likes l
        JOIN posts p ON l.post_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    
    return $stmt->fetchAll();
}

// Helper function to get recent comments by user
function getRecentUserComments($user_id, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT c.*, p.image_path, p.caption, u.username, u.profile_pic, u.is_verified
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    
    return $stmt->fetchAll();
}

// Helper function to get recent follows by user
function getRecentUserFollows($user_id, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT f.*, u.username, u.profile_pic, u.is_verified, u.bio
        FROM follows f
        JOIN users u ON f.following_id = u.id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="activity-container">
            <h1 class="page-title">Your Activity</h1>
            
            <div class="activity-tabs">
                <button class="activity-tab active" data-tab="likes">Likes</button>
                <button class="activity-tab" data-tab="comments">Comments</button>
                <button class="activity-tab" data-tab="follows">Follows</button>
            </div>
            
            <div class="activity-content">
                <!-- Likes Tab -->
                <div class="activity-tab-content active" id="likes-tab">
                    <?php if (empty($recent_likes)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <h2>No Likes Yet</h2>
                            <p>When you like posts, they'll appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($recent_likes as $like): ?>
                                <div class="activity-item">
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <span class="activity-type">You liked a post by</span>
                                            <a href="profile.php?username=<?php echo $like['username']; ?>" class="activity-username">
                                                <?php echo $like['username']; ?>
                                                <?php if ($like['is_verified']): ?>
                                                    <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                <?php endif; ?>
                                            </a>
                                            <span class="activity-time"><?php echo timeAgo($like['created_at']); ?></span>
                                        </div>
                                        <p class="activity-caption"><?php echo substr($like['caption'], 0, 100); echo (strlen($like['caption']) > 100) ? '...' : ''; ?></p>
                                    </div>
                                    
                                    <div class="activity-media">
                                        <a href="post.php?id=<?php echo $like['post_id']; ?>">
                                            <img src="<?php echo $like['image_path']; ?>" alt="Post">
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Comments Tab -->
                <div class="activity-tab-content" id="comments-tab">
                    <?php if (empty($recent_comments)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            <h2>No Comments Yet</h2>
                            <p>When you comment on posts, they'll appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($recent_comments as $comment): ?>
                                <div class="activity-item">
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <span class="activity-type">You commented on a post by</span>
                                            <a href="profile.php?username=<?php echo $comment['username']; ?>" class="activity-username">
                                                <?php echo $comment['username']; ?>
                                                <?php if ($comment['is_verified']): ?>
                                                    <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                <?php endif; ?>
                                            </a>
                                            <span class="activity-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                        </div>
                                        <p class="activity-comment">Your comment: "<?php echo $comment['comment']; ?>"</p>
                                    </div>
                                    
                                    <div class="activity-media">
                                        <a href="post.php?id=<?php echo $comment['post_id']; ?>">
                                            <img src="<?php echo $comment['image_path']; ?>" alt="Post">
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Follows Tab -->
                <div class="activity-tab-content" id="follows-tab">
                    <?php if (empty($recent_follows)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <h2>No Follows Yet</h2>
                            <p>When you follow users, they'll appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list follows-list">
                            <?php foreach ($recent_follows as $follow): ?>
                                <div class="follow-item">
                                    <div class="follow-user">
                                        <a href="profile.php?username=<?php echo $follow['username']; ?>" class="follow-avatar">
                                            <img src="<?php echo $follow['profile_pic'] ? $follow['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $follow['username']; ?>">
                                        </a>
                                        
                                        <div class="follow-info">
                                            <a href="profile.php?username=<?php echo $follow['username']; ?>" class="follow-username">
                                                <?php echo $follow['username']; ?>
                                                <?php if ($follow['is_verified']): ?>
                                                    <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                <?php endif; ?>
                                            </a>
                                            <p class="follow-bio"><?php echo substr($follow['bio'], 0, 100); echo (strlen($follow['bio']) > 100) ? '...' : ''; ?></p>
                                            <span class="follow-time">Followed <?php echo timeAgo($follow['created_at']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="follow-action">
                                        <button class="btn btn-outline following-btn">Following</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Tab switching functionality
    const tabs = document.querySelectorAll('.activity-tab');
    const tabContents = document.querySelectorAll('.activity-tab-content');
    
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
    
    // Following button functionality
    const followingBtns = document.querySelectorAll('.following-btn');
    
    followingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('following')) {
                this.textContent = 'Follow';
                this.classList.remove('following');
                this.classList.remove('btn-outline');
                this.classList.add('btn-primary');
            } else {
                this.textContent = 'Following';
                this.classList.add('following');
                this.classList.add('btn-outline');
                this.classList.remove('btn-primary');
            }
        });
        
        // Show "Unfollow" on hover
        btn.addEventListener('mouseenter', function() {
            if (this.classList.contains('following')) {
                this.textContent = 'Unfollow';
            }
        });
        
        // Show "Following" when not hovering
        btn.addEventListener('mouseleave', function() {
            if (this.classList.contains('following')) {
                this.textContent = 'Following';
            }
        });
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

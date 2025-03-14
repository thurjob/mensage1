<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if username is provided
if (!isset($_GET['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_GET['username'];
$user = getUserProfile($username);

// Check if user exists
if (!$user) {
    header('Location: 404.php');
    exit;
}

$posts = getUserPosts($user['id']);
$followers = getFollowers($user['id']);
$following = getFollowing($user['id']);

$is_following = false;
if (isset($_SESSION['user_id'])) {
    $is_following = isFollowing($_SESSION['user_id'], $user['id']);
}

// Process follow/unfollow
if (isset($_POST['follow']) && isset($_SESSION['user_id'])) {
    followUser($_SESSION['user_id'], $user['id']);
    header('Location: profile.php?username=' . $username);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['username']; ?> - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $user['username']; ?>" class="profile-pic-large">
                </div>
                <div class="profile-info">
                    <div class="profile-username-container">
                        <h1 class="profile-username"><?php echo $user['username']; ?>
                            <?php if ($user['is_verified']): ?>
                                <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <?php endif; ?>
                        </h1>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $user['id']): ?>
                            <form action="profile.php?username=<?php echo $username; ?>" method="POST" class="follow-form">
                                <input type="hidden" name="follow" value="1">
                                <button type="submit" class="btn <?php echo $is_following ? 'btn-outline' : 'btn-primary'; ?>">
                                    <?php echo $is_following ? 'Following' : 'Follow'; ?>
                                </button>
                            </form>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user['id']): ?>
                            <a href="edit-profile.php" class="btn btn-outline">Edit Profile</a>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] && $_SESSION['user_id'] !== $user['id']): ?>
                            <div class="admin-actions">
                                <form action="admin/verify-user.php" method="POST" class="admin-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo $user['is_verified'] ? 'unverify' : 'verify'; ?>">
                                    <button type="submit" class="btn btn-small <?php echo $user['is_verified'] ? 'btn-outline' : 'btn-primary'; ?>">
                                        <?php echo $user['is_verified'] ? 'Remove Verification' : 'Verify User'; ?>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo count($posts); ?></span>
                            <span class="stat-label">posts</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo count($followers); ?></span>
                            <span class="stat-label">followers</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo count($following); ?></span>
                            <span class="stat-label">following</span>
                        </div>
                    </div>
                    
                    <div class="profile-bio">
                        <?php if ($user['full_name']): ?>
                            <h2 class="profile-name"><?php echo $user['full_name']; ?></h2>
                        <?php endif; ?>
                        
                        <?php if ($user['bio']): ?>
                            <p class="bio-text"><?php echo nl2br($user['bio']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="profile-tabs">
                <button class="tab-btn active" data-tab="posts">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Posts
                </button>
                <button class="tab-btn" data-tab="saved">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    Saved
                </button>
                <button class="tab-btn" data-tab="tagged">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Tagged
                </button>
            </div>
            
            <div class="tab-content" id="posts-tab">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <h2>No Posts Yet</h2>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user['id']): ?>
                            <p>Share your first photo or video</p>
                            <a href="create-post.php" class="btn btn-primary">Create Post</a>
                        <?php else: ?>
                            <p>When <?php echo $user['username']; ?> shares photos, you'll see them here.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($posts as $post): ?>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="post-grid-item">
                                <img src="<?php echo $post['image_path']; ?>" alt="Post by <?php echo $user['username']; ?>" class="post-grid-img">
                                <div class="post-grid-overlay">
                                    <div class="post-grid-stats">
                                        <div class="post-grid-stat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                            <span><?php echo getLikes($post['id']); ?></span>
                                        </div>
                                        <div class="post-grid-stat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                            <?php 
                                            $commentCount = count(getComments($post['id']));
                                            echo $commentCount;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content hidden" id="saved-tab">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user['id']): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                        <h2>Save</h2>
                        <p>Save photos and videos that you want to see again.</p>
                    </div>
                <?php else: ?>
                    <div class="private-content">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <h2>This Tab Is Private</h2>
                        <p>Only <?php echo $user['username']; ?> can see what they've saved.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content hidden" id="tagged-tab">
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    <h2>No Photos</h2>
                    <p>When people tag <?php echo $user['username']; ?> in photos, they'll appear here.</p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Tab switching functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            
            // Remove active class from all buttons and hide all contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.add('hidden'));
            
            // Add active class to clicked button and show corresponding content
            btn.classList.add('active');
            document.getElementById(tabId + '-tab').classList.remove('hidden');
        });
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

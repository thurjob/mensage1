<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get posts for explore page (posts from users the current user doesn't follow)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if ($is_logged_in) {
    $posts = getExplorePostsForUser($_SESSION['user_id'], $limit, $offset);
} else {
    $posts = getPosts($limit, $offset);
}

// Get dark mode preference
$dark_mode = false;
if ($is_logged_in) {
    $settings = getUserSettings($_SESSION['user_id']);
    $dark_mode = $settings['dark_mode'];
}

// Helper function to get explore posts for a user
function getExplorePostsForUser($user_id, $limit = 12, $offset = 0) {
    global $pdo;
    
    // Get posts from users the current user doesn't follow
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id != ? 
        AND p.user_id NOT IN (
            SELECT following_id FROM follows WHERE follower_id = ?
        )
        ORDER BY 
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) DESC,
            p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $user_id, $limit, $offset]);
    
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="explore-container">
            <div class="explore-header">
                <h1 class="page-title">Explore</h1>
                
                <div class="explore-search">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search users..." class="search-input">
                        <button type="submit" class="search-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    <h2>No Posts to Explore</h2>
                    <p>Check back later for new content to discover.</p>
                </div>
            <?php else: ?>
                <div class="explore-grid">
                    <?php foreach ($posts as $post): ?>
                        <div class="explore-item">
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="explore-post">
                                <img src="<?php echo $post['image_path']; ?>" alt="Post by <?php echo $post['username']; ?>" class="explore-image">
                                
                                <div class="explore-overlay">
                                    <div class="explore-stats">
                                        <div class="explore-stat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                            <span><?php echo $post['likes_count']; ?></span>
                                        </div>
                                        
                                        <div class="explore-stat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                            <span><?php echo $post['comments_count']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            
                            <div class="explore-user">
                                <a href="profile.php?username=<?php echo $post['username']; ?>" class="explore-user-link">
                                    <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $post['username']; ?>" class="explore-user-pic">
                                    <span class="explore-username">
                                        <?php echo $post['username']; ?>
                                        <?php if ($post['is_verified']): ?>
                                            <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="explore.php?page=<?php echo $page - 1; ?>" class="pagination-link prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php if (count($posts) == $limit): ?>
                        <a href="explore.php?page=<?php echo $page + 1; ?>" class="pagination-link next">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

<div class="feed-container">
    <div class="posts-container">
        <?php
        $posts = getPosts();
        if (empty($posts)): 
        ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <h2>No posts yet</h2>
                <p>Follow some users or create your first post</p>
                <a href="create-post.php" class="btn btn-primary">Create Post</a>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-header">
                        <a href="profile.php?username=<?php echo $post['username']; ?>" class="post-user">
                            <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $post['username']; ?>" class="post-user-pic">
                            <div class="post-user-info">
                                <span class="post-username"><?php echo $post['username']; ?>
                                    <?php if ($post['is_verified']): ?>
                                        <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </a>
                        <button class="post-options-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                        </button>
                    </div>
                    <div class="post-image">
                        <img src="<?php echo $post['image_path']; ?>" alt="Post by <?php echo $post['username']; ?>" class="post-img">
                    </div>
                    <div class="post-actions">
                        <button class="post-action-btn like-btn" data-post-id="<?php echo $post['id']; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </button>
                        <button class="post-action-btn comment-btn" data-post-id="<?php echo $post['id']; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </button>
                        <button class="post-action-btn share-btn" data-post-id="<?php echo $post['id']; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                    <div class="post-likes">
                        <span class="likes-count"><?php echo getLikes($post['id']); ?> likes</span>
                    </div>
                    <div class="post-caption">
                        <span class="caption-username"><?php echo $post['username']; ?></span>
                        <span class="caption-text"><?php echo $post['caption']; ?></span>
                    </div>
                    <div class="post-comments">
                        <?php 
                        $comments = getComments($post['id']);
                        if (!empty($comments)): 
                            $commentCount = count($comments);
                            if ($commentCount > 2) {
                                echo '<a href="post.php?id=' . $post['id'] . '" class="view-comments-link">View all ' . $commentCount . ' comments</a>';
                                $comments = array_slice($comments, 0, 2);
                            }
                            foreach ($comments as $comment): 
                        ?>
                            <div class="comment">
                                <span class="comment-username"><?php echo $comment['username']; ?></span>
                                <span class="comment-text"><?php echo $comment['comment']; ?></span>
                            </div>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </div>
                    <div class="post-time">
                        <span class="time-ago"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                    <div class="post-comment-form">
                        <form action="add-comment.php" method="POST" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="text" name="comment" placeholder="Add a comment..." class="comment-input">
                            <button type="submit" class="comment-submit">Post</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="sidebar">
        <div class="user-profile-card">
            <?php
            $user_id = $_SESSION['user_id'];
            $user = getUserProfile($_SESSION['username']);
            ?>
            <a href="profile.php?username=<?php echo $user['username']; ?>" class="user-profile-link">
                <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $user['username']; ?>" class="user-profile-pic">
                <div class="user-profile-info">
                    <span class="user-profile-username"><?php echo $user['username']; ?>
                        <?php if ($user['is_verified']): ?>
                            <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?php endif; ?>
                    </span>
                    <span class="user-profile-name"><?php echo $user['full_name']; ?></span>
                </div>
            </a>
        </div>
        
        <div class="suggestions-card">
            <div class="suggestions-header">
                <span class="suggestions-title">Suggestions For You</span>
                <a href="explore.php" class="see-all-link">See All</a>
            </div>
            <div class="suggestions-list">
                <?php
                // Get suggested users (not following)
                $stmt = $pdo->prepare("
                    SELECT u.* FROM users u
                    WHERE u.id != ? AND u.id NOT IN (
                        SELECT following_id FROM follows WHERE follower_id = ?
                    )
                    LIMIT 5
                ");
                $stmt->execute([$user_id, $user_id]);
                $suggestions = $stmt->fetchAll();
                
                foreach ($suggestions as $suggestion):
                ?>
                <div class="suggestion-item">
                    <a href="profile.php?username=<?php echo $suggestion['username']; ?>" class="suggestion-user">
                        <img src="<?php echo $suggestion['profile_pic'] ? $suggestion['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $suggestion['username']; ?>" class="suggestion-user-pic">
                        <div class="suggestion-user-info">
                            <span class="suggestion-username"><?php echo $suggestion['username']; ?>
                                <?php if ($suggestion['is_verified']): ?>
                                    <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" view  ?>
                                    <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <?php endif; ?>
                            </span>
                            <span class="suggestion-user-meta">Suggested for you</span>
                        </div>
                    </a>
                    <button class="follow-btn" data-user-id="<?php echo $suggestion['id']; ?>">Follow</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="footer-links-small">
            <a href="about.php">About</a> &middot;
            <a href="help.php">Help</a> &middot;
            <a href="privacy.php">Privacy</a> &middot;
            <a href="terms.php">Terms</a>
            <p class="copyright-small">© <?php echo date('Y'); ?> Monogram</p>
        </div>
    </div>
</div>

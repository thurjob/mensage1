<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if post ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = $_GET['id'];
$post = getPostById($post_id);

// Check if post exists
if (!$post) {
    header('Location: index.php');
    exit;
}

// Get post comments
$comments = getComments($post_id, 100); // Get up to 100 comments

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_liked = false;

if ($is_logged_in) {
    $is_liked = isPostLiked($_SESSION['user_id'], $post_id);
}

// Process comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $is_logged_in) {
    $comment = trim($_POST['comment']);
    
    if (!empty($comment)) {
        $result = addComment($_SESSION['user_id'], $post_id, $comment);
        
        if ($result) {
            // Redirect to avoid form resubmission
            header('Location: post.php?id=' . $post_id);
            exit;
        }
    }
}

// Get dark mode preference
$dark_mode = false;
if ($is_logged_in) {
    $settings = getUserSettings($_SESSION['user_id']);
    $dark_mode = $settings['dark_mode'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post by <?php echo $post['username']; ?> - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="post-detail-container">
            <div class="post-detail-card">
                <div class="post-detail-image">
                    <img src="<?php echo $post['image_path']; ?>" alt="Post by <?php echo $post['username']; ?>">
                </div>
                
                <div class="post-detail-content">
                    <div class="post-detail-header">
                        <div class="post-user-info">
                            <a href="profile.php?username=<?php echo $post['username']; ?>" class="post-user-link">
                                <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $post['username']; ?>" class="post-user-pic">
                                <div class="post-user-details">
                                    <span class="post-username"><?php echo $post['username']; ?>
                                        <?php if ($post['is_verified']): ?>
                                            <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                        
                        <div class="post-actions">
                            <?php if ($is_logged_in): ?>
                                <button type="button" class="post-action-btn share-btn" data-post-id="<?php echo $post['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="post-detail-caption">
                        <p><strong><?php echo $post['username']; ?></strong> <?php echo $post['caption']; ?></p>
                        <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                    
                    <div class="post-detail-comments">
                        <h3>Comments (<?php echo count($comments); ?>)</h3>
                        
                        <?php if (empty($comments)): ?>
                            <p class="no-comments">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment">
                                        <div class="comment-user">
                                            <a href="profile.php?username=<?php echo $comment['username']; ?>" class="comment-user-link">
                                                <img src="<?php echo $comment['profile_pic'] ? $comment['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $comment['username']; ?>" class="comment-user-pic">
                                            </a>
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <a href="profile.php?username=<?php echo $comment['username']; ?>" class="comment-username">
                                                    <?php echo $comment['username']; ?>
                                                    <?php if ($comment['is_verified']): ?>
                                                        <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                    <?php endif; ?>
                                                </a>
                                                <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                            </div>
                                            <p class="comment-text"><?php echo $comment['comment']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-detail-actions">
                        <div class="post-action-buttons">
                            <?php if ($is_logged_in): ?>
                                <button type="button" class="like-btn <?php echo $is_liked ? 'liked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                    <?php if ($is_liked): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php endif; ?>
                                </button>
                                
                                <button type="button" class="comment-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                </button>
                                
                                <button type="button" class="share-btn" data-post-id="<?php echo $post['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-likes">
                            <span class="likes-count"><?php echo $post['likes_count']; ?> likes</span>
                        </div>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                        <div class="post-comment-form">
                            <form action="post.php?id=<?php echo $post['id']; ?>" method="POST" class="comment-form">
                                <input type="text" name="comment" placeholder="Add a comment..." class="comment-input" required>
                                <button type="submit" class="comment-submit">Post</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Share Post Modal -->
    <?php if ($is_logged_in): ?>
    <div class="modal" id="sharePostModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Share Post</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="share-post-preview">
                    <img src="<?php echo $post['image_path']; ?>" alt="Post preview" class="share-post-image">
                    <div class="share-post-info">
                        <div class="share-post-user">
                            <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $post['username']; ?>" class="share-post-user-pic">
                            <span><?php echo $post['username']; ?></span>
                        </div>
                        <p class="share-post-caption"><?php echo substr($post['caption'], 0, 100); echo (strlen($post['caption']) > 100) ? '...' : ''; ?></p>
                    </div>
                </div>
                
                <div class="share-post-message">
                    <textarea id="shareMessage" placeholder="Write a message..." class="share-message-input"></textarea>
                </div>
                
                <div class="search-users">
                    <h4>Share with</h4>
                    <input type="text" id="userSearch" placeholder="Search for a user..." class="search-input">
                    <div id="searchResults" class="search-results"></div>
                </div>
                
                <div class="selected-users">
                    <h4>Selected Users</h4>
                    <div id="selectedUsersList" class="selected-users-list"></div>
                </div>
                
                <button id="sharePostBtn" class="btn btn-primary btn-block" disabled>Share</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            likePost(postId, this);
        });  {
            const postId = this.getAttribute('data-post-id');
            likePost(postId, this);
        });
    });

    // Like post function
    function likePost(postId, button) {
        fetch('like-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle like button appearance
                button.classList.toggle('liked');
                
                if (button.classList.contains('liked')) {
                    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>`;
                } else {
                    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>`;
                }

                // Update like count
                const likesCount = document.querySelector('.likes-count');
                if (likesCount) {
                    likesCount.textContent = data.likes + ' likes';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Share post functionality
    const shareButtons = document.querySelectorAll('.share-btn');
    const sharePostModal = document.getElementById('sharePostModal');
    const closeModal = document.querySelector('.close-modal');
    const userSearch = document.getElementById('userSearch');
    const searchResults = document.getElementById('searchResults');
    const selectedUsersList = document.getElementById('selectedUsersList');
    const sharePostBtn = document.getElementById('sharePostBtn');
    const shareMessage = document.getElementById('shareMessage');

    let selectedUsers = [];

    // Open share modal
    if (shareButtons) {
        shareButtons.forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                sharePostBtn.setAttribute('data-post-id', postId);
                sharePostModal.style.display = 'flex';
            });
        });
    }

    // Close modal
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            sharePostModal.style.display = 'none';
            // Reset selected users
            selectedUsers = [];
            selectedUsersList.innerHTML = '';
            searchResults.innerHTML = '';
            userSearch.value = '';
            shareMessage.value = '';
            sharePostBtn.disabled = true;
        });
    }

    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === sharePostModal) {
            sharePostModal.style.display = 'none';
            // Reset selected users
            selectedUsers = [];
            selectedUsersList.innerHTML = '';
            searchResults.innerHTML = '';
            userSearch.value = '';
            shareMessage.value = '';
            sharePostBtn.disabled = true;
        }
    });

    // User search functionality
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }
            
            fetch('search-users.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    
                    if (data.length === 0) {
                        searchResults.innerHTML = '<p class="no-results">No users found</p>';
                        return;
                    }
                    
                    data.forEach(user => {
                        // Skip already selected users
                        if (selectedUsers.some(u => u.id === user.id)) {
                            return;
                        }
                        
                        const userItem = document.createElement('div');
                        userItem.className = 'user-result';
                        
                        userItem.innerHTML = `
                            <img src="${user.profile_pic || 'assets/images/default-avatar.png'}" alt="${user.username}" class="user-result-avatar">
                            <div class="user-result-info">
                                <span class="user-result-name">${user.username}</span>
                                ${user.is_verified ? '<svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
                                <span class="user-result-fullname">${user.full_name || ''}</span>
                            </div>
                            <button class="add-user-btn">Add</button>
                        `;
                        
                        userItem.querySelector('.add-user-btn').addEventListener('click', function() {
                            addUser(user.id, user.username, user.profile_pic || 'assets/images/default-avatar.png', user.is_verified);
                            userItem.remove();
                        });
                        
                        searchResults.appendChild(userItem);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchResults.innerHTML = '<p class="no-results">Error searching for users</p>';
                });
        });
    }

    // Add user to selected list
    function addUser(id, username, profilePic, isVerified) {
        // Check if user is already selected
        if (selectedUsers.some(user => user.id === id)) {
            return;
        }
        
        // Add user to selected users array
        const user = {
            id: id,
            username: username,
            profilePic: profilePic,
            isVerified: isVerified
        };
        
        selectedUsers.push(user);
        
        // Add to selected users list
        const userElement = document.createElement('div');
        userElement.className = 'selected-user';
        userElement.dataset.id = id;
        
        userElement.innerHTML = `
            <img src="${profilePic}" alt="${username}" class="selected-user-avatar">
            <span class="selected-user-name">${username}</span>
            ${isVerified ? '<svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
            <button class="remove-user-btn" data-id="${id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;
        
        // Add remove button functionality
        userElement.querySelector('.remove-user-btn').addEventListener('click', function() {
            removeUser(id);
            userElement.remove();
        });
        
        selectedUsersList.appendChild(userElement);
        
        // Enable share button if at least one user is selected
        sharePostBtn.disabled = selectedUsers.length === 0;
        
        // Clear search input
        userSearch.value = '';
        searchResults.innerHTML = '';
    }

    // Remove user from selected list
    function removeUser(id) {
        selectedUsers = selectedUsers.filter(user => user.id !== id);
        
        // Disable share button if no users are selected
        sharePostBtn.disabled = selectedUsers.length === 0;
    }

    // Share post
    if (sharePostBtn) {
        sharePostBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const message = shareMessage.value.trim();
            
            if (selectedUsers.length === 0) {
                return;
            }
            
            // Disable button to prevent multiple submissions
            this.disabled = true;
            this.textContent = 'Sharing...';
            
            // Share with each selected user
            const sharePromises = selectedUsers.map(user => {
                return fetch('share-post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&user_id=${user.id}&message=${encodeURIComponent(message)}`,
                })
                .then(response => response.json());
            });
            
            Promise.all(sharePromises)
                .then(results => {
                    // Check if all shares were successful
                    const allSuccessful = results.every(data => data.success);
                    
                    if (allSuccessful) {
                        alert('Post shared successfully!');
                        
                        // Close modal and reset
                        sharePostModal.style.display = 'none';
                        selectedUsers = [];
                        selectedUsersList.innerHTML = '';
                        shareMessage.value = '';
                    } else {
                        alert('There was an error sharing the post with some users.');
                    }
                    
                    // Re-enable button
                    sharePostBtn.disabled = false;
                    sharePostBtn.textContent = 'Share';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error sharing the post.');
                    
                    // Re-enable button
                    sharePostBtn.disabled = false;
                    sharePostBtn.textContent = 'Share';
                });
        });
    }

    // Focus comment input when comment button is clicked
    const commentBtn = document.querySelector('.comment-btn');
    if (commentBtn) {
        commentBtn.addEventListener('click', function() {
            document.querySelector('.comment-input').focus();
        });
    }
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>


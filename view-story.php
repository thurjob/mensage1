<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if story ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$story_id = $_GET['id'];
$story = getStoryById($story_id);

// Check if story exists and hasn't expired
if (!$story) {
    header('Location: index.php');
    exit;
}

// Mark story as viewed if user is logged in
if (isset($_SESSION['user_id'])) {
    viewStory($_SESSION['user_id'], $story_id);
}

// Get user's other active stories
$user_stories = getUserStories($story['user_id']);

// Find current story index
$current_index = 0;
foreach ($user_stories as $index => $s) {
    if ($s['id'] == $story_id) {
        $current_index = $index;
        break;
    }
}

// Get previous and next story IDs
$prev_story_id = ($current_index > 0) ? $user_stories[$current_index - 1]['id'] : null;
$next_story_id = ($current_index < count($user_stories) - 1) ? $user_stories[$current_index + 1]['id'] : null;

// Get users with stories for the story tray
$users_with_stories = getUsersWithStories();

// Check if story is liked by current user
$is_liked = false;
if (isset($_SESSION['user_id'])) {
    $is_liked = isStoryLiked($_SESSION['user_id'], $story_id);
}

// Process like action
if (isset($_POST['like']) && isset($_SESSION['user_id'])) {
    likeStory($_SESSION['user_id'], $story_id);
    header('Location: view-story.php?id=' . $story_id);
    exit;
}

// Process reply action
$reply_sent = false;
if (isset($_POST['reply']) && isset($_SESSION['user_id'])) {
    $reply = trim($_POST['reply']);
    if (!empty($reply)) {
        $result = replyToStory($_SESSION['user_id'], $story_id, $reply);
        if ($result) {
            $reply_sent = true;
        }
    }
}

// Get story replies
$replies = getStoryReplies($story_id);

// Get dark mode preference
$dark_mode = false;
if (isset($_SESSION['user_id'])) {
    $settings = getUserSettings($_SESSION['user_id']);
    $dark_mode = $settings['dark_mode'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body class="story-view-body <?php if ($dark_mode): ?>dark-mode<?php endif; ?>">
    <div class="story-container">
        <div class="story-header">
            <div class="story-user-info">
                <a href="profile.php?username=<?php echo $story['username']; ?>" class="story-user-link">
                    <img src="<?php echo $story['profile_pic'] ? $story['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $story['username']; ?>" class="story-user-pic">
                    <div class="story-user-details">
                        <span class="story-username"><?php echo $story['username']; ?>
                            <?php if ($story['is_verified']): ?>
                                <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <?php endif; ?>
                        </span>
                        <span class="story-time"><?php echo timeAgo($story['created_at']); ?></span>
                    </div>
                </a>
            </div>
            
            <div class="story-actions">
                <a href="index.php" class="story-close-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </a>
            </div>
        </div>
        
        <div class="story-content">
            <?php if ($prev_story_id): ?>
                <a href="view-story.php?id=<?php echo $prev_story_id; ?>" class="story-nav story-prev">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
            <?php endif; ?>
            
            <div class="story-media-container">
                <div class="story-progress-bar">
                    <?php foreach ($user_stories as $s): ?>
                        <div class="story-progress <?php echo $s['id'] == $story_id ? 'active' : ($s['id'] < $story_id ? 'viewed' : ''); ?>"></div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($story['type'] == 'video'): ?>
                    <video src="<?php echo $story['media_path']; ?>" controls autoplay class="story-media story-video"></video>
                <?php else: ?>
                    <img src="<?php echo $story['media_path']; ?>" alt="Story by <?php echo $story['username']; ?>" class="story-media">
                <?php endif; ?>
                
                <div class="story-info">
                    <div class="story-views">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span><?php echo getStoryViews($story['id']); ?></span>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="button" class="story-like-btn <?php echo $is_liked ? 'liked' : ''; ?>" data-story-id="<?php echo $story_id; ?>">
                            <?php if ($is_liked): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <?php endif; ?>
                            <span><?php echo getStoryLikes($story['id']); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $story['user_id']): ?>
                    <div class="story-reply-form">
                        <form action="view-story.php?id=<?php echo $story_id; ?>" method="POST" id="storyReplyForm">
                            <input type="text" name="reply" placeholder="Reply to <?php echo $story['username']; ?>..." class="story-reply-input">
                            <button type="submit" class="story-reply-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <?php if ($reply_sent): ?>
                    <div class="reply-sent-message">Reply sent!</div>
                <?php endif; ?>
            </div>
            
            <?php if ($next_story_id): ?>
                <a href="view-story.php?id=<?php echo $next_story_id; ?>" class="story-nav story-next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="story-tray">
            <?php foreach ($users_with_stories as $user): ?>
                <a href="view-story.php?id=<?php echo $user['first_story_id']; ?>" class="story-tray-item <?php echo $user['id'] == $story['user_id'] ? 'active' : ''; ?>">
                    <div class="story-tray-avatar <?php echo (isset($_SESSION['user_id']) && hasUnseenStories($_SESSION['user_id'], $user['id'])) ? 'unseen' : ''; ?>">
                        <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $user['username']; ?>">
                    </div>
                    <span class="story-tray-username"><?php echo $user['username']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($replies) && isset($_SESSION['user_id']) && $story['user_id'] == $_SESSION['user_id']): ?>
        <div class="story-replies-container">
            <div class="story-replies-header">
                <h3>Replies</h3>
                <button class="close-replies">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="story-replies-list">
                <?php foreach ($replies as $reply): ?>
                <div class="story-reply">
                    <div class="story-reply-user">
                        <img src="<?php echo $reply['profile_pic'] ? $reply['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $reply['username']; ?>">
                        <div class="story-reply-user-info">
                            <span class="story-reply-username"><?php echo $reply['username']; ?></span>
                            <span class="story-reply-time"><?php echo timeAgo($reply['created_at']); ?></span>
                        </div>
                    </div>
                    <div class="story-reply-text"><?php echo $reply['reply']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    // Auto-advance to next story after 5 seconds for images
    <?php if ($story['type'] == 'image' && $next_story_id): ?>
    setTimeout(function() {
        window.location.href = 'view-story.php?id=<?php echo $next_story_id; ?>';
    }, 5000);
    <?php endif; ?>
    
    // For videos, advance when video ends
    <?php if ($story['type'] == 'video' && $next_story_id): ?>
    document.querySelector('.story-video').addEventListener('ended', function() {
        window.location.href = 'view-story.php?id=<?php echo $next_story_id; ?>';
    });
    <?php endif; ?>
    
    // Progress bar animation
    document.querySelector('.story-progress.active').classList.add('animate');
    
    // Story replies toggle
    const closeReplies = document.querySelector('.close-replies');
    if (closeReplies) {
        closeReplies.addEventListener('click', function() {
            document.querySelector('.story-replies-container').classList.toggle('hidden');
        });
    }
    
    // Like story functionality
    const likeBtn = document.querySelector('.story-like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const storyId = this.getAttribute('data-story-id');
            likeStory(storyId, this);
        });
    }

    // Like story function
    function likeStory(storyId, button) {
        fetch('like-story.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'story_id=' + storyId,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle like button appearance
                if (data.liked) {
                    button.classList.add('liked');
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span>${data.likes}</span>
                    `;
                } else {
                    button.classList.remove('liked');
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span>${data.likes}</span>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>


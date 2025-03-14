<?php
// User authentication functions
function registerUser($username, $email, $password) {
    global $pdo;
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        return false; // User already exists
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$username, $email, $hashedPassword]);
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_verified'] = $user['is_verified'];
        return true;
    }
    
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
}

// Post functions
function createPost($user_id, $image_path, $caption) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, image_path, caption, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$user_id, $image_path, $caption]);
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

function getPosts($limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    
    return $stmt->fetchAll();
}

function getUserPosts($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

function getPostById($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    
    return $stmt->fetch();
}

// Like functions
function likePost($user_id, $post_id) {
    global $pdo;
    
    // Check if already liked
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    
    if ($stmt->rowCount() > 0) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        return $stmt->execute([$user_id, $post_id]);
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id, created_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$user_id, $post_id]);
        
        if ($result) {
            // Add notification
            $post = getPostById($post_id);
            if ($post && $post['user_id'] != $user_id) {
                addNotification($post['user_id'], $user_id, 'like', $post_id);
            }
        }
        
        return $result;
    }
}

function getLikes($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

function isPostLiked($user_id, $post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    
    return $stmt->rowCount() > 0;
}

// Comment functions
function addComment($user_id, $post_id, $comment) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$user_id, $post_id, $comment]);
    
    if ($result) {
        $comment_id = $pdo->lastInsertId();
        
        // Add notification
        $post = getPostById($post_id);
        if ($post && $post['user_id'] != $user_id) {
            addNotification($post['user_id'], $user_id, 'comment', $post_id, $comment_id);
        }
        
        return $comment_id;
    }
    
    return false;
}

function getComments($post_id, $limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_pic, u.is_verified 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$post_id, $limit, $offset]);
    
    return $stmt->fetchAll();
}

function getCommentCount($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

// User profile functions
function getUserProfile($username) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    return $stmt->fetch();
}

function getUserById($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch();
}

function updateProfile($user_id, $bio, $profile_pic = null) {
    global $pdo;
    
    if ($profile_pic) {
        $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?");
        return $stmt->execute([$bio, $profile_pic, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        return $stmt->execute([$bio, $user_id]);
    }
}

// Admin functions
function getAllUsers($limit = 20, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    
    return $stmt->fetchAll();
}

function verifyUser($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function unverifyUser($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 0 WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function makeAdmin($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function removeAdmin($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function deleteUser($user_id) {
    global $pdo;
    
    // Don't allow deleting the current user
    if ($user_id == $_SESSION['user_id']) {
        return false;
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$user_id]);
}

// Follow functions
function followUser($follower_id, $following_id) {
    global $pdo;
    
    // Check if already following
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$follower_id, $following_id]);
    
    if ($stmt->rowCount() > 0) {
        // Unfollow
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        return $stmt->execute([$follower_id, $following_id]);
    } else {
        // Follow
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$follower_id, $following_id]);
        
        if ($result) {
            // Add notification
            addNotification($following_id, $follower_id, 'follow');
        }
        
        return $result;
    }
}

function getFollowers($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT u.* FROM follows f
        JOIN users u ON f.follower_id = u.id
        WHERE f.following_id = ?
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

function getFollowing($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT u.* FROM follows f
        JOIN users u ON f.following_id = u.id
        WHERE f.follower_id = ?
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

function isFollowing($follower_id, $following_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$follower_id, $following_id]);
    
    return $stmt->rowCount() > 0;
}

function getFollowersCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE following_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

function getFollowingCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE follower_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

// Helper functions
function uploadImage($file, $directory = 'uploads/') {
    $target_dir = $directory;
    
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}

// Story functions
function createStory($user_id, $media_path, $type = 'image') {
    global $pdo;
    
    // Stories expire after 24 hours
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare("INSERT INTO stories (user_id, media_path, type, created_at, expires_at) VALUES (?, ?, ?, NOW(), ?)");
    $result = $stmt->execute([$user_id, $media_path, $type, $expires_at]);
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

function getActiveStories() {
    global $pdo;
    
    // Get stories that haven't expired yet, grouped by user
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM story_views WHERE story_id = s.id) as view_count,
        (SELECT COUNT(*) FROM story_likes WHERE story_id = s.id) as like_count
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.expires_at > NOW()
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    
    return $stmt->fetchAll();
}

function getUserStories($user_id) {
    global $pdo;
    
    // Get active stories for a specific user
    $stmt = $pdo->prepare("
        SELECT s.*, 
        (SELECT COUNT(*) FROM story_views WHERE story_id = s.id) as view_count,
        (SELECT COUNT(*) FROM story_likes WHERE story_id = s.id) as like_count
        FROM stories s
        WHERE s.user_id = ? AND s.expires_at > NOW()
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

function getStoryById($story_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM story_views WHERE story_id = s.id) as view_count,
        (SELECT COUNT(*) FROM story_likes WHERE story_id = s.id) as like_count
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$story_id]);
    
    return $stmt->fetch();
}

function getUsersWithStories() {
    global $pdo;
    
    // Get users who have active stories
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.username, u.profile_pic, u.is_verified,
        (SELECT COUNT(*) FROM stories WHERE user_id = u.id AND expires_at > NOW()) as story_count,
        (SELECT MIN(id) FROM stories WHERE user_id = u.id AND expires_at > NOW()) as first_story_id
        FROM users u
        JOIN stories s ON u.id = s.user_id
        WHERE s.expires_at > NOW()
        ORDER BY 
            CASE WHEN u.id = ? THEN 0 ELSE 1 END, 
            s.created_at DESC
    ");
    
    // Put current user's stories first if logged in
    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $stmt->execute([$current_user_id]);
    
    return $stmt->fetchAll();
}

function viewStory($user_id, $story_id) {
    global $pdo;
    
    // Check if already viewed
    $stmt = $pdo->prepare("SELECT * FROM story_views WHERE user_id = ? AND story_id = ?");
    $stmt->execute([$user_id, $story_id]);
    
    if ($stmt->rowCount() > 0) {
        return true; // Already viewed
    }
    
    // Add view
    $stmt = $pdo->prepare("INSERT INTO story_views (user_id, story_id, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$user_id, $story_id]);
}

function likeStory($user_id, $story_id) {
    global $pdo;
    
    // Check if already liked
    $stmt = $pdo->prepare("SELECT * FROM story_likes WHERE user_id = ? AND story_id = ?");
    $stmt->execute([$user_id, $story_id]);
    
    if ($stmt->rowCount() > 0) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM story_likes WHERE user_id = ? AND story_id = ?");
        return $stmt->execute([$user_id, $story_id]);
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO story_likes (user_id, story_id, created_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$user_id, $story_id]);
        
        if ($result) {
            // Add notification
            $story = getStoryById($story_id);
            if ($story && $story['user_id'] != $user_id) {
                addNotification($story['user_id'], $user_id, 'story_like', null, null, $story_id);
            }
        }
        
        return $result;
    }
}

function isStoryLiked($user_id, $story_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM story_likes WHERE user_id = ? AND story_id = ?");
    $stmt->execute([$user_id, $story_id]);
    
    return $stmt->rowCount() > 0;
}

function getStoryLikes($story_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM story_likes WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

function getStoryViews($story_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM story_views WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

function getUserStoriesCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM stories WHERE user_id = ? AND expires_at > NOW()");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

function hasUnseenStories($viewer_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM stories s
        LEFT JOIN story_views sv ON s.id = sv.story_id AND sv.user_id = ?
        WHERE s.user_id = ? AND s.expires_at > NOW() AND sv.id IS NULL
    ");
    $stmt->execute([$viewer_id, $user_id]);
    $result = $stmt->fetch();
    
    return $result['count'] > 0;
}

// Clean up expired stories (can be run via cron job)
function cleanupExpiredStories() {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM stories WHERE expires_at <= NOW()");
    return $stmt->execute();
}

// Message functions
function sendMessage($sender_id, $receiver_id, $message) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$sender_id, $receiver_id, $message]);
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

function getConversations($user_id) {
    global $pdo;
    
    // Get all users that the current user has exchanged messages with
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            CASE 
                WHEN m.sender_id = ? THEN m.receiver_id
                ELSE m.sender_id
            END as user_id,
            u.username, u.profile_pic, u.is_verified,
            (SELECT message FROM messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)
             ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages 
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)
             ORDER BY created_at DESC LIMIT 1) as last_message_time,
            (SELECT COUNT(*) FROM messages 
             WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM messages m
        JOIN users u ON (
            CASE 
                WHEN m.sender_id = ? THEN m.receiver_id
                ELSE m.sender_id
            END = u.id
        )
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    
    return $stmt->fetchAll();
}

function getMessages($user_id, $other_user_id) {
    global $pdo;
    
    // Mark messages as read
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$other_user_id, $user_id]);
    
    // Get messages between the two users
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.profile_pic, u.is_verified
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
    
    return $stmt->fetchAll();
}

function getUnreadMessagesCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM messages 
        WHERE receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

// Story reply functions
function replyToStory($user_id, $story_id, $reply) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO story_replies (user_id, story_id, reply, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$user_id, $story_id, $reply]);
    
    if ($result) {
        $reply_id = $pdo->lastInsertId();
        
        // Add notification
        $story = getStoryById($story_id);
        if ($story && $story['user_id'] != $user_id) {
            addNotification($story['user_id'], $user_id, 'story_reply', null, null, $story_id, $reply);
        }
        
        return $reply_id;
    }
    
    return false;
}

function getStoryReplies($story_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT sr.*, u.username, u.profile_pic, u.is_verified
        FROM story_replies sr
        JOIN users u ON sr.user_id = u.id
        WHERE sr.story_id = ?
        ORDER BY sr.created_at ASC
    ");
    $stmt->execute([$story_id]);
    
    return $stmt->fetchAll();
}

// User settings functions
function getUserSettings($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM user_settings WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Create default settings if they don't exist
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        return ['user_id' => $user_id, 'dark_mode' => 0];
    }
    
    return $settings;
}

function updateDarkModePreference($user_id, $dark_mode) {
    global $pdo;
    
    // Check if settings exist
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing settings
        $stmt = $pdo->prepare("UPDATE user_settings SET dark_mode = ? WHERE user_id = ?");
        return $stmt->execute([$dark_mode, $user_id]);
    } else {
        // Create new settings
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, dark_mode) VALUES (?, ?)");
        return $stmt->execute([$user_id, $dark_mode]);
    }
}

// Apply story effects
function applyStoryEffect($image_path, $effect) {
    // Require GD library
    if (!extension_loaded('gd')) {
        return false;
    }
    
    // Get image info
    $info = getimagesize($image_path);
    if (!$info) {
        return false;
    }
    
    // Create image resource based on file type
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($image_path);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($image_path);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($image_path);
            break;
        default:
            return false;
    }
    
    // Apply effect
    switch ($effect) {
        case 'grayscale':
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            break;
        case 'sepia':
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 30);
            break;
        case 'negative':
            imagefilter($image, IMG_FILTER_NEGATE);
            break;
        case 'brightness':
            imagefilter($image, IMG_FILTER_BRIGHTNESS, 20);
            break;
        case 'contrast':
            imagefilter($image, IMG_FILTER_CONTRAST, -20);
            break;
        case 'blur':
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
            break;
        default:
            // No effect
            break;
    }
    
    // Create new filename
    $new_path = pathinfo($image_path, PATHINFO_DIRNAME) . '/' . 
                pathinfo($image_path, PATHINFO_FILENAME) . '_' . $effect . '.' . 
                pathinfo($image_path, PATHINFO_EXTENSION);
    
    // Save image with effect
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            imagejpeg($image, $new_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $new_path, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($image, $new_path);
            break;
    }
    
    // Free memory
    imagedestroy($image);
    
    return $new_path;
}

// Notification functions
function addNotification($user_id, $from_user_id, $type, $post_id = null, $comment_id = null, $story_id = null, $message = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, story_id, message, is_read, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    return $stmt->execute([$user_id, $from_user_id, $type, $post_id, $comment_id, $story_id, $message]);
}

function getNotifications($user_id, $limit = 20, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT n.*, u.username, u.profile_pic, u.is_verified,
        p.image_path as post_image, s.media_path as story_media
        FROM notifications n
        JOIN users u ON n.from_user_id = u.id
        LEFT JOIN posts p ON n.post_id = p.id
        LEFT JOIN stories s ON n.story_id = s.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    
    return $stmt->fetchAll();
}

function markNotificationsAsRead($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    return $stmt->execute([$user_id]);
}

function getUnreadNotificationsCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return $result['count'];
}

// Share post functions
function sharePost($user_id, $post_id, $shared_with_id, $message = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO shared_posts (user_id, post_id, shared_with_id, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $result = $stmt->execute([$user_id, $post_id, $shared_with_id, $message]);
    
    if ($result) {
        // Send a direct message about the shared post
        $post = getPostById($post_id);
        $user = getUserById($user_id);
        
        $dm_message = $user['username'] . " shared a post with you";
        if ($message) {
            $dm_message .= ": " . $message;
        }
        $dm_message .= "\n\nView post: " . $_SERVER['HTTP_ORIGIN'] . "/post.php?id=" . $post_id;
        
        sendMessage($user_id, $shared_with_id, $dm_message);
        
        // Add notification
        addNotification($shared_with_id, $user_id, 'share', $post_id, null, null, $message);
        
        return $pdo->lastInsertId();
    }
    
    return false;
}

function getSharedPosts($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT sp.*, p.*, u.username as shared_by_username, u.profile_pic as shared_by_pic, u.is_verified as shared_by_verified,
        pu.username as post_owner_username, pu.profile_pic as post_owner_pic, pu.is_verified as post_owner_verified
        FROM shared_posts sp
        JOIN posts p ON sp.post_id = p.id
        JOIN users u ON sp.user_id = u.id
        JOIN users pu ON p.user_id = pu.id
        WHERE sp.shared_with_id = ?
        ORDER BY sp.created_at DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

// Search functions
function searchUsers($query, $limit = 10) {
    global $pdo;
    
    $query = '%' . $query . '%';
    
    $stmt = $pdo->prepare("
        SELECT id, username, full_name, profile_pic, is_verified
        FROM users
        WHERE username LIKE ? OR full_name LIKE ?
        LIMIT ?
    ");
    $stmt->execute([$query, $query, $limit]);
    
    return $stmt->fetchAll();
}

// Report functions
function reportContent($reporter_id, $reason, $reported_user_id = null, $reported_post_id = null, $reported_comment_id = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, reported_user_id, reported_post_id, reported_comment_id, reason, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    return $stmt->execute([$reporter_id, $reported_user_id, $reported_post_id, $reported_comment_id, $reason]);
}

function getReports($status = null, $limit = 20, $offset = 0) {
    global $pdo;
    
    $query = "
        SELECT r.*, 
        ru.username as reporter_username,
        tu.username as reported_user_username,
        p.image_path as post_image,
        c.comment as reported_comment
        FROM reports r
        JOIN users ru ON r.reporter_id = ru.id
        LEFT JOIN users tu ON r.reported_user_id = tu.id
        LEFT JOIN posts p ON r.reported_post_id = p.id
        LEFT JOIN comments c ON r.reported_comment_id = c.id
    ";
    
    if ($status) {
        $query .= " WHERE r.status = ?";
        $params = [$status, $limit, $offset];
    } else {
        $params = [$limit, $offset];
    }
    
    $query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function updateReportStatus($report_id, $status) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $report_id]);
}


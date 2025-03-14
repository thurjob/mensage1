<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    header('Location: messages.php');
    exit;
}

$other_user_id = $_GET['user_id'];
$other_user = getUserById($other_user_id);

// Check if user exists
if (!$other_user) {
    header('Location: messages.php');
    exit;
}

// Get messages between the two users
$messages = getMessages($_SESSION['user_id'], $other_user_id);

// Get user's conversations for sidebar
$conversations = getConversations($_SESSION['user_id']);

// Process message sending
$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $result = sendMessage($_SESSION['user_id'], $other_user_id, $message);
        
        if ($result) {
            $message_sent = true;
            // Redirect to avoid form resubmission
            header('Location: conversation.php?user_id=' . $other_user_id);
            exit;
        }
    }
}

// Helper function to get user by ID
function getUserById($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch();
}

// Get dark mode preference
$settings = getUserSettings($_SESSION['user_id']);
$dark_mode = $settings['dark_mode'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation with <?php echo $other_user['username']; ?> - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="messages-container">
            <div class="messages-sidebar">
                <div class="messages-header">
                    <h2>Messages</h2>
                    <a href="#" class="new-message-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    </a>
                </div>
                
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="empty-conversations">
                            <p>No conversations yet</p>
                            <p>Start messaging with your friends</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="conversation.php?user_id=<?php echo $conversation['user_id']; ?>" class="conversation-item <?php echo $conversation['user_id'] == $other_user_id ? 'active' : ''; ?>">
                                <div class="conversation-avatar">
                                    <img src="<?php echo $conversation['profile_pic'] ? $conversation['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $conversation['username']; ?>">
                                    <?php if ($conversation['unread_count'] > 0 && $conversation['user_id'] != $other_user_id): ?>
                                        <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-name">
                                        <?php echo $conversation['username']; ?>
                                        <?php if ($conversation['is_verified']): ?>
                                            <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-preview">
                                        <span class="last-message"><?php echo substr($conversation['last_message'], 0, 30); echo (strlen($conversation['last_message']) > 30) ? '...' : ''; ?></span>
                                        <span class="message-time"><?php echo timeAgo($conversation['last_message_time']); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="messages-content">
                <div class="conversation-header">
                    <a href="profile.php?username=<?php echo $other_user['username']; ?>" class="conversation-user">
                        <img src="<?php echo $other_user['profile_pic'] ? $other_user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $other_user['username']; ?>" class="conversation-user-pic">
                        <div class="conversation-user-info">
                            <span class="conversation-username"><?php echo $other_user['username']; ?>
                                <?php if ($other_user['is_verified']): ?>
                                    <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <?php endif; ?>
                            </span>
                        </div>
                    </a>
                    <div class="conversation-actions">
                        <button class="conversation-info-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </button>
                    </div>
                </div>
                
                <div class="messages-list" id="messagesList">
                    <?php if (empty($messages)): ?>
                        <div class="no-messages">
                            <p>No messages yet</p>
                            <p>Start a conversation with <?php echo $other_user['username']; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                                <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                                    <img src="<?php echo $message['profile_pic'] ? $message['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $message['username']; ?>" class="message-avatar">
                                <?php endif; ?>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo $message['message']; ?>
                                    </div>
                                    <div class="message-time">
                                        <?php echo date('g:i A', strtotime($message['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="message-form-container">
                    <form action="conversation.php?user_id=<?php echo $other_user_id; ?>" method="POST" class="message-form">
                        <input type="text" name="message" placeholder="Message..." class="message-input" autocomplete="off" required>
                        <button type="submit" class="message-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"  width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- New Message Modal -->
    <div class="modal" id="newMessageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-users">
                    <input type="text" id="userSearch" placeholder="Search for a user..." class="search-input">
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Scroll to bottom of messages
    const messagesList = document.getElementById('messagesList');
    messagesList.scrollTop = messagesList.scrollHeight;
    
    // Modal functionality
    const modal = document.getElementById('newMessageModal');
    const newMessageBtn = document.querySelector('.new-message-btn');
    const closeModal = document.querySelector('.close-modal');
    
    function openModal() {
        modal.style.display = 'flex';
    }
    
    function closeModalFunc() {
        modal.style.display = 'none';
    }
    
    newMessageBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal();
    });
    
    closeModal.addEventListener('click', closeModalFunc);
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModalFunc();
        }
    });
    
    // User search functionality
    const userSearch = document.getElementById('userSearch');
    const searchResults = document.getElementById('searchResults');
    
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
                    const userItem = document.createElement('a');
                    userItem.href = 'conversation.php?user_id=' + user.id;
                    userItem.className = 'user-result';
                    
                    userItem.innerHTML = `
                        <img src="${user.profile_pic || 'assets/images/default-avatar.png'}" alt="${user.username}" class="user-result-avatar">
                        <div class="user-result-info">
                            <span class="user-result-name">${user.username}</span>
                            ${user.is_verified ? '<svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
                            <span class="user-result-fullname">${user.full_name || ''}</span>
                        </div>
                    `;
                    
                    searchResults.appendChild(userItem);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                searchResults.innerHTML = '<p class="no-results">Error searching for users</p>';
            });
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>


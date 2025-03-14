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

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];

// Check if other user exists
$other_user = getUserById($other_user_id);
if (!$other_user) {
    header('Location: messages.php');
    exit;
}

// Get messages between users
$messages = getMessages($user_id, $other_user_id);

// Process new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $result = sendMessage($user_id, $other_user_id, $message);
        
        if ($result) {
            // Redirect to avoid form resubmission
            header('Location: conversation.php?user_id=' . $other_user_id);
            exit;
        }
    }
}

// Get dark mode preference
$settings = getUserSettings($user_id);
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
                    <h2><?php echo $_SESSION['username']; ?></h2>
                    <a href="messages.php" class="back-to-messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </a>
                </div>
                
                <div class="conversations-list">
                    <?php
                    // Get conversations
                    $conversations = getConversations($user_id);
                    
                    if (!empty($conversations)):
                        foreach ($conversations as $conversation):
                            $is_active = $conversation['user_id'] == $other_user_id;
                    ?>
                        <a href="conversation.php?user_id=<?php echo $conversation['user_id']; ?>" class="conversation-item <?php echo $is_active ? 'active' : ''; ?>">
                            <div class="conversation-avatar">
                                <img src="<?php echo $conversation['profile_pic'] ? $conversation['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $conversation['username']; ?>">
                                <?php if ($conversation['unread_count'] > 0 && !$is_active): ?>
                                    <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    <?php echo $conversation['username']; ?>
                                    <?php if ($conversation['is_verified']): ?>
                                        <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="conversation-preview">
                                    <span class="last-message"><?php echo substr($conversation['last_message'], 0, 30); echo (strlen($conversation['last_message']) > 30) ? '...' : ''; ?></span>
                                    <span class="message-time"><?php echo timeAgo($conversation['last_message_time']); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            
            <div class="messages-content">
                <div class="conversation-header">
                    <div class="conversation-user">
                        <img src="<?php echo $other_user['profile_pic'] ? $other_user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $other_user['username']; ?>" class="conversation-user-pic">
                        <div class="conversation-user-info">
                            <span class="conversation-username">
                                <?php echo $other_user['username']; ?>
                                <?php if ($other_user['is_verified']): ?>
                                    <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="conversation-actions">
                        <a href="profile.php?username=<?php echo $other_user['username']; ?>" class="conversation-info-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </a>
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
                            <div class="message <?php echo $message['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <?php if ($message['sender_id'] != $user_id): ?>
                                    <img src="<?php echo $message['profile_pic'] ? $message['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $message['username']; ?>" class="message-avatar">
                                <?php endif; ?>
                                
                                <div class="message-content">
                                    <div class="message-bubble"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                    <span class="message-time"><?php echo timeAgo($message['created_at']); ?></span>
                                </div>
                                
                                <?php if ($message['sender_id'] == $user_id): ?>
                                    <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $_SESSION['username']; ?>" class="message-avatar">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="message-form-container">
                    <form action="conversation.php?user_id=<?php echo $other_user_id; ?>" method="POST" class="message-form">
                        <input type="text" name="message" placeholder="Message..." class="message-input" required>
                        <button type="submit" class="message-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Scroll to bottom of messages list
    const messagesList = document.getElementById('messagesList');
    messagesList.scrollTop = messagesList.scrollHeight;
    
    // Auto refresh messages every 10 seconds
    setInterval(function() {
        fetch('get-messages.php?user_id=<?php echo $other_user_id; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update messages list
                    let messagesHtml = '';
                    
                    if (data.messages.length === 0) {
                        messagesHtml = `
                            <div class="no-messages">
                                <p>No messages yet</p>
                                <p>Start a conversation with <?php echo $other_user['username']; ?></p>
                            </div>
                        `;
                    } else {
                        data.messages.forEach(message => {
                            const isCurrentUser = message.sender_id == <?php echo $user_id; ?>;
                            const messageClass = isCurrentUser ? 'sent' : 'received';
                            const avatarSrc = message.profile_pic ? message.profile_pic : 'assets/images/default-avatar.png';
                            const avatarAlt = isCurrentUser ? '<?php echo $_SESSION['username']; ?>' : message.username;
                            
                            messagesHtml += `
                                <div class="message ${messageClass}">
                                    ${!isCurrentUser ? `<img src="${avatarSrc}" alt="${avatarAlt}" class="message-avatar">` : ''}
                                    
                                    <div class="message-content">
                                        <div class="message-bubble">${message.message.replace(/\n/g, '<br>')}</div>
                                        <span class="message-time">${message.time_ago}</span>
                                    </div>
                                    
                                    ${isCurrentUser ? `<img src="${avatarSrc}" alt="${avatarAlt}" class="message-avatar">` : ''}
                                </div>
                            `;
                        });
                    }
                    
                    // Only update if there are new messages
                    if (data.messages.length !== <?php echo count($messages); ?>) {
                        messagesList.innerHTML = messagesHtml;
                        messagesList.scrollTop = messagesList.scrollHeight;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }, 10000);
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

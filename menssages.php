<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get conversations
$conversations = getConversations($user_id);

// Get dark mode preference
$settings = getUserSettings($user_id);
$dark_mode = $settings['dark_mode'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Monogram</title>
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
                    <button class="new-message-btn" id="newMessageBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="12" y1="11" x2="12" y2="11"></line><line x1="12" y1="8" x2="12" y2="8"></line><line x1="12" y1="14" x2="12" y2="14"></line></svg>
                    </button>
                </div>
                
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="no-conversations">
                            <p>No conversations yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="conversation.php?user_id=<?php echo $conversation['user_id']; ?>" class="conversation-item">
                                <div class="conversation-avatar">
                                    <img src="<?php echo $conversation['profile_pic'] ? $conversation['profile_pic'] : 'assets/images/default-avatar.png'; ?>" alt="<?php echo $conversation['username']; ?>">
                                    <?php if ($conversation['unread_count'] > 0): ?>
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="messages-content">
                <div class="start-conversation">
                    <div class="start-conversation-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="9" y1="10" x2="15" y2="10"></line><line x1="12" y1="7" x2="12" y2="13"></line></svg>
                    </div>
                    
                    <h2>Your Messages</h2>
                    <p>Send private messages to a friend or group</p>
                    
                    <button class="btn btn-primary" id="startConversationBtn">Send Message</button>
                </div>
            </div>
        </div>
    </main>
    
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
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // New message modal
    const newMessageBtn = document.getElementById('newMessageBtn');
    const startConversationBtn = document.getElementById('startConversationBtn');
    const newMessageModal = document.getElementById('newMessageModal');
    const closeModal = document.querySelector('.close-modal');
    const userSearch = document.getElementById('userSearch');
    const searchResults = document.getElementById('searchResults');
    
    // Open modal
    function openModal() {
        newMessageModal.style.display = 'flex';
        userSearch.focus();
    }
    
    // Close modal
    function closeModalFunc() {
        newMessageModal.style.display = 'none';
        userSearch.value = '';
        searchResults.innerHTML = '';
    }
    
    newMessageBtn.addEventListener('click', openModal);
    startConversationBtn.addEventListener('click', openModal);
    
    closeModal.addEventListener('click', closeModalFunc);
    
    window.addEventListener('click', function(e) {
        if (e.target === newMessageModal) {
            closeModalFunc();
        }
    });
    
    // User search functionality
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
                    const userItem = document.createElement('div');
                    userItem.className = 'user-result';
                    
                    userItem.innerHTML = `
                        <img src="${user.profile_pic || 'assets/images/default-avatar.png'}" alt="${user.username}" class="user-result-avatar">
                        <div class="user-result-info">
                            <span class="user-result-name">${user.username}</span>
                            ${user.is_verified ? '<svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
                            <span class="user-result-fullname">${user.full_name || ''}</span>
                        </div>
                    `;
                    
                    userItem.addEventListener('click', function() {
                        window.location.href = 'conversation.php?user_id=' + user.id;
                    });
                    
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

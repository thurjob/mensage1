<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$preview_image = '';
$selected_effect = '';
$selected_users = [];

// Process story creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['preview']) && !empty($_FILES['story_media']['name'])) {
        // Preview with effect
        $file_type = $_FILES['story_media']['type'];
        $type = 'image';
        
        // Check if it's a video
        if (strpos($file_type, 'video/') === 0) {
            $type = 'video';
            $media_path = uploadMedia($_FILES['story_media'], 'uploads/temp/', ['mp4', 'mov']);
        } else {
            $media_path = uploadImage($_FILES['story_media'], 'uploads/temp/');
        }
        
        if ($media_path) {
            $preview_image = $media_path;
            
            // Apply effect if selected
            if (isset($_POST['effect']) && $_POST['effect'] !== 'none' && $type === 'image') {
                $effect_path = applyStoryEffect($media_path, $_POST['effect']);
                if ($effect_path) {
                    $preview_image = $effect_path;
                    $selected_effect = $_POST['effect'];
                }
            }
            
            // Store selected users if any
            if (isset($_POST['selected_users']) && !empty($_POST['selected_users'])) {
                $selected_users = json_decode($_POST['selected_users'], true);
            }
        } else {
            $error = 'Failed to upload media for preview. Please try again.';
        }
    } elseif (isset($_POST['publish'])) {
        if (empty($_FILES['story_media']['name']) && empty($_POST['preview_image'])) {
            $error = 'Please select an image or video to upload';
        } else {
            $media_path = '';
            $type = 'image';
            
            // Use existing preview image or upload new one
            if (!empty($_POST['preview_image'])) {
                $media_path = $_POST['preview_image'];
                
                // Move from temp to stories folder
                $new_path = str_replace('uploads/temp/', 'uploads/stories/', $media_path);
                $dir = dirname($new_path);
                
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                if (copy($media_path, $new_path)) {
                    $media_path = $new_path;
                }
                
                // Determine type based on file extension
                $ext = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp4', 'mov'])) {
                    $type = 'video';
                }
            } else {
                $file_type = $_FILES['story_media']['type'];
                
                // Check if it's a video
                if (strpos($file_type, 'video/') === 0) {
                    $type = 'video';
                    $media_path = uploadMedia($_FILES['story_media'], 'uploads/stories/', ['mp4', 'mov']);
                } else {
                    $media_path = uploadImage($_FILES['story_media'], 'uploads/stories/');
                    
                    // Apply effect if selected
                    if (isset($_POST['effect']) && $_POST['effect'] !== 'none') {
                        $effect_path = applyStoryEffect($media_path, $_POST['effect']);
                        if ($effect_path) {
                            $media_path = $effect_path;
                        }
                    }
                }
            }
            
            if ($media_path) {
                $story_id = createStory($_SESSION['user_id'], $media_path, $type);
                
                if ($story_id) {
                    $success = 'Story created successfully!';
                    
                    // Share with selected users if any
                    if (isset($_POST['selected_users']) && !empty($_POST['selected_users'])) {
                        $selected_users = json_decode($_POST['selected_users'], true);
                        
                        foreach ($selected_users as $user) {
                            $user_id = $user['id'];
                            $message = "I shared a story with you. Check it out!";
                            $message .= "\n\nView story: " . $_SERVER['HTTP_ORIGIN'] . "/view-story.php?id=" . $story_id;
                            
                            sendMessage($_SESSION['user_id'], $user_id, $message);
                        }
                    }
                    
                    // Redirect to the feed
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Failed to create story. Please try again.';
                }
            } else {
                $error = 'Failed to upload media. Please try again.';
            }
        }
    }
}

// Helper function to upload videos
function uploadMedia($file, $directory = 'uploads/', $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov']) {
    $target_dir = $directory;
    
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file is a valid upload
    if (!in_array($extension, $allowed_extensions)) {
        return false;
    }
    
    // Check file size (limit to 50MB for videos)
    if ($file["size"] > 50000000) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
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
    <title>Create Story - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="create-story-container">
            <h1 class="page-title">Create New Story</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="create-story.php" method="POST" enctype="multipart/form-data" class="create-story-form">
                <?php if ($preview_image): ?>
                    <div class="story-preview-container">
                        <div class="story-preview-wrapper">
                            <?php 
                            $ext = strtolower(pathinfo($preview_image, PATHINFO_EXTENSION));
                            if (in_array($ext, ['mp4', 'mov'])): 
                            ?>
                                <video src="<?php echo $preview_image; ?>" controls class="story-preview-media"></video>
                            <?php else: ?>
                                <img src="<?php echo $preview_image; ?>" alt="Story Preview" class="story-preview-media">
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="preview_image" value="<?php echo $preview_image; ?>">
                        <input type="hidden" name="selected_users" id="selectedUsersInput" value="<?php echo htmlspecialchars(json_encode($selected_users)); ?>">
                        
                        <?php if (!in_array($ext, ['mp4', 'mov'])): ?>
                            <div class="story-effects">
                                <h3>Apply Effect</h3>
                                <div class="effects-list">
                                    <label class="effect-item <?php echo $selected_effect === 'none' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="none" <?php echo $selected_effect === 'none' ? 'checked' : ''; ?>>
                                        <span>None</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'grayscale' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="grayscale" <?php echo $selected_effect === 'grayscale' ? 'checked' : ''; ?>>
                                        <span>B&W</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'sepia' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="sepia" <?php echo $selected_effect === 'sepia' ? 'checked' : ''; ?>>
                                        <span>Sepia</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'negative' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="negative" <?php echo $selected_effect === 'negative' ? 'checked' : ''; ?>>
                                        <span>Negative</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'brightness' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="brightness" <?php echo $selected_effect === 'brightness' ? 'checked' : ''; ?>>
                                        <span>Bright</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'contrast' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="contrast" <?php echo $selected_effect === 'contrast' ? 'checked' : ''; ?>>
                                        <span>Contrast</span>
                                    </label>
                                    <label class="effect-item <?php echo $selected_effect === 'blur' ? 'active' : ''; ?>">
                                        <input type="radio" name="effect" value="blur" <?php echo $selected_effect === 'blur' ? 'checked' : ''; ?>>
                                        <span>Blur</span>
                                    </label>
                                </div>
                                <button type="submit" name="preview" class="btn btn-outline">Apply Effect</button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="story-share-options">
                            <h3>Share with</h3>
                            <button type="button" id="shareWithUsers" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                Select Users
                            </button>
                            
                            <div id="selectedUsersList" class="selected-users-list">
                                <?php foreach ($selected_users as $user): ?>
                                <div class="selected-user" data-id="<?php echo $user['id']; ?>">
                                    <img src="<?php echo $user['profilePic']; ?>" alt="<?php echo $user['username']; ?>" class="selected-user-avatar">
                                    <span class="selected-user-name"><?php echo $user['username']; ?></span>
                                    <?php if ($user['isVerified']): ?>
                                    <svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <?php endif; ?>
                                    <button type="button" class="remove-user-btn" data-id="<?php echo $user['id']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="story-actions">
                            <a href="create-story.php" class="btn btn-outline">Cancel</a>
                            <button type="submit" name="publish" class="btn btn-primary">Share to Story</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="story_media">Upload Image or Video</label>
                        <div class="story-upload-container">
                            <div class="story-preview" id="storyPreview">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                <p>Add to your story</p>
                            </div>
                            <input type="file" id="story_media" name="story_media" accept="image/*,video/*" required>
                            <label for="story_media" class="story-upload-btn">Select Media</label>
                        </div>
                        <p class="story-info">Your story will be visible for 24 hours</p>
                    </div>
                    
                    <div class="form-group">
                        <div class="story-effects">
                            <h3>Apply Effect</h3>
                            <div class="effects-list">
                                <label class="effect-item active">
                                    <input type="radio" name="effect" value="none" checked>
                                    <span>None</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="grayscale">
                                    <span>B&W</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="sepia">
                                    <span>Sepia</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="negative">
                                    <span>Negative</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="brightness">
                                    <span>Bright</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="contrast">
                                    <span>Contrast</span>
                                </label>
                                <label class="effect-item">
                                    <input type="radio" name="effect" value="blur">
                                    <span>Blur</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="preview" class="btn btn-outline">Preview</button>
                        <button type="submit" name="publish" class="btn btn-primary">Share to Story</button>
                    </div>
                    
                    <input type="hidden" name="selected_users" id="selectedUsersInput" value="[]">
                <?php endif; ?>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Share with Users Modal -->
    <div class="modal" id="shareUsersModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Share with Users</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-users">
                    <input type="text" id="userSearch" placeholder="Search for a user..." class="search-input">
                    <div id="searchResults" class="search-results"></div>
                </div>
                
                <div class="selected-users">
                    <h4>Selected Users</h4>
                    <div id="modalSelectedUsersList" class="selected-users-list"></div>
                </div>
                
                <button id="confirmSelectedUsers" class="btn btn-primary btn-block">Confirm</button>
            </div>
        </div>
    </div>
    
    <script>
    // Media preview functionality
    document.getElementById('story_media')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('storyPreview');
                
                if (file.type.startsWith('video/')) {
                    preview.innerHTML = `<video src="${e.target.result}" controls></video>`;
                } else {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Effect selection
    const effectItems = document.querySelectorAll('.effect-item');
    effectItems.forEach(item => {
        item.addEventListener('click', function() {
            effectItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Share with users functionality
    const shareWithUsersBtn = document.getElementById('shareWithUsers');
    const shareUsersModal = document.getElementById('shareUsersModal');
    const closeModal = document.querySelector('.close-modal');
    const userSearch = document.getElementById('userSearch');
    const searchResults = document.getElementById('searchResults');
    const modalSelectedUsersList = document.getElementById('modalSelectedUsersList');
    const confirmSelectedUsersBtn = document.getElementById('confirmSelectedUsers');
    const selectedUsersInput = document.getElementById('selectedUsersInput');
    const mainSelectedUsersList = document.getElementById('selectedUsersList');
    
    let selectedUsers = [];
    
    // Initialize selected users from hidden input
    try {
        selectedUsers = JSON.parse(selectedUsersInput.value || '[]');
    } catch (e) {
        selectedUsers = [];
    }
    
    // Open share modal
    if (shareWithUsersBtn) {
        shareWithUsersBtn.addEventListener('click', function() {
            shareUsersModal.style.display = 'flex';
            
            // Populate modal selected users list
            modalSelectedUsersList.innerHTML = '';
            selectedUsers.forEach(user => {
                addUserToModalList(user);
            });
        });
    }
    
    // Close modal
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            shareUsersModal.style.display = 'none';
        });
    }
    
    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === shareUsersModal) {
            shareUsersModal.style.display = 'none';
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
        
        // Add to modal selected users list
        addUserToModalList(user);
        
        // Update hidden input
        selectedUsersInput.value = JSON.stringify(selectedUsers);
        
        // Clear search input
        userSearch.value = '';
        searchResults.innerHTML = '';
    }
    
    // Add user to modal selected list
    function addUserToModalList(user) {
        const userElement = document.createElement('div');
        userElement.className = 'selected-user';
        userElement.dataset.id = user.id;
        
        userElement.innerHTML = `
            <img src="${user.profilePic}" alt="${user.username}" class="selected-user-avatar">
            <span class="selected-user-name">${user.username}</span>
            ${user.isVerified ? '<svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
            <button class="remove-user-btn" data-id="${user.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;
        
        // Add remove button functionality
        userElement.querySelector('.remove-user-btn').addEventListener('click', function() {
            removeUser(user.id);
            userElement.remove();
        });
        
        // Add to modal selected users list
        modalSelectedUsersList.appendChild(userElement);
    }
    
    // Remove user from selected list
    function removeUser(id) {
        selectedUsers = selectedUsers.filter(user => user.id !== id);
        selectedUsersInput.value = JSON.stringify(selectedUsers);
        
        // Remove from main selected users list if it exists
        if (mainSelectedUsersList) {
            const userElement = mainSelectedUsersList.querySelector(`.selected-user[data-id="${id}"]`);
            if (userElement) {
                userElement.remove();
            }
        }
    }
    
    // Confirm selected users
    if (confirmSelectedUsersBtn) {
        confirmSelectedUsersBtn.addEventListener('click', function() {
            // Update main selected users list
            if (mainSelectedUsersList) {
                mainSelectedUsersList.innerHTML = '';
                selectedUsers.forEach(user => {
                    const userElement = document.createElement('div');
                    userElement.className = 'selected-user';
                    userElement.dataset.id = user.id;
                    
                    userElement.innerHTML = `
                        <img src="${user.profilePic}" alt="${user.username}" class="selected-user-avatar">
                        <span class="selected-user-name">${user.username}</span>
                        ${user.isVerified ? '<svg class="verified-badge" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : ''}
                        <button type="button" class="remove-user-btn" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    `;
                    
                    // Add remove button functionality
                    userElement.querySelector('.remove-user-btn').addEventListener('click', function() {
                        removeUser(user.id);
                        userElement.remove();
                    });
                    
                    mainSelectedUsersList.appendChild(userElement);
                });
            }
            
            // Close modal
            shareUsersModal.style.display = 'none';
        });
    }
    
    // Handle remove user buttons in main list
    document.querySelectorAll('#selectedUsersList .remove-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            removeUser(userId);
            this.closest('.selected-user').remove();
        });
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>


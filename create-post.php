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

// Process post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'];
    
    if (empty($_FILES['image']['name'])) {
        $error = 'Please select an image to upload';
    } else {
        $image_path = uploadImage($_FILES['image'], 'uploads/posts/');
        
        if ($image_path) {
            $post_id = createPost($_SESSION['user_id'], $image_path, $caption);
            
            if ($post_id) {
                $success = 'Post created successfully!';
                // Redirect to the post page
                header('Location: post.php?id=' . $post_id);
                exit;
            } else {
                $error = 'Failed to create post. Please try again.';
            }
        } else {
            $error = 'Failed to upload image. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Monogram</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="create-post-container">
            <h1 class="page-title">Create New Post</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="create-post.php" method="POST" enctype="multipart/form-data" class="create-post-form">
                <div class="form-group">
                    <label for="image">Image</label>
                    <div class="image-upload-container">
                        <div class="image-preview" id="imagePreview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <p>No image selected</p>
                        </div>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <label for="image" class="image-upload-btn">Select Image</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="caption">Caption</label>
                    <textarea id="caption" name="caption" placeholder="Write a caption..." rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Share Post</button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$users = getAllUsers($limit, $offset);

// Get total users count for pagination
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];
$total_pages = ceil($total_users / $limit);

// Get posts count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
$total_posts = $stmt->fetch()['count'];

// Get verified users count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1");
$verified_users = $stmt->fetch()['count'];

// Get admin users count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
$admin_users = $stmt->fetch()['count'];

// Process actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'verify':
                if (verifyUser($user_id)) {
                    $message = 'User verified successfully';
                }
                break;
            case 'unverify':
                if (unverifyUser($user_id)) {
                    $message = 'User verification removed successfully';
                }
                break;
            case 'make_admin':
                if (makeAdmin($user_id)) {
                    $message = 'User made admin successfully';
                }
                break;
            case 'remove_admin':
                if (removeAdmin($user_id)) {
                    $message = 'Admin privileges removed successfully';
                }
                break;
            case 'delete':
                if (deleteUser($user_id)) {
                    $message = 'User deleted successfully';
                }
                break;
        }
    }
}

// Helper function to delete user
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

// Get dark mode preference
$settings = getUserSettings($_SESSION['user_id']);
$dark_mode = $settings['dark_mode'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Monogram</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <?php endif; ?>
</head>
<body <?php if ($dark_mode): ?>class="dark-mode"<?php endif; ?>>
    <header class="admin-header">
        <div class="container admin-header-container">
            <a href="../index.php" class="admin-logo">Monogram</a>
            <div class="admin-user-info">
                <span>Logged in as: <?php echo $_SESSION['username']; ?></span>
                <a href="../logout.php" class="admin-logout">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                <a href="users.php" class="admin-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Users
                </a>
                <a href="posts.php" class="admin-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    Posts
                </a>
                <a href="reports.php" class="admin-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                    Reports
                </a>
                <a href="settings.php" class="admin-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Settings
                </a>
            </nav>
        </div>
        
        <main class="admin-content">
            <h1 class="admin-page-title">Admin Dashboard</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon users-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="admin-stat-info">
                        <h3 class="admin-stat-title">Total Users</h3>
                        <p class="admin-stat-value"><?php echo $total_users; ?></p>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-icon posts-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </div>
                    <div class="admin-stat-info">
                        <h3 class="admin-stat-title">Total Posts</h3>
                        <p class="admin-stat-value"><?php echo $total_posts; ?></p>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-icon verified-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="admin-stat-info">
                        <h3 class="admin-stat-title">Verified Users</h3>
                        <p class="admin-stat-value"><?php echo $verified_users; ?></p>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    </div>
                    <div class="admin-stat-info">
                        <h3 class="admin-stat-title">Admin Users</h3>
                        <p class="admin-stat-value"><?php echo $admin_users; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Recent Users</h2>
                    <a href="users.php" class="admin-section-link">View All</a>
                </div>
                
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <img src="<?php echo $user['profile_pic'] ? '../' . $user['profile_pic'] : '../assets/images/default-avatar.png'; ?>" alt="<?php echo $user['username']; ?>" class="user-table-pic">
                                            <span><?php echo $user['username']; ?>
                                                <?php if ($user['is_verified']): ?>
                                                    <svg class="verified-badge-small" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge badge-admin">Admin</span>
                                        <?php elseif ($user['is_verified']): ?>
                                            <span class="badge badge-verified">Verified</span>
                                        <?php else: ?>
                                            <span class="badge badge-user">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="admin-actions-dropdown">
                                            <button class="admin-actions-btn">
                                                Actions
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </button>
                                            <div class="admin-actions-content">
                                                <a href="../profile.php?username=<?php echo $user['username']; ?>" class="admin-action-link">View Profile</a>
                                                
                                                <form action="dashboard.php" method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    
                                                    <?php if ($user['is_verified']): ?>
                                                        <input type="hidden" name="action" value="unverify">
                                                        <button type="submit" class="admin-action-btn">Remove Verification</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="action" value="verify">
                                                        <button type="submit" class="admin-action-btn">Verify User</button>
                                                    <?php endif; ?>
                                                </form>
                                                
                                                <form action="dashboard.php" method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    
                                                    <?php if ($user['is_admin']): ?>
                                                        <input type="hidden" name="action" value="remove_admin">
                                                        <button type="submit" class="admin-action-btn">Remove Admin</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="action" value="make_admin">
                                                        <button type="submit" class="admin-action-btn">Make Admin</button>
                                                    <?php endif; ?>
                                                </form>
                                                
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="admin-action-btn admin-action-delete">Delete User</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    <?php if ($total_pages > 1): ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="dashboard.php?page=<?php echo $i; ?>" class="admin-page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Dropdown functionality for action buttons
    document.querySelectorAll('.admin-actions-btn').forEach(button => {
        button.addEventListener('click', function() {
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('show');
            
            // Close other dropdowns
            document.querySelectorAll('.admin-actions-content').forEach(content => {
                if (content !== dropdown) {
                    content.classList.remove('show');
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.admin-actions-btn')) {
            document.querySelectorAll('.admin-actions-content').forEach(content => {
                content.classList.remove('show');
            });
        }
    });
    </script>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>


<?php
session_start();
require '../includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// 1. Identify if the current user is the Master Admin
$stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$current_user = $stmt->fetch();

$master_email = 'admin@foodlink.com';
$is_master_admin = ($current_user && $current_user['email'] === $master_email);

// 2. Handle Adding a New User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status = $_POST['status'];

    // SECURITY: Only Master Admin can create new Admin accounts
    if ($role === 'admin' && !$is_master_admin) {
        $error_msg = "Permission Denied: Only the Master Admin can create new Admin accounts.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error_msg = "An account with that email already exists!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $password, $role, $status])) {
                $success_msg = "User added successfully!";
            } else {
                $error_msg = "Failed to add user.";
            }
        }
    }
}

// 3. Handle Deleting a User
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Fetch details of the user being deleted
    $target_stmt = $pdo->prepare("SELECT role, email FROM users WHERE user_id = ?");
    $target_stmt->execute([$delete_id]);
    $target_user = $target_stmt->fetch();

    if ($target_user) {
        // SECURITY CHECKS
        if ($target_user['email'] === $master_email) {
            $error_msg = "Critical Denied: The Master Admin account cannot be deleted.";
        } elseif ($target_user['role'] === 'admin' && !$is_master_admin) {
            $error_msg = "Permission Denied: Only the Master Admin can delete other Admins.";
        } elseif ($delete_id === $current_user_id) {
            $error_msg = "You cannot delete your own account!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$delete_id]);
            header("Location: manage_users.php");
            exit();
        }
    }
}

// 4. Handle Suspending/Activating a User
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $toggle_id = (int)$_GET['id'];
    $new_status = $_GET['toggle_status'] === 'active' ? 'active' : 'suspended';
    
    // Fetch details of the user being suspended
    $target_stmt = $pdo->prepare("SELECT role, email FROM users WHERE user_id = ?");
    $target_stmt->execute([$toggle_id]);
    $target_user = $target_stmt->fetch();

    if ($target_user) {
        // SECURITY CHECKS
        if ($target_user['email'] === $master_email) {
            $error_msg = "Critical Denied: The Master Admin account cannot be suspended.";
        } elseif ($target_user['role'] === 'admin' && !$is_master_admin) {
            $error_msg = "Permission Denied: Only the Master Admin can suspend other Admins.";
        } elseif ($toggle_id === $current_user_id) {
            $error_msg = "You cannot change your own status!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$new_status, $toggle_id]);
            header("Location: manage_users.php");
            exit();
        }
    }
}

// Fetch all users for the table
$search_query = "";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $search_query = "WHERE name LIKE ? OR email LIKE ?";
    $params = [$search, $search];
}

$users_stmt = $pdo->prepare("SELECT * FROM users $search_query ORDER BY user_id DESC");
$users_stmt->execute($params);
$users = $users_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-page-body">

    <div class="admin-container" style="max-width: 1200px;">
        <div class="header-section">
            <h2>👥 Manage Users</h2>
            <a href="index.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php if(isset($success_msg)) echo "<div class='alert success'>$success_msg</div>"; ?>
        <?php if(isset($error_msg)) echo "<div class='alert error'>$error_msg</div>"; ?>

        <div class="grid-layout">
            
            <div>
                <div class="upload-card">
                    <h3>Add New Account</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name:</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Aung Aung">
                        </div>
                        <div class="form-group">
                            <label>Email Address:</label>
                            <input type="email" name="email" class="form-control" required placeholder="e.g. user@example.com">
                        </div>
                        <div class="form-group">
                            <label>Temporary Password:</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>
                        
                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Account Role:</label>
                                <select name="role" class="form-control" required>
                                    <option value="customer">Customer</option>
                                    <option value="vendor">Vendor (Restaurant)</option>
                                    <option value="rider">Delivery Rider</option>
                                    
                                    <?php if($is_master_admin): ?>
                                        <option value="admin" style="font-weight: bold; color: red;">Admin</option>
                                    <?php endif; ?>
                                    
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="add_user" class="btn-primary" style="margin-top: 10px;">Create Account</button>
                    </form>
                </div>
            </div>

            <div style="overflow-x: auto;">
                
                <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="background: #fff;">
                    <button type="submit" class="btn-primary" style="width: auto; margin: 0; padding: 0 25px;">Search</button>
                    <?php if(isset($_GET['search'])): ?>
                        <a href="manage_users.php" class="btn-primary" style="width: auto; margin: 0; background: #666; padding: 15px;">Clear</a>
                    <?php endif; ?>
                </form>

                <table class="admin-table">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php if(count($users) > 0): ?>
                        <?php foreach($users as $user): ?>
                        
                        <?php 
                            $user_status = $user['status'] ?? 'active'; 
                            
                            // Check Permissions for the specific row
                            $is_target_master = ($user['email'] === $master_email);
                            $can_manage = true;
                            
                            if ($is_target_master) {
                                $can_manage = false; // Nobody can touch Master Admin
                            } elseif ($user['role'] === 'admin' && !$is_master_admin) {
                                $can_manage = false; // Junior admin cannot touch other admins
                            } elseif ($user['user_id'] === $current_user_id) {
                                $can_manage = false; // Cannot touch yourself
                            }
                        ?>
                        
                        <tr>
                            <td>
                                <strong style="color: var(--text-dark);"><?= htmlspecialchars($user['name']) ?></strong>
                                <?php if($is_target_master): ?>
                                    <span title="Master Admin" style="font-size:12px;">👑</span>
                                <?php endif; ?>
                                <br>
                                <span style="font-size: 13px; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></span>
                            </td>
                            <td>
                                <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars($user_status) ?>">
                                    <?= ucfirst(htmlspecialchars($user_status)) ?>
                                </span>
                            </td>
                            <td style="display: flex; gap: 10px;">
                                
                                <?php if($can_manage): ?>
                                
                                    <?php if($user_status === 'active'): ?>
                                        <a href="manage_users.php?toggle_status=suspended&id=<?= $user['user_id'] ?>" class="action-link" style="background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2;">Suspend</a>
                                    <?php else: ?>
                                        <a href="manage_users.php?toggle_status=active&id=<?= $user['user_id'] ?>" class="action-link status-active" style="border: 1px solid #c8e6c9;">Activate</a>
                                    <?php endif; ?>
                                    
                                    <a href="manage_users.php?delete=<?= $user['user_id'] ?>" class="action-link delete-btn" onclick="return confirm('WARNING: Deleting a user will also delete all their orders/data. Are you sure?')">Delete</a>
                                
                                <?php elseif ($is_target_master): ?>
                                    <span style="font-size: 12px; font-weight: 700; color: #111; background: #eee; padding: 4px 8px; border-radius: 4px;">Master Account</span>
                                <?php elseif ($user['user_id'] === $current_user_id): ?>
                                    <span style="font-size: 12px; color: #999; font-style: italic;">Current Session</span>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #dc3545; font-style: italic; font-weight: 600;">Restricted 🔒</span>
                                <?php endif; ?>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #777;">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
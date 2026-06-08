<?php
session_start();
require '../includes/db.php';

// 1. Security Check: ONLY Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Check if the admin is trying to change their password
    if (!empty($new_password)) {
        
        // They MUST provide the current password
        if (empty($current_password)) {
            $error_msg = "You must enter your Current Password to set a New Password.";
        } 
        // Enforce strong passwords for Admins (min 6 chars)
        elseif (strlen($new_password) < 6) {
            $error_msg = "New password must be at least 6 characters long.";
        }
        else {
            // Fetch current hashed password to verify
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$admin_id]);
            $user_db = $stmt->fetch();

            if (password_verify($current_password, $user_db['password'])) {
                // Hash the new password and update
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE user_id = ?");
                
                if ($update_stmt->execute([$name, $phone, $hashed_password, $admin_id])) {
                    $_SESSION['name'] = $name; 
                    $success_msg = "Profile and password updated securely!";
                } else {
                    $error_msg = "Failed to update profile.";
                }
            } else {
                $error_msg = "Incorrect Current Password. Profile not updated.";
            }
        }
        
    } else {
        // Just updating Name and Phone
        $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
        if ($update_stmt->execute([$name, $phone, $admin_id])) {
            $_SESSION['name'] = $name; 
            $success_msg = "Profile details updated successfully!";
        } else {
            $error_msg = "Failed to update profile.";
        }
    }
}

// 3. Fetch current admin data to fill the form
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 40px; }
        .toggle-password { 
            position: absolute; right: 15px; cursor: pointer; 
            font-size: 18px; user-select: none; opacity: 0.6; transition: 0.2s;
        }
        .toggle-password:hover { opacity: 1; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">FoodLink Admin</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_categories.php" class="translatable" data-en="Manage Categories" data-my="အမျိုးအစားများ စီမံရန်">Manage Categories</a></li>
            <li><a href="manage_users.php" class="translatable" data-en="Manage Users" data-my="အသုံးပြုသူများကို စီမံရန်">Manage Users</a></li>
            <li><a href="manage_banners.php" class="translatable" data-en="Manage Banners" data-my="ဘန်နာများ စီမံရန်">Manage Banners</a></li>
            <li><a href="manage_restaurants.php" class="translatable" data-en="Manage Restaurants" data-my="စားသောက်ဆိုင်များ စီမံရန်">Manage Restaurants</a></li>
            <li><a href="manage_delivery.php" class="translatable" data-en="Manage Delivery" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees</a></li>
            <li><a href="finances.php" class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
            <li><a href="profile.php" class="active translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Admin Profile 🛡️" data-my="အက်ဒမင် ပရိုဖိုင် 🛡️">Admin Profile 🛡️</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($success_msg): ?>
            <div class="alert success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="dashboard-card" style="max-width: 600px;">
            <form method="POST">
                <div class="form-group">
                    <label class="translatable" data-en="Full Name" data-my="အမည်အပြည့်အစုံ">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="translatable" data-en="Email Address (Cannot be changed)" data-my="အီးမေးလ် (ပြောင်းလဲ၍မရပါ)">Email Address (Cannot be changed)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: #eee; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label class="translatable" data-en="Phone Number" data-my="ဖုန်းနံပါတ်">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>

                <div style="margin-top: 30px; border-top: 2px dashed var(--border-light); padding-top: 20px;">
                    <h3 style="font-size: 16px; margin-bottom: 15px;" class="translatable" data-en="Change Password (Optional)" data-my="စကားဝှက် ပြောင်းရန် (ရွေးချယ်နိုင်သည်)">Change Password (Optional)</h3>
                    
                    <div class="form-group">
                        <label class="translatable" data-en="Current Password" data-my="လက်ရှိ စကားဝှက်">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="currentPassword" class="form-control" placeholder="Required if changing password...">
                            <span class="toggle-password" onclick="togglePassword('currentPassword', this)">👁️</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="translatable" data-en="New Password" data-my="စကားဝှက်အသစ်">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="newPassword" class="form-control" placeholder="Enter new password...">
                            <span class="toggle-password" onclick="togglePassword('newPassword', this)">👁️</span>
                        </div>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-primary translatable" data-en="Save Securely" data-my="လုံခြုံစွာ သိမ်းဆည်းမည်">Save Securely</button>
            </form>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
    <script>
        // Dynamic function to toggle any password field
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconElement.textContent = '🙈';
            } else {
                input.type = 'password';
                iconElement.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
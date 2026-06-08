<?php
session_start();
require '../includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: ../login.php");
    exit();
}

$rider_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Check if the user is trying to change their password
    if (!empty($new_password)) {
        
        // If they want a new password, they MUST provide the current one
        if (empty($current_password)) {
            $error_msg = "You must enter your Current Password to set a New Password.";
        } else {
            // Fetch their current hashed password from the database to verify
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$rider_id]);
            $user_db = $stmt->fetch();

            // Verify if the typed current password matches the database
            if (password_verify($current_password, $user_db['password'])) {
                
                // Success! Hash the new password and update everything
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE user_id = ?");
                
                if ($update_stmt->execute([$name, $phone, $hashed_password, $rider_id])) {
                    $_SESSION['name'] = $name; 
                    $success_msg = "Profile and password updated successfully!";
                } else {
                    $error_msg = "Failed to update profile.";
                }

            } else {
                // The current password they typed was wrong!
                $error_msg = "Incorrect Current Password. Profile not updated.";
            }
        }
        
    } else {
        // They are NOT changing their password. Just update Name and Phone.
        $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
        if ($update_stmt->execute([$name, $phone, $rider_id])) {
            $_SESSION['name'] = $name; 
            $success_msg = "Profile details updated successfully!";
        } else {
            $error_msg = "Failed to update profile.";
        }
    }
}

// 3. Fetch current user data to fill the form
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->execute([$rider_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Rider Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Rider Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Delivery Jobs" data-my="ပို့ဆောင်ရန် အလုပ်များ">Delivery Jobs</a></li>
            <li><a href="earnings.php" class="translatable" data-en="My Earnings" data-my="ကျွန်ုပ်၏ ဝင်ငွေများ">My Earnings</a></li>
            <li><a href="profile.php" class="active translatable" data-en="Profile Settings" data-my="ပရိုဖိုင် ဆက်တင်များ">Profile Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Profile Settings ⚙️" data-my="ပရိုဖိုင် ဆက်တင်များ ⚙️">Profile Settings ⚙️</h2>
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
                        <input type="password" name="current_password" class="form-control" placeholder="Required if changing password...">
                    </div>

                    <div class="form-group">
                        <label class="translatable" data-en="New Password" data-my="စကားဝှက်အသစ်">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password...">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-primary translatable" data-en="Save Changes" data-my="ပြောင်းလဲမှုများကို သိမ်းမည်">Save Changes</button>
            </form>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
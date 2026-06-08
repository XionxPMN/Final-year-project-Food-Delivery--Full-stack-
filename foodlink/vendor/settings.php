<?php
session_start();
require '../includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Check if the vendor already has a restaurant profile
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$restaurant = $stmt->fetch();

// --- Handle Password Change ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "New passwords do not match!";
        $messageType = "error";
    } else {
        // Fetch current user data to check password
        $user_stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $user_stmt->execute([$vendor_id]);
        $user = $user_stmt->fetch();

        // Verify Current Password
        if ($user && password_verify($current_password, $user['password'])) {
            // Hash the new password and update
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if ($update_pass->execute([$hashed_password, $vendor_id])) {
                $message = "Password updated successfully!";
                $messageType = "success";
            } else {
                $message = "Database error. Could not update password.";
                $messageType = "error";
            }
        } else {
            $message = "Incorrect current password!";
            $messageType = "error";
        }
    }
}

// --- Handle Open / Close Shop Toggle ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_shop'])) {
    $new_status = $restaurant['is_open'] == 1 ? 0 : 1;
    $toggle_stmt = $pdo->prepare("UPDATE restaurants SET is_open = ? WHERE vendor_id = ?");
    if ($toggle_stmt->execute([$new_status, $vendor_id])) {
        $message = $new_status == 1 ? "Your shop is now OPEN!" : "Your shop is now CLOSED!";
        $messageType = "success";
        $stmt->execute([$vendor_id]);
        $restaurant = $stmt->fetch();
    }
}

// --- Handle Profile Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    $name = $_POST['name'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];
    
    $image_url = $restaurant ? $restaurant['image_url'] : 'assets/default_restaurant.png'; 

    if (isset($_FILES['rest_image']) && $_FILES['rest_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['rest_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = uniqid('rest_') . '.' . $ext;
            $upload_dir = '../uploads/restaurants/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true); 
            $destination = $upload_dir . $newName;

            if (move_uploaded_file($_FILES['rest_image']['tmp_name'], $destination)) {
                $image_url = 'uploads/restaurants/' . $newName;
            } else {
                $message = "Failed to upload image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid image format.";
            $messageType = "error";
        }
    }

    if (empty($messageType)) {
        if ($restaurant) {
            $update = $pdo->prepare("UPDATE restaurants SET name = ?, city = ?, phone = ?, image_url = ? WHERE vendor_id = ?");
            if ($update->execute([$name, $city, $phone, $image_url, $vendor_id])) {
                $message = "Shop settings updated successfully!";
                $messageType = "success";
                $stmt->execute([$vendor_id]);
                $restaurant = $stmt->fetch();
            }
        } else {
            $insert = $pdo->prepare("INSERT INTO restaurants (vendor_id, name, city, phone, image_url, status, is_open) VALUES (?, ?, ?, ?, ?, 'pending', 1)");
            if ($insert->execute([$vendor_id, $name, $city, $phone, $image_url])) {
                $message = "Shop profile created! Waiting for Admin approval.";
                $messageType = "success";
                $stmt->execute([$vendor_id]);
                $restaurant = $stmt->fetch();
            }
        }
    }
}

$rest_name = $restaurant ? htmlspecialchars($restaurant['name']) : '';
$rest_phone = $restaurant ? htmlspecialchars($restaurant['phone'] ?? '') : '';
$rest_city = $restaurant ? $restaurant['city'] : 'Yangon';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Settings - Vendor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .profile-grid { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px; }
        .form-section { flex: 2; min-width: 300px; background: var(--white); padding: 30px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); }
        .info-section { flex: 1; min-width: 250px; }
        .status-box { background: var(--bg-light); padding: 20px; border-radius: 12px; border: 1px solid var(--border-light); text-align: center; margin-bottom: 20px; }
        .current-image { width: 100%; max-width: 200px; height: 150px; object-fit: cover; border-radius: 12px; margin-bottom: 15px; border: 2px solid var(--border-light); }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_menu.php" class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
                <li><a href="history.php" class="translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="active translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="active translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div><h2 class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</h2></div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="form-section">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-dark); border-bottom: 2px solid var(--border-light); padding-bottom: 10px;" class="translatable" data-en="<?= $restaurant ? 'Edit Shop Profile' : 'Setup Your Restaurant' ?>" data-my="<?= $restaurant ? 'ဆိုင်ပရိုဖိုင် ပြင်ဆင်ရန်' : 'စားသောက်ဆိုင် အချက်အလက်ဖြည့်ရန်' ?>">
                    <?= $restaurant ? 'Edit Shop Profile' : 'Setup Your Restaurant' ?>
                </h3>
                
                <form method="POST" action="settings.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="translatable" data-en="Restaurant Name" data-my="စားသောက်ဆိုင် အမည်">Restaurant Name</label>
                        <input type="text" name="name" class="form-control" value="<?= $rest_name ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="translatable" data-en="City / Location" data-my="မြို့ / တည်နေရာ">City / Location</label>
                        <select name="city" class="form-control" required>
                            <option value="Yangon" <?= ($rest_city == 'Yangon') ? 'selected' : '' ?>>Yangon</option>
                            <option value="Mandalay" <?= ($rest_city == 'Mandalay') ? 'selected' : '' ?>>Mandalay</option>
                            <option value="Naypyidaw" <?= ($rest_city == 'Naypyidaw') ? 'selected' : '' ?>>Naypyidaw</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="translatable" data-en="Contact Phone Number" data-my="ဆက်သွယ်ရန် ဖုန်းနံပါတ်">Contact Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= $rest_phone ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="translatable" data-en="Restaurant Cover Image" data-my="စားသောက်ဆိုင် မျက်နှာဖုံးပုံ">Restaurant Cover Image</label>
                        <?php if($restaurant && !empty($restaurant['image_url'])): ?>
                            <div>
                                <img src="../<?= htmlspecialchars($restaurant['image_url']) ?>" class="current-image" alt="Current Image">
                            </div>
                            <small style="color: var(--text-muted); display: block; margin-bottom: 10px;">Upload a new image to change your cover, or leave blank to keep current.</small>
                            <input type="file" name="rest_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <?php else: ?>
                            <input type="file" name="rest_image" class="form-control" accept="image/png, image/jpeg, image/webp" required>
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="save_profile" value="1">
                    <button type="submit" class="btn-primary translatable" data-en="Save Settings" data-my="သိမ်းဆည်းမည်">Save Settings</button>
                </form>

                <h3 style="margin-top: 40px; margin-bottom: 20px; color: var(--text-dark); border-bottom: 2px solid var(--border-light); padding-bottom: 10px;" class="translatable" data-en="Change Password" data-my="စကားဝှက် ပြောင်းရန်">Change Password</h3>
                <form method="POST" action="settings.php">
                    <div class="form-group">
                        <label class="translatable" data-en="Current Password" data-my="လက်ရှိ စကားဝှက်">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="translatable" data-en="New Password" data-my="စကားဝှက် အသစ်">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="translatable" data-en="Confirm New Password" data-my="စကားဝှက် အသစ် အတည်ပြုပါ">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <input type="hidden" name="change_password" value="1">
                    <button type="submit" class="btn-primary" style="background: var(--text-dark);">Update Password</button>
                </form>

            </div>

            <div class="info-section">
                <div class="status-box">
                    <h4 style="margin: 0 0 15px 0; color: var(--text-dark);" class="translatable" data-en="Shop Control" data-my="ဆိုင်ထိန်းချုပ်မှု">Shop Control</h4>
                    
                    <?php if(!$restaurant): ?>
                        <p style="color: var(--text-muted); font-size: 14px;">Please fill out the form to register your restaurant.</p>
                    <?php else: ?>
                        
                        <?php if($restaurant['status'] == 'pending'): ?>
                            <p style="color: #856404; font-size: 14px; background: #FFF3CD; padding: 10px; border-radius: 8px;">Your profile is under review by the Admin.</p>
                        <?php elseif($restaurant['status'] == 'approved'): ?>
                            
                            <?php if($restaurant['is_open'] == 1): ?>
                                <p style="color: #28a745; font-weight: 700; margin-bottom: 15px;">SHOP IS OPEN</p>
                                <form method="POST">
                                    <input type="hidden" name="toggle_shop" value="1">
                                    <button type="submit" class="btn-primary" style="background: #dc3545; margin-top:0;">Close Shop Temporarily</button>
                                </form>
                            <?php else: ?>
                                <p style="color: #dc3545; font-weight: 700; margin-bottom: 15px;">SHOP IS CLOSED</p>
                                <form method="POST">
                                    <input type="hidden" name="toggle_shop" value="1">
                                    <button type="submit" class="btn-primary" style="background: #28a745; margin-top:0;">Open Shop Now</button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="status-box" style="text-align: left;">
                    <h4 style="margin: 0 0 10px 0; color: var(--text-dark);" class="translatable" data-en="Account Info" data-my="အကောင့်အချက်အလက်">Account Info</h4>
                    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 5px;"><strong>Name:</strong> <?= htmlspecialchars($_SESSION['name']) ?></p>
                    <p style="font-size: 14px; color: var(--text-muted);"><strong>Role:</strong> Vendor</p>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
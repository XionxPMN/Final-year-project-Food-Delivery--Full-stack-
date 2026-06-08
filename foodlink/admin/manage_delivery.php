<?php
session_start();
require '../includes/db.php';

// 1. Role-Based Access Control: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// ==========================================
// HANDLE FORM SUBMISSIONS
// ==========================================

// Action 1: Update Single Restaurant Fee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_single_fee'])) {
    $rest_id = (int)$_POST['restaurant_id'];
    $new_fee = (int)$_POST['delivery_fee'];
    
    $stmt = $pdo->prepare("UPDATE restaurants SET delivery_fee = ? WHERE restaurant_id = ?");
    if ($stmt->execute([$new_fee, $rest_id])) {
        $success_msg = "Delivery fee updated successfully!";
    } else {
        $error_msg = "Failed to update fee.";
    }
}

// Action 2: BULK Update Fees by City
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update_city'])) {
    $target_city = trim($_POST['target_city']);
    $new_bulk_fee = (int)$_POST['bulk_fee'];
    
    if (!empty($target_city)) {
        // Only update approved restaurants in that specific city
        $stmt = $pdo->prepare("UPDATE restaurants SET delivery_fee = ? WHERE city = ? AND status = 'approved'");
        if ($stmt->execute([$new_bulk_fee, $target_city])) {
            $affected = $stmt->rowCount();
            $success_msg = "Bulk update successful! Changed fees to " . number_format($new_bulk_fee) . " Ks for $affected restaurants in $target_city.";
        } else {
            $error_msg = "Failed to bulk update fees.";
        }
    }
}

// ==========================================
// FETCH DATA FOR UI
// ==========================================

// Get all unique cities that currently have approved restaurants
$cities_stmt = $pdo->query("SELECT DISTINCT city FROM restaurants WHERE status = 'approved' AND city != '' ORDER BY city ASC");
$cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle Search and Filter
$search_query = "WHERE status = 'approved'";
$params = [];

$filter_city = $_GET['filter_city'] ?? 'All';
$search_name = $_GET['search'] ?? '';

if ($filter_city !== 'All') {
    $search_query .= " AND city = ?";
    $params[] = $filter_city;
}

if (!empty($search_name)) {
    $search_query .= " AND name LIKE ?";
    $params[] = "%" . $search_name . "%";
}

$sql = "SELECT restaurant_id, name, city, delivery_fee, image_url FROM restaurants $search_query ORDER BY city ASC, name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Delivery Fees - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .bulk-card { background: #fff9f0; border: 1px solid #ffe0b2; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: var(--shadow-card); }
        .bulk-card h3 { color: #e65100; margin-top: 0; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .flex-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .rest-logo { width: 50px; height: 50px; min-width: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc; }
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
            <li><a href="manage_delivery.php" class="active translatable" data-en="Manage Delivery" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees</a></li>
            <li><a href="finances.php" class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
            <li><a href="profile.php" class="translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Manage Delivery Fees" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees 🚚</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($success_msg) echo "<div class='alert success'>$success_msg</div>"; ?>
        <?php if($error_msg) echo "<div class='alert error'>$error_msg</div>"; ?>

        <div class="bulk-card">
            <h3>⚡ Bulk Update by Location</h3>
            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Instantly change the delivery fee for ALL approved restaurants in a specific city.</p>
            
            <form method="POST" class="flex-form">
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <label>Select City</label>
                    <select name="target_city" class="form-control" required style="background: #fff;">
                        <option value="">-- Choose Location --</option>
                        <?php foreach($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <label>New Delivery Fee (Ks)</label>
                    <input type="number" name="bulk_fee" class="form-control" placeholder="e.g. 2000" min="0" required style="background: #fff;">
                </div>
                
                <button type="submit" name="bulk_update_city" class="btn-primary" style="margin: 0; width: auto; padding: 14px 30px; background: #e65100;" onclick="return confirm('Are you sure you want to change the fee for EVERY restaurant in this city?')">Apply to All</button>
            </form>
        </div>

        <div class="dashboard-card">
            <h3 style="margin-top: 0;">Individual Shop Fees</h3>
            
            <form method="GET" class="flex-form" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div style="flex: 1; min-width: 200px;">
                    <select name="filter_city" class="form-control" style="margin: 0;">
                        <option value="All">📍 All Locations</option>
                        <?php foreach($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city) ?>" <?= $filter_city === $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="flex: 2; min-width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Search restaurant name..." value="<?= htmlspecialchars($search_name) ?>" style="margin: 0;">
                </div>
                
                <button type="submit" class="btn-primary" style="width: auto; margin: 0; padding: 14px 25px;">Filter</button>
                <a href="manage_delivery.php" class="btn-primary" style="width: auto; margin: 0; background: #666; text-decoration: none; padding: 14px 25px;">Clear</a>
            </form>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <tr>
                        <th>Restaurant</th>
                        <th>Location</th>
                        <th>Current Fee</th>
                        <th>Update Fee</th>
                    </tr>
                    
                    <?php if(count($restaurants) > 0): ?>
                        <?php foreach($restaurants as $rest): ?>
                        <tr>
                            <td style="display: flex; gap: 15px; align-items: center;">
                                <img src="../<?= htmlspecialchars($rest['image_url'] ?? 'assets/default_restaurant.png') ?>" class="rest-logo" alt="Logo">
                                <strong><?= htmlspecialchars($rest['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #555;">📍 <?= htmlspecialchars($rest['city']) ?></span>
                            </td>
                            <td>
                                <?php if($rest['delivery_fee'] == 0): ?>
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 12px;">Free Delivery</span>
                                <?php else: ?>
                                    <strong style="color: var(--primary-color); font-size: 16px;"><?= number_format($rest['delivery_fee']) ?> Ks</strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 5px; align-items: center; margin: 0;">
                                    <input type="hidden" name="restaurant_id" value="<?= $rest['restaurant_id'] ?>">
                                    <input type="number" name="delivery_fee" value="<?= (int)($rest['delivery_fee'] ?? 0) ?>" class="form-control" style="width: 100px; padding: 8px; margin: 0; text-align: right;" min="0" required>
                                    <span style="font-size: 13px; font-weight: 600; color: #777;">Ks</span>
                                    <button type="submit" name="update_single_fee" class="btn-primary" style="padding: 8px 15px; width: auto; font-size: 13px; margin: 0;">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #777;">No restaurants found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
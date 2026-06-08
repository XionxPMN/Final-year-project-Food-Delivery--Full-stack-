<?php
session_start();
require '../includes/db.php';

// 1. Role-Based Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$messageType = '';

// ==========================================
// HANDLE APPROVE / SUSPEND / DELETE ACTIONS
// ==========================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $rest_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve' || $action === 'unsuspend') {
        // Force the database back to the exact ENUM 'approved'
        $stmt = $pdo->prepare("UPDATE restaurants SET status = 'approved' WHERE restaurant_id = ?");
        if ($stmt->execute([$rest_id])) {
            $message = "Restaurant successfully approved/unsuspended and is now LIVE!";
            $messageType = "success";
        }
    } 
    elseif ($action === 'suspend') {
        // We use 'rejected' as the strict ENUM state to safely suspend/hide it
        $stmt = $pdo->prepare("UPDATE restaurants SET status = 'rejected' WHERE restaurant_id = ?");
        if ($stmt->execute([$rest_id])) {
            $message = "Restaurant has been suspended and hidden from customers.";
            $messageType = "error"; // Show as red alert
        }
    }
    elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
        if ($stmt->execute([$rest_id])) {
            $message = "Restaurant and all its menus have been permanently deleted.";
            $messageType = "success";
        }
    }
}

// ==========================================
// FETCH ALL RESTAURANTS WITH VENDOR INFO
// ==========================================
$search_query = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_query = "WHERE r.name LIKE :search OR r.city LIKE :search OR u.name LIKE :search";
    $rest_stmt = $pdo->prepare("
        SELECT r.*, u.name as vendor_name, u.phone as vendor_phone 
        FROM restaurants r 
        LEFT JOIN users u ON r.vendor_id = u.user_id 
        $search_query 
        ORDER BY r.restaurant_id DESC
    ");
    $rest_stmt->execute(['search' => "%$search%"]);
} else {
    $rest_stmt = $pdo->query("
        SELECT r.*, u.name as vendor_name, u.phone as vendor_phone 
        FROM restaurants r 
        LEFT JOIN users u ON r.vendor_id = u.user_id 
        ORDER BY r.restaurant_id DESC
    ");
}

$restaurants = $rest_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Restaurants - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .rest-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-card); }
        .rest-table th, .rest-table td { padding: 15px 20px; border-bottom: 1px solid var(--border-light); text-align: left; vertical-align: middle; }
        .rest-table th { background: #f9f9f9; color: var(--text-muted); font-size: 13px; text-transform: uppercase; }
        .rest-table tr:last-child td { border-bottom: none; }
        
        .shop-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-light); }
        
        .badge { padding: 5px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase; display: inline-block;}
        
        /* Status Badges */
        .status-approved { background: #D4EDDA; color: #155724; }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-rejected { background: #F8D7DA; color: #721C24; } /* Acts as Suspended */

        /* Action Buttons */
        .btn-sm { padding: 6px 12px; font-size: 12px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; transition: 0.2s; cursor: pointer; border: none; }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-suspend { background: #ff9800; color: white; }
        .btn-suspend:hover { background: #e65100; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { flex: 1; max-width: 400px; padding: 10px 15px; border: 1px solid var(--border-light); border-radius: 6px; font-family: 'Poppins'; }
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
            <li><a href="manage_restaurants.php" class="active translatable" data-en="Manage Restaurants" data-my="စားသောက်ဆိုင်များ စီမံရန်">Manage Restaurants</a></li>
            <li><a href="manage_delivery.php" class="translatable" data-en="Manage Delivery" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees</a></li>
            <li><a href="finances.php" class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
            <li><a href="profile.php" class="translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2>Manage Restaurants</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form class="search-bar" method="GET" action="manage_restaurants.php">
            <input type="text" name="search" placeholder="Search by restaurant name, city, or vendor name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">Search</button>
            <?php if(isset($_GET['search'])): ?>
                <a href="manage_restaurants.php" class="btn-primary" style="background: #6c757d; padding: 10px 20px; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>

        <div style="overflow-x: auto;">
            <table class="rest-table">
                <tr>
                    <th>Image</th>
                    <th>Restaurant Details</th>
                    <th>Vendor Info</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php if(count($restaurants) > 0): ?>
                    <?php foreach($restaurants as $r): ?>
                        <tr>
                            <td>
                                <img src="../<?= htmlspecialchars($r['image_url']) ?>" alt="Shop Image" class="shop-img" onerror="this.src='../assets/default_restaurant.png'">
                            </td>
                            <td>
                                <strong style="color: var(--text-dark); display: block; font-size: 16px;"><?= htmlspecialchars($r['name']) ?></strong>
                                <span style="font-size: 13px; color: var(--text-muted);">📍 <?= htmlspecialchars($r['city']) ?></span><br>
                                <span style="font-size: 13px; color: var(--text-muted);">📞 <?= htmlspecialchars($r['phone'] ?: 'N/A') ?></span>
                            </td>
                            <td>
                                <span style="font-weight: 500; color: var(--primary-color);"><?= htmlspecialchars($r['vendor_name'] ?: 'Unknown') ?></span><br>
                                <span style="font-size: 13px; color: var(--text-muted);"><?= htmlspecialchars($r['vendor_phone'] ?: '') ?></span>
                            </td>
                            
                            <td>
                                <?php 
                                    // Make sure we catch empty strings or weird values from the database
                                    $raw_status = strtolower(trim($r['status'] ?? ''));
                                    
                                    if ($raw_status === 'rejected' || $raw_status === '' || $raw_status === 'suspended') {
                                        $display_status = 'suspended';
                                        $badge_class = 'status-rejected';
                                    } else {
                                        $display_status = $raw_status;
                                        $badge_class = 'status-' . $raw_status;
                                    }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($display_status) ?></span>
                            </td>
                            
                            <td>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php if ($raw_status === 'pending'): ?>
                                        <a href="manage_restaurants.php?action=approve&id=<?= $r['restaurant_id'] ?>" class="btn-sm btn-approve" onclick="return confirm('Approve this restaurant?');">✅ Approve</a>
                                        <a href="manage_restaurants.php?action=suspend&id=<?= $r['restaurant_id'] ?>" class="btn-sm btn-suspend" onclick="return confirm('Reject this application?');">❌ Reject</a>
                                    
                                    <?php elseif ($raw_status === 'approved'): ?>
                                        <a href="manage_restaurants.php?action=suspend&id=<?= $r['restaurant_id'] ?>" class="btn-sm btn-suspend" onclick="return confirm('Suspend this restaurant? It will be hidden from customers.');">⏸️ Suspend</a>
                                    
                                    <?php else: // Catches 'rejected', empty strings, or any other broken status as suspended ?>
                                        <a href="manage_restaurants.php?action=unsuspend&id=<?= $r['restaurant_id'] ?>" class="btn-sm btn-approve" onclick="return confirm('Unsuspend this restaurant and make it live again?');">▶️ Unsuspend</a>
                                    <?php endif; ?>
                                    
                                    <a href="manage_restaurants.php?action=delete&id=<?= $r['restaurant_id'] ?>" class="btn-sm btn-delete" onclick="return confirm('WARNING: Are you sure you want to permanently delete this restaurant? This cannot be undone!');">🗑️ Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #777;">No restaurants found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
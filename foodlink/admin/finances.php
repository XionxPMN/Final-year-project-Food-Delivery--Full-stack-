<?php
session_start();
require '../includes/db.php';

// 1. Role-Based Access Control: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// CALCULATE REVENUE & COMMISSIONS (15%)
// ==========================================
// We calculate revenue by summing up the actual order_items (price * qty).
// This completely ignores the delivery fee, ensuring your 15% cut is ONLY on the food!

$sql = "
    SELECT 
        r.restaurant_id,
        r.name as restaurant_name,
        r.image_url,
        COUNT(DISTINCT o.order_id) as total_delivered_orders,
        SUM(oi.price * oi.quantity) as total_food_revenue,
        (SUM(oi.price * oi.quantity) * 0.15) as admin_commission,
        (SUM(oi.price * oi.quantity) * 0.85) as vendor_payout
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN menu_items m ON oi.item_id = m.item_id
    JOIN restaurants r ON m.restaurant_id = r.restaurant_id
    WHERE o.status = 'delivered'
    GROUP BY r.restaurant_id
    ORDER BY admin_commission DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$finances = $stmt->fetchAll();

// Calculate Grand Totals for the Top Cards
$grand_total_revenue = 0;
$grand_total_commission = 0;
$grand_total_payout = 0;

foreach ($finances as $row) {
    $grand_total_revenue += $row['total_food_revenue'];
    $grand_total_commission += $row['admin_commission'];
    $grand_total_payout += $row['vendor_payout'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Overview - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Finance Specific Styles */
        .finance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .finance-card { background: var(--white); padding: 25px; border-radius: 12px; border: 1px solid var(--border-light); box-shadow: var(--shadow-card); position: relative; overflow: hidden; }
        .finance-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; }
        .fc-revenue::before { background: #1565c0; } /* Blue */
        .fc-commission::before { background: #2e7d32; } /* Green */
        .fc-payout::before { background: #e65100; } /* Orange */
        
        .finance-card h3 { font-size: 14px; color: var(--text-muted); text-transform: uppercase; margin: 0 0 10px 0; }
        .finance-card .amount { font-size: 28px; font-weight: 800; color: var(--text-dark); }
        
        .rest-logo { width: 50px; height: 50px; min-width: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        
        .money-badge { padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 14px; display: inline-block; }
        .mb-green { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .mb-orange { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
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
            <li><a href="finances.php" class="active translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
                 <li><a href="profile.php" class="active translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview 💰</h2>
                <p style="font-size: 13px; color: #666; margin-top: 5px;">Showing data for all <strong>Delivered</strong> orders (Excluding Delivery Fees).</p>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <div class="finance-grid">
            <div class="finance-card fc-revenue">
                <h3>Total Food Sales (Gross)</h3>
                <div class="amount"><?= number_format($grand_total_revenue) ?> Ks</div>
            </div>
            <div class="finance-card fc-commission">
                <h3>Platform Income (15%)</h3>
                <div class="amount" style="color: #2e7d32;">+ <?= number_format($grand_total_commission) ?> Ks</div>
            </div>
            <div class="finance-card fc-payout">
                <h3>Vendor Payouts (85%)</h3>
                <div class="amount" style="color: #e65100;">- <?= number_format($grand_total_payout) ?> Ks</div>
            </div>
        </div>

        <div class="dashboard-card">
            <h3 style="margin-top: 0; display: flex; justify-content: space-between; align-items: center;">
                Revenue by Restaurant
                <span style="font-size: 12px; font-weight: 500; color: #777; background: #eee; padding: 4px 10px; border-radius: 50px;">Sorted by Highest Income</span>
            </h3>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <tr>
                        <th>Restaurant</th>
                        <th style="text-align: center;">Orders Completed</th>
                        <th style="text-align: right;">Total Food Sales</th>
                        <th style="text-align: right; background: #fdfdfd;">Vendor Keeps (85%)</th>
                        <th style="text-align: right; background: #f4fcf5; color: #1b5e20;">Your Cut (15%)</th>
                    </tr>
                    
                    <?php if(count($finances) > 0): ?>
                        <?php foreach($finances as $row): ?>
                        <tr>
                            <td style="display: flex; gap: 15px; align-items: center;">
                                <img src="../<?= htmlspecialchars($row['image_url'] ?? 'assets/default_restaurant.png') ?>" class="rest-logo" alt="Logo">
                                <strong style="font-size: 15px;"><?= htmlspecialchars($row['restaurant_name']) ?></strong>
                            </td>
                            
                            <td style="text-align: center; font-weight: 600; color: #555;">
                                <?= number_format($row['total_delivered_orders']) ?>
                            </td>
                            
                            <td style="text-align: right; font-weight: 600;">
                                <?= number_format($row['total_food_revenue']) ?> Ks
                            </td>
                            
                            <td style="text-align: right; background: #fdfdfd;">
                                <span class="money-badge mb-orange">
                                    <?= number_format($row['vendor_payout']) ?> Ks
                                </span>
                            </td>
                            
                            <td style="text-align: right; background: #f4fcf5;">
                                <span class="money-badge mb-green">
                                    + <?= number_format($row['admin_commission']) ?> Ks
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #777;">No delivered orders recorded yet. Sales data will appear here once customers start receiving their food!</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
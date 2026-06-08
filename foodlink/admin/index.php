<?php
session_start();
require '../includes/db.php';

// 1. Role-Based Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// HANDLE DASHBOARD QUICK ACTIONS
// ==========================================
$message = '';
$messageType = '';

// Catch Approve Action
if (isset($_GET['approve_rest'])) {
    $rest_id = (int)$_GET['approve_rest'];
    if ($pdo->prepare("UPDATE restaurants SET status = 'approved' WHERE restaurant_id = ?")->execute([$rest_id])) {
        $_SESSION['dash_msg'] = "Restaurant approved successfully and is now live!";
        $_SESSION['dash_msg_type'] = "success";
    }
    header("Location: index.php");
    exit();
}

// Catch Reject/Delete Action
if (isset($_GET['reject_rest'])) {
    $rest_id = (int)$_GET['reject_rest'];
    if ($pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = ?")->execute([$rest_id])) {
        $_SESSION['dash_msg'] = "Restaurant application has been rejected and deleted.";
        $_SESSION['dash_msg_type'] = "error";
    }
    header("Location: index.php");
    exit();
}

// Check for session messages after the page reloads
if (isset($_SESSION['dash_msg'])) {
    $message = $_SESSION['dash_msg'];
    $messageType = $_SESSION['dash_msg_type'];
    unset($_SESSION['dash_msg'], $_SESSION['dash_msg_type']);
}

// ==========================================
// FETCH SYSTEM-WIDE METRICS
// ==========================================

// Total Platform Revenue
$rev_stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'");
$total_revenue = $rev_stmt->fetchColumn() ?: 0;

// Total Orders
$order_stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $order_stmt->fetchColumn() ?: 0;

// User Breakdown
$user_stmt = $pdo->query("SELECT role, COUNT(user_id) as count FROM users GROUP BY role");
$user_breakdown = $user_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$total_customers = $user_breakdown['customer'] ?? 0;
$total_vendors = $user_breakdown['vendor'] ?? 0;
$total_riders = $user_breakdown['rider'] ?? 0;

// Pending Restaurants
$pending_rest_stmt = $pdo->query("SELECT * FROM restaurants WHERE status = 'pending' LIMIT 5");
$pending_restaurants = $pending_rest_stmt->fetchAll();

// ==========================================
// NEW: TOP PERFORMING RESTAURANTS (LEADERBOARD)
// ==========================================
$ranking_stmt = $pdo->query("
    SELECT 
        r.name, 
        r.city,
        COUNT(DISTINCT o.order_id) as total_orders,
        SUM(oi.quantity * oi.price) as total_sales_revenue
    FROM restaurants r
    JOIN menu_items m ON r.restaurant_id = m.restaurant_id
    JOIN order_items oi ON m.item_id = oi.item_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'delivered'
    GROUP BY r.restaurant_id, r.name, r.city
    ORDER BY total_orders DESC
    LIMIT 5
");
$top_restaurants = $ranking_stmt->fetchAll();

// ==========================================
// PREPARE DATA FOR BAR CHART
// ==========================================
$chart_labels = [];
$chart_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date_key = date('Y-m-d', strtotime("-$i days"));
    $display_date = date('M d', strtotime("-$i days"));
    $chart_labels[] = $display_date;
    $chart_data[$date_key] = 0;
}

$daily_stmt = $pdo->query("
    SELECT DATE(created_at) as order_date, COUNT(order_id) as daily_orders 
    FROM orders 
    WHERE created_at >= DATE(NOW()) - INTERVAL 6 DAY 
    GROUP BY DATE(created_at)
");

while ($row = $daily_stmt->fetch()) {
    $date_key = $row['order_date'];
    if (isset($chart_data[$date_key])) {
        $chart_data[$date_key] = (int)$row['daily_orders'];
    }
}
$final_daily_orders = array_values($chart_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; text-align: center; border: 1px solid var(--border-light); box-shadow: var(--shadow-card); }
        .stat-card h3 { color: var(--text-muted); font-size: 14px; margin: 0 0 10px 0; text-transform: uppercase; }
        .stat-card .number { font-size: 28px; font-weight: 800; color: var(--primary-color); }
        
        .action-list { list-style: none; padding: 0; margin: 0; }
        .action-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee; }
        .action-item:last-child { border-bottom: none; }

        /* Ranking Styles */
        .rank-badge { display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center; border-radius: 50%; font-weight: bold; color: white; margin-right: 10px; }
        .rank-1 { background: #FFD700; } /* Gold */
        .rank-2 { background: #C0C0C0; } /* Silver */
        .rank-3 { background: #CD7F32; } /* Bronze */
        .rank-other { background: var(--border-light); color: var(--text-dark); }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">FoodLink Admin</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="active translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_categories.php" class="translatable" data-en="Manage Categories" data-my="အမျိုးအစားများ စီမံရန်">Manage Categories</a></li>
            <li><a href="manage_users.php" class="translatable" data-en="Manage Users" data-my="အသုံးပြုသူများကို စီမံရန်">Manage Users</a></li>
            <li><a href="manage_banners.php" class="translatable" data-en="Manage Banners" data-my="ဘန်နာများ စီမံရန်">Manage Banners</a></li>
            <li><a href="manage_restaurants.php" class="translatable" data-en="Manage Restaurants" data-my="စားသောက်ဆိုင်များ စီမံရန်">Manage Restaurants</a></li>
            <li><a href="manage_delivery.php" class="translatable" data-en="Manage Delivery" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees</a></li>
            <li><a href="finances.php" class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
            <li><a href="profile.php" class="translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Command Center" data-my="စီမံခန့်ခွဲမှု ဗဟိုဌာန">Command Center</h2>
                <span style="color: #666; font-size: 14px;">Welcome back, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Platform Revenue</h3>
                <div class="number"><?= number_format($total_revenue, 0) ?> Ks</div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?= number_format($total_orders) ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Customers</h3>
                <div class="number" style="color: #1565c0;"><?= number_format($total_customers) ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Vendors</h3>
                <div class="number" style="color: #e65100;"><?= number_format($total_vendors) ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="dashboard-card" style="min-height: 0;">
                <h3>Platform Order Volume (Last 7 Days)</h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="ordersBarChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
            <div class="dashboard-card" style="min-height: 0;">
                <h3>User Demographics</h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="usersPieChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <div class="dashboard-card" style="margin: 0;">
                <h3 style="color: #d32f2f;">⚠️ Restaurants Awaiting Approval</h3>
                
                <?php if($message): ?>
                    <div class="alert <?= $messageType ?>" style="margin-bottom: 15px;"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if(count($pending_restaurants) > 0): ?>
                    <ul class="action-list">
                        <?php foreach($pending_restaurants as $rest): ?>
                            <li class="action-item">
                                <div>
                                    <strong><?= htmlspecialchars($rest['name']) ?></strong><br>
                                    <span style="font-size: 13px; color: #777;">📍 <?= htmlspecialchars($rest['city']) ?></span>
                                </div>
                                
                                <div style="display: flex; gap: 10px; margin-top: 10px;">
                                    <a href="index.php?approve_rest=<?= $rest['restaurant_id'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: #28a745; text-decoration: none;">Approve</a>
                                    
                                    <a href="index.php?reject_rest=<?= $rest['restaurant_id'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: #dc3545; text-decoration: none;" onclick="return confirm('Are you sure you want to REJECT and delete this application?')">Reject</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); padding: 10px 0;">All caught up! No pending restaurants.</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card" style="margin: 0;">
                <h3 style="color: var(--primary-color);">🏆 Top Performing Restaurants</h3>
                
                <?php if(count($top_restaurants) > 0): ?>
                    <ul class="action-list">
                        <?php 
                        $rank = 1;
                        foreach($top_restaurants as $top_rest): 
                            // Determine badge color based on rank
                            $badge_class = ($rank <= 3) ? "rank-{$rank}" : "rank-other";
                        ?>
                            <li class="action-item">
                                <div style="display: flex; align-items: center;">
                                    <span class="rank-badge <?= $badge_class ?>"><?= $rank ?></span>
                                    <div>
                                        <strong><?= htmlspecialchars($top_rest['name']) ?></strong><br>
                                        <span style="font-size: 13px; color: #777;">📍 <?= htmlspecialchars($top_rest['city']) ?></span>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="color: var(--text-dark); font-size: 15px;"><?= number_format($top_rest['total_orders']) ?> Orders</strong><br>
                                    <span style="font-size: 13px; color: #28a745; font-weight: 600;"><?= number_format($top_rest['total_sales_revenue'], 0) ?> Ks</span>
                                </div>
                            </li>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); padding: 10px 0;">No sales data available yet.</p>
                <?php endif; ?>
            </div>

        </div> </div>

    <script src="../assets/translate.js"></script>
    
    <script>
        // --- BAR CHART ---
        const barCtx = document.getElementById('ordersBarChart').getContext('2d');
        const dailyLabels = <?= json_encode($chart_labels) ?>;
        const dailyData = <?= json_encode($final_daily_orders) ?>;

        new Chart(barCtx, {
            type: 'line', 
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Total Orders',
                    data: dailyData,
                    borderColor: 'rgba(94, 0, 6, 1)',
                    backgroundColor: 'rgba(94, 0, 6, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        // --- PIE CHART ---
        const pieCtx = document.getElementById('usersPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Customers', 'Vendors', 'Riders'],
                datasets: [{
                    data: [<?= $total_customers ?>, <?= $total_vendors ?>, <?= $total_riders ?>],
                    backgroundColor: ['#1565c0', '#e65100', '#4527a0'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false 
            }
        });
    </script>
</body>
</html>
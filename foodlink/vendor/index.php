<?php
session_start();
require '../includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

// Get the vendor's restaurant ID
$stmt = $pdo->prepare("SELECT restaurant_id, name FROM restaurants WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$restaurant = $stmt->fetch();

$restaurant_id = $restaurant ? $restaurant['restaurant_id'] : null;

// Initialize stats
$monthly_revenue = 0;
$total_orders = 0;
$pending_orders = 0;
$top_items = [];

// NEW: Arrays for our Daily Chart
$chart_labels = [];
$final_daily_data = [];

if ($restaurant_id) {
    // 1. Calculate Monthly Revenue & Total Orders (For the Top Number Cards)
    $stats_stmt = $pdo->prepare("
        SELECT 
            SUM(oi.quantity * m.price) as revenue, 
            COUNT(DISTINCT o.order_id) as orders
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN menu_items m ON oi.item_id = m.item_id
        WHERE m.restaurant_id = ? 
        AND o.status = 'delivered' 
        AND MONTH(o.created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(o.created_at) = YEAR(CURRENT_DATE())
    ");
    $stats_stmt->execute([$restaurant_id]);
    $stats = $stats_stmt->fetch();
    $monthly_revenue = $stats['revenue'] ?? 0;
    $total_orders = $stats['orders'] ?? 0;

    // 2. Count Active/Pending Orders right now
    $pending_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT o.order_id) as pending
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN menu_items m ON oi.item_id = m.item_id
        WHERE m.restaurant_id = ? AND o.status IN ('pending', 'confirmed', 'preparing')
    ");
    $pending_stmt->execute([$restaurant_id]);
    $pending_orders = $pending_stmt->fetchColumn() ?? 0;

    // 3. Get Top 5 Most In-Demand Items (All time)
    $top_stmt = $pdo->prepare("
        SELECT 
            m.name, 
            m.image_url,
            SUM(oi.quantity) as total_sold, 
            SUM(oi.quantity * m.price) as total_earned
        FROM order_items oi
        JOIN menu_items m ON oi.item_id = m.item_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE m.restaurant_id = ? AND o.status = 'delivered'
        GROUP BY m.item_id
        ORDER BY total_sold DESC 
        LIMIT 5
    ");
    $top_stmt->execute([$restaurant_id]);
    $top_items = $top_stmt->fetchAll();

    // 4. NEW: Calculate DAILY Revenue for the last 7 days!
    $chart_data = [];
    
    // First, create the last 7 days (so days with 0 sales still show up on the chart)
    for ($i = 6; $i >= 0; $i--) {
        $date_key = date('Y-m-d', strtotime("-$i days")); // e.g., "2026-03-20"
        $display_date = date('M d', strtotime("-$i days")); // e.g., "Mar 20"
        
        $chart_labels[] = $display_date;
        $chart_data[$date_key] = 0; // Default to 0 Ks
    }

    // Now, fetch the actual daily sales from the database
    $daily_stmt = $pdo->prepare("
        SELECT 
            DATE(o.created_at) as order_date,
            SUM(oi.quantity * m.price) as daily_revenue
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN menu_items m ON oi.item_id = m.item_id
        WHERE m.restaurant_id = ? 
        AND o.status = 'delivered' 
        AND o.created_at >= DATE(NOW()) - INTERVAL 6 DAY
        GROUP BY DATE(o.created_at)
    ");
    $daily_stmt->execute([$restaurant_id]);
    
    // Replace the 0s with actual revenue where it exists
    while ($row = $daily_stmt->fetch()) {
        $date_key = $row['order_date'];
        if (isset($chart_data[$date_key])) {
            $chart_data[$date_key] = (int)$row['daily_revenue'];
        }
    }
    
    // Flatten the array so Chart.js can read just the numbers
    $final_daily_data = array_values($chart_data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vendor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* =========================================
           GLASSMORPHISM STYLES 
           ========================================= */
        .dashboard-body {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            min-height: 100vh;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.65) !important;
            backdrop-filter: blur(12px) saturate(150%);
            -webkit-backdrop-filter: blur(12px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05) !important;
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { padding: 25px; border-radius: 12px; text-align: center; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: var(--text-muted); font-size: 14px; margin: 0 0 10px 0; text-transform: uppercase; }
        .stat-card .number { font-size: 28px; font-weight: 800; color: var(--primary-color); }
        
        .demand-list { list-style: none; padding: 0; margin: 0; }
        .demand-item { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .demand-item:last-child { border-bottom: none; }
        .demand-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; margin-right: 15px; }
        .demand-info { display: flex; align-items: center; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar glass-panel">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="active translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_menu.php" class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
            <li><a href="history.php" class="translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar glass-panel">
            <div>
                <h2 class="translatable" data-en="Shop Overview" data-my="ဆိုင် အကျဉ်းချုပ်">Shop Overview</h2>
                <span style="color: var(--text-muted); font-size: 14px;">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn glass-panel" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if(!$restaurant_id): ?>
            <div class="alert error translatable" data-en="Please go to Shop Settings to set up your restaurant profile first!" data-my="ကျေးဇူးပြု၍ ဆိုင်ဆက်တင်များသို့သွား၍ သင့်ဆိုင်ပရိုဖိုင်ကို အရင်ဖြည့်စွက်ပါ။">
                Please go to Shop Settings to set up your restaurant profile first!
            </div>
        <?php else: ?>

            <div class="stats-grid">
                <div class="stat-card glass-panel">
                    <h3 class="translatable" data-en="Monthly Income" data-my="လစဉ် ဝင်ငွေ">Monthly Income</h3>
                    <div class="number"><?= number_format($monthly_revenue, 0) ?> Ks</div>
                </div>
                <div class="stat-card glass-panel">
                    <h3 class="translatable" data-en="Completed Orders (This Month)" data-my="ပြီးစီးသော အော်ဒါများ (ဒီလ)">Completed Orders (This Month)</h3>
                    <div class="number"><?= number_format($total_orders) ?></div>
                </div>
                <div class="stat-card glass-panel" style="border: 2px solid rgba(245, 166, 35, 0.5) !important;">
                    <h3 class="translatable" data-en="Active Orders (Action Needed)" data-my="လက်ရှိ အော်ဒါများ (လုပ်ဆောင်ရန်)">Active Orders (Action Needed)</h3>
                    <div class="number" style="color: var(--text-dark);"><?= number_format($pending_orders) ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <div class="dashboard-card glass-panel" style="min-height: 0;">
                    <h3 class="translatable" data-en="Daily Revenue (Last 7 Days)" data-my="နေ့စဉ်ဝင်ငွေ (လွန်ခဲ့သော ၇ ရက်)">Daily Revenue (Last 7 Days)</h3>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="revenueBarChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>

                <div class="dashboard-card glass-panel" style="min-height: 0;">
                    <h3 class="translatable" data-en="Top Items Breakdown" data-my="အရောင်းရဆုံး အစားအစာများ">Top Items Breakdown</h3>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="itemsPieChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-card glass-panel">
                <h3 style="margin-top:0;" class="translatable" data-en="Top Selling Items 🏆" data-my="အရောင်းရဆုံး အစားအစာများ 🏆">Top Selling Items 🏆</h3>
                
                <?php if(count($top_items) > 0): ?>
                    <ul class="demand-list">
                        <?php foreach($top_items as $index => $item): ?>
                            <li class="demand-item">
                                <div class="demand-info">
                                    <h2 style="color: var(--text-muted); margin: 0 15px 0 0; width: 25px;">#<?= $index + 1 ?></h2>
                                    <img src="../<?= htmlspecialchars($item['image_url']) ?>" class="demand-img">
                                    <div>
                                        <strong style="font-size: 16px; color: var(--text-dark);"><?= htmlspecialchars($item['name']) ?></strong><br>
                                        <span style="font-size: 13px; color: var(--text-muted);"><?= number_format($item['total_sold']) ?> portions sold</span>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="color: #28a745; font-size: 16px;">+ <?= number_format($item['total_earned'], 0) ?> Ks</strong>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">No sales data available yet. Deliver your first order to see rankings!</p>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

    <script src="../assets/translate.js"></script>
    <script>
        <?php if($restaurant_id): ?>
        // --- 1. BAR CHART (DAILY REVENUE) ---
        const barCtx = document.getElementById('revenueBarChart').getContext('2d');
        
        // Pass the dynamic Daily Dates and Daily Sales from PHP to Javascript
        const dailyLabels = <?= json_encode($chart_labels) ?>;
        const dailyData = <?= json_encode($final_daily_data) ?>;

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: dailyLabels, 
                datasets: [{
                    label: 'Revenue (Ks)',
                    data: dailyData, 
                    backgroundColor: 'rgba(94, 0, 6, 0.85)', // Maroon
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // --- 2. PIE CHART (Top Items) ---
        const pieCtx = document.getElementById('itemsPieChart').getContext('2d');
        
        const itemNames = <?= json_encode(array_column($top_items, 'name')) ?>;
        const itemSales = <?= json_encode(array_column($top_items, 'total_sold')) ?>;

        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: itemNames.length > 0 ? itemNames : ['No Data'],
                datasets: [{
                    data: itemSales.length > 0 ? itemSales : [100],
                    backgroundColor: [
                        'rgba(94, 0, 6, 0.85)', 
                        'rgba(245, 166, 35, 0.85)', 
                        'rgba(211, 47, 47, 0.85)', 
                        'rgba(74, 74, 74, 0.85)', 
                        'rgba(204, 204, 204, 0.85)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false 
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>

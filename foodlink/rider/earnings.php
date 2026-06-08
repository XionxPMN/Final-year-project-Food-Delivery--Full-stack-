<?php
session_start();
require '../includes/db.php';

// 1. Security Check: ONLY Riders allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: ../login.php");
    exit();
}

$rider_id = $_SESSION['user_id'];

// 2. Fetch Delivered Orders & Calculate Delivery Fee (Rider's Pay)
// Formula: Total Order Amount - Cost of Food = Delivery Fee
$sql = "
    SELECT 
        o.order_id, 
        o.created_at, 
        o.payment_method,
        o.total_amount,
        (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.order_id) as food_total,
        (o.total_amount - (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.order_id)) as rider_earning
    FROM orders o
    WHERE o.rider_id = ? AND o.status = 'delivered'
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$rider_id]);
$completed_jobs = $stmt->fetchAll();

// 3. Calculate Total All-Time Earnings
$total_earnings = 0;
foreach ($completed_jobs as $job) {
    $total_earnings += $job['rider_earning'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Earnings - Rider Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .earnings-card { background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); color: var(--white); padding: 30px; border-radius: 12px; box-shadow: var(--shadow-hover); text-align: center; margin-bottom: 30px; }
        .earnings-card h3 { margin: 0; font-size: 16px; font-weight: 500; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px;}
        .earnings-card .amount { font-size: 40px; font-weight: 800; margin: 10px 0 0 0; }
        
        .history-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-card); }
        .history-table th, .history-table td { padding: 15px 20px; border-bottom: 1px solid var(--border-light); text-align: left; }
        .history-table th { background: #f9f9f9; color: var(--text-muted); font-size: 13px; text-transform: uppercase; }
        .history-table tr:last-child td { border-bottom: none; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Rider Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Delivery Jobs" data-my="ပို့ဆောင်ရန် အလုပ်များ">Delivery Jobs</a></li>
            <li><a href="earnings.php" class="active translatable" data-en="My Earnings" data-my="ကျွန်ုပ်၏ ဝင်ငွေများ">My Earnings</a></li>
            <li><a href="profile.php" class="translatable" data-en="Profile Settings" data-my="ပရိုဖိုင် ဆက်တင်များ">Profile Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="My Earnings 💰" data-my="ကျွန်ုပ်၏ ဝင်ငွေများ 💰">My Earnings 💰</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <div class="earnings-card">
            <h3 class="translatable" data-en="Total All-Time Earnings" data-my="စုစုပေါင်း ဝင်ငွေ">Total All-Time Earnings</h3>
            <div class="amount"><?= number_format($total_earnings, 0) ?> Ks</div>
        </div>

        <h3 style="color: var(--text-dark); margin-bottom: 15px;" class="translatable" data-en="Completed Jobs History" data-my="ပြီးစီးခဲ့သော အလုပ်များ">Completed Jobs History</h3>
        
        <div style="overflow-x: auto;">
            <table class="history-table">
                <tr>
                    <th>Date</th>
                    <th>Order ID</th>
                    <th>Payment Type</th>
                    <th style="text-align: right;">Your Payout (Delivery Fee)</th>
                </tr>
                <?php if(count($completed_jobs) > 0): ?>
                    <?php foreach($completed_jobs as $job): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 14px;"><?= date('d M Y, h:i A', strtotime($job['created_at'])) ?></td>
                            <td><strong style="color: var(--text-dark);">#<?= $job['order_id'] ?></strong></td>
                            <td>
                                <span style="background: var(--bg-light); padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                    <?= htmlspecialchars($job['payment_method']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; color: #2e7d32; font-weight: 800; font-size: 16px;">
                                + <?= number_format($job['rider_earning'], 0) ?> Ks
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #777;" class="translatable" data-en="You haven't completed any deliveries yet." data-my="သင် မည်သည့်အော်ဒါမှ ပို့ဆောင်ပြီးစီးခြင်း မရှိသေးပါ။">You haven't completed any deliveries yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
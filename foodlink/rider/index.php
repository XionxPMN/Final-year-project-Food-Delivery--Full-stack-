<?php
session_start();
require '../includes/db.php';

// 1. Security Check: ONLY Riders allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: ../login.php");
    exit();
}

$rider_id = $_SESSION['user_id'];
$message = '';

// 2. Handle "Accept Order"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    // We strictly check that the rider_id is NULL so two drivers don't take the same order!
    $stmt = $pdo->prepare("UPDATE orders SET status = 'delivering', rider_id = ? WHERE order_id = ? AND status = 'preparing' AND rider_id IS NULL");
    if ($stmt->execute([$rider_id, $order_id]) && $stmt->rowCount() > 0) {
        $message = "Order #$order_id accepted! Head to the restaurant to pick it up.";
    } else {
        $message = "Sorry! Another rider already took this order.";
    }
}

// 3. Handle "Complete Delivery"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE order_id = ? AND rider_id = ?");
    if ($stmt->execute([$order_id, $rider_id])) {
        $message = "Order #$order_id marked as delivered. Great job!";
    }
}

// 4. Fetch Available Jobs (Orders that are 'preparing' and have no rider yet)
$avail_stmt = $pdo->query("SELECT * FROM orders WHERE status = 'preparing' AND rider_id IS NULL ORDER BY created_at ASC");
$available_orders = $avail_stmt->fetchAll();

// 5. Fetch This Rider's Active Deliveries
$my_stmt = $pdo->prepare("SELECT * FROM orders WHERE status = 'delivering' AND rider_id = ? ORDER BY created_at DESC");
$my_stmt->execute([$rider_id]);
$my_deliveries = $my_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - FoodLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .rider-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-top: 15px; }
        .delivery-card { background: var(--white); border-radius: 12px; padding: 25px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); }
        .delivery-card.active-job { border-color: var(--primary-color); border-width: 2px; box-shadow: 0 4px 15px rgba(94, 0, 6, 0.1); }
        
        .location-block { background: var(--bg-light); padding: 15px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; border-left: 4px solid var(--text-dark); }
        .location-block.dropoff { border-left-color: var(--primary-color); }
        .location-block p { margin: 5px 0; color: var(--text-dark); }
        
        .payment-tag { display: inline-block; padding: 5px 10px; background: #e8f5e9; color: #2e7d32; border-radius: 6px; font-weight: 700; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Rider Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="active translatable" data-en="Delivery Jobs" data-my="ပို့ဆောင်ရန် အလုပ်များ">Delivery Jobs</a></li>
            <li><a href="earnings.php" class="translatable" data-en="My Earnings" data-my="ကျွန်ုပ်၏ ဝင်ငွေများ">My Earnings</a></li>
            <li><a href="profile.php" class="translatable" data-en="Profile Settings" data-my="ပရိုဖိုင် ဆက်တင်များ">Profile Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Rider Dashboard" data-my="ပို့ဆောင်သူ ဒက်ရှ်ဘုတ်">Rider Dashboard</h2>
                <span style="color: #666; font-size: 14px;">Stay safe on the road, <?= htmlspecialchars($_SESSION['name']) ?>! 🛵</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h3 style="color: var(--text-dark); margin-top: 30px;" class="translatable" data-en="My Active Deliveries 🛵" data-my="လက်ရှိ ပို့ဆောင်နေသော အော်ဒါများ 🛵">My Active Deliveries 🛵</h3>
        <div class="rider-grid" style="margin-bottom: 40px;">
            <?php if(count($my_deliveries) > 0): ?>
                <?php foreach($my_deliveries as $order): ?>
                    <div class="delivery-card active-job">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3 style="margin: 0; color: var(--primary-color);">Order #<?= $order['order_id'] ?></h3>
                            <span class="payment-tag"><?= $order['payment_method'] ?>: <?= number_format($order['total_amount'], 0) ?> Ks</span>
                        </div>

                        <div class="location-block dropoff">
                            <strong style="color: var(--primary-color);">📍 Drop-off at Customer:</strong>
                            <p><strong>Address:</strong> <?= htmlspecialchars($order['delivery_address']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone_number']) ?></p>
                            <?php if(!empty($order['notes'])): ?>
                                <p style="color: var(--primary-color); font-weight: 600;"> Note: <?= htmlspecialchars($order['notes']) ?></p>
                            <?php endif; ?>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" name="complete_order" class="btn-primary translatable" data-en="Mark as Delivered" data-my="ပို့ဆောင်ပြီးကြောင်း အတည်ပြုမည်" style="width: 100%; font-size: 16px;">Mark as Delivered </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted);" class="translatable" data-en="You don't have any active deliveries right now." data-my="ယခုပို့ဆောင်နေသော အော်ဒါ မရှိသေးပါ။">You don't have any active deliveries right now.</p>
            <?php endif; ?>
        </div>


        <h3 style="color: var(--text-dark); border-top: 2px dashed var(--border-light); padding-top: 30px;" class="translatable" data-en="Available Orders to Pick Up " data-my="ပို့ဆောင်ရန် အသင့်ဖြစ်နေသော အော်ဒါများ ">Available Orders to Pick Up </h3>
        <div class="rider-grid">
            <?php if(count($available_orders) > 0): ?>
                <?php foreach($available_orders as $order): ?>
                    
                    <?php
                    // Fetch the restaurant details for this specific order
                    $rest_stmt = $pdo->prepare("SELECT DISTINCT r.name, r.city FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id JOIN restaurants r ON m.restaurant_id = r.restaurant_id WHERE oi.order_id = ?");
                    $rest_stmt->execute([$order['order_id']]);
                    $restaurants = $rest_stmt->fetchAll();
                    ?>

                    <div class="delivery-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3 style="margin: 0; color: var(--text-dark);">Order #<?= $order['order_id'] ?></h3>
                            <span style="font-weight: 700; color: var(--text-muted);"><?= number_format($order['total_amount'], 0) ?> Ks</span>
                        </div>

                        <div class="location-block">
                            <strong>🏪 Pick-up from:</strong>
                            <?php foreach($restaurants as $r): ?>
                                <p><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['city']) ?>)</p>
                            <?php endforeach; ?>
                        </div>

                        <div class="location-block dropoff">
                            <strong style="color: var(--primary-color);">📍 Drop-off to:</strong>
                            <p><?= htmlspecialchars($order['delivery_address']) ?></p>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" name="accept_order" class="btn-primary translatable" data-en="Accept Delivery" data-my="ပို့ဆောင်ရန် လက်ခံမည်" style="width: 100%; background: var(--text-dark);">Accept Delivery</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; background: var(--white); padding: 40px; text-align: center; border-radius: 12px; box-shadow: var(--shadow-sm);">
                    <p style="color: var(--text-muted);" class="translatable" data-en="No food is currently preparing. Waiting for restaurants..." data-my="ယခုအချိန်တွင် ပြင်ဆင်နေသော အော်ဒါ မရှိသေးပါ။">No food is currently preparing. Waiting for restaurants...</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>

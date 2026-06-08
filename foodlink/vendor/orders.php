<?php
session_start();
require '../includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// 2. Get the vendor's restaurant ID
$stmt = $pdo->prepare("SELECT restaurant_id FROM restaurants WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$restaurant = $stmt->fetch();

// If they haven't set up their profile yet, they can't have orders!
if (!$restaurant) {
    $restaurant_id = null;
} else {
    $restaurant_id = $restaurant['restaurant_id'];
}

// ==========================================
// 3. Handle Status Updates (WITH RIDER LOCK)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $order_id = (int)$_POST['order_id'];

    // SECURITY CHECK: Get the current status from the DB right now
    $check_stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
    $check_stmt->execute([$order_id]);
    $current_db_status = strtolower(trim($check_stmt->fetchColumn()));

    // If the rider already took it or delivered it, BLOCK the vendor from changing it backwards!
    if ($current_db_status === 'delivering' || $current_db_status === 'delivered') {
        header("Location: orders.php?msg=locked&id=" . $order_id);
        exit();
    }

    // If it's safe, update it!
    $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($update_stmt->execute([$new_status, $order_id])) {
        // REDIRECT immediately to clear browser POST history
        header("Location: orders.php?msg=updated&id=" . $order_id);
        exit();
    }
}

// Catch messages from the URL after redirect
if (isset($_GET['msg']) && isset($_GET['id'])) {
    if ($_GET['msg'] === 'updated') {
        $message = "Order #" . (int)$_GET['id'] . " status updated successfully!";
        $messageType = "success";
    } elseif ($_GET['msg'] === 'locked') {
        $message = "Cannot update Order #" . (int)$_GET['id'] . " because the Rider has already picked it up or delivered it!";
        $messageType = "error"; // Shows in red
    }
}

// ==========================================
// 4. Fetch all active orders (Hide Delivered AND Completed)
// ==========================================
$orders = [];
if ($restaurant_id) {
    $orders_sql = "
        SELECT * FROM orders 
        WHERE LOWER(TRIM(status)) != 'delivered' 
        AND LOWER(TRIM(status)) != 'completed'
        AND order_id IN (
            SELECT oi.order_id 
            FROM order_items oi
            JOIN menu_items m ON oi.item_id = m.item_id
            WHERE m.restaurant_id = ?
        )
        ORDER BY created_at DESC
    ";
    
    $orders_stmt = $pdo->prepare($orders_sql);
    $orders_stmt->execute([$restaurant_id]);
    $orders = $orders_stmt->fetchAll();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Orders - Vendor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; margin-top: 20px; }
        .order-card { background: var(--white); border-radius: 12px; padding: 25px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); position: relative; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed var(--border-light); padding-bottom: 15px; margin-bottom: 15px; }
        .order-id { font-size: 20px; font-weight: 800; color: var(--primary-color); }
        .order-time { font-size: 13px; color: var(--text-muted); }
        
        .customer-info { background: var(--bg-light); padding: 15px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .customer-info p { margin: 5px 0; color: var(--text-dark); }
        
        .food-list { list-style: none; padding: 0; margin-bottom: 20px; }
        .food-list li { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px; font-weight: 500; }
        
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-confirmed { background: #D1ECF1; color: #0C5460; }
        .status-preparing { background: #CCE5FF; color: #004085; }
        .status-delivering { background: #E2E3E5; color: #383D41; }
        .status-delivered { background: #D4EDDA; color: #155724; }
        
        .status-form { display: flex; gap: 10px; margin-top: 15px; border-top: 1px solid var(--border-light); padding-top: 15px;}
        .status-select { flex: 1; padding: 10px; border: 1px solid var(--border-light); border-radius: 6px; font-family: 'Poppins'; outline: none; cursor: pointer; }
        .btn-update { background: var(--text-dark); color: white; border: none; padding: 10px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-update:hover { background: var(--primary-color); }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_menu.php" class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="active translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
            <li><a href="history.php" class="translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div><h2 class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</h2></div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if(!$restaurant_id): ?>
            <div class="alert error translatable" data-en="Please complete your Restaurant Profile first to start receiving orders." data-my="အော်ဒါများ လက်ခံရရှိရန် သင့်စားသောက်ဆိုင် ပရိုဖိုင်ကို အရင်ဖြည့်စွက်ပါ။">
                Please complete your Restaurant Profile first to start receiving orders.
            </div>
        <?php else: ?>
            <div class="order-grid">
                <?php if(count($orders) > 0): ?>
                    <?php foreach($orders as $order): ?>
                        
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id">#<?= $order['order_id'] ?></span>
                                <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                            </div>
                            
                            <div class="order-time">🕒 <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                            
                            <div class="customer-info" style="margin-top: 15px;">
                                <p><strong>📞 Phone:</strong> <?= htmlspecialchars($order['phone_number']) ?></p>
                                <p><strong>📍 Address:</strong> <?= htmlspecialchars($order['delivery_address']) ?></p>
                                <p><strong>💳 Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                                <?php if(!empty($order['notes'])): ?>
                                    <p style="color: var(--primary-color); font-weight: 600;">📝 Note: <?= htmlspecialchars($order['notes']) ?></p>
                                <?php endif; ?>
                            </div>

                            <ul class="food-list">
                                <?php
                                $items_stmt = $pdo->prepare("
                                    SELECT oi.quantity, m.name 
                                    FROM order_items oi 
                                    JOIN menu_items m ON oi.item_id = m.item_id 
                                    WHERE oi.order_id = ? AND m.restaurant_id = ?
                                ");
                                $items_stmt->execute([$order['order_id'], $restaurant_id]);
                                $order_items = $items_stmt->fetchAll();
                                
                                foreach($order_items as $item):
                                ?>
                                    <li>
                                        <span><strong style="color: var(--primary-color);"><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['name']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div style="font-size: 18px; font-weight: 800; text-align: right; color: var(--text-dark);">
                                <span class="translatable" data-en="Total:" data-my="စုစုပေါင်း:">Total:</span> <?= number_format($order['total_amount'], 0) ?> Ks
                            </div>

                           <?php if(strtolower(trim($order['status'])) == 'delivering'): ?>
                                <div style="margin-top: 15px; padding: 12px; background: #E2E3E5; border-radius: 6px; text-align: center; font-weight: 600; color: #383D41; border: 1px dashed #b8bcc2;">
                                    🛵 Out for Delivery (Rider has picked up)
                                </div>
                            <?php else: ?>
                                <form method="POST" action="orders.php" class="status-form">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>Preparing Food</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update translatable" data-en="Update" data-my="အတည်ပြုမည်">Update</button>
                                </form>
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; background: var(--white); padding: 50px; text-align: center; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light);">
                        <h3 style="color: var(--text-muted);" class="translatable" data-en="No orders yet." data-my="အော်ဒါ မရှိသေးပါ။">No orders yet.</h3>
                        <p class="translatable" data-en="When customers place an order, it will appear here instantly!" data-my="ဝယ်ယူသူများ အော်ဒါတင်သည့်အခါ ဤနေရာတွင် ချက်ချင်းပေါ်လာပါမည်!">When customers place an order, it will appear here instantly!</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
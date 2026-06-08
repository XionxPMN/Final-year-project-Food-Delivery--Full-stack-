<?php
session_start();
require 'includes/db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================================
// NEW: HANDLE REORDER LOGIC
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'reorder' && isset($_GET['id'])) {
    $reorder_id = (int)$_GET['id'];

    // 1. Verify the order actually belongs to this user (Security Check)
    $verify_stmt = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
    $verify_stmt->execute([$reorder_id, $user_id]);

    if ($verify_stmt->fetch()) {
        // 2. Fetch the exact items and quantities from that past order
        $items_stmt = $pdo->prepare("SELECT item_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$reorder_id]);
        $order_items = $items_stmt->fetchAll();

        // 3. Clear their current cart and replace it with the reorder items
        $_SESSION['cart'] = [];
        foreach ($order_items as $item) {
            $_SESSION['cart'][] = [
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity']
            ];
        }

        // 4. Send them straight to the cart to review and checkout!
        header("Location: cart.php");
        exit();
    }
}

// Fetch all orders for this user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .orders-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .customer-order-card { display: flex; justify-content: space-between; align-items: center; background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); margin-bottom: 15px; transition: 0.3s; flex-wrap: wrap; gap: 15px;}
        .customer-order-card:hover { border-color: var(--primary-color); box-shadow: var(--shadow-card); }
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase; display: inline-block; margin-top: 5px;}
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-confirmed { background: #D1ECF1; color: #0C5460; }
        .status-preparing { background: #CCE5FF; color: #004085; }
        .status-delivering { background: #E2E3E5; color: #383D41; }
        .status-delivered { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">FoodLink</a>
        <div class="nav-actions">
            <a href="index.php" class="translatable" data-en="Back to Menu" data-my="မီနူးသို့ ပြန်သွားမည်" style="color: var(--text-dark); text-decoration: none; font-weight: 600;">Back to Menu</a>
        </div>
    </nav>

    <div class="orders-container">
        <h2 style="color: var(--text-dark); margin-bottom: 25px;" class="translatable" data-en="My Order History" data-my="ကျွန်ုပ်၏ အော်ဒါမှတ်တမ်း">My Order History</h2>

        <?php if(count($orders) > 0): ?>
            <?php foreach($orders as $order): ?>
                <div class="customer-order-card">
                    <div>
                        <h3 style="margin: 0 0 5px; color: var(--text-dark);">Order #<?= $order['order_id'] ?></h3>
                        <p style="margin: 0 0 5px; color: var(--text-muted); font-size: 14px;">📅 <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?> • <strong><?= number_format($order['total_amount'], 0) ?> Ks</strong></p>
                        <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                    </div>
                    
                    <div class="action-buttons">
                        <?php if($order['status'] === 'pending' || $order['status'] === 'preparing' || $order['status'] === 'ready'): ?>
                            
                            <a href="track_order.php?id=<?= $order['order_id'] ?>" class="btn-primary" style="padding: 10px 20px; font-size: 14px; width: auto; margin: 0;">Track Order</a>
                        
                        <?php else: ?>
                            
                            <a href="receipt.php?id=<?= $order['order_id'] ?>" class="btn-primary translatable" data-en="Receipt" data-my="ဖြတ်ပိုင်း" style="padding: 10px 20px; font-size: 14px; background: var(--bg-light); color: var(--text-dark); border: 1px solid var(--border-light); width: auto; margin: 0;">Receipt</a>
                            
                            <a href="my_orders.php?action=reorder&id=<?= $order['order_id'] ?>" class="btn-primary translatable" data-en="Reorder" data-my="ထပ်မံမှာယူမည်" style="padding: 10px 20px; font-size: 14px; background: #28a745; width: auto; margin: 0;">Reorder 🔄</a>
                            
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: var(--white); border-radius: 12px; border: 1px solid var(--border-light);">
                <p style="color: var(--text-muted);" class="translatable" data-en="You haven't placed any orders yet." data-my="သင် အော်ဒါတင်ထားခြင်း မရှိသေးပါ။">You haven't placed any orders yet.</p>
                <a href="index.php" class="btn-primary" style="display: inline-block; width: auto; margin-top: 15px;">Start Browsing</a>
            </div>
        <?php endif; ?>

    </div>
    
    <script src="assets/translate.js"></script>
</body>
</html>
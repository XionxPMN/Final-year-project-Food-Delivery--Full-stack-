<?php
session_start();
require 'includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 2. Fetch the specific order, but ONLY if this user owns it!
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Receipt not found or you do not have permission to view it.");
}

// 3. Fetch the items for this order
$items_stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, m.name, r.name as restaurant_name
    FROM order_items oi
    JOIN menu_items m ON oi.item_id = m.item_id
    JOIN restaurants r ON m.restaurant_id = r.restaurant_id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll();

// Calculate subtotal
$subtotal = 0;
foreach($order_items as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$delivery_fee = $order['total_amount'] - $subtotal;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $order_id ?> - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: var(--bg-light); display: flex; justify-content: center; padding: 40px 20px; }
        
        .receipt-card { background: var(--white); width: 100%; max-width: 450px; padding: 40px 30px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); font-family: 'Courier New', Courier, monospace; /* Gives it a realistic receipt feel */ color: #333; }
        
        .receipt-header { text-align: center; margin-bottom: 30px; border-bottom: 2px dashed #ccc; padding-bottom: 20px; }
        .receipt-header h1 { margin: 0; font-family: 'Poppins', sans-serif; color: var(--primary-color); font-size: 24px; font-weight: 800; }
        .receipt-header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .receipt-details { margin-bottom: 20px; font-size: 14px; }
        .receipt-details p { margin: 4px 0; }
        
        .item-list { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        .item-list th { text-align: left; border-bottom: 1px solid #ddd; padding-bottom: 8px; }
        .item-list td { padding: 8px 0; vertical-align: top; }
        .item-list .qty { width: 40px; }
        .item-list .price { text-align: right; }
        
        .totals { border-top: 2px dashed #ccc; padding-top: 15px; margin-bottom: 30px; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .grand-total { font-size: 18px; font-weight: bold; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        
        .action-buttons { display: flex; gap: 15px; margin-top: 20px; font-family: 'Poppins', sans-serif; }
        .action-buttons a, .action-buttons button { flex: 1; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.3s; }
        
        .btn-back { background: var(--bg-light); color: var(--text-dark); border: 1px solid var(--border-light); }
        .btn-back:hover { background: #e2e2e2; }
        .btn-print { background: var(--primary-color); color: var(--white); border: none; }
        .btn-print:hover { background: var(--primary-hover); }

        /* Print CSS magic! Hides buttons and centers the receipt on the paper */
        @media print {
            body { background: white; padding: 0; }
            .action-buttons { display: none !important; }
            .receipt-card { box-shadow: none; border: none; padding: 0; margin: 0 auto; }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        
        <div class="receipt-header">
            <h1>FoodLink Myanmar</h1>
            <p>Official E-Receipt</p>
        </div>

        <div class="receipt-details">
            <p><strong>Order No:</strong> #<?= $order['order_id'] ?></p>
            <p><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            <p><strong>Status:</strong> <?= strtoupper($order['status']) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        </div>

        <table class="item-list">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Item</th>
                    <th class="price">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order_items as $item): ?>
                    <tr>
                        <td class="qty"><?= $item['quantity'] ?>x</td>
                        <td>
                            <?= htmlspecialchars($item['name']) ?><br>
                            <small style="color: #888; font-size: 11px;">[<?= htmlspecialchars($item['restaurant_name']) ?>]</small>
                        </td>
                        <td class="price"><?= number_format($item['price'] * $item['quantity'], 0) ?> Ks</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span><?= number_format($subtotal, 0) ?> Ks</span>
            </div>
            <div class="totals-row">
                <span>Delivery Fee</span>
                <span><?= number_format($delivery_fee, 0) ?> Ks</span>
            </div>
            <div class="totals-row grand-total">
                <span>TOTAL</span>
                <span><?= number_format($order['total_amount'], 0) ?> Ks</span>
            </div>
        </div>

        <div style="text-align: center; color: #666; font-size: 12px; border-top: 2px dashed #ccc; padding-top: 20px;">
            <p>Thank you for using FoodLink Myanmar!</p>
            <p>Delivery Address: <?= htmlspecialchars($order['delivery_address']) ?></p>
            <p>Phone: <?= htmlspecialchars($order['phone_number']) ?></p>
        </div>

        <div class="action-buttons">
            <a href="my_orders.php" class="btn-back">← Back</a>
            <button onclick="window.print()" class="btn-print">🖨️ Print Receipt</button>
        </div>

    </div>

</body>
</html>
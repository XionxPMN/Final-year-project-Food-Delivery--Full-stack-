<?php
session_start();
require '../includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

// 2. Get the vendor's restaurant ID
$stmt = $pdo->prepare("SELECT restaurant_id, name FROM restaurants WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$restaurant = $stmt->fetch();

$restaurant_id = $restaurant ? $restaurant['restaurant_id'] : null;
$past_orders = [];
$total_revenue = 0;

// 3. Fetch ONLY 'delivered' orders for this restaurant
if ($restaurant_id) {
    $orders_sql = "
        SELECT DISTINCT o.* FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN menu_items m ON oi.item_id = m.item_id
        WHERE m.restaurant_id = ? 
        AND TRIM(LOWER(o.status)) = 'delivered'
        ORDER BY o.created_at DESC
    ";
    $orders_stmt = $pdo->prepare($orders_sql);
    $orders_stmt->execute([$restaurant_id]);
    $past_orders = $orders_stmt->fetchAll();

    // Calculate total revenue from these past orders
    foreach ($past_orders as $order) {
        $rev_stmt = $pdo->prepare("
            SELECT SUM(oi.price * oi.quantity) as order_total
            FROM order_items oi
            JOIN menu_items m ON oi.item_id = m.item_id
            WHERE oi.order_id = ? AND m.restaurant_id = ?
        ");
        $rev_stmt->execute([$order['order_id'], $restaurant_id]);
        $total_revenue += $rev_stmt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Vendor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .revenue-card { background: linear-gradient(135deg, var(--primary-color) 0%, #4a0003 100%); color: var(--white); padding: 30px; border-radius: 12px; box-shadow: var(--shadow-hover); text-align: center; margin-bottom: 30px; }
        .revenue-card h3 { margin: 0; font-size: 16px; font-weight: 500; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px;}
        .revenue-card .amount { font-size: 40px; font-weight: 800; margin: 10px 0 0 0; }
        
        .history-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-card); }
        .history-table th, .history-table td { padding: 15px 20px; border-bottom: 1px solid var(--border-light); text-align: left; }
        .history-table th { background: #f9f9f9; color: var(--text-muted); font-size: 13px; text-transform: uppercase; }
        .history-table tr:last-child td { border-bottom: none; }
        .badge-delivered { background: #D4EDDA; color: #155724; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background-color: var(--white); padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; max-height: 90vh; overflow-y: auto;}
        .close-btn { position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #aaa; transition: 0.2s; }
        .close-btn:hover { color: #333; }
        .modal h3 { margin-top: 0; color: var(--primary-color); border-bottom: 2px dashed var(--border-light); padding-bottom: 10px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .detail-label { font-weight: 600; color: var(--text-muted); }
        .detail-value { font-weight: 500; color: var(--text-dark); text-align: right; }
        .modal-items-list { background: var(--bg-light); padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_menu.php" class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
            <li><a href="history.php" class="active translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Order History 📚" data-my="အော်ဒါမှတ်တမ်း 📚">Order History 📚</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if($restaurant_id): ?>
            <div class="revenue-card">
                <h3 class="translatable" data-en="Total All-Time Food Revenue" data-my="စုစုပေါင်း ရောင်းရငွေ">Total All-Time Food Revenue</h3>
                <div class="amount"><?= number_format($total_revenue, 0) ?> Ks</div>
            </div>

            <h3 style="color: var(--text-dark); margin-bottom: 15px;" class="translatable" data-en="Past Deliveries" data-my="ပို့ဆောင်ပြီးသော အော်ဒါများ">Past Deliveries</h3>
            
            <div style="overflow-x: auto;">
                <table class="history-table">
                    <tr>
                        <th>Date & Time</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php if(count($past_orders) > 0): ?>
                        <?php foreach($past_orders as $order): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-size: 14px;">
                                    <?= date('d M Y', strtotime($order['created_at'])) ?><br>
                                    <small><?= date('h:i A', strtotime($order['created_at'])) ?></small>
                                </td>
                                <td><strong style="color: var(--primary-color);">#<?= $order['order_id'] ?></strong></td>
                                <td>
                                    <span style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($order['phone_number']) ?></span><br>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($order['delivery_address']) ?></small>
                                </td>
                                <td>
                                    <?php
                                    // Fetch specific items for this row
                                 $items_stmt = $pdo->prepare("SELECT oi.quantity, m.name, oi.price FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id WHERE oi.order_id = ? AND m.restaurant_id = ?");
                                    $items_stmt->execute([$order['order_id'], $restaurant_id]);
                                    $items = $items_stmt->fetchAll();
                                    
                                    // Calculate this specific restaurant's subtotal for this order
                                    $order_subtotal = 0;

                                    foreach($items as $i) {
                                        echo "<div style='font-size: 13px; color: #444;'>{$i['quantity']}x " . htmlspecialchars($i['name']) . "</div>";
                                        $order_subtotal += ($i['price'] * $i['quantity']);
                                    }

                                    // Package data for JS Modal
                                    $modalData = [
                                        'id' => $order['order_id'],
                                        'date' => date('d M Y, h:i A', strtotime($order['created_at'])),
                                        'phone' => $order['phone_number'],
                                        'address' => $order['delivery_address'],
                                        'payment' => $order['payment_method'],
                                        'notes' => $order['notes'] ?: 'None',
                                        'total' => number_format($order_subtotal, 0) . ' Ks',
                                        'items' => $items
                                    ];
                                    $jsonData = htmlspecialchars(json_encode($modalData), ENT_QUOTES, 'UTF-8');
                                    ?>
                                </td>
                                <td><span class="badge-delivered">Delivered ✅</span></td>
                                <td>
                                    <button class="btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="openOrderModal(<?= $jsonData ?>)">View Detail</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #777;" class="translatable" data-en="No delivered orders found yet." data-my="ပို့ဆောင်ပြီးသော အော်ဒါများ မရှိသေးပါ။">No delivered orders found yet.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        <?php else: ?>
            <div class="alert error">Please complete your Restaurant Profile first.</div>
        <?php endif; ?>

    </div>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeOrderModal()">&times;</span>
            <h3 id="modalOrderId">Order Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value" id="modalDate"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer Phone:</span>
                <span class="detail-value" id="modalPhone"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Delivery Address:</span>
                <span class="detail-value" id="modalAddress"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value" id="modalPayment"></span>
            </div>
            
            <div class="modal-items-list" id="modalItems">
                </div>

            <div class="detail-row">
                <span class="detail-label">Customer Notes:</span>
                <span class="detail-value" id="modalNotes" style="color: #d32f2f; font-style: italic;"></span>
            </div>

            <div class="detail-row" style="border-top: 2px solid var(--border-light); padding-top: 15px; margin-top: 15px;">
                <span class="detail-label" style="font-size: 16px; color: var(--text-dark);">Total Revenue:</span>
                <span class="detail-value" id="modalTotal" style="font-size: 18px; color: var(--primary-color); font-weight: 800;"></span>
            </div>
        </div>
    </div>

    <script src="../assets/translate.js"></script>
    <script>
        // Modal Logic
        const modal = document.getElementById('orderModal');

        function openOrderModal(data) {
            document.getElementById('modalOrderId').innerText = 'Order #' + data.id + ' Details';
            document.getElementById('modalDate').innerText = data.date;
            document.getElementById('modalPhone').innerText = data.phone;
            document.getElementById('modalAddress').innerText = data.address;
            document.getElementById('modalPayment').innerText = data.payment;
            document.getElementById('modalNotes').innerText = data.notes;
            document.getElementById('modalTotal').innerText = data.total;

            // Generate Items List
            let itemsHtml = '<strong style="display:block; margin-bottom:10px;">Ordered Items:</strong>';
            data.items.forEach(item => {
                itemsHtml += `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 14px;">
                        <span><strong style="color: var(--primary-color);">${item.quantity}x</strong> ${item.name}</span>
                    </div>`;
            });
            document.getElementById('modalItems').innerHTML = itemsHtml;

            // Show Modal
            modal.style.display = 'flex';
        }

        function closeOrderModal() {
            modal.style.display = 'none';
        }

        // Close modal if user clicks outside the box
        window.onclick = function(event) {
            if (event.target == modal) {
                closeOrderModal();
            }
        }
    </script>
</body>
</html>
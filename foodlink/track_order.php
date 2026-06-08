<?php
session_start();
require 'includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or access denied.");
}

$status = $order['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?= $order_id ?> - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .tracking-container { max-width: 600px; margin: 50px auto; background: var(--white); padding: 40px; border-radius: 16px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); }
        .tracking-header { text-align: center; margin-bottom: 40px; border-bottom: 2px dashed var(--border-light); padding-bottom: 20px; }
        .tracking-header h2 { color: var(--primary-color); font-weight: 800; margin-bottom: 5px; }
        
        /* Progress Bar Styles */
        .status-list { list-style: none; padding: 0; margin: 0; position: relative; }
        .status-list::before { content: ''; position: absolute; left: 20px; top: 10px; bottom: 10px; width: 4px; background: var(--border-light); border-radius: 4px; z-index: 1; }
        
        .status-step { display: flex; align-items: center; margin-bottom: 30px; position: relative; z-index: 2; opacity: 0.4; transition: 0.3s; }
        .status-step:last-child { margin-bottom: 0; }
        .status-step.completed { opacity: 1; }
        
        .step-icon { width: 44px; height: 44px; background: var(--bg-light); border: 4px solid var(--border-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 20px; transition: 0.3s; background: var(--white); }
        .status-step.completed .step-icon { border-color: var(--primary-color); background: var(--primary-color); color: var(--white); }
        .status-step.active .step-icon { border-color: var(--accent-color); background: var(--accent-color); color: var(--white); box-shadow: 0 0 0 5px rgba(245, 166, 35, 0.2); }
        
        .step-text h4 { margin: 0; color: var(--text-dark); font-size: 18px; font-weight: 700; }
        .step-text p { margin: 0; color: var(--text-muted); font-size: 13px; }

        /* The Celebration Modal */
        .success-modal {
            display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.75); backdrop-filter: blur(8px);
            align-items: center; justify-content: center;
        }
        .success-modal-content {
            background: var(--white); padding: 40px; border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3); width: 90%; max-width: 400px;
            text-align: center; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .success-icon { font-size: 70px; margin-bottom: 10px; display: block; animation: bounce 1s infinite alternate; }
        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-10px); } }
        .success-modal h2 { color: #28a745; font-size: 28px; margin-bottom: 10px; font-weight: 800; }
        .success-modal p { color: var(--text-muted); margin-bottom: 30px; font-size: 16px; }
        .btn-receipt { background: var(--primary-color); color: var(--white); padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 700; display: inline-block; width: 100%; transition: 0.3s; }
        .btn-receipt:hover { background: var(--primary-hover); transform: translateY(-3px); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">FoodLink</a>
        <div class="nav-actions">
            <a href="my_orders.php" style="color: var(--text-dark); text-decoration: none; font-weight: 600;">← Back to Orders</a>
        </div>
    </nav>

    <div class="tracking-container">
        <div class="tracking-header">
            <h2>Order #<?= $order_id ?></h2>
            <p>Total: <strong><?= number_format($order['total_amount'], 0) ?> Ks</strong></p>
        </div>

        <ul class="status-list">
            <li class="status-step <?= in_array($status, ['pending', 'confirmed', 'preparing', 'delivering', 'delivered']) ? 'completed active' : '' ?>">
                <div class="step-icon">📋</div>
                <div class="step-text">
                    <h4>Order Placed</h4>
                    <p>Waiting for restaurant to confirm.</p>
                </div>
            </li>

            <li class="status-step <?= in_array($status, ['confirmed', 'preparing', 'delivering', 'delivered']) ? 'completed active' : '' ?>">
                <div class="step-icon">👨‍🍳</div>
                <div class="step-text">
                    <h4>Preparing Food</h4>
                    <p>The restaurant is making your order.</p>
                </div>
            </li>

            <li class="status-step <?= in_array($status, ['delivering', 'delivered']) ? 'completed active' : '' ?>">
                <div class="step-icon">🛵</div>
                <div class="step-text">
                    <h4>Out for Delivery</h4>
                    <p>The rider is on the way to you!</p>
                </div>
            </li>

            <li class="status-step <?= ($status === 'delivered') ? 'completed active' : '' ?>">
                <div class="step-icon">🎉</div>
                <div class="step-text">
                    <h4>Delivered</h4>
                    <p>Enjoy your meal!</p>
                </div>
            </li>
        </ul>
    </div>

    <div id="deliverySuccessModal" class="success-modal">
        <div class="success-modal-content">
            <span class="success-icon">🎉🍔</span>
            <h2 class="translatable" data-en="Order Delivered!" data-my="အော်ဒါ ရောက်ပါပြီ!">Order Delivered!</h2>
            <p class="translatable" data-en="Your food has arrived successfully. Enjoy your meal!" data-my="သင့်အစားအစာ ရောက်ရှိပါပြီ။ အရသာရှိရှိ သုံးဆောင်ပါ!">Your food has arrived successfully. Enjoy your meal!</p>
            
            <a href="receipt.php?id=<?= $order_id ?>" class="btn-receipt translatable" data-en="View Receipt" data-my="ဖြတ်ပိုင်း ကြည့်မည်">View Receipt</a>
        </div>
    </div>

    <script src="assets/translate.js"></script>
    <script>
        // 1. Store the exact state the page loaded with
        const orderId = <?= $order_id ?>;
        let currentStatus = "<?= htmlspecialchars($status) ?>";

        // 2. Only start tracking if it is NOT delivered yet
        if (currentStatus !== 'delivered') {
            
            // 3. Ask the server for an update every 3 seconds
            const tracker = setInterval(() => {
                
                fetch(`check_order_status.php?id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        
                        if (data.status) {
                            // If the status has changed from what we currently show...
                            if (data.status !== currentStatus) {
                                
                                // Scenario A: It just changed to 'Delivered'!
                                if (data.status === 'delivered') {
                                    clearInterval(tracker); // Stop checking
                                    document.getElementById('deliverySuccessModal').style.display = 'flex'; // Pop the modal!
                                } 
                                // Scenario B: It just changed to 'Preparing' or 'Delivering'
                                else {
                                    // Instantly refresh the page to update the visual Progress Bar steps!
                                    window.location.reload(); 
                                }
                            }
                        }
                        
                    })
                    .catch(error => console.error('Tracking Error:', error));
                    
            }, 3000); // 3000 ms = 3 seconds
            
        } else {
            // If the user opens this page and it was ALREADY delivered previously, 
            // you can choose to just show the receipt button, but we won't run the interval.
        }
    </script>
</body>
</html>
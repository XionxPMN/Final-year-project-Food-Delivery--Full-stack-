<?php
session_start();
require 'includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

// 2. Check if the cart is actually empty
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$error_message = '';

// ==========================================
// 3. SECURELY FETCH CART DATA FROM DATABASE
// ==========================================
$total_price = 0;
$delivery_fee = 0;
$restaurant_id = null;
$restaurant_city = '';
$restaurant_name = '';
$cart_details = [];

foreach ($cart_items as $cart_item) {
    // UPDATED: Now also fetching r.city as restaurant_city
    $stmt = $pdo->prepare("
        SELECT m.*, r.restaurant_id, r.name as restaurant_name, r.city as restaurant_city, r.delivery_fee 
        FROM menu_items m 
        JOIN restaurants r ON m.restaurant_id = r.restaurant_id 
        WHERE m.item_id = ?
    ");
    $stmt->execute([$cart_item['item_id']]);
    $item = $stmt->fetch();

    if ($item) {
        // Set the dynamic details from the restaurant table ONCE
        if ($restaurant_id === null) {
            $restaurant_id = $item['restaurant_id'];
            $delivery_fee = $item['delivery_fee']; 
            $restaurant_city = $item['restaurant_city'];
            $restaurant_name = $item['restaurant_name'];
        }

        // Calculate price taking into account any discounts
        $price = $item['price'];
        if (isset($item['discount_percent']) && $item['discount_percent'] > 0) {
            $price = $price - ($price * ($item['discount_percent'] / 100));
        }

        $subtotal = $price * $cart_item['quantity'];
        $total_price += $subtotal;

        // Save into a clean array for the HTML and the Order Insert
        $cart_details[] = [
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'quantity' => $cart_item['quantity'],
            'price' => $price,
            'subtotal' => $subtotal
        ];
    }
}

$grand_total = $total_price + $delivery_fee;

// ==========================================
// 4. PROCESS THE ORDER (Bulletproof Validation)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $user_id = $_SESSION['user_id'];
    $phone = trim($_POST['phone'] ?? '');
    $customer_city = trim($_POST['customer_city'] ?? ''); 
    $raw_address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'COD';

    // We will store all errors in an array. If this array stays empty, the order is safe to process!
    $validation_errors = [];

    // 1. STRICT PHONE VALIDATION (The Iron Wall)
    // Strip spaces, dashes, and plus signs just to check the raw characters
    $clean_phone = str_replace([' ', '-', '+'], '', $phone);
    
    // ctype_digit mathematically guarantees that ONLY numbers exist in the string
    if (empty($clean_phone) || !ctype_digit($clean_phone)) {
        $validation_errors[] = "📱 Invalid Phone Number! Letters and symbols are not allowed. Please use numbers only.";
    } elseif (strlen($clean_phone) < 7 || strlen($clean_phone) > 15) {
        $validation_errors[] = "📱 Phone number must be between 7 and 15 digits.";
    }

    // 2. THE CITY WALL
    if (strtolower($customer_city) !== strtolower(trim($restaurant_city))) {
        $validation_errors[] = " Delivery Failed! {$restaurant_name} is located in {$restaurant_city}. They cannot deliver to {$customer_city}.";
    } 

    // 3. CHECK IF WE HAVE ANY ERRORS
    if (!empty($validation_errors)) {
        // If there are errors, DO NOT process the order. Show the errors on screen.
        $error_message = implode("", $validation_errors);
    } else {
        // NO ERRORS! 100% SAFE TO PROCEED:
        $full_delivery_address = $customer_city . " - " . $raw_address;

        try {
            $pdo->beginTransaction();

            // A. Insert the main Order record (Using $full_delivery_address)
            $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, phone_number, notes, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $order_stmt->execute([$user_id, $grand_total, $full_delivery_address, $phone, $notes, $payment_method]);
            
            $order_id = $pdo->lastInsertId();

            // B. Insert order items using the securely fetched prices
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_details as $details) {
                $item_stmt->execute([$order_id, $details['item_id'], $details['quantity'], $details['price']]);
            }

            $pdo->commit();
            
            // Clear the cart
            unset($_SESSION['cart']);
            
            // INSTANTLY send them to the live tracking page!
            header("Location: track_order.php?id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Database Error: " . $e->getMessage(); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .checkout-grid { display: flex; gap: 30px; max-width: 1000px; margin: 40px auto; padding: 0 5%; align-items: flex-start; flex-wrap: wrap; }
        .checkout-form { flex: 2; min-width: 300px; background: var(--white); padding: 30px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); }
        .order-summary { flex: 1; min-width: 300px; background: var(--bg-light); padding: 30px; border-radius: 12px; border: 1px solid var(--border-light); position: sticky; top: 100px; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: var(--text-dark); }
        .summary-divider { border-bottom: 1px dashed var(--border-light); margin: 15px 0; }
        .summary-total { display: flex; justify-content: space-between; font-size: 20px; font-weight: 800; color: var(--primary-color); margin-top: 15px; }
        
        /* Payment Selection Cards */
        .payment-options { display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; }
        .payment-card { display: flex; align-items: center; gap: 15px; padding: 15px 20px; border: 2px solid var(--border-light); border-radius: 10px; cursor: pointer; transition: 0.3s; background: var(--white); }
        .payment-card:hover { border-color: var(--primary-color); background: var(--bg-light); }
        .payment-card input[type="radio"] { accent-color: var(--primary-color); transform: scale(1.3); cursor: pointer; }
        .payment-card span { font-weight: 600; font-size: 15px; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .payment-icon { width: 35px; height: 35px; object-fit: contain; border-radius: 6px; }
    </style>
</head>
<body>

    <nav class="navbar" style="justify-content: center;">
        <a href="index.php" class="navbar-brand">FoodLink <span style="color:var(--text-dark); font-weight:500; font-size:18px;" class="translatable" data-en="| Secure Checkout" data-my="| လုံခြုံသော ငွေပေးချေမှု">| Secure Checkout</span></a>
    </nav>

    <?php if($error_message): ?>
        <div class="alert error" style="max-width: 1000px; margin: 20px auto;"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
        
        <div class="checkout-form">
            <h2 style="margin-bottom: 25px; color: var(--text-dark);" class="translatable" data-en="Delivery Details" data-my="ပို့ဆောင်မည့် အချက်အလက်များ">Delivery Details</h2>
            
            <form method="POST" action="checkout.php">
                <div class="form-group">
                    <label class="translatable" data-en="Phone Number" data-my="ဖုန်းနံပါတ်">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. 09 123 456 789" required>
                </div>
                
                <div class="form-group">
                    <label class="translatable" data-en="Select Your City" data-my="သင့်မြို့ကို ရွေးချယ်ပါ">Select Your City</label>
                    <select name="customer_city" class="form-control" required style="margin-bottom: 5px; cursor: pointer;">
                        <option value="" disabled selected class="translatable" data-en="Where do you want this delivered?" data-my="မည်သည့်နေရာသို့ ပို့ဆောင်စေလိုသနည်း။">Where do you want this delivered?</option>
                        <option value="Yangon" <?= isset($_POST['customer_city']) && $_POST['customer_city'] == 'Yangon' ? 'selected' : '' ?>>Yangon</option>
                        <option value="Mandalay" <?= isset($_POST['customer_city']) && $_POST['customer_city'] == 'Mandalay' ? 'selected' : '' ?>>Mandalay</option>
                        <option value="Bago" <?= isset($_POST['customer_city']) && $_POST['customer_city'] == 'Bago' ? 'selected' : '' ?>>Bago</option>
                    </select>
                    <small style="color: var(--text-muted);">
                        <span class="translatable" data-en="Note: Ordering from" data-my="မှတ်ချက်။ ။">Note: Ordering from</span> 
                        <strong style="color: var(--primary-color);"><?= htmlspecialchars($restaurant_name) ?></strong> 
                        (<span class="translatable" data-en="Located in" data-my="တည်ရှိရာမြို့">Located in</span> <strong><?= htmlspecialchars($restaurant_city) ?></strong>).
                    </small>
                </div>

                <div class="form-group">
                    <label class="translatable" data-en="Detailed Delivery Address" data-my="ပို့ဆောင်ရမည့် လိပ်စာအပြည့်အစုံ">Detailed Delivery Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Street, Township, Floor/Apartment Number..." required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="translatable" data-en="Note for Rider/Restaurant (Optional)" data-my="မှတ်ချက် (ရွေးချယ်ရန်)">Note for Rider/Restaurant (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Please ring the doorbell, extra spicy..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>
                
                <h3 style="margin: 30px 0 15px; font-size: 16px; color: var(--text-dark);" class="translatable" data-en="Select Payment Method" data-my="ငွေပေးချေမည့်စနစ် ရွေးချယ်ပါ">Select Payment Method</h3>
                <div class="payment-options">
                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <span><span style="font-size: 24px;">💵</span> Cash on Delivery (COD)</span>
                    </label>
                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="KBZPay">
                        <span><img src="assets/kbz.jpg" alt="KBZPay" class="payment-icon" onerror="this.style.display='none'"> KBZPay</span>
                    </label>
                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="WavePay">
                        <span><img src="assets/wave.png" alt="WavePay" class="payment-icon" onerror="this.style.display='none'"> WavePay</span>
                    </label>
                </div>

                <input type="hidden" name="place_order" value="1">
                <button type="submit" class="btn-primary translatable" data-en="Place Order - <?= number_format($grand_total, 0) ?> Ks" data-my="အော်ဒါတင်မည် - <?= number_format($grand_total, 0) ?> Ks" style="font-size: 18px; padding: 15px; width: 100%;">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);" class="translatable" data-en="Order Summary" data-my="အော်ဒါ အနှစ်ချုပ်">Order Summary</h3>
            
            <?php foreach($cart_details as $item): ?>
                <div class="summary-item">
                    <span><strong style="color: var(--primary-color); margin-right: 5px;"><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['name']) ?></span>
                    <span style="font-weight: 600;"><?= number_format($item['subtotal'], 0) ?> Ks</span>
                </div>
            <?php endforeach; ?>
            
            <div class="summary-divider"></div>
            
            <div class="summary-item">
                <span class="translatable" data-en="Subtotal" data-my="စုစုပေါင်း (ကုန်ကျငွေ)">Subtotal</span>
                <span><?= number_format($total_price, 0) ?> Ks</span>
            </div>
            <div class="summary-item">
                <span class="translatable" data-en="Delivery Fee" data-my="ပို့ဆောင်ခ">Delivery Fee</span>
                <?php if($delivery_fee == 0): ?>
                    <span style="color: #2e7d32; font-weight: bold;">Free</span>
                <?php else: ?>
                    <span><?= number_format($delivery_fee, 0) ?> Ks</span>
                <?php endif; ?>
            </div>
            
            <div class="summary-divider"></div>
            
            <div class="summary-total">
                <span class="translatable" data-en="Total" data-my="စုစုပေါင်း">Total</span>
                <span><?= number_format($grand_total, 0) ?> Ks</span>
            </div>
        </div>

    </div>

    <script src="assets/translate.js"></script>
</body>
</html>
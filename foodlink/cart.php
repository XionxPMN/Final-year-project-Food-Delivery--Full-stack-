<?php
session_start();
require 'includes/db.php';

// 1. Strict Security Check: Must be logged in to view cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ==========================================
// HANDLE CART ACTIONS (+, -, clear)
// ==========================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $item_id = (int)$_GET['id'];

    foreach ($_SESSION['cart'] as $key => &$cart_item) {
        if ($cart_item['item_id'] == $item_id) {
            if ($action === 'add') {
                $cart_item['quantity'] += 1;
            } elseif ($action === 'minus') {
                $cart_item['quantity'] -= 1;
                // Remove item entirely if quantity drops to 0
                if ($cart_item['quantity'] <= 0) {
                    unset($_SESSION['cart'][$key]);
                }
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$key]);
            }
            break;
        }
    }
    // Re-index the array after removing an item so it stays clean
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

// Handle "Empty Cart" button
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">FoodLink</a>
        <div class="nav-actions">
            <button class="lang-btn" onclick="toggleLanguage()" id="langToggle">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                မြန်မာ
            </button>
            <a href="index.php" class="translatable" data-en="Back to Menu" data-my="မီနူးသို့ ပြန်သွားမည်" style="color: var(--text-dark); text-decoration: none; font-weight: 600;">Back to Menu</a>
        </div>
    </nav>

    <div class="cart-container">
        <h2 class="translatable" data-en="Your Cart 🛒" data-my="သင့်ခြင်းတောင်း 🛒" style="margin-bottom: 20px;">Your Cart 🛒</h2>

        <div class="cart-card">
            <?php if (empty($_SESSION['cart'])): ?>
                
                <div style="text-align: center; padding: 40px 0;">
                    <h3 style="color: var(--text-muted);" class="translatable" data-en="Your cart is empty." data-my="သင့်ခြင်းတောင်း လွတ်နေပါသည်။">Your cart is empty.</h3>
                    <a href="index.php" class="btn-primary" style="display: inline-block; width: auto; margin-top: 20px; padding: 12px 30px;">Browse Food</a>
                </div>

            <?php else: ?>
                
                <?php 
                $grand_total = 0;
                
                // Loop through the session IDs, and fetch the actual food data from the database
                foreach ($_SESSION['cart'] as $cart_item): 
                    $stmt = $pdo->prepare("
                        SELECT m.*, r.name as restaurant_name 
                        FROM menu_items m 
                        JOIN restaurants r ON m.restaurant_id = r.restaurant_id 
                        WHERE m.item_id = ?
                    ");
                    $stmt->execute([$cart_item['item_id']]);
                    $item = $stmt->fetch();
                    
                    if ($item):
                        // Calculate price considering any discounts
                        $price = $item['price'];
                        if (isset($item['discount_percent']) && $item['discount_percent'] > 0) {
                            $price = $price - ($price * ($item['discount_percent'] / 100));
                        }
                        
                        $subtotal = $price * $cart_item['quantity'];
                        $grand_total += $subtotal;
                ?>
                    
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <img src="<?= htmlspecialchars($item['image_url'] ?? 'assets/default_food.png') ?>" alt="Food" class="cart-img" onerror="this.src='assets/default_food.png'">
                            <div>
                                <strong style="font-size: 16px; color: var(--text-dark);"><?= htmlspecialchars($item['name']) ?></strong><br>
                                <span style="font-size: 13px; color: var(--text-muted);">🏪 <?= htmlspecialchars($item['restaurant_name']) ?></span><br>
                                <span style="font-size: 14px; font-weight: 700; color: var(--primary-color);"><?= number_format($price, 0) ?> Ks</span>
                            </div>
                        </div>
                        
                        <div style="text-align: right;">
                            <div class="qty-control">
                                <a href="cart.php?action=minus&id=<?= $item['item_id'] ?>" class="qty-btn">-</a>
                                <span style="font-weight: 700; width: 20px; text-align: center;"><?= $cart_item['quantity'] ?></span>
                                <a href="cart.php?action=add&id=<?= $item['item_id'] ?>" class="qty-btn">+</a>
                            </div>
                            <div style="font-weight: 800; color: var(--text-dark); margin-top: 5px;">
                                <?= number_format($subtotal, 0) ?> Ks
                            </div>
                        </div>
                    </div>

                <?php 
                    endif;
                endforeach; 
                ?>
                
                <div class="cart-total-box">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="cart.php?clear=1" class="empty-btn translatable" data-en="Empty Cart" data-my="ခြင်းတောင်း ရှင်းမည်" onclick="return confirm('Are you sure you want to empty your cart?')">Empty Cart</a>
                        
                        <div style="font-size: 20px; font-weight: 800;">
                            <span class="translatable" data-en="Total:" data-my="စုစုပေါင်း:">Total:</span> 
                            <span style="color: var(--primary-color);"><?= number_format($grand_total, 0) ?> Ks</span>
                        </div>
                    </div>
                    
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="checkout.php" class="checkout-btn translatable" data-en="Proceed to Checkout" data-my="ငွေရှင်းမည်">Proceed to Checkout</a>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="assets/translate.js"></script>
</body>
</html>
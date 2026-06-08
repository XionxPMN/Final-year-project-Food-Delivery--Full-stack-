<?php
session_start();
require 'includes/db.php';

// 1. Strict Security Check: Must be logged in to add to cart!
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $item_id = (int)$_GET['id'];

    // 2. Find out WHICH restaurant makes this specific item
    $stmt = $pdo->prepare("SELECT restaurant_id FROM menu_items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $new_item_rest_id = $stmt->fetchColumn();

    if ($new_item_rest_id) {
        
        // 3. Initialize cart if empty, and LOCK it to this restaurant
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
            $_SESSION['cart_restaurant_id'] = $new_item_rest_id; 
        } 
        
        // 4. If cart has items, check if the new item is from the SAME restaurant
        else {
            if (isset($_SESSION['cart_restaurant_id']) && $_SESSION['cart_restaurant_id'] != $new_item_rest_id) {
                // BLOCKED: Trying to mix restaurants!
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
                
                // Append the error flag to the URL so we can show a popup
                $redirect_url = strpos($referer, '?') !== false ? $referer . '&error=mixed_cart' : $referer . '?error=mixed_cart';
                header("Location: " . $redirect_url);
                exit();
            }
        }

        // 5. ADD TO CART LOGIC (Secure structure: ID and Quantity only)
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['item_id'] == $item_id) {
                $cart_item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'item_id' => $item_id,
                'quantity' => 1
            ];
        }
    }
}

// 6. Send the user back to the page they were just on safely
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

// (Clean up the URL so the error doesn't get stuck in a loop)
$referer = preg_replace('/([&?])error=mixed_cart/i', '', $referer);
$referer = rtrim($referer, '?&');

header("Location: " . $referer);
exit();
?>
<?php
session_start();
require 'includes/db.php';

// Ensure only logged-in customers can leave a rating
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$messageType = '';

// Check if the restaurant exists
$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    die("Restaurant not found.");
}

// Handle the Star Rating Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = (int)$_POST['rating'];

    if ($rating >= 1 && $rating <= 5) {
        // Optional: Check if they already rated this restaurant
        $check = $pdo->prepare("SELECT review_id FROM reviews WHERE restaurant_id = ? AND customer_id = ?");
        $check->execute([$restaurant_id, $customer_id]);
        
        if ($check->rowCount() > 0) {
            // Update their existing rating if they rate again
            $update = $pdo->prepare("UPDATE reviews SET rating = ? WHERE restaurant_id = ? AND customer_id = ?");
            if ($update->execute([$rating, $restaurant_id, $customer_id])) {
                $message = "Your rating has been updated!";
                $messageType = "success";
            }
        } else {
            // Insert a new rating
            // Notice: No comment field here!
            $insert = $pdo->prepare("INSERT INTO reviews (restaurant_id, customer_id, rating) VALUES (?, ?, ?)");
            if ($insert->execute([$restaurant_id, $customer_id, $rating])) {
                $message = "Thank you for rating!";
                $messageType = "success";
            } else {
                $message = "Error submitting rating.";
                $messageType = "error";
            }
        }
    } else {
        $message = "Please select a star.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate <?= htmlspecialchars($restaurant['name']) ?> - FoodLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: var(--bg-color);">
    <div class="auth-wrapper" style="margin: 50px auto;">
        <div class="auth-card" style="text-align: center;">
            <h2 style="color: var(--primary-color);">Rate your food</h2>
            <p style="color:#666; margin-bottom: 10px;">Tap a star to rate <strong><?= htmlspecialchars($restaurant['name']) ?></strong></p>

            <?php if($message): ?>
                <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="rating-container">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5" title="5 stars">★</label>
                    
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4" title="4 stars">★</label>
                    
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3" title="3 stars">★</label>
                    
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2" title="2 stars">★</label>
                    
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1" title="1 star">★</label>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 10px;">Submit Rating</button>
            </form>
            
            <div style="margin-top: 25px;">
                <a href="index.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
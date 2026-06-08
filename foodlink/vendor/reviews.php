<?php
session_start();
require '../includes/db.php';

// 1. Security Check: ONLY Vendors allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

// 2. Fetch the Vendor's Restaurant ID
$rest_stmt = $pdo->prepare("SELECT restaurant_id, name FROM restaurants WHERE vendor_id = ? LIMIT 1");
$rest_stmt->execute([$vendor_id]);
$restaurant = $rest_stmt->fetch();

$reviews = [];
$avg_rating = 0;
$total_reviews = 0;

if ($restaurant) {
    $restaurant_id = $restaurant['restaurant_id'];

    // 3. Fetch all reviews for this specific restaurant
    $rev_stmt = $pdo->prepare("
        SELECT r.rating, r.comment, r.created_at, u.name as customer_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.restaurant_id = ? 
        ORDER BY r.created_at DESC
    ");
    $rev_stmt->execute([$restaurant_id]);
    $reviews = $rev_stmt->fetchAll();

    // 4. Calculate stats
    $total_reviews = count($reviews);
    if ($total_reviews > 0) {
        $sum = 0;
        foreach ($reviews as $rev) {
            $sum += $rev['rating'];
        }
        $avg_rating = $sum / $total_reviews;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Vendor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); text-align: center; }
        .stat-card h3 { margin: 0; font-size: 14px; color: var(--text-muted); text-transform: uppercase; }
        .stat-card .value { font-size: 32px; font-weight: 800; color: var(--primary-color); margin-top: 10px; }
        
        .review-card { background: var(--white); padding: 20px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px; }
        .review-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-light); padding-bottom: 10px; }
        .review-stars { color: var(--accent-color); font-size: 18px; letter-spacing: 2px; }
        .review-comment { color: var(--text-dark); font-size: 15px; line-height: 1.6; font-style: italic; }
        .review-date { font-size: 12px; color: #aaa; text-align: right; }
    </style>
</head>
<body class="dashboard-body">

    <div class="sidebar">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="menu.php" class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
            <li><a href="history.php" class="translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="active translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Customer Reviews ⭐" data-my="ဖောက်သည်များ၏ သုံးသပ်ချက်များ ⭐">Customer Reviews ⭐</h2>
                <span style="color: #666; font-size: 14px;">
                    <?= $restaurant ? "Viewing feedback for <strong>" . htmlspecialchars($restaurant['name']) . "</strong>" : "No restaurant profile found." ?>
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if (!$restaurant): ?>
            <div class="alert error translatable" data-en="You need to set up your restaurant profile first before you can receive reviews." data-my="သုံးသပ်ချက်များ လက်ခံရယူရန် သင့်စားသောက်ဆိုင် ပရိုဖိုင်ကို ဦးစွာ သတ်မှတ်ရန် လိုအပ်ပါသည်။">
                You need to set up your restaurant profile first before you can receive reviews.
            </div>
        <?php else: ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="translatable" data-en="Average Rating" data-my="ပျမ်းမျှ အဆင့်သတ်မှတ်ချက်">Average Rating</h3>
                    <div class="value">
                        <?= number_format($avg_rating, 1) ?> <span style="color: var(--accent-color); font-size: 24px;">★</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3 class="translatable" data-en="Total Reviews" data-my="စုစုပေါင်း သုံးသပ်ချက်">Total Reviews</h3>
                    <div class="value" style="color: var(--text-dark);">
                        <?= number_format($total_reviews) ?>
                    </div>
                </div>
            </div>

            <h3 style="color: var(--text-dark); margin-bottom: 20px;" class="translatable" data-en="All Feedback" data-my="သုံးသပ်ချက် အားလုံး">All Feedback</h3>

            <div>
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong style="color: var(--text-dark); font-size: 16px;">
                                    👤 <?= htmlspecialchars($rev['customer_name']) ?>
                                </strong>
                                <div class="review-stars">
                                    <?= str_repeat('★', $rev['rating']) ?><span style="color: #ddd;"><?= str_repeat('★', 5 - $rev['rating']) ?></span>
                                </div>
                            </div>
                            
                            <div class="review-comment">
                                "<?= nl2br(htmlspecialchars($rev['comment'])) ?>"
                            </div>
                            
                            <div class="review-date">
                                📅 <?= date('F j, Y, g:i a', strtotime($rev['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="background: var(--white); padding: 40px; text-align: center; border-radius: 12px; border: 1px dashed var(--border-light);">
                        <p style="color: var(--text-muted); margin: 0;" class="translatable" data-en="No reviews yet! Keep serving great food to get your first star." data-my="သုံးသပ်ချက်များ မရှိသေးပါ! အကောင်းဆုံး အစားအစာများကို ဆက်လက်ရောင်းချပေးပါ။">
                            No reviews yet! Keep serving great food to get your first star.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
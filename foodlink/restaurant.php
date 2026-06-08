<?php
session_start();
require 'includes/db.php';

// 1. Check if an ID was provided in the URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$rest_id = (int)$_GET['id'];

// ==========================================
// NEW: HANDLE REVIEW SUBMISSION
// ==========================================
$review_msg = '';
$review_msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);

        // Check if the user already reviewed this restaurant
        $check = $pdo->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND restaurant_id = ?");
        $check->execute([$user_id, $rest_id]);
        
        if ($check->rowCount() > 0) {
            $review_msg = "You have already reviewed this restaurant!";
            $review_msg_type = "error";
        } else {
            // Insert new review
            $insert = $pdo->prepare("INSERT INTO reviews (restaurant_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$rest_id, $user_id, $rating, $comment])) {
                header("Location: restaurant.php?id=$rest_id&msg=reviewed");
                exit();
            }
        }
    } else {
        $review_msg = "You must be logged in to leave a review.";
        $review_msg_type = "error";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'reviewed') {
    $review_msg = "Thank you for your feedback!";
    $review_msg_type = "success";
}

// 2. Fetch the Specific Restaurant's Details
$stmt = $pdo->prepare("
    SELECT r.*, 
           IFNULL(AVG(rev.rating), 0) as avg_rating, 
           COUNT(rev.review_id) as total_reviews 
    FROM restaurants r
    LEFT JOIN reviews rev ON r.restaurant_id = rev.restaurant_id
    WHERE r.restaurant_id = ? AND r.status = 'approved' AND r.is_open = 1
    GROUP BY r.restaurant_id
");
$stmt->execute([$rest_id]);
$restaurant = $stmt->fetch();

// If someone tries to go to a restaurant that doesn't exist or is closed
if (!$restaurant) {
    header("Location: index.php");
    exit();
}

// 3. Fetch ONLY the Menu Items for this specific restaurant
$menu_stmt = $pdo->prepare("
    SELECT m.*, c.category_name 
    FROM menu_items m
    LEFT JOIN menu_categories c ON m.category_id = c.category_id
    WHERE m.restaurant_id = ? AND m.is_available = 1
    ORDER BY c.category_name ASC, m.name ASC
");
$menu_stmt->execute([$rest_id]);
$menu_items = $menu_stmt->fetchAll();

// 4. Extract Unique Categories for our Minimalist Filter
$unique_categories = [];
foreach ($menu_items as $item) {
    if (!empty($item['category_name'])) {
        $unique_categories[$item['category_id']] = $item['category_name'];
    }
}

// 5. Select the specific item the vendor marked as "Today's Special"
$special_stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? AND is_special = 1 AND is_available = 1 LIMIT 1");
$special_stmt->execute([$rest_id]);
$special_item = $special_stmt->fetch();

// ==========================================
// NEW: FETCH EXISTING REVIEWS 
// ==========================================
$rev_stmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name as customer_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.restaurant_id = ? 
    ORDER BY r.created_at DESC
");
$rev_stmt->execute([$rest_id]);
$reviews = $rev_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant['name']) ?> - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Interactive Star Rating CSS */
        .rating-container { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; margin-bottom: 15px; }
        .rating-container input { display: none; }
        .rating-container label { font-size: 35px; color: #ccc; cursor: pointer; transition: 0.2s; }
        .rating-container input:checked ~ label, 
        .rating-container label:hover, 
        .rating-container label:hover ~ label { color: var(--accent-color); }
        .review-card { background: var(--white); padding: 20px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light); margin-bottom: 15px; }
    </style>
</head>
<body> 

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">FoodLink</a>
        
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="nav-actions" id="navActions">
            <button class="lang-btn" onclick="toggleLanguage()" id="langToggle">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                မြန်မာ
            </button>

            <a href="index.php" class="nav-link translatable" data-en="Home" data-my="ပင်မစာမျက်နှာ">Home</a>
            <a href="restaurants.php" class="nav-link translatable" data-en="Restaurants" data-my="စားသောက်ဆိုင်များ">Restaurants</a>

            <?php $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>
            <a href="cart.php" class="cart-btn">
                🛒 <span class="translatable" data-en="Cart" data-my="ခြင်းတောင်း">Cart</span> (<?= $cart_count ?>)
            </a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-greeting">
                    <span class="translatable" data-en="Hi," data-my="မင်္ဂလာပါ,">Hi,</span> <?= htmlspecialchars($_SESSION['name']) ?>!
                </span>
                <a href="my_orders.php" class="nav-link translatable" data-en="My Orders" data-my="ကျွန်ုပ်၏ အော်ဒါများ">📦 My Orders</a>
                <a href="logout.php" class="logout-btn translatable" data-en="Logout" data-my="ထွက်မည်">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn translatable" data-en="Log In / Register" data-my="အကောင့်ဝင်မည် / စာရင်းသွင်းမည်">Log In / Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="rest-hero" style="background-image: url('<?= htmlspecialchars($restaurant['image_url'] ?? 'assets/default_restaurant.png') ?>');">
        <div class="rest-hero-content">
            <h1 class="rest-hero-title"><?= htmlspecialchars($restaurant['name']) ?></h1>
            <div class="rest-hero-meta">
                <span class="rating-badge">★ <?= number_format($restaurant['avg_rating'], 1) ?> (<?= $restaurant['total_reviews'] ?> Reviews)</span>
                <span>📍 <?= htmlspecialchars($restaurant['city']) ?></span>
                <?php if(!empty($restaurant['phone'])): ?>
                    <span>📞 <?= htmlspecialchars($restaurant['phone']) ?></span>
                <?php endif; ?>
            </div>
            <?php if(!empty($restaurant['description'])): ?>
                <p style="margin-top: 15px; max-width: 600px; color: #bbb; line-height: 1.5;">
                    <?= htmlspecialchars($restaurant['description']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="menu-container">

        <?php if($special_item): ?>
            <h2 class="section-title translatable" data-en="Today's Special ⭐" data-my="ယနေ့ အထူးအစီအစဉ် ⭐">Today's Special ⭐</h2>
            <div class="special-card">
                <div style="position: relative;">
                    <?php if(isset($special_item['discount_percent']) && $special_item['discount_percent'] > 0): ?>
                        <span class="discount-badge" style="left: 15px; top: 15px;"><?= $special_item['discount_percent'] ?>% OFF</span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($special_item['image_url'] ?? 'assets/default_food.png') ?>" class="special-img" alt="Special Food" onclick="openImageModal('<?= htmlspecialchars($special_item['image_url'] ?? 'assets/default_food.png') ?>', event)" style="cursor: pointer;">
                </div>
                
                <div class="special-info">
                    <span class="special-badge translatable" data-en="Chef's Pick" data-my="စားဖိုမှူး၏ ရွေးချယ်မှု">Chef's Pick</span>
                    <h3 class="special-title"><?= htmlspecialchars($special_item['name']) ?></h3>
                    <?php if(!empty($special_item['description'])): ?>
                        <p class="special-desc"><?= htmlspecialchars($special_item['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="special-footer">
                        <div>
                            <?php if(isset($special_item['discount_percent']) && $special_item['discount_percent'] > 0): ?>
                                <?php 
                                    $s_discount = $special_item['price'] * ($special_item['discount_percent'] / 100);
                                    $s_final = $special_item['price'] - $s_discount;
                                ?>
                                <div style="font-size: 14px; color: #999; text-decoration: line-through; margin-bottom: 2px;">
                                    <?= number_format($special_item['price'], 0) ?> Ks
                                </div>
                                <span class="special-price"><?= number_format($s_final, 0) ?> Ks</span>
                            <?php else: ?>
                                <span class="special-price"><?= number_format($special_item['price'], 0) ?> Ks</span>
                            <?php endif; ?>
                        </div>
                        <a href="add_to_cart.php?id=<?= $special_item['item_id'] ?>" class="btn-primary" style="padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: bold; width: auto; margin-top: 0;">Add to Cart</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="section-title translatable" data-en="Full Menu" data-my="မီနူး အပြည့်အစုံ" style="margin-top: 50px;">Full Menu</h2>
        
        <?php if(count($unique_categories) > 0): ?>
            <div class="minimal-filter">
                <a href="#" class="filter-link active translatable" data-target="all" data-en="All Items" data-my="အားလုံး">All Items</a>
                <?php foreach($unique_categories as $id => $name): ?>
                    <a href="#" class="filter-link" data-target="<?= $id ?>"><?= htmlspecialchars($name) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="food-grid" id="foodContainer" style="padding: 10px 0 60px;">
            <?php if(count($menu_items) > 0): ?>
                <?php foreach($menu_items as $item): ?>
                    <div class="food-card" data-category="<?= $item['category_id'] ?>">
                        
                        <div style="position: relative;">
                            <?php if(isset($item['discount_percent']) && $item['discount_percent'] > 0): ?>
                                <span class="discount-badge"><?= $item['discount_percent'] ?>% OFF</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($item['image_url'] ?? 'assets/default_food.png') ?>" alt="Food" class="food-card-img" onclick="openImageModal('<?= htmlspecialchars($item['image_url'] ?? 'assets/default_food.png') ?>', event)">
                        </div>

                        <div class="food-card-content">
                            <h3 class="food-title"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="food-vendor" style="color: var(--primary-color);">🏷️ <?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></p>
                            
                            <?php if(!empty($item['description'])): ?>
                                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px; line-height: 1.4;">
                                    <?= htmlspecialchars($item['description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="food-footer" style="display: flex; justify-content: space-between; align-items: flex-end;">
                                <div>
                                    <?php if(isset($item['discount_percent']) && $item['discount_percent'] > 0): ?>
                                        <?php 
                                            $discount_amount = $item['price'] * ($item['discount_percent'] / 100);
                                            $final_price = $item['price'] - $discount_amount;
                                        ?>
                                        <div style="font-size: 13px; color: #999; text-decoration: line-through; margin-bottom: 2px;">
                                            <?= number_format($item['price'], 0) ?> Ks
                                        </div>
                                        <span class="food-price"><?= number_format($final_price, 0) ?> Ks</span>
                                    <?php else: ?>
                                        <span class="food-price"><?= number_format($item['price'], 0) ?> Ks</span>
                                    <?php endif; ?>
                                </div>
                                <object><a href="add_to_cart.php?id=<?= $item['item_id'] ?>" class="add-btn">+</a></object>
                            </div>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: var(--white); border-radius: 12px; border: 1px dashed var(--border-light);">
                    <h3 class="translatable" data-en="This restaurant has not added any menu items yet." data-my="ဤစားသောက်ဆိုင်တွင် မီနူးများ မထည့်ရသေးပါ။" style="color: var(--text-muted);">This restaurant has not added any menu items yet.</h3>
                </div>
            <?php endif; ?>
        </div>

    </div> 

    <div class="content-partition-gray" style="padding: 50px 5%; margin: 40px 0 0 0;">
        <div style="max-width: 1000px; margin: 0 auto;">
            
            <h2 class="section-title translatable" style="margin: 0 0 30px 0; padding: 0;" data-en="Customer Reviews ⭐" data-my="ဖောက်သည်များ၏ သုံးသပ်ချက်များ ⭐">Customer Reviews ⭐</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
                
                <div>
                    <div style="background: var(--white); padding: 30px; border-radius: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--border-light);">
                        <h3 style="margin-top: 0; font-size: 18px; border-bottom: 2px solid var(--border-light); padding-bottom: 10px; margin-bottom: 20px;">Leave a Rating</h3>
                        
                        <?php if($review_msg): ?>
                            <div class="alert <?= $review_msg_type ?>"><?= htmlspecialchars($review_msg) ?></div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST">
                                <div class="rating-container">
                                    <input type="radio" name="rating" id="star5" value="5" required><label for="star5">★</label>
                                    <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                                    <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                                    <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                                    <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                                </div>
                                
                                <textarea name="comment" class="form-control" rows="4" placeholder="Tell us about your food..." required style="margin-bottom: 15px; resize: vertical;"></textarea>
                                <button type="submit" name="submit_review" class="btn-primary" style="margin: 0;">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px;">
                                <p style="color: #777; margin-bottom: 15px;">You must be logged in to leave a review.</p>
                                <a href="login.php" class="btn-primary" style="width: auto; padding: 10px 20px; display: inline-block;">Log In</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 15px; max-height: 500px; overflow-y: auto; padding-right: 10px;">
                    <?php if(count($reviews) > 0): ?>
                        <?php foreach($reviews as $rev): ?>
                            <div class="review-card">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong style="color: var(--text-dark);"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                                    
                                    <span style="color: var(--accent-color); font-size: 16px;">
                                        <?= str_repeat('★', $rev['rating']) ?><span style="color: #ddd;"><?= str_repeat('★', 5 - $rev['rating']) ?></span>
                                    </span>
                                </div>
                                <p style="color: var(--text-muted); font-size: 14px; margin: 0; line-height: 1.5;">
                                    "<?= htmlspecialchars($rev['comment']) ?>"
                                </p>
                                <small style="color: #aaa; display: block; margin-top: 10px;">
                                    <?= date('F j, Y', strtotime($rev['created_at'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 40px; text-align: center; background: var(--white); border-radius: 12px; border: 1px dashed var(--border-light);">
                            <h4 style="color: #777;">No reviews yet.</h4>
                            <p style="font-size: 14px; color: #999;">Be the first to review this restaurant!</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <div id="foodImageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-modal">&times;</span>
        <img class="image-modal-content" id="expandedImg">
    </div>

    <footer class="main-footer" style="margin-top: 0;">
        <div class="footer-bottom" style="border-top: none;">
            &copy; <?= date("Y") ?> FoodLink Myanmar. All rights reserved.
        </div>
    </footer>

    <script src="assets/translate.js"></script>
    <script>
        function toggleMobileMenu() {
            document.getElementById('navActions').classList.toggle('show');
        }

        // Image Modal Logic
        function openImageModal(imgSrc, event) {
            event.preventDefault(); 
            event.stopPropagation();
            const modal = document.getElementById("foodImageModal");
            const modalImg = document.getElementById("expandedImg");
            modal.style.display = "flex";
            modalImg.src = imgSrc;
            document.body.style.overflow = 'hidden'; 
        }

        function closeImageModal() {
            document.getElementById("foodImageModal").style.display = "none";
            document.body.style.overflow = 'auto'; 
        }

        // Minimalist Filter Logic
        document.addEventListener('DOMContentLoaded', () => {
            const filterLinks = document.querySelectorAll('.filter-link');
            const foodCards = document.querySelectorAll('.food-card');

            filterLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    filterLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    
                    const targetCat = link.getAttribute('data-target');
                    
                    foodCards.forEach(card => {
                        const cardCat = card.getAttribute('data-category');
                        if (targetCat === 'all' || targetCat === cardCat) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
        // Check for Mixed Cart Error in URL
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error') && urlParams.get('error') === 'mixed_cart') {
        alert("Oops! 🍔🍟\nYou can only order from one restaurant at a time.\n\nPlease empty your current cart if you want to order from a different shop!");
        
        // Clean the URL so it doesn't pop up again if they refresh
        window.history.replaceState(null, null, window.location.pathname);
    }
});
    </script>
</body>
</html>
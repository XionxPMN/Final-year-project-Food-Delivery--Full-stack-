<?php
session_start();
require 'includes/db.php';

// Capture selected city from Navbar dropdown
if (isset($_GET['city'])) {
    $_SESSION['user_city'] = $_GET['city'];
}
$selected_city = isset($_SESSION['user_city']) ? $_SESSION['user_city'] : 'All';

// ==========================================
// Fetch All Approved & Open Restaurants + Ratings
// ==========================================
$sql = "
    SELECT r.*, 
           IFNULL(AVG(rev.rating), 0) as avg_rating, 
           COUNT(rev.review_id) as total_reviews 
    FROM restaurants r
    LEFT JOIN reviews rev ON r.restaurant_id = rev.restaurant_id
    WHERE r.status = 'approved' AND r.is_open = 1
";

$params = [];
if ($selected_city !== 'All') {
    $sql .= " AND r.city = ?";
    $params[] = $selected_city;
}

$sql .= " GROUP BY r.restaurant_id ORDER BY r.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Restaurants - FoodLink Myanmar</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* =========================================
           RESTAURANT PAGE SPECIFIC STYLES
           ========================================= */
        .page-header {
            background: var(--bg-light);
            padding: 40px 5%;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
        }
        .page-header h1 {
            font-size: 32px;
            color: var(--text-dark);
            margin-bottom: 20px;
            font-weight: 800;
        }
        
        .rest-search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        .rest-search-input {
            width: 100%;
            padding: 15px 25px;
            border-radius: 50px;
            border: 2px solid var(--border-light);
            font-size: 16px;
            outline: none;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .rest-search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(94, 0, 6, 0.1);
        }

        /* The Grid for Restaurants */
        .rest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding: 40px 5% 80px;
        }
        .rest-grid-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-light);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }
        .rest-grid-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-color);
        }
        .rest-grid-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-light);
        }
        .rest-grid-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .rest-grid-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .rest-grid-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px dashed var(--border-light);
        }
        .rating-pill {
            background: var(--accent-color);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .location-tag {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }
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
            
            <form method="GET" action="restaurants.php" id="locationForm" style="margin: 0;">
                <select name="city" class="location-select" onchange="document.getElementById('locationForm').submit()" style="width: 100%;">
                    <option value="All" <?= $selected_city == 'All' ? 'selected' : '' ?>>📍 All Locations</option>
                    <option value="Yangon" <?= $selected_city == 'Yangon' ? 'selected' : '' ?>>📍 Yangon (ရန်ကုန်)</option>
                    <option value="Mandalay" <?= $selected_city == 'Mandalay' ? 'selected' : '' ?>>📍 Mandalay (မန္တလေး)</option>
                    <option value="Naypyidaw" <?= $selected_city == 'Naypyidaw' ? 'selected' : '' ?>>📍 Naypyidaw (နေပြည်တော်)</option>
                </select>
            </form>

            <button class="lang-btn" onclick="toggleLanguage()" id="langToggle">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                မြန်မာ
            </button>

            <a href="index.php" class="translatable" data-en="Home" data-my="ပင်မစာမျက်နှာ" style="color: var(--text-dark); text-decoration: none; font-weight: 600; margin-right: 10px;">Home</a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="my_orders.php" class="translatable" data-en="My Orders" data-my="ကျွန်ုပ်၏ အော်ဒါများ" style="color: var(--text-dark); text-decoration: none; font-weight: 600; margin-right: 20px; margin-left: 15px;">📦 My Orders</a>
                <?php $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>
            <a href="cart.php" class="cart-btn">
                🛒 <span class="translatable" data-en="Cart" data-my="ခြင်းတောင်း">Cart</span> (<?= $cart_count ?>)
            </a>

                <a href="logout.php" class="translatable" data-en="Logout" data-my="ထွက်မည်" style="color: #dc3545; text-decoration: none; font-weight: 600; background: #ffebee; padding: 6px 15px; border-radius: 50px;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="translatable" data-en="Log In / Register" data-my="အကောင့်ဝင်မည် / စာရင်းသွင်းမည်" style="color: #333; text-decoration: none; font-weight: 600; padding: 8px 15px; border: 2px solid #eaeaea; border-radius: 50px; transition: 0.3s; margin-left: 15px;">Log In / Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="translatable" data-en="<?= $selected_city === 'All' ? 'All Restaurants' : 'Restaurants in ' . htmlspecialchars($selected_city) ?>" data-my="<?= $selected_city === 'All' ? 'စားသောက်ဆိုင် အားလုံး' : htmlspecialchars($selected_city) . ' ရှိ စားသောက်ဆိုင်များ' ?>">
            <?= $selected_city === 'All' ? 'All Restaurants' : 'Restaurants in ' . htmlspecialchars($selected_city) ?>
        </h1>
        
        <div class="rest-search-container">
            <input type="text" id="restSearchInput" class="rest-search-input translatable-placeholder" placeholder="Search for a restaurant name..." data-en-placeholder="Search for a restaurant name..." data-my-placeholder="စားသောက်ဆိုင်အမည်ဖြင့် ရှာဖွေပါ...">
        </div>
    </div>

    <div class="rest-grid" id="restContainer">
        <?php if(count($restaurants) > 0): ?>
            <?php foreach($restaurants as $rest): ?>
                <a href="restaurant.php?id=<?= $rest['restaurant_id'] ?>" class="rest-grid-card" data-name="<?= strtolower(htmlspecialchars($rest['name'])) ?>">
                    
                    <img src="<?= htmlspecialchars($rest['image_url'] ?? 'assets/default_restaurant.png') ?>" alt="Restaurant" class="rest-grid-img">
                    
                    <div class="rest-grid-info">
                        <h3 class="rest-grid-title"><?= htmlspecialchars($rest['name']) ?></h3>
                        
                        <?php if(!empty($rest['description'])): ?>
                            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px; line-height: 1.4;">
                                <?= htmlspecialchars(substr($rest['description'], 0, 60)) ?>...
                            </p>
                        <?php else: ?>
                            <div style="flex-grow: 1;"></div>
                        <?php endif; ?>

                        <div class="rest-grid-meta">
                            <span class="location-tag">📍 <?= htmlspecialchars($rest['city']) ?></span>
                            
                            <?php if($rest['total_reviews'] > 0): ?>
                                <span class="rating-pill">★ <?= number_format($rest['avg_rating'], 1) ?></span>
                            <?php else: ?>
                                <span class="rating-pill" style="background: #ccc; color: #666;">New</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: var(--white); border-radius: 12px; border: 1px dashed var(--border-light);">
                <h3 class="translatable" data-en="No restaurants found in this location." data-my="ယခုနေရာတွင် စားသောက်ဆိုင်များ မတွေ့ရှိပါ။" style="color: var(--text-muted);">No restaurants found in this location.</h3>
            </div>
        <?php endif; ?>
    </div>

    <footer class="main-footer">
        <div class="footer-grid">
            <div class="footer-col" style="flex: 2;">
                <span class="footer-brand">FoodLink Myanmar</span>
                <p class="translatable" data-en="Delivering the best local flavors directly to your door." data-my="အကောင်းဆုံး ဒေသအစားအစာများကို သင့်အိမ်တိုင်ရာရောက် ပို့ဆောင်ပေးနေပါသည်။" style="color: #aaa; font-size: 14px; line-height: 1.6; max-width: 300px;">
                    Delivering the best local flavors directly to your door.
                </p>
            </div>
            <div class="footer-col">
                <h4 class="translatable" data-en="About Us" data-my="ကျွန်ုပ်တို့အကြောင်း">About Us</h4>
                <ul>
                    <li><a href="#" class="translatable" data-en="Our Story" data-my="ကျွန်ုပ်တို့၏ သမိုင်းကြောင်း">Our Story</a></li>
                    <li><a href="terms.php" class="translatable" data-en="Terms & Conditions" data-my="စည်းမျဉ်းနှင့် စည်းကမ်းများ">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="translatable" data-en="Contact" data-my="ဆက်သွယ်ရန်">Contact</h4>
                <ul>
                    <li><a href="#">support@foodlink.com.mm</a></li>
                    <li><a href="#">+95 9 123 456 789</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date("Y") ?> FoodLink Myanmar. All rights reserved.
        </div>
    </footer>

    <script src="assets/translate.js"></script>
    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('navActions').classList.toggle('show');
        }

        // Live Restaurant Search Filter
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('restSearchInput');
            const restCards = document.querySelectorAll('.rest-grid-card');

            if(searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    restCards.forEach(card => {
                        const restName = card.getAttribute('data-name');
                        if(restName.includes(searchTerm)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
            
            // Translate placeholders if language is toggled
            const langToggleBtn = document.getElementById('langToggle');
            if(langToggleBtn && searchInput) {
                langToggleBtn.addEventListener('click', () => {
                    setTimeout(() => {
                        const currentLang = localStorage.getItem('foodlink_lang') || 'en';
                        if(currentLang === 'my') {
                            searchInput.placeholder = searchInput.getAttribute('data-my-placeholder');
                        } else {
                            searchInput.placeholder = searchInput.getAttribute('data-en-placeholder');
                        }
                    }, 50); // slight delay to allow toggle to happen
                });
                
                // Initial load check
                const initialLang = localStorage.getItem('foodlink_lang') || 'en';
                if(initialLang === 'my') {
                    searchInput.placeholder = searchInput.getAttribute('data-my-placeholder');
                }
            }
        });
    </script>
</body>
</html>
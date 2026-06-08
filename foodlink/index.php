<?php
session_start();
require 'includes/db.php';

// Capture selected city
if (isset($_GET['city'])) {
    $_SESSION['user_city'] = $_GET['city'];
}
$selected_city = isset($_SESSION['user_city']) ? $_SESSION['user_city'] : 'Yangon';

// Capture selected category and search
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch Categories
$cat_stmt = $pdo->query("SELECT * FROM menu_categories ORDER BY category_name ASC");
$categories = $cat_stmt->fetchAll();

// Fetch Active Promotional Banners
$banner_stmt = $pdo->query("SELECT * FROM promotional_banners WHERE is_active = 1 ORDER BY banner_id DESC LIMIT 5");
$banners = $banner_stmt->fetchAll();

// ==========================================
// 1. FETCH RESTAURANTS
// ==========================================
$rest_sql = "SELECT * FROM restaurants WHERE status = 'approved' AND is_open = 1";
$rest_params = [];

if ($selected_city !== 'All') {
    $rest_sql .= " AND city = ?";
    $rest_params[] = $selected_city;
}
//  Limited to 5 restaurants for a clean homepage preview
$rest_sql .= " ORDER BY name ASC LIMIT 5";

$rest_stmt = $pdo->prepare($rest_sql);
$rest_stmt->execute($rest_params);
$restaurants = $rest_stmt->fetchAll();

// ==========================================
// 2. FETCH MENU ITEMS (Server-Side Filtering)
// ==========================================
$item_sql = "
    SELECT m.*, r.name as restaurant_name 
    FROM menu_items m 
    JOIN menu_categories c ON m.category_id = c.category_id 
    JOIN restaurants r ON m.restaurant_id = r.restaurant_id 
    WHERE r.status = 'approved' 
    AND r.is_open = 1 
    AND m.is_available = 1
";
$item_params = [];

// Filter by City
if ($selected_city !== 'All') {
    $item_sql .= " AND r.city = ?";
    $item_params[] = $selected_city;
}

// Filter by Category
if ($selected_category !== 'all') {
    $item_sql .= " AND m.category_id = ?";
    $item_params[] = $selected_category;
}

// Filter by Search Bar
if (!empty($search_keyword)) {
    $item_sql .= " AND (m.name LIKE ? OR r.name LIKE ?)";
    $item_params[] = "%$search_keyword%";
    $item_params[] = "%$search_keyword%";
}

// If they are actively searching or filtering, show 24 results (Newest first)
// If they are just browsing the homepage, show 10 RANDOM results!
if ($selected_category !== 'all' || !empty($search_keyword)) {
    $item_sql .= " ORDER BY m.item_id DESC LIMIT 24";
} else {
    $item_sql .= " ORDER BY RAND() LIMIT 10";
}

$item_stmt = $pdo->prepare($item_sql);
$item_stmt->execute($item_params);
$items = $item_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodLink Myanmar</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Smooth scrolling for anchor tags */
        html { scroll-behavior: smooth; }
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
            
            <form method="GET" action="" id="locationForm" style="margin: 0;">
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

            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="color: var(--primary-color); font-weight: 700; font-size: 15px; margin-right: 15px; margin-left: 15px;">
                    <span class="translatable" data-en="Hi," data-my="မင်္ဂလာပါ,">Hi,</span> <?= htmlspecialchars($_SESSION['name']) ?>!
                </span>
                <a href="my_orders.php" class="translatable" data-en="My Orders" data-my="ကျွန်ုပ်၏ အော်ဒါများ" style="color: var(--text-dark); text-decoration: none; font-weight: 600; margin-right: 20px;">📦 My Orders</a>
                <a href="restaurants.php" class="translatable" data-en="Restaurants" data-my="စားသောက်ဆိုင်များ" style="color: var(--text-dark); text-decoration: none; font-weight: 600; margin-right: 10px;">Restaurants</a>

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

    <?php if(count($banners) > 0 && $selected_category === 'all' && empty($search_keyword)): ?>
    <div class="promo-slider-container">
        <button class="slider-arrow arrow-left" onclick="moveSlide(-1)">❮</button>
        <button class="slider-arrow arrow-right" onclick="moveSlide(1)">❯</button>

        <div class="promo-track" id="promoTrack">
            <?php foreach($banners as $banner): ?>
                <a href="restaurant.php?id=<?= $banner['restaurant_id'] ?>" class="promo-slide">
                    <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="Promotion">
                </a>
            <?php endforeach; ?>
        </div>

        <div class="slider-dots" id="sliderDots">
            <?php foreach($banners as $index => $banner): ?>
                <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="setSlide(<?= $index ?>)"></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <section class="hero-section" style="padding-top: 40px; border-top: 5px solid var(--accent-color);">
        <h1 class="translatable" data-en="What are you craving today?" data-my="ဒီနေ့ ဘာစားချင်လဲ?">What are you craving today?</h1>
        
        <form method="GET" action="index.php#menu-section" class="search-bar">
            <?php if($selected_category !== 'all'): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Search for burgers, pizza, drinks..." value="<?= htmlspecialchars($search_keyword) ?>">
            <button type="submit" class="translatable" data-en="Search" data-my="ရှာဖွေမည်">Search</button>
        </form>
    </section>

    <?php if($selected_category === 'all' && empty($search_keyword)): ?>
    <section id="restaurants-section" class="content-partition-primary">
      <h2 class="section-title translatable" style="margin-top: 0; color: var(--primary-color);" data-en="Restaurants in <?= htmlspecialchars($selected_city) ?>" data-my="<?= htmlspecialchars($selected_city) ?> ရှိ စားသောက်ဆိုင်များ">Restaurants in <?= htmlspecialchars($selected_city) ?></h2>
        
        <div class="restaurant-scroll">
            <?php if(count($restaurants) > 0): ?>
                <?php foreach($restaurants as $rest): ?>
                    <a href="restaurant.php?id=<?= $rest['restaurant_id'] ?>" class="rest-card" style="border: none; box-shadow: 0 8px 20px rgba(0,0,0,0.25);">
                        <img src="<?= htmlspecialchars($rest['image_url'] ?? 'assets/default_restaurant.png') ?>" alt="Restaurant" class="rest-img">
                        <div class="rest-info">
                            <h4><?= htmlspecialchars($rest['name']) ?></h4>
                            <p><?= htmlspecialchars($rest['city']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="translatable" data-en="No restaurants available in this location right now." data-my="ယခုနေရာတွင် စားသောက်ဆိုင်များ မရှိသေးပါ။" style="color: var(--white); padding-left: 5%;">No restaurants available in this location right now.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section id="menu-section" class="content-partition-gray" style="<?= ($selected_category !== 'all' || !empty($search_keyword)) ? 'padding-top: 50px;' : '' ?>">
        
        <h2 class="section-title translatable" style="margin-top: 0;" data-en="<?= !empty($search_keyword) ? 'Search Results' : 'Explore Menu' ?>" data-my="<?= !empty($search_keyword) ? 'ရှာဖွေမှု ရလဒ်များ' : 'မီနူး ကြည့်ရှုမည်' ?>">
            <?= !empty($search_keyword) ? 'Search Results' : 'Explore Menu' ?>
        </h2>
        
        <?php if(!empty($search_keyword)): ?>
            <p style="padding: 0 5%; color: var(--text-muted); margin-top: -10px; margin-bottom: 20px;">
                Showing matches for "<strong style="color: var(--primary-color);"><?= htmlspecialchars($search_keyword) ?></strong>"
                <a href="index.php#menu-section" style="margin-left: 15px; color: #dc3545; text-decoration: none; font-size: 13px; font-weight: 600;">✖ Clear Search</a>
            </p>
        <?php endif; ?>
        
       <div class="category-container collapsed" id="categoryContainer">
            <a href="index.php?category=all#menu-section" class="cat-pill <?= $selected_category === 'all' ? 'active' : '' ?> translatable" data-en="All Items" data-my="အားလုံး">All Items</a>
            <?php foreach($categories as $cat): ?>
                <a href="index.php?category=<?= $cat['category_id'] ?><?= !empty($search_keyword) ? '&search='.urlencode($search_keyword) : '' ?>#menu-section" class="cat-pill <?= $selected_category == $cat['category_id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($categories) > 8): ?>
            <div style="text-align: center; margin-top: 15px; margin-bottom: 30px;">
                <button id="toggleCatBtn" class="cat-toggle-btn translatable" data-en="Show More Categories ▼" data-my="အမျိုးအစားများ ထပ်ပြပါ ▼">Show More Categories ▼</button>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 30px;"></div>
        <?php endif; ?>

        <div class="food-grid" style="padding-bottom: 20px;">
            <?php if(count($items) > 0): ?>
                <?php foreach($items as $item): ?>
                    <a href="add_to_cart.php?id=<?= $item['item_id'] ?>" class="food-card" style="text-decoration: none;">
                        
                        <img src="<?= htmlspecialchars($item['image_url'] ?? 'assets/default_food.png') ?>" alt="Food" class="food-card-img" onclick="openImageModal('<?= htmlspecialchars($item['image_url'] ?? 'assets/default_food.png') ?>', event)">
                        
                        <div class="food-card-content">
                            <h3 class="food-title"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="food-vendor">🏪 <?= htmlspecialchars($item['restaurant_name']) ?></p>
                            
                            <div class="food-footer">
                                <span class="food-price"><?= number_format($item['price'], 0) ?> Ks</span>
                                <object><a href="add_to_cart.php?id=<?= $item['item_id'] ?>" class="add-btn">+</a></object>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #777; border: 1px dashed var(--border-light); background: var(--white); border-radius: 12px;">
                    <h3 class="translatable" data-en="No food items found matching your criteria." data-my="သင်ရှာဖွေထားသော အစားအစာများ မရှိပါ။">No food items found matching your criteria.</h3>
                    <a href="index.php#menu-section" class="btn-primary" style="display: inline-block; width: auto; margin-top: 15px; padding: 10px 25px;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 10px; padding-bottom: 30px;">
            <a href="restaurants.php" class="translatable" data-en="See All Restaurants ➔" data-my="စားသောက်ဆိုင်အားလုံးကို ကြည့်မည် ➔" style="display: inline-block; padding: 12px 30px; border: 2px solid var(--primary-color); color: var(--primary-color); border-radius: 50px; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 15px;" onmouseover="this.style.background='var(--primary-color)'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='var(--primary-color)';">
                See All Restaurants ➔
            </a>
        </div>

    </section>

    <div id="foodImageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-modal">&times;</span>
        <img class="image-modal-content" id="expandedImg">
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
                <h4 class="translatable" data-en="For Partners" data-my="လုပ်ငန်းရှင်များအတွက်">For Partners</h4>
                <ul>
                    <li><a href="#" class="translatable" data-en="To Add Advertisement" data-my="ကြော်ငြာထည့်ရန်">To Add Advertisement</a></li>
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

        // Slider Logic
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.getElementById('promoTrack');
            const slides = document.querySelectorAll('.promo-slide');
            const dots = document.querySelectorAll('.dot');
            const totalSlides = slides.length;
            
            if (totalSlides === 0 || !track) return;

            let currentIndex = 0;
            let slideInterval;

            function updateSlider() {
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                dots.forEach(dot => dot.classList.remove('active'));
                if(dots[currentIndex]) dots[currentIndex].classList.add('active');
            }

            window.moveSlide = function(step) {
                currentIndex = (currentIndex + step + totalSlides) % totalSlides;
                updateSlider();
                resetInterval(); 
            }

            window.setSlide = function(index) {
                currentIndex = index;
                updateSlider();
                resetInterval();
            }

            function autoSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            }

            function resetInterval() {
                clearInterval(slideInterval);
                slideInterval = setInterval(autoSlide, 4500); 
            }

            if (totalSlides > 1) resetInterval();
        });
        
        // Mixed Cart Error Alert
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error') && urlParams.get('error') === 'mixed_cart') {
                alert("You can only order from one restaurant at a time.\n\nPlease empty your current cart if you want to order from a different shop!");
                window.history.replaceState(null, null, window.location.pathname);
            }
        });
        // Expand/Collapse Categories Logic
        const catContainer = document.getElementById('categoryContainer');
        const toggleCatBtn = document.getElementById('toggleCatBtn');

        if (toggleCatBtn && catContainer) {
            toggleCatBtn.addEventListener('click', () => {
                const isExpanded = catContainer.classList.toggle('expanded');
                catContainer.classList.toggle('collapsed', !isExpanded);
                
                // Update text and translation data based on state
                if (isExpanded) {
                    toggleCatBtn.setAttribute('data-en', 'Show Less Categories ▲');
                    toggleCatBtn.setAttribute('data-my', 'အမျိုးအစားများ လျှော့ပြပါ ▲');
                } else {
                    toggleCatBtn.setAttribute('data-en', 'Show More Categories ▼');
                    toggleCatBtn.setAttribute('data-my', 'အမျိုးအစားများ ထပ်ပြပါ ▼');
                }
                
                // Re-apply the correct language instantly
                const currentLang = localStorage.getItem('foodlink_lang') || 'en';
                toggleCatBtn.innerHTML = toggleCatBtn.getAttribute(`data-${currentLang}`);
            });
        }
    </script>
    
</body>
</html>
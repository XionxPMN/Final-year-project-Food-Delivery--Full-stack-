<?php
session_start();
require '../includes/db.php';

// Security Check: Only Vendors allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// 1. Get the vendor's restaurant ID
$stmt = $pdo->prepare("SELECT restaurant_id, name FROM restaurants WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$restaurant = $stmt->fetch();
$restaurant_id = $restaurant ? $restaurant['restaurant_id'] : null;

// Fetch all global categories for the dropdown menu
$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY category_name ASC")->fetchAll();

if ($restaurant_id) {
    // ==========================================
    // HANDLE ADDING A NEW MENU ITEM
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
        $name = trim($_POST['item_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $discount_percent = isset($_POST['discount_percent']) ? (int)$_POST['discount_percent'] : 0;
        $category_id = (int)$_POST['category_id'];
        $image_url = 'assets/default_food.png'; 

        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['item_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = uniqid('food_') . '.' . $ext;
                $upload_dir = '../assets/uploads/menu/';
                
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                
                $destination = $upload_dir . $newName;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $destination)) {
                    $image_url = 'assets/uploads/menu/' . $newName;
                } else {
                    $message = "Failed to upload image.";
                    $messageType = "error";
                }
            } else {
                $message = "Invalid image format.";
                $messageType = "error";
            }
        }

        if (empty($messageType)) {
            $insert = $pdo->prepare("INSERT INTO menu_items (restaurant_id, category_id, name, description, price, discount_percent, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$restaurant_id, $category_id, $name, $description, $price, $discount_percent, $image_url])) {
                $message = "Item added to menu successfully!";
                $messageType = "success";
            } else {
                $message = "Database error.";
                $messageType = "error";
            }
        }
    }

    // ==========================================
    // HANDLE UPDATING AN EXISTING ITEM
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
        $item_id = (int)$_POST['item_id'];
        $name = trim($_POST['item_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $discount_percent = isset($_POST['discount_percent']) ? (int)$_POST['discount_percent'] : 0;
        $category_id = (int)$_POST['category_id'];

        $update_query = "UPDATE menu_items SET name = ?, description = ?, price = ?, discount_percent = ?, category_id = ?";
        $params = [$name, $description, $price, $discount_percent, $category_id];

        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['item_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = uniqid('food_') . '.' . $ext;
                $upload_dir = '../assets/uploads/menu/';
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                $destination = $upload_dir . $newName;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $destination)) {
                    $update_query .= ", image_url = ?";
                    $params[] = 'assets/uploads/menu/' . $newName;
                }
            }
        }

        $update_query .= " WHERE item_id = ? AND restaurant_id = ?";
        $params[] = $item_id;
        $params[] = $restaurant_id;

        $update = $pdo->prepare($update_query);
        if ($update->execute($params)) {
            header("Location: manage_menu.php?msg=updated");
            exit();
        } else {
            $message = "Database error updating item.";
            $messageType = "error";
        }
    }

    // ==========================================
    // HANDLE TOGGLING AVAILABILITY
    // ==========================================
    if (isset($_GET['toggle_availability']) && isset($_GET['id'])) {
        $item_id = (int)$_GET['id'];
        $new_status = (int)$_GET['toggle_availability']; 
        
        $toggle_stmt = $pdo->prepare("UPDATE menu_items SET is_available = ? WHERE item_id = ? AND restaurant_id = ?");
        $toggle_stmt->execute([$new_status, $item_id, $restaurant_id]);
        header("Location: manage_menu.php");
        exit();
    }

    // ==========================================
    // HANDLE TOGGLING "TODAY'S SPECIAL"
    // ==========================================
    if (isset($_GET['toggle_special']) && isset($_GET['id'])) {
        $item_id = (int)$_GET['id'];
        $make_special = (int)$_GET['toggle_special']; 
        
        if ($make_special === 1) {
            // First, remove the "Special" status from ALL other items for this restaurant
            $pdo->prepare("UPDATE menu_items SET is_special = 0 WHERE restaurant_id = ?")->execute([$restaurant_id]);
            // Then, make this specific item the Special
            $pdo->prepare("UPDATE menu_items SET is_special = 1 WHERE item_id = ? AND restaurant_id = ?")->execute([$item_id, $restaurant_id]);
        } else {
            // Simply remove the Special status
            $pdo->prepare("UPDATE menu_items SET is_special = 0 WHERE item_id = ? AND restaurant_id = ?")->execute([$item_id, $restaurant_id]);
        }
        
        header("Location: manage_menu.php?msg=special_updated");
        exit();
    }

    // ==========================================
    // HANDLE DELETING AN ITEM
    // ==========================================
    if (isset($_GET['delete'])) {
        $delete_id = (int)$_GET['delete'];
        
        $img_stmt = $pdo->prepare("SELECT image_url FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
        $img_stmt->execute([$delete_id, $restaurant_id]);
        $item_to_delete = $img_stmt->fetch();
        
        if ($item_to_delete && $item_to_delete['image_url'] !== 'assets/default_food.png') {
            if (file_exists("../" . $item_to_delete['image_url'])) {
                unlink("../" . $item_to_delete['image_url']);
            }
        }

        $del_stmt = $pdo->prepare("DELETE FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
        if ($del_stmt->execute([$delete_id, $restaurant_id])) {
            header("Location: manage_menu.php?msg=deleted");
            exit();
        }
    }

    // Capture success messages from redirects
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'deleted') {
            $message = "Menu item deleted forever.";
            $messageType = "success";
        } elseif ($_GET['msg'] === 'updated') {
            $message = "Menu item updated successfully!";
            $messageType = "success";
        } elseif ($_GET['msg'] === 'special_updated') {
            $message = "Today's Special has been updated!";
            $messageType = "success";
        }
    }

    // Check if we are in "Edit Mode"
    $edit_item = null;
    if (isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
        $stmt->execute([$_GET['edit'], $restaurant_id]);
        $edit_item = $stmt->fetch();
    }

    // Fetch this vendor's menu items
    $menu_stmt = $pdo->prepare("
        SELECT m.*, c.category_name 
        FROM menu_items m
        LEFT JOIN menu_categories c ON m.category_id = c.category_id
        WHERE m.restaurant_id = ?
        ORDER BY m.item_id DESC
    ");
    $menu_stmt->execute([$restaurant_id]);
    $menu_items = $menu_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Vendor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-body">

    <div class="sidebar glass-panel">
        <div class="sidebar-logo">Vendor Panel</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="translatable" data-en="Dashboard" data-my="ဒက်ရှ်ဘုတ်">Dashboard</a></li>
            <li><a href="manage_menu.php" class="active translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</a></li>
            <li><a href="orders.php" class="translatable" data-en="Incoming Orders" data-my="ဝင်လာသော အော်ဒါများ">Incoming Orders</a></li>
            <li><a href="history.php" class="translatable" data-en="Order History" data-my="အော်ဒါမှတ်တမ်း">Order History</a></li>
            <li><a href="reviews.php" class="translatable" data-en="Customer Reviews" data-my="သုံးသပ်ချက်များ">Customer Reviews</a></li>
            <li><a href="settings.php" class="translatable" data-en="Shop Settings" data-my="ဆိုင်ဆက်တင်များ">Shop Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar glass-panel">
            <div>
                <h2 class="translatable" data-en="Manage Menu" data-my="မီနူး စီမံရန်">Manage Menu</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn glass-panel" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <?php if(!$restaurant_id): ?>
            <div class="alert error translatable" data-en="Please set up your Shop Settings before adding menu items!" data-my="ကျေးဇူးပြု၍ မီနူးများမထည့်မီ ဆိုင်ဆက်တင်များကို အရင်ဖြည့်စွက်ပါ။">
                Please set up your Shop Settings before adding menu items!
            </div>
        <?php else: ?>

            <?php if($message): ?>
                <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                
                <div class="dashboard-card glass-panel" style="flex: 1; min-width: 300px;">
                    <h3 class="translatable" data-en="<?= $edit_item ? 'Edit Item ✏️' : 'Add New Item 🍔' ?>" data-my="<?= $edit_item ? 'အစားအစာ ပြင်ဆင်ရန် ✏️' : 'အစားအစာအသစ် ထည့်ရန် 🍔' ?>" style="<?= $edit_item ? 'color: #F5A623;' : '' ?>">
                        <?= $edit_item ? 'Edit Item ✏️' : 'Add New Item 🍔' ?>
                    </h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Food Name</label>
                            <input type="text" name="item_name" class="form-control" required placeholder="e.g. Spicy Chicken Burger" value="<?= $edit_item ? htmlspecialchars($edit_item['name']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select category...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= ($edit_item && $edit_item['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Price (Ks)</label>
                                <input type="number" name="price" class="form-control" required placeholder="e.g. 5000" value="<?= $edit_item ? (int)$edit_item['price'] : '' ?>">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Discount (%)</label>
                                <input type="number" name="discount_percent" class="form-control" min="0" max="100" placeholder="0" value="<?= $edit_item ? (int)$edit_item['discount_percent'] : '0' ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe the ingredients..."><?= $edit_item ? htmlspecialchars($edit_item['description']) : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Food Image</label>
                            <?php if($edit_item): ?>
                                <div style="margin-bottom: 10px;">
                                    <img src="../<?= htmlspecialchars($edit_item['image_url']) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                </div>
                                <small style="color:#777;">Upload new image to replace, or leave blank to keep.</small>
                                <input type="file" name="item_image" class="form-control" accept="image/png, image/jpeg, image/webp" style="background: #fff;">
                            <?php else: ?>
                                <input type="file" name="item_image" class="form-control" accept="image/png, image/jpeg, image/webp" required style="background: #fff;">
                            <?php endif; ?>
                        </div>

                        <?php if($edit_item): ?>
                            <input type="hidden" name="item_id" value="<?= $edit_item['item_id'] ?>">
                            <input type="hidden" name="update_item" value="1">
                            <button type="submit" class="btn-primary" style="width: 100%; background: #F5A623; color: #fff;">Update Item</button>
                            <a href="manage_menu.php" style="display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; font-size: 14px; font-weight: 600;">Cancel Edit</a>
                        <?php else: ?>
                            <input type="hidden" name="add_item" value="1">
                            <button type="submit" class="btn-primary" style="width: 100%;">Upload to Menu</button>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="dashboard-card glass-panel" style="flex: 2; min-width: 400px; background: transparent; border: none !important; box-shadow: none !important; padding: 0;">
                    <h3 class="translatable" data-en="Your Current Menu" data-my="သင့်လက်ရှိ မီနူးများ">Your Current Menu</h3>
                    
                    <div class="menu-grid">
                        <?php if(count($menu_items) > 0): ?>
                            <?php foreach($menu_items as $item): ?>
                                <div class="menu-card <?= $item['is_available'] ? '' : 'disabled-item' ?>" style="<?= $item['is_special'] ? 'border: 2px solid #F5A623;' : '' ?>">
                                    <div style="position: relative;">
                                        <?php if(isset($item['discount_percent']) && $item['discount_percent'] > 0): ?>
                                            <span class="discount-badge"><?= $item['discount_percent'] ?>% OFF</span>
                                        <?php endif; ?>
                                        <img src="../<?= htmlspecialchars($item['image_url']) ?>" class="menu-img" alt="Food">
                                    </div>
                                    
                                    <div class="menu-info" style="position: relative;">
                                        <?php if($item['is_special']): ?>
                                            <span style="position: absolute; top: 10px; right: 10px; background: #F5A623; color: white; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">⭐ Special</span>
                                        <?php endif; ?>

                                        <span class="menu-cat"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></span>
                                        <h4 class="menu-title"><?= htmlspecialchars($item['name']) ?></h4>
                                        
                                        <?php if(isset($item['discount_percent']) && $item['discount_percent'] > 0): ?>
                                            <?php 
                                                $d_amt = $item['price'] * ($item['discount_percent'] / 100);
                                                $f_price = $item['price'] - $d_amt;
                                            ?>
                                            <div style="font-size: 12px; color: #999; text-decoration: line-through;"><?= number_format($item['price'], 0) ?> Ks</div>
                                            <div class="menu-price"><?= number_format($f_price, 0) ?> Ks</div>
                                        <?php else: ?>
                                            <div class="menu-price"><?= number_format($item['price'], 0) ?> Ks</div>
                                        <?php endif; ?>

                                        <?php if(!$item['is_available']): ?>
                                            <span style="color: #d32f2f; font-size: 12px; font-weight: bold; display: block; margin-top: 5px;">[ Sold Out ]</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="action-bar" style="flex-wrap: wrap;">
                                        <a href="manage_menu.php?edit=<?= $item['item_id'] ?>" class="action-btn btn-edit">Edit</a>
                                        
                                        <?php if($item['is_available']): ?>
                                            <a href="manage_menu.php?toggle_availability=0&id=<?= $item['item_id'] ?>" class="action-btn btn-toggle-off">Sold Out</a>
                                        <?php else: ?>
                                            <a href="manage_menu.php?toggle_availability=1&id=<?= $item['item_id'] ?>" class="action-btn btn-toggle-on">Available</a>
                                        <?php endif; ?>
                                        
                                        <?php if($item['is_special']): ?>
                                            <a href="manage_menu.php?toggle_special=0&id=<?= $item['item_id'] ?>" class="action-btn" style="background: #fff9e6; color: #d39e00; border-right: 1px solid var(--border-light);">★ Un-Star</a>
                                        <?php else: ?>
                                            <a href="manage_menu.php?toggle_special=1&id=<?= $item['item_id'] ?>" class="action-btn" style="color: #F5A623; border-right: 1px solid var(--border-light);">☆ Star</a>
                                        <?php endif; ?>
                                        
                                        <a href="manage_menu.php?delete=<?= $item['item_id'] ?>" class="action-btn btn-delete" onclick="return confirm('Delete this item from your menu permanently?')">Del</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #fff; border-radius: 12px; border: 1px dashed #ccc;">
                                <h4 style="color: #777;">Your menu is empty.</h4>
                                <p style="font-size: 14px; color: #999;">Upload your first food item using the form!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
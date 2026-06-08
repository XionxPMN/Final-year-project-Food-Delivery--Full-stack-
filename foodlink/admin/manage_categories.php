<?php
session_start();
require '../includes/db.php';

// Role-Based Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle Category Deletion (NEW)
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Optional: Fetch the image URL first so we can delete the actual file from the server
    $img_stmt = $pdo->prepare("SELECT image_url FROM menu_categories WHERE category_id = ?");
    $img_stmt->execute([$delete_id]);
    $cat_to_delete = $img_stmt->fetch();
    
    if ($cat_to_delete && $cat_to_delete['image_url'] !== 'assets/default_category.png') {
        $file_path = "../" . $cat_to_delete['image_url'];
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the image file
        }
    }

    // Delete from database
    $del_stmt = $pdo->prepare("DELETE FROM menu_categories WHERE category_id = ?");
    if ($del_stmt->execute([$delete_id])) {
        // Use a session variable to pass the success message so it survives the redirect
        $_SESSION['action_msg'] = "Category deleted successfully!";
        $_SESSION['action_type'] = "success";
        header("Location: manage_categories.php");
        exit();
    } else {
        $message = "Error deleting category.";
        $messageType = "error";
    }
}

// Check for session messages (from redirects like deletion)
if (isset($_SESSION['action_msg'])) {
    $message = $_SESSION['action_msg'];
    $messageType = $_SESSION['action_type'];
    unset($_SESSION['action_msg']);
    unset($_SESSION['action_type']);
}

// Handle Category Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $image_url = 'assets/default_category.png'; // Default fallback

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['category_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
            $newName = uniqid('cat_') . '.' . $ext;
            
            // ADDED: Create the folder if it doesn't exist during EDIT
            $upload_dir = '../uploads/categories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); 
            }
            $destination = $upload_dir . $newName;

            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $destination)) {
                $image_url = 'uploads/categories/' . $newName;
            } else {
                $message = "Failed to upload image.";
                $messageType = "error";
            }
        }
    }

    if (empty($messageType)) {
        $insert = $pdo->prepare("INSERT INTO menu_categories (category_name, image_url) VALUES (?, ?)");
        if ($insert->execute([$category_name, $image_url])) {
            $message = "Category created successfully!";
            $messageType = "success";
        } else {
            $message = "Database error.";
            $messageType = "error";
        }
    }
}

// Handle Category Update (Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    
    // Start building the query assuming only the name is updated
    $update_query = "UPDATE menu_categories SET category_name = ?";
    $params = [$category_name];

    // Check if a NEW image was uploaded
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['category_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = uniqid('cat_') . '.' . $ext;
            $destination = '../uploads/categories/' . $newName;

            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $destination)) {
                // Add the image to the update query
                $update_query .= ", image_url = ?";
                $params[] = 'uploads/categories/' . $newName;
            } else {
                $message = "Failed to upload new image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
            $messageType = "error";
        }
    }

    if (empty($messageType)) {
        // Finish the query by adding the WHERE clause
        $update_query .= " WHERE category_id = ?";
        $params[] = $category_id;

        $update = $pdo->prepare($update_query);
        if ($update->execute($params)) {
            $message = "Category updated successfully!";
            $messageType = "success";
        } else {
            $message = "Database error updating category.";
            $messageType = "error";
        }
    }
}

// Check if we are in "Edit Mode"
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_categories WHERE category_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_category = $stmt->fetch();
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM menu_categories ORDER BY category_id DESC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .cat-card { background: #fff; border: 1px solid #e1e5eb; border-radius: 8px; overflow: hidden; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .cat-img { width: 100%; height: 120px; object-fit: cover; background: #f4f7f6; }
        .cat-title { padding: 10px; font-weight: 600; color: var(--primary-color); font-size: 14px; flex-grow: 1; }
        
        .action-buttons { display: flex; border-top: 1px solid #e1e5eb; }
        .edit-btn, .del-btn { flex: 1; text-decoration: none; padding: 8px; font-size: 13px; font-weight: 600; transition: background 0.2s; }
        
        .edit-btn { background: #f4f7f6; color: var(--text-color); border-right: 1px solid #e1e5eb; }
        .edit-btn:hover { background: var(--primary-light); color: var(--primary-color); }
        
        .del-btn { background: #ffebee; color: #c62828; }
        .del-btn:hover { background: #c62828; color: #fff; }
        
        .cancel-btn { display: block; text-align: center; margin-top: 10px; color: #777; text-decoration: none; font-size: 14px; }
        .cancel-btn:hover { color: #d9534f; }
    </style>
</head>
<body class="dashboard-body">

   <div class="sidebar">
        <div class="sidebar-logo">FoodLink Admin</div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="active translatable" data-en="Dashboard" data-my="အကောင့်များ စီမံရန်">Dashboard</a></li>
            <li><a href="manage_categories.php" class="translatable" data-en="Manage Categories" data-my="အမျိုးအစားများ စီမံရန်">Manage Categories</a></li>
            <li><a href="manage_users.php" class="translatable" data-en="Manage Users" data-my="အသုံးပြုသူများကို စီမံရန်">Manage Users</a></li>
            <li><a href="manage_banners.php" class="translatable" data-en="Manage Banners" data-my="ဘန်နာများ စီမံရန်">Manage Banners</a></li>
            <li><a href="manage_restaurants.php" class="translatable" data-en="Manage Restaurants" data-my="စားသောက်ဆိုင်များ စီမံရန်">Manage Restaurants</a></li>
            <li><a href="manage_delivery.php" class="translatable" data-en="Manage Delivery" data-my="ပို့ဆောင်ခ စီမံရန်">Manage Delivery Fees</a></li>
            <li><a href="finances.php" class="translatable" data-en="Financial Overview" data-my="ငွေကြေးစီမံခန့်ခွဲမှု">Financial Overview</a></li>
            <li><a href="profile.php" class="translatable" data-en="Admin Profile" data-my="ပရိုဖိုင် ဆက်တင်များ">Admin Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h2 class="translatable" data-en="Global Food Categories" data-my="အစားအစာ အမျိုးအစားများ">Global Food Categories</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
                <a href="../logout.php" class="logout-btn translatable" data-en="Logout" data-my="အကောင့်ထွက်မည်">Logout</a>
            </div>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
            
            <div class="dashboard-card" style="flex: 1; min-width: 300px;">
                
                <?php if($edit_category): ?>
                    <h3 style="color: #F5A623;">Edit Category</h3>
                <?php else: ?>
                    <h3 class="translatable" data-en="Create New Category" data-my="အမျိုးအစားအသစ် ဖန်တီးရန်">Create New Category</h3>
                <?php endif; ?>
                
                <?php if($message): ?>
                    <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST" action="manage_categories.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" value="<?= $edit_category ? htmlspecialchars($edit_category['category_name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category Image</label>
                        <?php if($edit_category): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="../<?= htmlspecialchars($edit_category['image_url']) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            </div>
                            <small style="color:#777;">Upload a new image to replace the current one, or leave blank to keep it.</small>
                            <input type="file" name="category_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <?php else: ?>
                            <input type="file" name="category_image" class="form-control" accept="image/png, image/jpeg, image/webp" required>
                        <?php endif; ?>
                    </div>

                    <?php if($edit_category): ?>
                        <input type="hidden" name="category_id" value="<?= $edit_category['category_id'] ?>">
                        <input type="hidden" name="update_category" value="1">
                        <button type="submit" class="btn-primary" style="background: #F5A623;">Update Category</button>
                        <a href="manage_categories.php" class="cancel-btn">Cancel Edit</a>
                    <?php else: ?>
                        <input type="hidden" name="add_category" value="1">
                        <button type="submit" class="btn-primary translatable" data-en="Upload & Save" data-my="ပုံတင်ပြီး သိမ်းမည်">Upload & Save</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="dashboard-card" style="flex: 2; min-width: 400px;">
                <h3 class="translatable" data-en="Active Categories" data-my="လက်ရှိ အမျိုးအစားများ">Active Categories</h3>
                <div class="cat-grid">
                    <?php foreach($categories as $cat): ?>
                        <div class="cat-card">
                            <img src="../<?= htmlspecialchars($cat['image_url']) ?>" alt="Category" class="cat-img">
                            <div class="cat-title"><?= htmlspecialchars($cat['category_name']) ?></div>
                            <div class="action-buttons">
                                <a href="manage_categories.php?edit=<?= $cat['category_id'] ?>" class="edit-btn">Edit</a>
                                <a href="manage_categories.php?delete=<?= $cat['category_id'] ?>" class="del-btn" onclick="return confirm('Are you sure you want to delete the <?= htmlspecialchars(addslashes($cat['category_name'])) ?> category? This might affect menu items linked to it!')">Del</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/translate.js"></script>
</body>
</html>
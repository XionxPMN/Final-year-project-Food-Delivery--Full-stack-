<?php
session_start();
require '../includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Banner Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $restaurant_id = $_POST['restaurant_id'];
    
    // Simple Image Upload Logic
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $target_dir = "../assets/uploads/banners/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }
        
        $file_name = time() . '_' . basename($_FILES["banner_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
            // Save the path relative to the root index.php
            $db_image_path = "assets/uploads/banners/" . $file_name;
            
            $stmt = $pdo->prepare("INSERT INTO promotional_banners (restaurant_id, image_url) VALUES (?, ?)");
            $stmt->execute([$restaurant_id, $db_image_path]);
            $success_msg = "Banner added successfully!";
        } else {
            $error_msg = "Failed to upload image. Check folder permissions.";
        }
    }
}

// Handle Banner Deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Optional: Fetch image path to delete the actual file from the server
    $img_stmt = $pdo->prepare("SELECT image_url FROM promotional_banners WHERE banner_id = ?");
    $img_stmt->execute([$delete_id]);
    $img = $img_stmt->fetch();
    if ($img && file_exists("../" . $img['image_url'])) {
        unlink("../" . $img['image_url']);
    }

    $stmt = $pdo->prepare("DELETE FROM promotional_banners WHERE banner_id = ?");
    $stmt->execute([$delete_id]);
    header("Location: manage_banners.php");
    exit();
}

// Fetch all restaurants for the dropdown
$restaurants = $pdo->query("SELECT restaurant_id, name FROM restaurants WHERE status = 'approved' ORDER BY name ASC")->fetchAll();

// Fetch current banners
$banners = $pdo->query("
    SELECT b.*, r.name as restaurant_name 
    FROM promotional_banners b 
    JOIN restaurants r ON b.restaurant_id = r.restaurant_id 
    ORDER BY b.banner_id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Banners - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-page-body">

    <div class="admin-container">
        <div class="header-section">
            <h2>🖼️ Manage Homepage Banners</h2>
            <a href="index.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php if(isset($success_msg)) echo "<div class='alert success'>$success_msg</div>"; ?>
        <?php if(isset($error_msg)) echo "<div class='alert error'>$error_msg</div>"; ?>

        <div class="grid-layout">
            <div class="upload-card">
                <h3>Add New Banner</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Link to Restaurant:</label>
                        <select name="restaurant_id" class="form-control" required>
                            <option value="">Select a restaurant...</option>
                            <?php foreach($restaurants as $r): ?>
                                <option value="<?= $r['restaurant_id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banner Image (Ideal: 1600x900px):</label>
                        <input type="file" name="banner_image" class="form-control" accept="image/*" required style="background: #fff;">
                    </div>
                    <button type="submit" name="add_banner" class="btn-primary" style="margin-top: 20px;">Upload Banner</button>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <tr>
                        <th>Preview Image</th>
                        <th>Linked Restaurant</th>
                        <th>Action</th>
                    </tr>
                    <?php if(count($banners) > 0): ?>
                        <?php foreach($banners as $b): ?>
                        <tr>
                            <td style="width: 250px;">
                                <img src="../<?= htmlspecialchars($b['image_url']) ?>" class="banner-preview">
                            </td>
                            <td><strong><?= htmlspecialchars($b['restaurant_name']) ?></strong></td>
                            <td>
                                <a href="manage_banners.php?delete=<?= $b['banner_id'] ?>" class="action-link delete-btn" onclick="return confirm('Are you sure you want to delete this banner from the homepage?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 30px; color: #777;">No active banners found. Upload one to get started!</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
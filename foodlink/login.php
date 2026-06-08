<?php
session_start();
require 'includes/db.php';

$message = '';

// If they are already logged in, send them to their correct dashboard!
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin/index.php");
    elseif ($_SESSION['role'] === 'vendor') header("Location: vendor/index.php");
    elseif ($_SESSION['role'] === 'rider') header("Location: rider/index.php");
    else header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 1. Verify Password
    if ($user && password_verify($password, $user['password'])) {
        
        // 2. NEW: CHECK IF SUSPENDED
        $user_status = $user['status'] ?? 'active';
        
        if ($user_status === 'suspended') {
            // STOP! Don't log them in. Show an error message instead.
            $message = "Your account has been suspended. Please contact support.";
        } else {
            // 3. SUCCESS! Log them in and save their details in the session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // THE MAGIC TRAFFIC CONTROLLER: Send them to the right folder!
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } elseif ($user['role'] === 'vendor') {
                header("Location: vendor/index.php");
            } elseif ($user['role'] === 'rider') {
                header("Location: rider/index.php"); 
            } else {
                // Check if they were trying to checkout before logging in
                if (isset($_GET['redirect']) && $_GET['redirect'] == 'checkout') {
                    header("Location: checkout.php");
                } else {
                    header("Location: index.php"); // Normal Customer
                }
            }
            exit();
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg-light); margin: 0; }
        .auth-container { background: var(--white); padding: 40px; border-radius: 16px; box-shadow: var(--shadow-card); width: 100%; max-width: 400px; border: 1px solid var(--border-light); text-align: center; position: relative; }
        .auth-container h2 { margin-top: 10px; margin-bottom: 25px; font-weight: 800; font-size: 28px; }
        .auth-container .form-group { text-align: left; margin-bottom: 20px; }
        
        /* Password Toggle Styles */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; width: 100%; }
        .toggle-password {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #888;
            padding: 0;
            outline: none;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: var(--primary-color); }
        
        /* NEW: Back to Home Link Styles */
        .back-link {
            display: inline-block;
            text-align: left;
            width: 100%;
            margin-bottom: 15px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--primary-color); }
    </style>
</head>
<body>

    <div class="auth-container">
        <a href="index.php" class="back-link translatable" data-en="&larr; Back to Home" data-my="&larr; ပင်မစာမျက်နှာသို့ ပြန်သွားမည်">&larr; Back to Home</a>
        
        <h2><a href="index.php" style="color: var(--primary-color); text-decoration: none;">FoodLink</a></h2>
        
        <h3 class="translatable" data-en="Welcome Back" data-my="ပြန်လည်ကြိုဆိုပါသည်">Welcome Back</h3>

        <?php if($message): ?>
            <div class="alert error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="translatable" data-en="Email Address" data-my="အီးမေးလ်လိပ်စာ">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label class="translatable" data-en="Password" data-my="စကားဝှက်">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="loginPassword" name="password" class="form-control" required placeholder="••••••••" style="margin: 0;">
                    <button type="button" class="toggle-password" onclick="toggleVisibility()" id="eyeIcon">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-primary translatable" style="width: 100%; margin-top: 10px; padding: 12px; font-size: 16px;" data-en="Log In" data-my="အကောင့်ဝင်မည်">Log In</button>
        </form>

        <p style="margin-top: 25px; color: var(--text-muted); font-size: 14px;">
            <span class="translatable" data-en="Don't have an account?" data-my="အကောင့်မရှိသေးဘူးလား?">Don't have an account?</span> 
            <a href="register.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;" class="translatable" data-en="Register Here" data-my="ဒီမှာ စာရင်းသွင်းပါ">Register Here</a>
        </p>
    </div>

    <script src="assets/translate.js"></script>
    
    <script>
        function toggleVisibility() {
            const passwordInput = document.getElementById("loginPassword");
            const eyeIcon = document.getElementById("eyeIcon");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.innerText = "🙈"; 
            } else {
                passwordInput.type = "password";
                eyeIcon.innerText = "👁️"; 
            }
        }
    </script>
</body>
</html>
<?php
session_start();
require 'includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer'; // HARDCODED: Admins/Vendors created elsewhere

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = "Email already registered!";
        $messageType = "error";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($insert->execute([$name, $email, $phone, $password, $role])) {
            $message = "Registration successful! You can now log in.";
            $messageType = "success";
        } else {
            $message = "Error during registration.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg-light); margin: 0; padding: 20px;}
        .auth-card { background: var(--white); padding: 40px; border-radius: 16px; box-shadow: var(--shadow-card); width: 100%; max-width: 450px; border: 1px solid var(--border-light); }
        .brand-logo { text-align: center; color: var(--primary-color); font-size: 32px; font-weight: 800; margin-bottom: 20px;}
        .auth-links { margin-top: 25px; text-align: center; font-size: 14px; color: var(--text-muted); }
        .auth-links a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        
        /* Validation Error Text */
        .error-text { color: #dc3545; font-size: 12px; margin-top: 5px; display: none; }
        
        /* Password Toggle Styles */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; width: 100%; }
        .toggle-password { position: absolute; right: 15px; background: none; border: none; cursor: pointer; font-size: 18px; color: #888; padding: 0; outline: none; transition: color 0.3s; }
        .toggle-password:hover { color: var(--primary-color); }

        /* Password Strength Bar */
        .strength-container { margin-top: 10px; width: 100%; background-color: #e0e0e0; border-radius: 5px; height: 6px; overflow: hidden; display: none;}
        .strength-bar { height: 100%; width: 0%; transition: all 0.4s ease; border-radius: 5px; }
        .strength-text { font-size: 12px; margin-top: 5px; font-weight: 600; display: none;}
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="brand-logo">FoodLink</div>
        
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 class="translatable" data-en="Create an Account" data-my="အကောင့်အသစ်ဖွင့်ရန်" style="font-size: 22px; color: #222; margin:0;">Create an Account</h2>
            <p class="translatable" data-en="Order food from local restaurants" data-my="ဒေသတွင်းစားသောက်ဆိုင်များမှ မှာယူပါ" style="color: #777; font-size: 13px; margin-top: 5px;">Order food from local restaurants</p>
        </div>
        
        <?php if($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="translatable" data-en="Full Name" data-my="အမည်အပြည့်အစုံ">Full Name</label>
                <input type="text" name="name" id="regName" class="form-control" placeholder="e.g. Aung Aung" required>
                <div class="error-text" id="nameError">Name must contain only letters and spaces.</div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="translatable" data-en="Email Address" data-my="အီးမေးလ်လိပ်စာ">Email Address</label>
                <input type="email" name="email" id="regEmail" class="form-control" placeholder="name@example.com" required>
                <div class="error-text" id="emailError">Please enter a valid email address.</div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="translatable" data-en="Phone Number" data-my="ဖုန်းနံပါတ်">Phone Number</label>
                <input type="text" name="phone" id="regPhone" class="form-control" placeholder="09xxxxxxxxx" required>
                <div class="error-text" id="phoneError">Phone must start with 09 and contain 9 to 11 digits.</div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="translatable" data-en="Password" data-my="စကားဝှက်">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="regPassword" class="form-control" placeholder="••••••••" style="margin: 0;" required>
                    <button type="button" class="toggle-password" onclick="toggleVisibility()" id="eyeIcon">👁️</button>
                </div>
                <div class="strength-container" id="strengthContainer">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
            </div>

            <button type="submit" class="btn-primary translatable" id="submitBtn" style="width: 100%; padding: 12px; font-size: 16px;" data-en="Sign Up" data-my="အကောင့်ဖွင့်မည်">Sign Up</button>
        </form>

        <div class="auth-links">
            <span class="translatable" data-en="Already have an account?" data-my="အကောင့်ရှိပြီးသားလား?">Already have an account?</span> 
            <a href="login.php" class="translatable" data-en="Log in here" data-my="ဒီမှာ အကောင့်ဝင်ပါ">Log in here</a>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
        </div>
    </div>
</div>

<script src="assets/translate.js"></script>

<script>
    // 1. Password Visibility Toggle
    function toggleVisibility() {
        const passwordInput = document.getElementById("regPassword");
        const eyeIcon = document.getElementById("eyeIcon");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.innerText = "🙈"; 
        } else {
            passwordInput.type = "password";
            eyeIcon.innerText = "👁️"; 
        }
    }

    // 2. Real-time Validation Setup
    const nameInput = document.getElementById('regName');
    const emailInput = document.getElementById('regEmail');
    const phoneInput = document.getElementById('regPhone');
    const passwordInput = document.getElementById('regPassword');
    const form = document.getElementById('registerForm');

    // Regex Patterns
    const namePattern = /^[A-Za-z\s]+$/; 
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^09[0-9]{7,9}$/; // Must start with 09, followed by 7 to 9 digits

    // Name Validation
    nameInput.addEventListener('input', function() {
        if (!namePattern.test(this.value) && this.value.length > 0) {
            document.getElementById('nameError').style.display = 'block';
        } else {
            document.getElementById('nameError').style.display = 'none';
        }
    });

    // Email Validation
    emailInput.addEventListener('input', function() {
        if (!emailPattern.test(this.value) && this.value.length > 0) {
            document.getElementById('emailError').style.display = 'block';
        } else {
            document.getElementById('emailError').style.display = 'none';
        }
    });

    // Phone Validation
    phoneInput.addEventListener('input', function() {
        if (!phonePattern.test(this.value) && this.value.length > 0) {
            document.getElementById('phoneError').style.display = 'block';
        } else {
            document.getElementById('phoneError').style.display = 'none';
        }
    });

    // 3. Password Strength Meter
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        const strengthContainer = document.getElementById('strengthContainer');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        
        if (val.length > 0) {
            strengthContainer.style.display = 'block';
            strengthText.style.display = 'block';
        } else {
            strengthContainer.style.display = 'none';
            strengthText.style.display = 'none';
        }

        if (val.length > 5) strength += 1; // Minimum length
        if (val.match(/[a-z]+/)) strength += 1; // Lowercase
        if (val.match(/[A-Z]+/)) strength += 1; // Uppercase
        if (val.match(/[0-9]+/)) strength += 1; // Numbers
        if (val.match(/[$@#&!]+/)) strength += 1; // Special Char

        switch(strength) {
            case 0:
            case 1:
                strengthBar.style.width = '25%';
                strengthBar.style.backgroundColor = '#dc3545'; // Red
                strengthText.innerText = 'Weak';
                strengthText.style.color = '#dc3545';
                break;
            case 2:
            case 3:
                strengthBar.style.width = '50%';
                strengthBar.style.backgroundColor = '#ffc107'; // Yellow
                strengthText.innerText = 'Medium';
                strengthText.style.color = '#ffc107';
                break;
            case 4:
                strengthBar.style.width = '75%';
                strengthBar.style.backgroundColor = '#17a2b8'; // Blue
                strengthText.innerText = 'Strong';
                strengthText.style.color = '#17a2b8';
                break;
            case 5:
                strengthBar.style.width = '100%';
                strengthBar.style.backgroundColor = '#28a745'; // Green
                strengthText.innerText = 'Very Strong';
                strengthText.style.color = '#28a745';
                break;
        }
    });

    // Prevent submission if errors exist
    form.addEventListener('submit', function(e) {
        if (!namePattern.test(nameInput.value) || 
            !emailPattern.test(emailInput.value) || 
            !phonePattern.test(phoneInput.value)) {
            e.preventDefault();
            alert("Please fix the errors in the form before submitting.");
        }
    });
</script>
</body>
</html>
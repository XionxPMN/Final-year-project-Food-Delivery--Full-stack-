<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - FoodLink Myanmar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .terms-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 40px 50px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-light);
        }
        .terms-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px dashed var(--border-light);
            padding-bottom: 20px;
        }
        .terms-header h1 {
            color: var(--primary-color);
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .terms-content h3 {
            color: var(--text-dark);
            font-size: 20px;
            font-weight: 700;
            margin: 30px 0 10px;
        }
        .terms-content p, .terms-content li {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .terms-content ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .terms-container { padding: 30px 20px; margin: 20px; }
            .terms-header h1 { font-size: 26px; }
        }
    </style>
</head>
<body style="background: var(--bg-light);">

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">FoodLink</a>
        <div class="nav-actions" style="display: flex; align-items: center; gap: 15px;">
            <button class="lang-btn" onclick="toggleLanguage()" id="langToggle" style="margin:0;">မြန်မာ</button>
            <a href="index.php" class="btn-primary translatable" data-en="Back to Home" data-my="ပင်မစာမျက်နှာသို့ ပြန်သွားမည်" style="display: inline-block; padding: 10px 20px; margin: 0; width: auto; font-size: 14px;">Back to Home</a>
        </div>
    </nav>

    <div class="terms-container">
        <div class="terms-header">
            <h1 class="translatable" data-en="Terms & Conditions" data-my="စည်းမျဉ်းနှင့် စည်းကမ်းများ">Terms & Conditions</h1>
            <p class="translatable" data-en="Last Updated: March 2026" data-my="နောက်ဆုံးမွမ်းမံမှု - မတ်လ ၂၀၂၆">Last Updated: March 2026</p>
        </div>

        <div class="terms-content">
            <h3 class="translatable" data-en="1. Introduction" data-my="၁။ နိဒါန်း">1. Introduction</h3>
            <p class="translatable" data-en="Welcome to FoodLink Myanmar. By accessing or using our website and platform, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not use our services." data-my="FoodLink Myanmar မှကြိုဆိုပါသည်။ ကျွန်ုပ်တို့၏ ဝန်ဆောင်မှုများကို အသုံးပြုခြင်းဖြင့် အောက်ပါ စည်းမျဉ်းစည်းကမ်းများကို သဘောတူလက်ခံရာရောက်ပါသည်။">
                Welcome to FoodLink Myanmar. By accessing or using our website and platform, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not use our services.
            </p>

            <h3 class="translatable" data-en="2. User Accounts" data-my="၂။ အသုံးပြုသူ အကောင့်များ">2. User Accounts</h3>
            <ul>
                <li class="translatable" data-en="You must provide accurate, current, and complete information during the registration process." data-my="အကောင့်ဖွင့်ရာတွင် မှန်ကန်သော အချက်အလက်များကို ပေးသွင်းရပါမည်။">You must provide accurate, current, and complete information during the registration process.</li>
                <li class="translatable" data-en="You are responsible for safeguarding your password and for all activities that occur under your account." data-my="သင့်အကောင့်၏ လုံခြုံရေးနှင့် လုပ်ဆောင်ချက်များအားလုံးအတွက် သင်ကိုယ်တိုင် တာဝန်ယူရပါမည်။">You are responsible for safeguarding your password and for all activities that occur under your account.</li>
            </ul>

            <h3 class="translatable" data-en="3. Orders and Payments" data-my="၃။ အော်ဒါများနှင့် ငွေပေးချေမှု">3. Orders and Payments</h3>
            <p class="translatable" data-en="All orders placed through FoodLink Myanmar are subject to restaurant availability. We accept Cash on Delivery (COD), KBZPay, and WavePay. Prices listed on the platform include taxes but may exclude delivery fees, which are calculated at checkout." data-my="အော်ဒါအားလုံးသည် စားသောက်ဆိုင်၏ ရရှိနိုင်မှုအပေါ် မူတည်ပါသည်။ ငွေပေးချေမှုအတွက် COD, KBZPay နှင့် WavePay တို့ကို လက်ခံပါသည်။ ပို့ဆောင်ခကို ငွေရှင်းသည့် အဆင့်တွင် ထည့်သွင်းတွက်ချက်ပါမည်။">
                All orders placed through FoodLink Myanmar are subject to restaurant availability. We accept Cash on Delivery (COD), KBZPay, and WavePay. Prices listed on the platform include taxes but may exclude delivery fees, which are calculated at checkout.
            </p>

            <h3 class="translatable" data-en="4. Vendor & Rider Responsibilities" data-my="၄။ ရောင်းချသူနှင့် ပို့ဆောင်သူများ၏ တာဝန်များ">4. Vendor & Rider Responsibilities</h3>
            <ul>
                <li class="translatable" data-en="Vendors are solely responsible for the quality, safety, and correct pricing of the food items they list." data-my="ရောင်းချသူများသည် အစားအစာ၏ အရည်အသွေး၊ လုံခြုံမှုနှင့် ဈေးနှုန်းမှန်ကန်မှုတို့အတွက် တာဝန်ရှိသည်။">Vendors are solely responsible for the quality, safety, and correct pricing of the food items they list.</li>
                <li class="translatable" data-en="Riders operate as independent contractors and are responsible for delivering food safely and within a reasonable timeframe." data-my="ပို့ဆောင်သူများသည် အစားအစာများကို ဘေးကင်းလုံခြုံစွာနှင့် အချိန်မီ ပို့ဆောင်ပေးရန် တာဝန်ရှိသည်။">Riders operate as independent contractors and are responsible for delivering food safely and within a reasonable timeframe.</li>
            </ul>


            <h3 class="translatable" data-en="5. Limitation of Liability" data-my="၅။ တာဝန်ယူမှု ကန့်သတ်ချက်">5. Limitation of Liability</h3>
            <p class="translatable" data-en="FoodLink Myanmar acts solely as a technology platform connecting customers, restaurants, and riders. We are not liable for any allergic reactions, food poisoning, or damages resulting from the consumption of food ordered through our platform." data-my="FoodLink Myanmar သည် နည်းပညာပလက်ဖောင်းတစ်ခုသာဖြစ်ပြီး၊ အစားအစာကြောင့်ဖြစ်ပေါ်လာသော ဓာတ်မတည့်မှု၊ အစာဆိပ်သင့်မှု သို့မဟုတ် အခြားထိခိုက်နစ်နာမှုများအတွက် တာဝန်ယူမည်မဟုတ်ပါ။">
                FoodLink Myanmar acts solely as a technology platform connecting customers, restaurants, and riders. We are not liable for any allergic reactions, food poisoning, or damages resulting from the consumption of food ordered through our platform.
            </p>

            <p style="margin-top: 40px; font-weight: 600; color: var(--text-dark); text-align: center;" class="translatable" data-en="If you have any questions about these Terms, please contact support@foodlink.com.mm" data-my="ဤစည်းမျဉ်းများနှင့်ပတ်သက်၍ မေးမြန်းလိုပါက support@foodlink.com.mm သို့ ဆက်သွယ်နိုင်ပါသည်။">
                If you have any questions about these Terms, please contact support@foodlink.com.mm
            </p>
        </div>
    </div>

    <script src="assets/translate.js"></script>
</body>
</html>
<?php
include_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Language Logic
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ur'; // Default Urdu
$dir = ($lang == 'en') ? 'ltr' : 'rtl';
$align = ($lang == 'en') ? 'left' : 'right';

// Translations
$trans = [
    'home' => ['ur' => 'مرکزی صفحہ', 'en' => 'Home'],
    'reports' => ['ur' => 'گوشوارہ جات', 'en' => 'Reports'],
    'history' => ['ur' => 'تاریخ و تعارف', 'en' => 'History'],
    'services' => ['ur' => 'خدمات', 'en' => 'Services'],
    'contact' => ['ur' => 'رابطہ', 'en' => 'Contact'],
    'login' => ['ur' => 'لاگ ان', 'en' => 'Login'],
    'rights' => ['ur' => 'جملہ حقوق محفوظ ہیں۔', 'en' => 'All Rights Reserved.'],
    'powered' => ['ur' => 'تیار کردہ: تنظیم سسٹم', 'en' => 'Powered by Tanzeem System'],
    'read_more' => ['ur' => 'مزید پڑھیں', 'en' => 'Read More'],
    'view_all' => ['ur' => 'تمام خدمات دیکھیں', 'en' => 'View All Services'],
    'family_tree' => ['ur' => 'شجرہ نسب', 'en' => 'Family Tree']
];

// Fetch Settings
if (!isset($details)) {
    $details = [];
    try {
        $stmt = $pdo->query("SELECT * FROM settings");
        while ($row = $stmt->fetch()) {
            $details[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) { }
}

$org_name = ($lang == 'en') ? ($details['org_name_en'] ?? 'Tanzeem') : ($details['org_name'] ?? 'تنظیم');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $org_name; ?></title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    
    <!-- Bootstrap 5 -->
    <?php if($lang == 'ur'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <?php endif; ?>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jameel+Noori+Nastaleeq&family=Noto+Nastaliq+Urdu:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #004d40; /* Deep Green */
            --secondary-color: #ffd700; /* Gold */
            --accent-color: #2c3e50; /* Dark Slate */
            --light-bg: #f8f9fa;
        }

        body {
            font-family: <?php echo ($lang == 'ur') ? "'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu'" : "'Poppins'"; ?>, sans-serif;
            background-color: var(--light-bg);
            overflow-x: hidden;
            text-align: <?php echo $align; ?>;
        }

        /* Navbar Styling */
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 15px 0;
            transition: all 0.3s;
        }

        .navbar-brand {
            font-size: 1.6rem;
            font-weight: bold;
            color: #fff !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            border: 2px solid var(--secondary-color);
            margin-left: 10px; /* Switch margin for RTL */
            margin-right: 0;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-size: 0.95rem;
            margin: 0 5px;
            position: relative;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--secondary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: width 0.3s;
        }

        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }

        /* login button */
        .btn-login {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-weight: bold;
            border-radius: 50px;
            padding: 8px 25px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #fff;
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }

        /* General Typography */
        h1, h2, h3, h4, .section-title {
            font-family: 'Jameel Noori Nastaleeq', sans-serif;
        }

        /* Page Headers */
        /* Page Headers */
        .page-header {
            background: linear-gradient(rgba(0, 77, 64, 0.95), rgba(0, 77, 64, 0.85));
            padding: 100px 0 60px;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }
        
        /* Hero Section for Home Page */
        .hero-section {
            background: linear-gradient(135deg, #004d40 0%, #00251a 100%);
            color: white;
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
        }

        .page-title {
            font-size: 3.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
            padding-bottom: 10px;
        }

        /* Footer */
        footer {
            background-color: #1a1a1a;
            color: #bbb;
            padding: 50px 0 20px;
        }
        
        footer h5 {
            color: white;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            display: inline-block;
            margin-bottom: 20px;
        }

        footer a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>

    <!-- Professional Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand py-0" href="index.php">
                <div class="d-flex align-items-center">
                    <img src="logo.jpeg" height="55" class="rounded-circle shadow-sm border border-2 border-warning me-2" alt="Logo">
                    <div class="lh-1">
                        <div class="fw-bold fs-4"><?php echo $org_name; ?></div>
                        <div class="motto-text text-white" style="font-size: 0.75rem; letter-spacing: 1px; font-family: 'Jameel Noori Nastaleeq', sans-serif; opacity: 0.9;">
                            <?php echo ($lang == 'en') ? 'Unity • Respect • Management' : 'اتحاد • احترام • انصرام'; ?>
                        </div>
                    </div>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php"><?php echo $trans['home'][$lang]; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="org_reports.php"><?php echo $trans['reports'][$lang]; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="org_history.php"><?php echo $trans['history'][$lang]; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="family_tree.php"><?php echo $trans['family_tree'][$lang]; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="org_services.php"><?php echo $trans['services'][$lang]; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="members.php"><?php echo ($lang == 'en') ? 'Management' : 'انتظامیہ'; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="org_contact.php"><?php echo $trans['contact'][$lang]; ?></a></li>
            </ul>
            <div class="d-flex gap-2">
                <!-- Lang Switcher -->
                <?php if($lang == 'ur'): ?>
                    <a href="?lang=en" class="btn btn-outline-light btn-sm d-flex align-items-center">English</a>
                <?php else: ?>
                    <a href="?lang=ur" class="btn btn-outline-light btn-sm d-flex align-items-center font-nastaleeq">اردو</a>
                <?php endif; ?>
                
                <a href="login.php" class="btn btn-login"><i class="fas fa-user-lock me-2"></i> <?php echo $trans['login'][$lang]; ?></a>
            </div>
        </div>
        </div>
    </nav>

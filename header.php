<?php include 'auth_session.php'; ?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیم اولاد حضرت حاجی بہادر صاحب کوہاٹ</title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <!-- Bootstrap 5 for Admin Panel -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js for Dashboard Graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-wrapper">
        <!-- JS for Sidebar Toggle -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var parents = document.querySelectorAll(".nav-parent");
                parents.forEach(function (parent) {
                    parent.addEventListener("click", function () {
                        // Toggle logic
                        var group = this.parentElement;
                        group.classList.toggle("active");
                    });
                });
            });

            function toggleSidebar() {
                const s = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                s.classList.toggle('active');
                if(overlay) overlay.classList.toggle('active');
            }
        </script>
        <!-- Overlay for mobile click-away -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <button class="mobile-toggle no-print btn btn-dark" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <aside class="sidebar no-print">
            <img src="logo.jpeg" alt="Logo" class="sidebar-logo">
            <h2>تنظیم اولاد حاجی بہادر</h2>
            <nav>
                <a href="dashboard.php" class="dashboard-link"><i class="fas fa-tachometer-alt"></i> ڈیش بورڈ (Dashboard)</a>
                
                <!-- Collections Group -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-layer-group"></i> چندہ (Collections)
                    </div>
                    <div class="nav-children">
                        <a href="add_collection.php" class="nav-sub"><i class="fas fa-plus-circle"></i> نیا اندراج (Add New)</a>
                        <a href="collections.php" class="nav-sub"><i class="fas fa-list"></i> ریکارڈز (View All)</a>
                    </div>
                </div>
                
                <!-- Monthly Ledger (Unified) -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-file-invoice-dollar"></i> کھاتہ (Monthly Ledger)
                    </div>
                    <div class="nav-children">
                        <a href="monthly_sheet.php" class="nav-sub"><i class="fas fa-calendar-alt"></i> نیا اندراج (New Sheet)</a>
                        <a href="history.php" class="nav-sub"><i class="fas fa-history"></i> ماہانہ ریکارڈ (History)</a>
                    </div>
                </div>

                <!-- Salaries Group -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-money-check-alt"></i> ماہانہ تنخواہ (Monthly Pay)
                    </div>
                    <div class="nav-children">
                        <a href="salaries.php" class="nav-sub"><i class="fas fa-file-invoice"></i> رسیدیں (Vouchers)</a>
                        <a href="employees.php" class="nav-sub"><i class="fas fa-users"></i> ملازمین (Employees)</a>
                        <a href="templates.php" class="nav-sub"><i class="fas fa-cog"></i> ٹیمپلیٹس (Templates)</a>
                    </div>
                </div>

                <!-- Notices Group -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-bullhorn"></i> نوٹیفکیشن (Notices)
                    </div>
                    <div class="nav-children">
                        <a href="add_notice.php" class="nav-sub"><i class="fas fa-plus-circle"></i> نیا اندراج (Add New)</a>
                        <a href="notices.php" class="nav-sub"><i class="fas fa-list"></i> ریکارڈز (View All)</a>
                    </div>
                </div>

                <!-- Reports Group -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-chart-bar"></i> رپورٹس (Reports)
                    </div>
                    <div class="nav-children">
                        <a href="year_report.php" class="nav-sub"><i class="fas fa-file-invoice"></i> سالانہ رپورٹ (Annual)</a>
                        <a href="multi_year_report.php" class="nav-sub"><i class="fas fa-layer-group"></i> کثیر سالانہ (Multi-Year)</a>
                    </div>
                </div>

                <!-- Record Services Group -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-book"></i> ریکارڈ سروسز (Services)
                    </div>
                    <div class="nav-children">
                        <a href="funeral_record.php" class="nav-sub"><i class="fas fa-praying-hands"></i> تجہیز و تکفین (Funeral Record)</a>
                    </div>
                </div>

                <!-- Admin Settings -->
                <div class="nav-group">
                    <div class="nav-parent">
                        <i class="fas fa-cog"></i> سیٹنگز (Settings)
                    </div>
                    <div class="nav-children">
                        <a href="admin_shajra.php" class="nav-sub"><i class="fas fa-sitemap"></i> شجرہ نسب مینجمنٹ (Shajra CRUD)</a>
                        <a href="admin_office_bearers.php" class="nav-sub"><i class="fas fa-user-tie"></i> عہدیداران (Office Bearers)</a>
                        <a href="admin_settings.php" class="nav-sub"><i class="fas fa-cogs"></i> سیٹنگز (Settings)</a>
                    </div>
                </div>

                
                <a href="backup.php" class="dashboard-link" style="margin-top:20px; color:black;"><i class="fas fa-database"></i> ڈیٹا بیس بیک اپ (Backup)</a>
                <a href="restore.php" class="dashboard-link" style="margin-top:5px; color:black;"><i class="fas fa-trash-restore"></i> ریسٹور بیک اپ (Restore)</a>

                <a href="logout.php" class="logout-btn" style="margin-top:20px; display:block; padding:10px; background:#d9534f; color:white; text-align:center; border-radius:5px;"><i class="fas fa-sign-out-alt"></i> لاگ آؤٹ (Logout)</a>
            </nav>
        </aside>
        <div class="content-wrapper">
            <!-- Organization Header (Standard for all pages) -->
            <?php 
            function get_org_header($title = "", $subtitle = "") {
                // Priority: $_GET['year'] > $GLOBALS['year'] > date('Y')
                $year = isset($_GET['year']) ? $_GET['year'] : (isset($GLOBALS['year']) ? $GLOBALS['year'] : date('Y'));
                
                $month_label = isset($GLOBALS['selected_month_label']) ? $GLOBALS['selected_month_label'] : '';
                
                // If specific label provided in globals (like for month name), use it, otherwise rely on subtitle
                $display_subtitle = $subtitle ?: ($month_label ? "$month_label $year" : "");
                ?>
                <div class="report-header text-center mb-4" style="border-bottom: 2.5px solid #000; padding-bottom: 15px; position: relative; width: 100%;">
                    <img src="logo.jpeg" style="width: 75px; height: 75px; position: absolute; right: 0; top: 0;" class="header-logo">
                    <h1 style="font-size: 28px; margin: 0; font-weight: bold; text-align: center; font-family: 'Jameel Noori Nastaleeq', serif;">تنظیم اولاد حضرت حاجی بہادرؒ، کوہاٹ (<?php echo $year; ?>)</h1>
                    <?php if($title): ?>
                        <h3 style="margin: 8px 0; font-weight:bold; border: 2px solid #000; display: inline-block; padding: 5px 40px; border-radius: 5px; background: #f8f9fa; font-size: 20px;"><?php echo $title; ?></h3>
                    <?php endif; ?>
                    <?php if($display_subtitle): ?>
                        <div class="header-subtitle-dynamic" style="font-size: 16px; font-weight: bold; margin-top: 5px; border-top: 1px solid #eee; display: block; padding-top: 5px;"><?php echo $display_subtitle; ?></div>
                    <?php endif; ?>
                </div>
            <?php } ?>

            <div class="container">


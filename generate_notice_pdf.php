<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Record not found");
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Notice - <?php echo htmlspecialchars($data['topic']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 40px; /* Reduced top/bottom padding */
        }
        /* Reuse Report Header Styles */
        .report-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px; /* Reduced padding */
        }
        .report-title {
            font-size: 20px; /* Reduced from 26/28 */
            font-weight: bold;
            margin: 0;
        }
        
        /* Notice Specifics */
        .notice-meta-row {
            position: relative;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            min-height: 30px;
        }
        .notice-meta {
            position: absolute;
            left: 0;
            bottom: 5px;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            z-index: 2; /* Ensure date stays on top if overlap */
        }
        .notice-topic {
            width: 100%;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            padding: 0;
            position: relative;
            z-index: 1;
        }
        .notice-content {
            font-size: 18px;
            line-height: 2;
            text-align: justify;
            direction: rtl; /* Ensure correct punctuations for Urdu block */
        }
        
        @page {
            margin: 10mm 10mm 5mm 10mm; /* Reduced Bottom margin to push footer down */
            size: A4;
        }

        @media print {
            .btn, .no-print { display: none !important; }
            .container { 
                padding: 0; 
                margin: 0; 
                max-width: 100%; 
                width: 100%;
            }
            body { 
                margin: 0; 
                padding: 0;
            }
            /* Explicitly ensure footer is visible */
            #page-footer {
                display: block !important;
            }
        }

        /* HTML Content Styles */
        .notice-content p { margin-bottom: 15px; }
        .notice-content table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; /* Reduced margin further */
            font-size: 12px; /* Smaller font for table */
        }
        .notice-content table td, .notice-content table th {
            border: 1px solid #000;
            padding: 2px; /* Tighter padding */
            text-align: center;
        }
        .notice-content strong { font-weight: bold; }
        .notice-content ul, .notice-content ol { margin-right: 40px; }

        /* Signature Styles */
        .signature-container {
            margin-top: 100px; /* Increased Gap significantly to push signatures down */
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            direction: ltr; /* Always LTR to control left/right positioning */
            padding: 0 20px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div style="text-align:right; margin-bottom:20px;" class="no-print">
            <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print)</button>
        </div>

        <!-- Header -->
        <div class="report-header" style="position: relative; height: 80px; display: flex; align-items: center; justify-content: center;">
             <img src="logo.jpeg" style="width:80px; position: absolute; right: 0; top: 0;">
             <div style="text-align: center; width: 100%;">
                 <h1 class="report-title">تنظیم حضرت حاجی بہادرؒ، کوہاٹ (سال <?php echo date('Y'); ?>)</h1>
             </div>
        </div>
        
        <!-- Date & Topic Row -->
        <div class="notice-meta-row">
            <div class="notice-meta">
                تاریخ (Date): <u><?php echo date('d/m/Y', strtotime($data['notice_date'])); ?></u>
            </div>
            <div class="notice-topic">
                "<?php echo htmlspecialchars($data['topic']); ?>"
            </div>
        </div>

        <!-- Content -->
        <?php
           $dir = ($data['lang'] ?? 'ur') === 'en' ? 'ltr' : 'rtl';
           $align = ($data['lang'] ?? 'ur') === 'en' ? 'left' : 'right';
        ?>
        <div class="notice-content" style="direction: <?php echo $dir; ?>; text-align: <?php echo $align; ?>;">
            <?php echo $data['details']; // Raw HTML Logic ?>
        </div>
        
        <!-- Footer Signatures -->
        <?php
        $sig_setting = $data['signatures'] ?? 'sadr_only';
        $is_urdu = ($data['lang'] ?? 'ur') === 'ur';
        
        // Titles
        $t_sadr = $is_urdu ? 'صدر تنظیم' : 'President';
        $t_naibsadr = $is_urdu ? 'نائب صدر' : 'Vice President';
        $t_gs = $is_urdu ? 'جنرل سیکریٹری' : 'General Secretary';
        $t_joint = $is_urdu ? 'جوائنٹ سیکرٹری' : 'Joint Secretary';
        $t_finance = $is_urdu ? 'فنانس سیکرٹری' : 'Finance Secretary';
        $t_info = $is_urdu ? 'انفارمیشن سیکرٹری' : 'Information Secretary';
        
        // Logic for Members Table
        if (strpos($sig_setting, 'members') !== false) {
            // Render Members Table ABOVE signatures
            // User requested fixed list: Naib Sadar, Gen Sec, Joint Sec, Finance Sec, Info Sec
            
            $cabinet_roles = [
                ['en' => 'Vice President', 'ur' => 'نائب صدر'],
                ['en' => 'General Secretary', 'ur' => 'جنرل سیکریٹری'],
                ['en' => 'Joint Secretary', 'ur' => 'جوائنٹ سیکرٹری'],
                ['en' => 'Finance Secretary', 'ur' => 'فنانس سیکرٹری'],
                ['en' => 'Information Secretary', 'ur' => 'انفارمیشن سیکرٹری']
            ];

            // Filter out Vice President if signing below
            if ($sig_setting === 'sadr_naibsadr_members') {
                $cabinet_roles = array_filter($cabinet_roles, function($role) {
                    return $role['en'] !== 'Vice President';
                });
            }

            // Filter out General Secretary if signing below
            if ($sig_setting === 'sadr_gensec_members') {
                $cabinet_roles = array_filter($cabinet_roles, function($role) {
                    return $role['en'] !== 'General Secretary';
                });
            }

            // Filter out Joint Secretary if signing below
            if ($sig_setting === 'sadr_joint_members') {
                $cabinet_roles = array_filter($cabinet_roles, function($role) {
                    return $role['en'] !== 'Joint Secretary';
                });
            }

            // Filter out Finance Secretary if signing below
            if ($sig_setting === 'sadr_finance_members') {
                $cabinet_roles = array_filter($cabinet_roles, function($role) {
                    return $role['en'] !== 'Finance Secretary';
                });
            }

            // Filter out Info Secretary if signing below
            if ($sig_setting === 'sadr_info_members') {
               $cabinet_roles = array_filter($cabinet_roles, function($role) {
                   return $role['en'] !== 'Information Secretary';
               });
           }
            
            // Re-index array
            $cabinet_roles = array_values($cabinet_roles);
            ?>
            <div style="margin-top: 20px; margin-bottom: 20px;">
                <table style="width:100%; border-collapse:collapse; direction: <?php echo $is_urdu ? 'rtl' : 'ltr'; ?>;">
                    <thead>
                        <tr>
                            <th style="border:1px solid #000; padding:5px; width:10%;">#</th>
                            <th style="border:1px solid #000; padding:5px; width:40%;">عہدہ (Designation)</th>
                            <th style="border:1px solid #000; padding:5px; width:50%;">دستخط (Signature)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        foreach($cabinet_roles as $role): 
                            $role_title = $is_urdu ? $role['ur'] : $role['en'];
                        ?>
                        <tr>
                            <td style="border:1px solid #000; padding:10px; text-align:center;"><?php echo $count++; ?></td>
                            <td style="border:1px solid #000; text-align:center; font-weight:bold;"><?php echo $role_title; ?></td>
                            <td style="border:1px solid #000;"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>

        <div class="signature-container">
            <?php if ($is_urdu): ?>
                 <!-- Urdu Layout: Sadr on LEFT -->
                 
                 <!-- Left Side: Sadr -->
                 <div class="signature-box">
                    <div class="signature-line"><?php echo $t_sadr; ?></div>
                 </div>

                 <!-- Right Side: Secondary Signature -->
                 <?php if ($sig_setting === 'sadr_naibsadr' || $sig_setting === 'sadr_naibsadr_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_naibsadr; ?></div>
                    </div>
                 <?php elseif ($sig_setting === 'sadr_gensec' || $sig_setting === 'sadr_gensec_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_gs; ?></div>
                    </div>
                 <?php elseif ($sig_setting === 'sadr_joint' || $sig_setting === 'sadr_joint_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_joint; ?></div>
                    </div>
                 <?php elseif ($sig_setting === 'sadr_finance' || $sig_setting === 'sadr_finance_members'): ?>
                     <div class="signature-box">
                        <div class="signature-line"><?php echo $t_finance; ?></div>
                    </div>
                 <?php elseif ($sig_setting === 'sadr_info' || $sig_setting === 'sadr_info_members'): ?>
                     <div class="signature-box">
                        <div class="signature-line"><?php echo $t_info; ?></div>
                    </div>
                 <?php else: ?>
                    <!-- Spacer -->
                    <div></div> 
                 <?php endif; ?>

            <?php else: ?>
                <!-- English Layout: Sadr on RIGHT -->
                
                <!-- Left Side: Secondary Signature -->
                <?php if ($sig_setting === 'sadr_naibsadr' || $sig_setting === 'sadr_naibsadr_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_naibsadr; ?></div>
                    </div>
                <?php elseif ($sig_setting === 'sadr_gensec' || $sig_setting === 'sadr_gensec_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_gs; ?></div>
                    </div>
                <?php elseif ($sig_setting === 'sadr_joint' || $sig_setting === 'sadr_joint_members'): ?>
                    <div class="signature-box">
                        <div class="signature-line"><?php echo $t_joint; ?></div>
                    </div>
                <?php elseif ($sig_setting === 'sadr_finance' || $sig_setting === 'sadr_finance_members'): ?>
                     <div class="signature-box">
                        <div class="signature-line"><?php echo $t_finance; ?></div>
                    </div>
                <?php elseif ($sig_setting === 'sadr_info' || $sig_setting === 'sadr_info_members'): ?>
                     <div class="signature-box">
                        <div class="signature-line"><?php echo $t_info; ?></div>
                    </div>
                 <?php else: ?>
                    <div></div> 
                <?php endif; ?>

                <!-- Right Side: Sadr -->
                <div class="signature-box">
                    <div class="signature-line"><?php echo $t_sadr; ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Prepared By Footer -->
        <div id="page-footer" class="print-only text-center mt-5" style="font-size: 10px; color: #000;">
            <p class="mb-0 fw-bold">Prepared by: Tanzeem Aulad Hazrat Haji Bahadur</p>
        </div>

    </div>
    
    <style>
    @media print {
        #page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            border-top: 1px solid #000; /* Solid line */
            padding-top: 5px;
            background: white;
            margin-bottom: 2mm;
            text-align: center;
            display: block !important;
            margin-top: 0 !important; /* Override mt-5 */
        }
        .mt-5 { margin-top: 0 !important; } /* global override for print footer if needed */
    }
    </style>

    </div>
</body>
</html>

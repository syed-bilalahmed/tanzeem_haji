<?php
include 'config.php';

// Fetch All Notices
$stmt = $pdo->query("SELECT * FROM notices ORDER BY notice_date DESC");
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$notices) {
    die("No notices to print.");
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>All Notices Print</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
            margin: 0;
            padding: 0;
        }
        .print-page {
            box-sizing: border-box;
            padding: 40px;
            width: 100%;
            max-width: 800px; /* Limit width for consistency */
            margin: 0 auto;
            page-break-after: always; /* Force new page after each notice */
            position: relative;
        }
        .print-page:last-child {
            page-break-after: auto;
        }

        /* Reuse Report Header Styles */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            position: relative; 
            height: 120px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .report-title {
            font-size: 32px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .notice-meta {
            text-align: left;
            margin-bottom: 30px;
            font-size: 16px;
            font-weight: bold;
        }
        .notice-topic {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 40px;
            padding: 10px;
        }
        
        /* Content Styles from Previous Steps */
        .notice-content {
            font-size: 18px; 
            line-height: 2; 
            text-align: justify; 
            direction: rtl; 
        }
        .notice-content p { margin-bottom: 15px; }
        .notice-content table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
            font-size: 18px;
        }
        .notice-content table td, .notice-content table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        @media print {
            .no-print { display: none; }
            body { margin: 15mm 20mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed; top:10px; right:10px; z-index:1000;">
        <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print All)</button>
    </div>

    <?php foreach ($notices as $data): ?>
    <div class="print-page">
        <!-- Header -->
        <div class="report-header">
             <img src="logo.jpeg" style="width:100px; position: absolute; right: 0; top: 10px;">
             <div style="text-align: center; width: 100%;">
                 <h1 class="report-title" style="font-size: 26px;">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
             </div>
        </div>
        
        <!-- Date -->
        <div class="notice-meta">
            تاریخ (Date): <u><?php echo date('d / m / Y', strtotime($data['notice_date'])); ?></u>
        </div>

        <!-- Topic -->
        <div class="notice-topic">
            <?php echo htmlspecialchars($data['topic']); ?>
        </div>

        <!-- Content -->
        <?php
            $dir = ($data['lang'] ?? 'ur') === 'en' ? 'ltr' : 'rtl';
            $align = ($data['lang'] ?? 'ur') === 'en' ? 'left' : 'right';
        ?>
        <div class="notice-content" style="direction: <?php echo $dir; ?>; text-align: <?php echo $align; ?>;">
            <?php echo $data['details']; ?>
        </div>
        
        <!-- Footer -->
        <div style="margin-top: 30px; display:flex; justify-content: flex-end; padding-right:50px; direction: ltr;">
            <?php $sig = ($data['lang'] ?? 'ur') === 'en' ? 'President' : 'صدر تنظیم'; ?>
            <div style="text-align:center; border-top:1px solid #000; width:200px; padding-top:5px; font-weight:bold;"><?php echo $sig; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</body>
</html>

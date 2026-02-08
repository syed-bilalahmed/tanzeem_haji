<?php
include 'config.php';

if (!isset($_GET['month'])) {
    die("Invalid Request");
}

$month = $_GET['month'];
$stmt = $pdo->prepare("
    SELECT sp.*, se.phone, se.name as emp_name, se.designation 
    FROM salary_payments sp 
    LEFT JOIN salary_employees se ON sp.employee_id = se.id
    WHERE sp.payment_month = ?
");
$stmt->execute([$month]);
$slips = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$slips) {
    die("No records found for this month.");
}
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Salaries - <?php echo $month; ?></title>
    <!-- Import Noto Nastaliq Urdu -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Noto Nastaliq Urdu', serif;
            background: #fff;
            margin: 0;
            padding: 0; 
            direction: rtl;
        }
        .slip-container {
            width: 100%;
            max-width: 100%;
            height: 82mm; /* Reduced further. 3 x 82 = 246mm. Header ~30mm. Total ~276mm < 297mm. Safe. */
            box-sizing: border-box;
            padding: 5px 40px; 
            border-bottom: 1px dashed #000;
            position: relative;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: center; 
        }
        
        .slip-header {
            text-align: center;
            font-size: 18px; /* Slightly smaller */
            font-weight: bold;
            margin: 2px 0;
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        
        .slip-body {
            font-size: 18px; 
            line-height: 1.6; /* Tighter line height to fit text */
            text-align: right;
            margin: 5px 0;
            flex-grow: 1; 
        }
        
        .signature-section {
            display: flex;
            flex-direction: column; 
            align-items: flex-end; 
            margin-top: 5px;
        }
        
        .sig-block {
            text-align: center;
            margin-left: 20px; 
            width: 200px;
        }
        .sig-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 5px 0;
            padding-top: 2px;
            font-size: 14px;
        }
        
        @media print {
            .no-print { display: none; }
            @page {
                size: A4;
                margin: 5mm; 
            }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:800px; margin:0 auto 10px auto; text-align:left;">
        <button onclick="window.print()" style="padding:10px 20px; font-size:16px;">Print Slips</button>
    </div>

    <!-- Main Report Header -->
    <div class="report-header text-center mb-4" style="border-bottom: 2.5px solid #000; padding-bottom: 15px; position: relative; width: 100%; max-width: 800px; margin: 0 auto 20px auto;">
        <img src="logo.jpeg" style="width: 75px; height: 75px; position: absolute; right: 0; top: 0;" class="header-logo">
        <h1 style="font-size: 28px; margin: 0; font-weight: bold; text-align: center;">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
        <h3 style="margin: 8px 0; font-weight:bold; border: 2px solid #000; display: inline-block; padding: 5px 40px; border-radius: 5px; background: #f8f9fa;">ماہانہ تنخواہ (Salary Vouchers)</h3>
        <div style="font-size: 20px; font-weight: bold; margin-top: 5px; border-top: 1px solid #eee; display: block;">رپورٹ برائے: <?php echo $month; ?></div>
    </div>

    <?php foreach ($slips as $slip): ?>
    <div class="slip-container">
        <!-- Header -->
        <div class="slip-header">
            باعث تحریر آنکہ
        </div>
        
        <!-- Generated Text -->
        <div class="slip-body">
            <?php 
            // Replace date placeholders with actual creation date
            $formatted_date = date('d/m/Y', strtotime($slip['created_at']));
            $text = $slip['details_text'];
            
            // Regex to find things like "تاریخ: __/__/____" or similar underscores
            $text = preg_replace('/تاریخ\s*[:։]\s*[_]+(\/[_]+)?(\/[_]+)?/', 'تاریخ: ' . $formatted_date, $text);
            
            echo $text; 
            ?>
        </div>
        
        <!-- Signature Section -->
        <!-- Image shows Signature on the Left side. In RTL, "Left" is flex-end. -->
        <div class="signature-section">
            <div class="sig-block">
                <div class="sig-name"><?php echo $slip['emp_name']; ?></div>
                <div class="sig-line">
                   (<?php echo $slip['designation']; ?>)
                </div>
                <div style="font-size:18px; direction:ltr; text-align:center;">
                    <?php echo $slip['phone']; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</body>
</html>

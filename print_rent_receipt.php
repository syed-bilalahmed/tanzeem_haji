<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid request - ID not found.");
}

$id = $_GET['id'];

// Fetch the receipt data
$stmt = $pdo->prepare("
    SELECT rc.*, r.shop_no, r.shop_name, r.shopkeeper_name
    FROM rent_collections rc
    JOIN renters r ON rc.renter_id = r.id
    WHERE rc.id = ?
");
$stmt->execute([$id]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receipt) {
    die("Receipt not found.");
}

$formatted_date = date('d-m-Y', strtotime($receipt['receipt_date']));

function renderReceipt($type, $receipt, $formatted_date) {
    $title = ($type == 'office') ? '' : '<div class="copy-badge">دکاندار کاپی</div>';
    
    // Construct month string
    $month_str = htmlspecialchars($receipt['month_from']);
    if (!empty($receipt['month_to'])) {
        $month_str .= " تا " . htmlspecialchars($receipt['month_to']);
    }
    
    $total_received = number_format($receipt['amount_received']);
    $shop_info = htmlspecialchars($receipt['shop_name']);
    $shopkeeper = htmlspecialchars($receipt['shopkeeper_name']);
    
    // Simplified Receipt Paragraph (as requested by user)
    $receipt_text = "مبلغ <strong>$total_received</strong> روپے دکان <strong>$shop_info</strong> کے دکاندار <strong>$shopkeeper</strong> نے بابت کرایہ دکان برائے <strong>$month_str</strong> ادا کر دیا ہے۔";

    return '
    <div class="receipt-section">
        '.$title.'
        
        <div class="receipt-header">
            <img src="logo.jpeg" class="header-logo">
            <div class="header-text">
                <h1>تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
                <h2>دکان کرایہ کی رسید</h2>
            </div>
            <div style="width: 50px;"></div>
        </div>
        
        <div class="receipt-meta">
            <div><strong>رسید نمبر:</strong> '.($receipt['receipt_no'] ? htmlspecialchars($receipt['receipt_no']) : $receipt['id']).'</div>
            <div style="text-align:left; direction:ltr;"><strong>تاریخ:</strong> '.$formatted_date.'</div>
        </div>
        
        <div class="receipt-body">
            <p class="receipt-paragraph">
                '.$receipt_text.'
            </p>
        </div>
        
        <div class="signatures">
            <div class="sig-box">
                <div class="line"></div>
                <div class="sig-text">دستخط دکاندار</div>
            </div>
            <div class="sig-box">
                <div class="line"></div>
                <div class="sig-text">فنانس سیکرٹری</div>
            </div>
        </div>
    </div>
    ';
}
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>کرایہ رسید #<?php echo $receipt['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Nastaliq Urdu', serif;
            margin: 0;
            padding: 0;
            background: #e9ecef;
            direction: rtl;
        }
        .a4-page {
            width: 210mm;
            height: 296mm;
            background: white;
            margin: 10mm auto;
            padding: 5mm 10mm; 
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-around; /* Distribute space evenly */
        }
        .receipt-section {
            height: 46%; /* Slightly less than half to ensure no overflow */
            box-sizing: border-box;
            position: relative;
            background: #fff;
            display: flex;
            flex-direction: column;
        }
        .cut-line {
            height: 0;
            border-top: 1px dashed #999;
            margin: 0; /* Kept minimal */
            position: relative;
        }
        .cut-line::after {
            content: "✂ یہاں سے کاٹیں";
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 10px;
            color: #666;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }
        
        .copy-badge {
            position: absolute;
            top: 0;
            left: 0;
            color: #555;
            font-size: 11px;
            font-weight: bold;
            font-family: 'Noto Nastaliq Urdu', serif;
            text-decoration: underline;
        }
        
        .receipt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px; /* Added breathing room */
            margin-top: 15px; /* Pushed down from top edge slightly */
        }
        .header-logo {
            width: 45px; /* Shrunk logo further */
            height: 45px;
        }
        .header-text {
            text-align: center;
            flex-grow: 1;
        }
        .header-text h1 {
            margin: 0;
            font-size: 18px; /* Compact title */
            color: #000;
        }
        .header-text h2 {
            margin: 0;
            font-size: 13px; 
            display: inline-block;
            background: #fff;
            padding: 0 8px;
        }
        
        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .receipt-body {
            margin: 10px 0 20px 0;
            /* Removed flex-grow to stop it from pushing signatures to the very bottom */
        }
        
        .receipt-paragraph {
            font-size: 16px;
            line-height: 2.5; 
            text-align: right;
            width: 100%;
            margin: 0;
        }
        
        .notes-box {
            font-size: 13px;
            margin-top: 5px;
            color: #444;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 20px; /* Force to be closer instead of auto pushing down */
            padding-top: 15px; 
            padding-bottom: 5px; 
        }
        .sig-box {
            text-align: center;
            width: 150px; 
        }
        .sig-box .line {
            border-top: 1px solid #000;
            margin-bottom: 4px;
        }
        .sig-text {
            font-size: 13px;
            color: #000;
            font-weight: bold;
        }
        
        @media print {
            body { background: white; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .a4-page { 
                margin: 0; 
                padding: 0; 
                box-shadow: none; 
                width: 100%;
                min-height: 100%;
                page-break-after: always;
            }
            .no-print { display: none; }
        }
        
        .print-controls {
            text-align: center;
            padding: 15px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: white;
            font-family: 'Noto Nastaliq Urdu', serif;
        }
        .btn-print { background: #0d6efd; }
        .btn-print:hover { background: #0b5ed7; }
        .btn-close { background: #6c757d; margin-right: 10px; }
        .btn-close:hover { background: #5c636a; }
    </style>
</head>
<body>
    <div class="no-print print-controls">
        <button onclick="window.print()" class="btn btn-print">پرنٹ کریں (Print)</button>
        <a href="edit_rent_receipt.php?id=<?php echo $id; ?>" class="btn" style="background: #ffc107; color: #000; text-decoration: none; margin-right: 10px;">ترمیم (Edit)</a>
        <a href="delete_rent_receipt.php?id=<?php echo $id; ?>" class="btn" style="background: #dc3545; color: #fff; text-decoration: none; margin-right: 10px;" onclick="return confirm('کیا آپ واقعی یہ رسید ڈیلیٹ کرنا چاہتے ہیں؟');">ڈیلیٹ (Delete)</a>
        <button onclick="window.close()" class="btn btn-close" style="margin-right: 10px;">بند کریں</button>
    </div>

    <div class="a4-page">
        <!-- Office Copy -->
        <?php echo renderReceipt('office', $receipt, $formatted_date); ?>
        
        <!-- Cut Line -->
        <div class="cut-line"></div>
        
        <!-- Shopkeeper Copy -->
        <?php echo renderReceipt('shopkeeper', $receipt, $formatted_date); ?>
    </div>
</body>
</html>

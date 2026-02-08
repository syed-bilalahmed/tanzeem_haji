<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM incomes WHERE id = ?");
$stmt->execute([$id]);
$inc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inc) {
    die("Record not found");
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Income Receipt - <?php echo $inc['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Nastaliq Urdu', serif; background: #fff; direction: rtl; }
        .receipt-container { 
            width: 148mm; /* A5 width approx */
            margin: 20px auto; 
            border: 2px solid #000; 
            padding: 30px; 
            position: relative;
        }
        .receipt-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .receipt-title { font-size: 24px; font-weight: bold; }
        .receipt-body { font-size: 18px; line-height: 2.5; }
        .amount-row { background: #f1f1f1; padding: 10px; border: 1px solid #000; font-weight: bold; font-size: 20px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { margin: 0 auto; border: 2px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mt-3">
        <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print Receipt)</button>
    </div>

    <div class="receipt-container">
        <img src="logo.jpeg" style="width:70px; position: absolute; right: 20px; top: 20px;">
        <div class="receipt-header">
            <h1 class="receipt-title">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
            <h3>آمدن رسید (Income Receipt)</h3>
        </div>

        <div class="receipt-body">
            <div class="d-flex justify-content-between mb-3">
                <span>نمبر شمار: <strong><?php echo $inc['id']; ?></strong></span>
                <span>تاریخ: <strong><?php echo date('d-m-Y', strtotime($inc['income_date'])); ?></strong></span>
            </div>

            <p>
                تفصیل: <strong><?php echo htmlspecialchars($inc['title']); ?></strong>
            </p>

            <table class="table table-bordered text-center mt-4">
                <thead>
                    <tr>
                        <th>کیش (Cash)</th>
                        <th>درگاہ فنڈ</th>
                        <th>قبرستان فنڈ</th>
                        <th>مسجد فنڈ</th>
                        <th>عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo number_format($inc['amount']); ?></td>
                        <td><?php echo number_format($inc['dargah_fund']); ?></td>
                        <td><?php echo number_format($inc['qabristan_fund']); ?></td>
                        <td><?php echo number_format($inc['masjid_fund']); ?></td>
                        <td><?php echo number_format($inc['urs_fund']); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-5 d-flex justify-content-between pt-4">
                <div style="width:200px; text-align:center; border-top:1px solid #000;">وصول کنندہ (Receiver)</div>
                <div style="width:200px; text-align:center; border-top:1px solid #000;">صدر تنظیم (President)</div>
            </div>
        </div>
    </div>
</body>
</html>

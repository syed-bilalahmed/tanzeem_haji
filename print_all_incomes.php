<?php
include 'config.php';
// Fetch Incomes
$stmt = $pdo->query("SELECT * FROM incomes ORDER BY income_date ASC"); // Ascending for report usually
$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totals = [
    'cash' => 0,
    'dargah' => 0,
    'qabristan' => 0,
    'masjid' => 0,
    'urs' => 0
];
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Income Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Nastaliq Urdu', serif; background: #fff; direction: rtl; }
        .report-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding: 20px 0; position: relative; }
        .report-title { font-size: 26px; font-weight: bold; margin: 0; }
        .table th { background-color: #000 !important; color: #fff !important; text-align: center; border: 1px solid #000; vertical-align: middle; }
        .table td { text-align: center; border: 1px solid #000; padding: 5px; font-size: 16px; vertical-align: middle; }
        .total-row td { background-color: #f8f9fa !important; font-weight: bold; border: 2px solid #000; }
        @media print {
            .no-print { display: none; }
            .table-dark { background-color: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
            @page { margin: 10mm; size: A4; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed; top:10px; right:10px;">
        <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print)</button>
    </div>
    <div class="container-fluid mt-2">
        <div class="report-header">
             <img src="logo.jpeg" style="width:80px; position: absolute; right: 10px; top: 10px;">
             <h1 class="report-title">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
             <h3 class="mt-2">آمدن (Income Report)</h3>
        </div>

        <table class="table table-bordered" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th width="10%">تاریخ (Date)</th>
                    <th width="25%">تفصیل (Details)</th>
                    <th width="13%">کیش (Cash)</th>
                    <th width="13%">درگاہ فنڈ</th>
                    <th width="13%">قبرستان فنڈ</th>
                    <th width="13%">مسجد فنڈ</th>
                    <th width="13%">عرس فنڈ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomes as $inc): 
                    $totals['cash'] += $inc['amount'];
                    $totals['dargah'] += $inc['dargah_fund'];
                    $totals['qabristan'] += $inc['qabristan_fund'];
                    $totals['masjid'] += $inc['masjid_fund'];
                    $totals['urs'] += $inc['urs_fund'];
                ?>
                <tr>
                    <td><?php echo date('d-m-Y', strtotime($inc['income_date'])); ?></td>
                    <td style="text-align:right; padding-right:10px;"><?php echo htmlspecialchars($inc['title']); ?></td>
                    <td><?php echo number_format($inc['amount']); ?></td>
                    <td><?php echo number_format($inc['dargah_fund']); ?></td>
                    <td><?php echo number_format($inc['qabristan_fund']); ?></td>
                    <td><?php echo number_format($inc['masjid_fund']); ?></td>
                    <td><?php echo number_format($inc['urs_fund']); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align:left; font-size:20px;">کل آمدن (Total Income):</td>
                    <td><?php echo number_format($totals['cash']); ?></td>
                    <td><?php echo number_format($totals['dargah']); ?></td>
                    <td><?php echo number_format($totals['qabristan']); ?></td>
                    <td><?php echo number_format($totals['masjid']); ?></td>
                    <td><?php echo number_format($totals['urs']); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-between mt-5 pt-4" dir="rtl">
            <div style="width:250px; text-align:center;">
                <div style="border-top:2px solid #000; padding-top:5px;">فنانس سیکرٹری (Finance Secretary)</div>
            </div>
            <div style="width:250px; text-align:center;">
                <div style="border-top:2px solid #000; padding-top:5px;">صدر تنظیم (President)</div>
            </div>
        </div>
    </div>
</body>
</html>

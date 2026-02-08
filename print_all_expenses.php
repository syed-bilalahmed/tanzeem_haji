<?php
include 'config.php';
// Fetch Expenses
$stmt = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC");
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>All Expense Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Noto Nastaliq Urdu', serif; background: #fff; }
        .report-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding: 20px 0; position: relative; }
        .report-title { font-size: 26px; font-weight: bold; margin: 0; }
        .table th { background-color: #000 !important; color: #fff !important; text-align: center; border: 1px solid #000; }
        .table td { text-align: center; border: 1px solid #000; padding: 8px; font-size: 16px; }
        @media print {
            .no-print { display: none; }
            .table-dark { background-color: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed; top:10px; right:10px;">
        <button onclick="window.print()" class="btn btn-primary">Print All</button>
    </div>
    <div class="container mt-4">
        <div class="report-header">
             <img src="logo.jpeg" style="width:80px; position: absolute; right: 0; top: 10px;">
             <h1 class="report-title">تنظیم حضرت حاجی بہادرؒ، کوہاٹ (سال <?php echo date('Y'); ?>)</h1>
             <h3 class="mt-2">اخراجات ریکارڈز (Expense Records)</h3>
        </div>

        <table class="table table-bordered table-striped" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th>نمبر شمار</th>
                    <th>تاریخ (Date)</th>
                    <th>عنوان (Category)</th>
                    <th>تفصیل (Details)</th>
                    <th>رقم (Amount)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($expenses as $exp): 
                    $total += $exp['amount'];
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($exp['expense_date'])); ?></td>
                    <td><?php echo htmlspecialchars($exp['category']); ?></td>
                    <td><?php echo htmlspecialchars($exp['details']); ?></td>
                    <td><strong><?php echo number_format($exp['amount']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr class="table-secondary" style="font-weight:bold;">
                    <td colspan="4" style="text-align:right;">کل میزان (Grand Total):</td>
                    <td><?php echo number_format($total); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-between mt-5" dir="rtl">
            <div style="width:200px; text-align:center; border-top:2px solid #000;">فنانس سیکرٹری</div>
            <div style="width:200px; text-align:center; border-top:2px solid #000;">صدر تنظیم</div>
        </div>
    </div>
</body>
</html>

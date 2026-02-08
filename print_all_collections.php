<?php
include 'config.php';

// Fetch All Collections
$stmt = $pdo->query("SELECT * FROM collections ORDER BY collection_date DESC");
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$collections) {
    die("No collections found.");
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>All Collections Print</title>
    <!-- We inline the critical CSS for print perfection -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'Noto Nastaliq Urdu', serif;
            margin: 0;
            padding: 0;
        }
        /* Override Bootstrap for Print */
        @media print {
            .table-dark { 
                background-color: #000 !important; 
                color: #fff !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .table-secondary {
                background-color: #e2e3e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none; }
            body { margin: 0; }
            .print-page { page-break-after: always; margin: 0; width: 100%; height: 100%; }
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .print-page {
            box-sizing: border-box;
            padding: 20px 0;
            width: 100%;
            position: relative;
            border-bottom: 4px dashed #ccc; /* Separator for screen view */
            margin-bottom: 40px;
        }
        @media print {
            .print-page { border-bottom: none; margin-bottom: 0; }
        }
        
        /* Shared Report Styles */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 14px;
        }
        .report-table th {
            background-color: #000 !important; /* Force Black */
            color: #fff !important;           /* Force White Text */
            border: 1px solid #fff;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        .double-border-left { border-left: 3px double #000 !important; }

        .footer-signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 0 50px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed; top:10px; right:10px; z-index:1000;">
        <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print All)</button>
    </div>

    <div class="container">
    <?php foreach ($collections as $data): ?>
        <div class="print-page">
            <!-- Header -->
            <div class="report-header" style="position: relative; height: 120px; display: flex; align-items: center; justify-content: center;">
                 <img src="logo.jpeg" style="width:100px; position: absolute; right: 0; top: 10px;">
                 <div style="text-align: center; width: 100%;">
                    <h1 class="report-title" style="font-size: 26px; margin-bottom: 5px;">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
                 </div>
            </div>
            
            <div style="text-align:right; margin-top:15px; font-weight:bold; font-size:16px;">
                <span>آج مورخہ: <u><?php echo date('d / m / Y', strtotime($data['collection_date'])); ?></u> (ماہ _______________) کی آمدن کا گوشوارہ / ذیل ممبران صاحبان کی موجودگی میں کھولے گئے</span>
            </div>

            <!-- Table -->
            <table class="report-table table table-bordered table-striped text-center" dir="rtl" style="margin-top:20px;">
            <thead class="table-dark">
                <tr>
                    <th rowspan="2" style="width:5%; vertical-align:middle;">نمبر شمار</th>
                    <th rowspan="2" style="width:10%; vertical-align:middle;">نوٹ مالیت</th>
                    <th colspan="2" class="double-border-left">دربار معہ صندوق (Darbar)</th>
                    <th colspan="2" class="double-border-left">مسجد اندرون (Inside)</th>
                    <th colspan="2" class="double-border-left">مسجد بیرون (Outside)</th>
                    <th rowspan="2" style="width: 15%; vertical-align:middle;">ٹوٹل رقم<br>(Total)</th>
                </tr>
                <tr>
                    <th>تعداد</th><th class="double-border-left">رقم</th>
                    <th>تعداد</th><th class="double-border-left">رقم</th>
                    <th>تعداد</th><th class="double-border-left">رقم</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $denoms = [5000, 1000, 500, 100, 50, 20, 10];
                $totals = ['darbar_qty'=>0, 'darbar_amt'=>0, 'andron_qty'=>0, 'andron_amt'=>0, 'beron_qty'=>0, 'beron_amt'=>0, 'cross_total'=>0];
                
                // Get row totals from DB or calculate
                $row_darbar_total = $data['darbar_total'] > 0 ? $data['darbar_total'] : 0;
                $row_andron_total = $data['andron_total'] > 0 ? $data['andron_total'] : 0;
                $row_beron_total = $data['beron_total'] > 0 ? $data['beron_total'] : 0;
                
                // If DB has 0 (legacy data before this feature), we might need to calc from denoms, 
                // BUT the new feature ensures saves have totals. 
                // Let's do a hybrid: Calculate from denoms to display breakdown, but use stored total for the Final Total column.
                // Actually, if it's "manual only", denoms are 0.
                
                $calc_darbar = 0; $calc_andron = 0; $calc_beron = 0;

                $i = 1;
                foreach($denoms as $d): 
                    $qty_darbar = $data['darbar_'.$d]; $amt_darbar = $qty_darbar * $d;
                    $totals['darbar_qty'] += $qty_darbar; $totals['darbar_amt'] += $amt_darbar;
                    $calc_darbar += $amt_darbar;
                    
                    $qty_andron = $data['andron_'.$d]; $amt_andron = $qty_andron * $d;
                    $totals['andron_qty'] += $qty_andron; $totals['andron_amt'] += $amt_andron;
                    $calc_andron += $amt_andron;
                    
                    $qty_beron = $data['beron_'.$d]; $amt_beron = $qty_beron * $d;
                    $totals['beron_qty'] += $qty_beron; $totals['beron_amt'] += $amt_beron;
                    $calc_beron += $amt_beron;
                    
                    $row_total = $amt_darbar + $amt_andron + $amt_beron;
                    // Note: We are summing row_total here for the loop display, 
                    // but the Grand Total at bottom should rely on the stored totals if they differ (manual override).
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo $d; ?></strong></td>
                    <td><?php echo $qty_darbar > 0 ? $qty_darbar . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_darbar > 0 ? number_format($amt_darbar) : '-'; ?></td>
                    <td><?php echo $qty_andron > 0 ? $qty_andron . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_andron > 0 ? number_format($amt_andron) : '-'; ?></td>
                    <td><?php echo $qty_beron > 0 ? $qty_beron . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_beron > 0 ? number_format($amt_beron) : '-'; ?></td>
                    <td><strong><?php echo number_format($row_total); ?></strong></td>
                </tr>
                <?php endforeach; 
                // Final Logic: If calc is 0 but stored is > 0, use stored.
                // Or simply: Use stored total if it exists, otherwise calc.
                // But wait, the loop outputted rows based on denoms. If denoms are 0, rows show 0.
                // The user wants to see the TOTAL.
                
                $final_darbar = ($calc_darbar > 0) ? $calc_darbar : $row_darbar_total;
                $final_andron = ($calc_andron > 0) ? $calc_andron : $row_andron_total;
                $final_beron = ($calc_beron > 0) ? $calc_beron : $row_beron_total;
                
                $grand_final = $final_darbar + $final_andron + $final_beron;
                ?>
                <tr class="table-secondary" style="font-weight:bold;">
                     <td colspan="2">کل میزان</td>
                     <td><?php echo number_format($totals['darbar_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_darbar); ?></td>
                     <td><?php echo number_format($totals['andron_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_andron); ?></td>
                     <td><?php echo number_format($totals['beron_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_beron); ?></td>
                     <td><?php echo number_format($grand_final); ?></td>
                </tr>
            </tbody>
            </table>

            <div style="margin-top:20px; text-align:right; font-size:24px; font-weight:bold; padding:15px; border:2px solid #000; background-color:#f9f9f9;">
                کل میزان (Grand Total): <?php echo number_format($grand_final); ?> روپے
            </div>

            <div class="footer-signatures">
                <div style="width:200px; text-align:center; border-top:2px solid #000; padding-top:5px;">فنانس سیکرٹری</div>
                <div style="width:200px; text-align:center; border-top:2px solid #000; padding-top:5px;">صدر تنظیم</div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</body>
</html>

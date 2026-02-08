<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Record not found");
}

function get_row_total($data, $denom) {
    // helper to calculate total for a specific row across all 3 locs? No, usually columns are independent.
    // Actually the user wants 3 separate columns.
    return 0; // Not needed
}

?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Monthly Report</title>
    <!-- We inline the critical CSS for print perfection -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'Noto Nastaliq Urdu', serif;
        }
        /* Override Bootstrap for Print */
        @media print {
            .table-dark { 
                background-color: #212529 !important; 
                color: #fff !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .table-secondary {
                background-color: #e2e3e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .report-subtitle {
            font-size: 18px;
            margin: 5px 0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
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
            border: 1px solid #fff;           /* White border for contrast */
            padding: 5px;
            text-align: center;
            font-size: 14px;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        /* Grid specific to the image provided */
        .double-border-left {
            border-left: 3px double #000 !important;
        }
        .double-border-right {
            border-right: 3px double #000 !important;
        }
        
        /* Layout for Officials lower table */
        .officials-table {
            width: 100%;
            margin-top: 20px;
            border: 2px solid #000;
        }
        .officials-table td, .officials-table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .footer-signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 0 50px;
        }
        .footer-signatures div {
            border-top: 2px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }
        
        /* Column Widths */
        .col-sno { width: 5%; }
        .col-denom { width: 10%; }
        .col-qty { width: 10%; }
        .col-amt { width: 15%; }
        
        @media print {
            .btn { display: none; }
            .container { padding: 0; margin: 0; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div style="text-align:right; margin-bottom:10px;" class="no-print">
            <button onclick="window.print()" class="btn btn-primary">پرنٹ کریں (Print)</button>
        </div>

        <!-- Header from Image -->
        <div class="report-header" style="position: relative; height: 120px; display: flex; align-items: center; justify-content: center;">
             <img src="logo.jpeg" style="width:100px; position: absolute; right: 0; top: 10px;">
             
             <div style="text-align: center; width: 100%;">
                <?php 
                $col_time = strtotime($data['collection_date']);
                $year = date('Y', $col_time);
                $month_num = date('n', $col_time);
                
                $urdu_months = [
                    1 => 'جنوری', 2 => 'فروری', 3 => 'مارچ', 4 => 'اپریل', 
                    5 => 'مئی', 6 => 'جون', 7 => 'جولائی', 8 => 'اگست', 
                    9 => 'ستمبر', 10 => 'اکتوبر', 11 => 'نومبر', 12 => 'دسمبر'
                ];
                $month_name = $urdu_months[$month_num];
                ?>
                <h1 class="report-title" style="font-size: 26px; margin-bottom: 5px;">تنظیم حضرت حاجی بہادرؒ، کوہاٹ (سال <?php echo $year; ?>)</h1>
             </div>
        </div>
            <div style="text-align:right; margin-top:15px; font-weight:bold; font-size:16px;">
                <span>آج مورخہ: <u><?php echo date('d / m / Y', $col_time); ?></u> (ماہ <?php echo $month_name; ?>) کی آمدن کا گوشوارہ / ذیل ممبران صاحبان کی موجودگی میں کھولے گئے</span>
            </div>
        </div>

        <!-- Main Data Table -->
        <table class="report-table table table-bordered table-striped text-center" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <!-- Serial & Note (Rightmost in RTL) -->
                    <th rowspan="2" style="width:5%; vertical-align:middle;">نمبر شمار</th>
                    <th rowspan="2" style="width:10%; vertical-align:middle;">نوٹ مالیت</th>

                    <!-- Darbar -->
                    <th colspan="2" class="double-border-left">دربار معہ صندوق (Darbar)</th>
                    
                    <!-- Andron -->
                    <th colspan="2" class="double-border-left">مسجد اندرون (Inside)</th>
                    
                    <!-- Beron -->
                    <th colspan="2" class="double-border-left">مسجد بیرون (Outside)</th>
                    
                    <!-- Total (Leftmost in RTL) -->
                    <th rowspan="2" style="width: 15%; vertical-align:middle;">ٹوٹل رقم<br>(Total)</th>
                </tr>
                <tr>
                    <!-- Darbar -->
                    <th>تعداد</th>
                    <th class="double-border-left">رقم</th>

                    <!-- Andron -->
                    <th>تعداد</th>
                    <th class="double-border-left">رقم</th>

                    <!-- Beron -->
                    <th>تعداد</th>
                    <th class="double-border-left">رقم</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $denoms = [5000, 1000, 500, 100, 50, 20, 10];
                $totals = [
                    'darbar_qty' => 0, 'darbar_amt' => 0,
                    'andron_qty' => 0, 'andron_amt' => 0,
                    'beron_qty' => 0, 'beron_amt' => 0,
                    'cross_total' => 0
                ];
                
                $i = 1;
                foreach($denoms as $d): 
                    // Calculations
                    $qty_darbar = $data['darbar_'.$d];
                    $amt_darbar = $qty_darbar * $d;
                    $totals['darbar_qty'] += $qty_darbar;
                    $totals['darbar_amt'] += $amt_darbar;
                    
                    $qty_andron = $data['andron_'.$d];
                    $amt_andron = $qty_andron * $d;
                    $totals['andron_qty'] += $qty_andron;
                    $totals['andron_amt'] += $amt_andron;
                    
                    $qty_beron = $data['beron_'.$d];
                    $amt_beron = $qty_beron * $d;
                    $totals['beron_qty'] += $qty_beron;
                    $totals['beron_amt'] += $amt_beron;
                    
                    $row_total = $amt_darbar + $amt_andron + $amt_beron;
                    $totals['cross_total'] += $row_total;
                ?>
                <tr>
                    <!-- Serial & Note (Right in RTL) -->
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo $d; ?></strong></td>

                    <!-- Darbar -->
                    <td><?php echo $qty_darbar > 0 ? $qty_darbar . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_darbar > 0 ? number_format($amt_darbar) : '-'; ?></td>

                    <!-- Andron -->
                    <td><?php echo $qty_andron > 0 ? $qty_andron . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_andron > 0 ? number_format($amt_andron) : '-'; ?></td>

                    <!-- Beron -->
                    <td><?php echo $qty_beron > 0 ? $qty_beron . ' x' : '-'; ?></td>
                    <td class="double-border-left"><?php echo $amt_beron > 0 ? number_format($amt_beron) : '-'; ?></td>

                    <!-- Total (Left in RTL) -->
                    <td><strong><?php echo number_format($row_total); ?></strong></td>
                </tr>
                <?php endforeach; 
                // Fallback Logic for Zero Denominations
                $row_darbar_total = $data['darbar_total'] > 0 ? $data['darbar_total'] : 0;
                $row_andron_total = $data['andron_total'] > 0 ? $data['andron_total'] : 0;
                $row_beron_total = $data['beron_total'] > 0 ? $data['beron_total'] : 0;

                $final_darbar = ($totals['darbar_amt'] > 0) ? $totals['darbar_amt'] : $row_darbar_total;
                $final_andron = ($totals['andron_amt'] > 0) ? $totals['andron_amt'] : $row_andron_total;
                $final_beron = ($totals['beron_amt'] > 0) ? $totals['beron_amt'] : $row_beron_total;
                
                $grand_final = $final_darbar + $final_andron + $final_beron;
                ?>
                
                <!-- Totals Row -->
                <tr class="table-secondary" style="font-weight:bold;">
                     <td colspan="2">کل میزان</td>
                     
                     <!-- Darbar -->
                     <td><?php echo number_format($totals['darbar_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_darbar); ?></td>
                     
                     <!-- Andron -->
                     <td><?php echo number_format($totals['andron_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_andron); ?></td>
                     
                     <!-- Beron -->
                     <td><?php echo number_format($totals['beron_qty']); ?></td>
                     <td class="double-border-left"><?php echo number_format($final_beron); ?></td>

                     <!-- Grand -->
                     <td><?php echo number_format($grand_final); ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Grand Total Summary -->
        <div style="margin-top:20px; text-align:right; font-size:24px; font-weight:bold; padding:15px; border:2px solid #000; background-color:#f9f9f9;">
            کل میزان (Grand Total): <?php echo number_format($grand_final); ?> روپے
        </div>

        <!-- Officials Table -->
        <table class="officials-table">
            <thead>
                <tr>
                    <th>نمبر شمار</th>
                    <th>عہدہ</th>
                    <th>نام</th>
                    <th>دستخط</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>نائب صدر</td>
                    <td><?php echo htmlspecialchars($data['naib_saddar']); ?></td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>جنرل سیکرٹری	</td>
                    <td><?php echo htmlspecialchars($data['general_secretary']); ?></td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Other 1</td>
                    <td><?php echo htmlspecialchars($data['joint_secretary']); ?></td>
                    <td>_________________</td>
                </tr>
                 <tr>
                    <td>4</td>
                    <td>انفارمیشن سیکرٹری</td>
                    <td><?php echo htmlspecialchars($data['information_secretary']); ?></td>
                    <td>_________________</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-signatures">
            <div style="width:200px; text-align:center; border-top:2px solid #000; padding-top:5px;">
                فنانس سیکرٹری
            </div>
            <div style="width:200px; text-align:center; border-top:2px solid #000; padding-top:5px;">
                صدر تنظیم
            </div>
        </div>

    </div>
</body>
</html>

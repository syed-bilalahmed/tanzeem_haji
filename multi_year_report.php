<?php
include 'config.php';
include 'header.php';

$start_year = isset($_GET['start_year']) ? $_GET['start_year'] : date('Y');
$end_year = isset($_GET['end_year']) ? $_GET['end_year'] : date('Y') + 4; // Default 5 year range

if ($end_year < $start_year) $end_year = $start_year;

// --- DATA PREPARATION ---
$years_data = [];
$grand_total_inc = 0;
$grand_total_exp = 0;
// Funds Aggregation across all years
$total_funds_inc = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];
$total_funds_exp = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];
$total_cat_col = ['darbar'=>0, 'indoor'=>0, 'outdoor'=>0];
$grand_total_col = 0;

for ($y = $start_year; $y <= $end_year; $y++) {
    // 1. Collections
    $st = $pdo->prepare("SELECT * FROM collections WHERE YEAR(collection_date) = ?");
    $st->execute([$y]);
    $cols = $st->fetchAll();
    
    $y_darbar = 0;
    $y_indoor = 0;
    $y_outdoor = 0;

    foreach($cols as $col) {
        $c_darbar = ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
        $c_indoor = ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
        $c_outdoor = ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
        
        // Fallback Logic
        if ($c_darbar == 0 && $col['darbar_total'] > 0) $c_darbar = $col['darbar_total'];
        if ($c_indoor == 0 && $col['andron_total'] > 0) $c_indoor = $col['andron_total'];
        if ($c_outdoor == 0 && $col['beron_total'] > 0) $c_outdoor = $col['beron_total'];

        $y_darbar += $c_darbar;
        $y_indoor += $c_indoor;
        $y_outdoor += $c_outdoor;
    }
    $y_col = $y_darbar + $y_indoor + $y_outdoor;

    // 2. Incomes
    $st = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM incomes WHERE YEAR(income_date) = ?");
    $st->execute([$y]);
    $inc = $st->fetch();

    // 3. Expenses
    $st = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM expenses WHERE YEAR(expense_date) = ?");
    $st->execute([$y]);
    $exp = $st->fetch();

    // Calculations
    $y_inc_total = ($inc['cash'] ?: 0);
    $y_exp_total = ($exp['cash'] ?: 0);

    $years_data[$y] = [
        'col' => $y_col,
        'inc_total' => $y_inc_total,
        'exp_total' => $y_exp_total,
        'net' => $y_inc_total - $y_exp_total,
        'cat_col' => [
            'darbar' => $y_darbar,
            'indoor' => $y_indoor,
            'outdoor' => $y_outdoor
        ],
        'funds_inc' => [
            'cash' => ($inc['cash']?:0), 
            'dargah' => $inc['dargah']?:0,
            'qabristan' => $inc['qabristan']?:0,
            'masjid' => $inc['masjid']?:0,
            'urs' => $inc['urs']?:0
        ],
        'funds_exp' => [
            'cash' => $exp['cash']?:0,
            'dargah' => $exp['dargah']?:0,
            'qabristan' => $exp['qabristan']?:0,
            'masjid' => $exp['masjid']?:0,
            'urs' => $exp['urs']?:0
        ]
    ];

    $grand_total_inc += $y_inc_total;
    $grand_total_exp += $y_exp_total;
    
    // Summing up totals
    foreach(['cash','dargah','qabristan','masjid','urs'] as $f) {
        $total_funds_inc[$f] += $years_data[$y]['funds_inc'][$f];
        $total_funds_exp[$f] += $years_data[$y]['funds_exp'][$f];
    }

    foreach(['darbar','indoor','outdoor'] as $c) {
        $total_cat_col[$c] += $years_data[$y]['cat_col'][$c];
    }
    $grand_total_col += $years_data[$y]['col'];
}
?>

<div class="card shadow-sm" dir="rtl">
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">کثیر سالانہ رپورٹ (Multi-Year Report)</h3>
        <div class="d-flex gap-2 align-items-end">
            <form class="d-flex gap-2">
                <div>
                    <label class="small text-muted">سے (From)</label>
                    <select name="start_year" class="form-control form-control-sm">
                        <?php for($y=2024;$y<=2035;$y++) echo "<option value='$y' ".($start_year==$y?'selected':'').">$y</option>"; ?>
                    </select>
                </div>
                <div>
                    <label class="small text-muted">تک (To)</label>
                    <select name="end_year" class="form-control form-control-sm">
                        <?php for($y=2024;$y<=2035;$y++) echo "<option value='$y' ".($end_year==$y?'selected':'').">$y</option>"; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-dark mb-0 align-self-end">دکھائیں</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-primary mb-0 align-self-end"><i class="fas fa-print"></i> پرنٹ</button>
        </div>
    </div>

    <!-- PRINT HEADER -->
    <div class="print-only">
        <?php get_org_header("کثیر سالانہ رپورٹ (Multi-Year Report)", "$start_year - $end_year"); ?>
    </div>

    <!-- 1. ANNUAL OVERVIEW -->
    <h4 class="mt-3 mb-3 border-bottom pb-2">سالانہ خلاصہ (Annual Overview)</h4>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle" style="border: 2px solid #000;">
            <thead class="bg-light fw-bold">
                <tr>
                    <th>سال (Year)</th>
                    <th>چندہ (Collections)</th>
                    <th>کل آمدنی (Total Income)</th>
                    <th>کل اخراجات (Total Expenses)</th>
                    <th>میزان (Net Balance)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($years_data as $y => $d): ?>
                <tr>
                    <td class="fw-bold"><?php echo $y; ?></td>
                    <td><?php echo number_format($d['col']); ?></td>
                    <td><?php echo number_format($d['inc_total']); ?></td>
                    <td class="text-danger"><?php echo number_format($d['exp_total']); ?></td>
                    <td class="fw-bold <?php echo $d['net']>=0?'text-success':'text-danger'; ?>"><?php echo number_format($d['net']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-dark text-white fw-bold">
                <tr>
                    <td>ٹوٹل (Totals)</td>
                    <td>-</td>
                    <td><?php echo number_format($grand_total_inc); ?></td>
                    <td><?php echo number_format($grand_total_exp); ?></td>
                    <td><?php echo number_format($grand_total_inc - $grand_total_exp); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- 1b. COLLECTIONS BREAKDOWN -->
    <h4 class="mt-4 mb-3 border-bottom pb-2">چندہ کی تفصیل (Collections Breakdown)</h4>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle" style="border: 2px solid #000;">
            <thead class="bg-light fw-bold">
                <tr>
                    <th>سال (Year)</th>
                    <th>دربار (Darbar)</th>
                    <th>اندرون (Indoor)</th>
                    <th>بیرون (Outdoor)</th>
                    <th>کل چندہ (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($years_data as $y => $d): ?>
                <tr>
                    <td class="fw-bold"><?php echo $y; ?></td>
                    <td><?php echo number_format($d['cat_col']['darbar']); ?></td>
                    <td><?php echo number_format($d['cat_col']['indoor']); ?></td>
                    <td><?php echo number_format($d['cat_col']['outdoor']); ?></td>
                    <td class="fw-bold"><?php echo number_format($d['col']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-dark text-white fw-bold">
                <tr>
                    <td>ٹوٹل (Totals)</td>
                    <td><?php echo number_format($total_cat_col['darbar']); ?></td>
                    <td><?php echo number_format($total_cat_col['indoor']); ?></td>
                    <td><?php echo number_format($total_cat_col['outdoor']); ?></td>
                    <td><?php echo number_format($grand_total_col); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Page 1 Footer -->
    <div class="d-flex justify-content-between print-footer mt-2" style="font-size: 10pt;">
        <div></div>
        <div class="fw-bold">Page 1 of 2</div>
    </div>

    <div class="page-break"></div>

    <!-- 2. DETAILS: WHERE IT IS USED (EXPENSES) -->
    <h4 class="mt-4 mb-3 border-bottom pb-2 text-danger">اخراجات کی تفصیل - کہاں استعمال ہوا (Annual Expenses Breakdown)</h4>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle" style="border: 2px solid #000;">
            <thead class="bg-light fw-bold">
                <tr>
                    <th>سال (Year)</th>
                    <th>کیش (Cash)</th>
                    <th>درگاہ فنڈ</th>
                    <th>قبرستان فنڈ</th>
                    <th>مسجد فنڈ</th>
                    <th>عرس فنڈ</th>
                    <th>کل خرچہ (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($years_data as $y => $d): ?>
                <tr>
                    <td class="fw-bold"><?php echo $y; ?></td>
                    <td><?php echo number_format($d['funds_exp']['cash']); ?></td>
                    <td><?php echo number_format($d['funds_exp']['dargah']); ?></td>
                    <td><?php echo number_format($d['funds_exp']['qabristan']); ?></td>
                    <td><?php echo number_format($d['funds_exp']['masjid']); ?></td>
                    <td><?php echo number_format($d['funds_exp']['urs']); ?></td>
                    <td class="fw-bold text-danger"><?php echo number_format($d['exp_total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-secondary text-white fw-bold">
                <tr>
                    <td>میزان (Totals)</td>
                    <?php foreach(['cash','dargah','qabristan','masjid','urs'] as $f) echo "<td>".number_format($total_funds_exp[$f])."</td>"; ?>
                    <td><?php echo number_format($grand_total_exp); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- 3. DETAILS: INCOME SOURCES -->
    <h4 class="mt-4 mb-3 border-bottom pb-2 text-success">آمدن کی تفصیل - ذرائع (Annual Income Breakdown)</h4>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle" style="border: 2px solid #000;">
            <thead class="bg-light fw-bold">
                <tr>
                    <th>سال (Year)</th>
                    <th>کیش + چندہ</th>
                    <th>درگاہ فنڈ</th>
                    <th>قبرستان فنڈ</th>
                    <th>مسجد فنڈ</th>
                    <th>عرس فنڈ</th>
                    <th>کل آمدن (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($years_data as $y => $d): ?>
                <tr>
                    <td class="fw-bold"><?php echo $y; ?></td>
                    <td><?php echo number_format($d['funds_inc']['cash']); ?></td>
                    <td><?php echo number_format($d['funds_inc']['dargah']); ?></td>
                    <td><?php echo number_format($d['funds_inc']['qabristan']); ?></td>
                    <td><?php echo number_format($d['funds_inc']['masjid']); ?></td>
                    <td><?php echo number_format($d['funds_inc']['urs']); ?></td>
                    <td class="fw-bold text-success"><?php echo number_format($d['inc_total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-secondary text-white fw-bold">
                <tr>
                    <td>میزان (Totals)</td>
                    <?php foreach(['cash','dargah','qabristan','masjid','urs'] as $f) echo "<td>".number_format($total_funds_inc[$f])."</td>"; ?>
                    <td><?php echo number_format($grand_total_inc); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- SIGNATURES -->
    <div class="print-only" style="margin-top: 100px !important;">
        <div style="display: flex !important; justify-content: space-between !important; width: 100% !important; align-items: flex-end !important;">
            <div style="width: 250px; text-align: center; border-top: 2px solid #000; padding-top: 10px; font-weight: bold; margin-left: 20px;">
                فنانس سیکرٹری (Finance Secretary)<br>
                Syed Afiat Hussain Shah
            </div>
            <div style="width: 250px; text-align: center; border-top: 2px solid #000; padding-top: 10px; font-weight: bold; margin-right: 20px;">
                صدر تنظیم (President)<br>
                Col Kamil Shah ( R )
            </div>
        </div>
    </div>

    <!-- Prepared By Footer Removed -->
    <div></div>

    <!-- Page 2 Footer -->
    <div class="d-flex justify-content-between print-footer mt-2" style="font-size: 10pt;">
        <div></div>
        <div class="fw-bold">Page 2 of 2</div>
    </div>
</div>

<style>
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    /* Reset global borders in print to avoid side lines */
    /* Reset global borders in print to avoid side lines */
    html, body, .main-wrapper, .content-wrapper, .container, .card {
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        overflow: visible !important;
        background-color: #fff !important;
        background: #fff !important;
    }
    
    /* Reduce top padding/margin for header optimization */
    .print-only { margin-top: 0 !important; padding-top: 0 !important; }
    
    /* Optimize shared header for this report */
    .report-header {
        margin-bottom: 10px !important;
        padding-bottom: 5px !important;
        border-bottom-width: 2px !important;
    }
    .report-header h1 { font-size: 22pt !important; margin: 0 !important; }
    .report-header h3 { font-size: 16pt !important; margin: 5px 0 !important; padding: 2px 20px !important; }
    .report-header img { width: 50px !important; height: 50px !important; } /* Smaller logo */
    
    .card { border: none !important; box-shadow: none !important; }
    
    /* Force strict black borders */
    .table-bordered, .table-bordered th, .table-bordered td { 
        border: 1px solid #000 !important; 
        border-color: #000 !important;
    }
    
    /* Avoid breaking inside tables */
    table, tr, td, th, tbody, thead, tfoot {
        page-break-inside: avoid !important;
    }
    
    /* Force Bold Totals */
    .fw-bold, strong, b, th, tfoot td, tr.fw-bold td {
        font-weight: 900 !important;
        color: #000 !important;
    }

    /* Make Total Rows Larger - Reduced slightly from 12pt to save space */
    tfoot td, tr.fw-bold td {
        font-size: 11pt !important;
    }

    /* Main Header Styling for Print - Reduced sizes */
    h1 { font-size: 20pt !important; margin-bottom: 5px !important; }
    h3 { font-size: 14pt !important; margin-bottom: 5px !important; }
    h4 { font-size: 12pt !important; margin-top: 10px !important; margin-bottom: 5px !important; }

    /* Remove Background Colors for Print */
    .bg-dark, .bg-light, .table-dark, .table-striped > tbody > tr:nth-of-type(odd) > * { 
        background-color: transparent !important; 
        color: #000 !important; 
        box-shadow: none !important;
    }
}
</style>

<?php include 'footer.php'; ?>

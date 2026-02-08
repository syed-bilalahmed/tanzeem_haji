<?php
include 'config.php';
include 'header.php';

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch organization info for header
// (get_org_header is available)

// DATA PREPARATION
$monthly_data = [];
$grand_total_inc = 0;
$grand_total_exp = 0;
$annual_funds_inc = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];
$annual_funds_exp = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];

// Category wise andrun/berun/darbar
$annual_cat_col = ['darbar'=>0, 'indoor'=>0, 'outdoor'=>0];

for($m=1; $m<=12; $m++) {
    $month_name = date('F', mktime(0, 0, 0, $m, 1));
    
    // Collections
    $st = $pdo->prepare("SELECT * FROM collections WHERE MONTH(collection_date) = ? AND YEAR(collection_date) = ?");
    $st->execute([$m, $year]);
    $cols = $st->fetchAll();
    $m_col = 0;
    foreach($cols as $col) {
        $c_darbar = ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
        $c_indoor = ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
        $c_outdoor = ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
        
        // Fallback Logic: If denomination calculation is 0, use total columns
        if ($c_darbar == 0 && $col['darbar_total'] > 0) $c_darbar = $col['darbar_total'];
        if ($c_indoor == 0 && $col['andron_total'] > 0) $c_indoor = $col['andron_total'];
        if ($c_outdoor == 0 && $col['beron_total'] > 0) $c_outdoor = $col['beron_total'];

        $m_col += ($c_darbar + $c_indoor + $c_outdoor);
        $annual_cat_col['darbar'] += $c_darbar;
        $annual_cat_col['indoor'] += $c_indoor;
        $annual_cat_col['outdoor'] += $c_outdoor;
    }
    
    // Incomes
    $st = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ?");
    $st->execute([$m, $year]);
    $inc = $st->fetch();
    
    // Expenses
    $st = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $st->execute([$m, $year]);
    $exp = $st->fetch();
    
    $m_inc_total = ($inc['cash'] ?: 0);
    $m_exp_total = ($exp['cash'] ?: 0);
    
    $monthly_data[] = [
        'name' => $month_name,
        'col' => $m_col,
        'inc' => $m_inc_total,
        'exp' => $m_exp_total,
        'net' => $m_inc_total - $m_exp_total
    ];
    
    $grand_total_inc += $m_inc_total;
    $grand_total_exp += $m_exp_total;
    
    // Fund Breakdown
    $annual_funds_inc['cash'] += ($inc['cash'] ?: 0);
    $annual_funds_inc['dargah'] += $inc['dargah'] ?: 0;
    $annual_funds_inc['qabristan'] += $inc['qabristan'] ?: 0;
    $annual_funds_inc['masjid'] += $inc['masjid'] ?: 0;
    $annual_funds_inc['urs'] += $inc['urs'] ?: 0;
    
    foreach(['cash','dargah','qabristan','masjid','urs'] as $f) $annual_funds_exp[$f] += $exp[$f] ?: 0;
}

// Calculate Grand Total Collection
$grand_total_col = 0;
foreach($monthly_data as $md) {
    $grand_total_col += $md['col'];
}
?>

<div class="card shadow-sm" dir="rtl">
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">سالانہ گوشوارہ رپورٹ (Annual Financial Report)</h3>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2">
                <select name="year" class="form-control form-control-sm">
                    <?php for($y=2024;$y<=2030;$y++) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>"; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-dark">منتخب کریں</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="fas fa-print"></i> پرنٹ کریں</button>
            <a href="multi_year_report.php" class="btn btn-sm btn-outline-dark">کثیر سالانہ (Multi-Year)</a>
        </div>
        <div class="form-check form-switch ms-3">
             <input class="form-check-input" type="checkbox" id="show_fs" checked onchange="toggleFS()">
             <label class="form-check-label fw-bold" for="show_fs">فنانس سیکرٹری دستخط (Show Finance Sec)</label>
        </div>
    </div>

    <div class="print-only">
        <?php get_org_header("سالانہ گوشوارہ رپورٹ (Annual Report)"); ?>
    </div>

    <div class="table-responsive mt-3">
        <!-- Removed inline border style -->
        <table class="table table-bordered text-center align-middle">
            <!-- Removed inline border-bottom style -->
            <thead class="bg-light fw-bold" style="font-size: 1.1em;">
                <tr>
                    <th>ماہ (Month)</th>
                    <th>چندہ (Collections)</th>
                    <th>کل آمدنی (Total Income)</th>
                    <th>کل اخراجات (Total Expenses)</th>
                    <th>ماہانہ میزان (Monthly Net)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($monthly_data as $m): ?>
                <tr>
                    <td class="fw-bold"><?php echo $m['name']; ?></td>
                    <td><?php echo number_format($m['col']); ?></td>
                    <td><?php echo number_format($m['inc']); ?></td>
                    <td class="text-danger"><?php echo number_format($m['exp']); ?></td>
                    <td class="fw-bold <?php echo $m['net']>=0 ? 'text-success':'text-danger'; ?>">
                        <?php echo number_format($m['net']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-dark text-white fw-bold" style="font-size: 1.2em;">
                <tr>
                    <td>میزان کل (Annual Totals)</td>
                    <td><?php echo number_format($grand_total_col); ?></td>
                    <td><?php echo number_format($grand_total_inc); ?></td>
                    <td><?php echo number_format($grand_total_exp); ?></td>
                    <td><?php echo number_format($grand_total_inc - $grand_total_exp); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Page 1 Footer -->
    <div class="d-flex justify-content-between print-footer mt-2" style="font-size: 10pt;">
        <div></div>
        <div class="fw-bold">Page 1 of 2</div>
    </div>

    <!-- Page Break for Summary Section -->
    <div class="page-break"></div>

    <div class="summary-section mt-4">
        <!-- Fund Status Table -->
        <h5 class="mb-3 pb-2">فنڈز کی سالانہ صورتحال (Annual Fund Status)</h5>
        <table class="table table-sm table-bordered border-dark mb-4">
            <thead class="bg-light">
                <tr>
                    <th>فنڈ (Fund)</th>
                    <th>آمدن</th>
                    <th>خرچہ</th>
                    <th>میزان</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $tot_inc = 0; $tot_exp = 0; $tot_net = 0;
                foreach(['cash','dargah','qabristan','masjid','urs'] as $f): 
                    $net = $annual_funds_inc[$f] - $annual_funds_exp[$f];
                    $tot_inc += $annual_funds_inc[$f];
                    $tot_exp += $annual_funds_exp[$f];
                    $tot_net += $net;
                ?>
                <tr>
                    <td class="text-capitalize"><?php echo $f; ?></td>
                    <td><?php echo number_format($annual_funds_inc[$f]); ?></td>
                    <td><?php echo number_format($annual_funds_exp[$f]); ?></td>
                    <td class="fw-bold"><?php echo number_format($net); ?></td>
                </tr>
                <?php endforeach; ?>
                <!-- Total Row -->
                <tr class="bg-light fw-bold" style="border-top: 2px solid #000 !important;">
                    <td>کل (Total)</td>
                    <td><?php echo number_format($tot_inc); ?></td>
                    <td><?php echo number_format($tot_exp); ?></td>
                    <td><?php echo number_format($tot_net); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Collections Breakdown Table -->
        <h5 class="mb-3 pb-2 mt-4">چندہ کی تفصیل (Collections Breakdown)</h5>
        <table class="table table-sm table-bordered border-dark">
            <thead class="bg-light">
                <tr>
                    <th>کیٹیگری (Category)</th>
                    <th>سالانہ رقم (Annual Amount)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>دربار معہ (Darbar)</td>
                    <td class="fw-bold"><?php echo number_format($annual_cat_col['darbar']); ?></td>
                </tr>
                <tr>
                    <td>مسجد اندرون (Masjid Indoor)</td>
                    <td class="fw-bold"><?php echo number_format($annual_cat_col['indoor']); ?></td>
                </tr>
                <tr>
                    <td>مسجد بیرون (Masjid Outdoor)</td>
                    <td class="fw-bold"><?php echo number_format($annual_cat_col['outdoor']); ?></td>
                </tr>
                <tr class="bg-light fw-bold" style="border-top: 2px solid #000 !important;">
                    <td>کل چندہ (Total)</td>
                    <td><?php echo number_format($annual_cat_col['darbar'] + $annual_cat_col['indoor'] + $annual_cat_col['outdoor']); ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Net Performance - Centered Below -->
        <div class="text-center py-4">
            <h4 class="mb-2">کل سالانہ بچت / خسارہ</h4>
            <h2 class="mb-0 fw-black"><?php echo number_format($grand_total_inc - $grand_total_exp); ?></h2>
            <small>(Net Annual Performance)</small>
        </div>

        <!-- Page 2 Footer -->
        <div class="d-flex justify-content-between print-footer mt-5" style="font-size: 10pt;">
            <div></div>
            <div class="fw-bold">Page 2 of 2</div>
        </div>
    </div>

    </div>

    <!-- SIGNATURES -->
    <div class="print-only" style="margin-top: 80px !important;">
        <div style="display: flex !important; justify-content: space-between !important; width: 100% !important; align-items: flex-end !important;">
            <div class="fs-sig" style="width: 250px; text-align: center; border-top: 2px solid #000; padding-top: 10px; font-weight: bold; margin-left: 20px;">
                فنانس سیکرٹری (Finance Secretary)<br>
                Syed Afiat Hussain Shah
            </div>
            <div style="width: 250px; text-align: center; border-top: 2px solid #000; padding-top: 10px; font-weight: bold; margin-right: 20px;">
                صدر تنظیم (President)<br>
                Col Kamil Shah ( R )
            </div>
        </div>
    </div>

    <!-- Prepared By Footer -->
    <div class="print-only text-center mt-5" style="border-top: 1px solid #ccc; padding-top: 10px; font-size: 10pt; color: #000;">
        <p class="mb-0 fw-bold">Prepared by: Tanzeem Aulad Hazrat Haji Bahadur</p>
    </div>
</div>

</div>

<style>
@media print {
    @page { size: A4 portrait; margin: 15mm; } /* Standard/Normal A4 margin */
    html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; background: #fff !important; zoom: 1.0; font-size: 10pt; }
    .main-wrapper, .content-wrapper, .content, .container, .container-fluid { 
        margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; display: block !important; 
    }
    .card { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
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
    .card { border: none !important; box-shadow: none !important; }
    
    /* Force strict black borders */
    .table-bordered, .table-bordered th, .table-bordered td { 
        border: 1px solid #000 !important; 
        border-color: #000 !important;
    }
    
    /* Force Bold Totals */
    .fw-bold, strong, b, th, tfoot td, tr.fw-bold td {
        font-weight: 900 !important;
        color: #000 !important;
    }

    /* Make Total Rows Larger */
    tfoot td, tr.fw-bold td {
        font-size: 12pt !important;
    }

    /* Main Header Styling for Print */
    h1, h3 {
        font-weight: 900 !important;
        color: #000 !important;
    }
    h1 { font-size: 24pt !important; }
    h3 { font-size: 18pt !important; }

    /* Remove Background Colors for Print */
    .bg-dark, .bg-light, .table-dark, .table-striped > tbody > tr:nth-of-type(odd) > * { 
        background-color: transparent !important; 
        color: #000 !important; 
        box-shadow: none !important;
    }
    
    thead th { font-size: 14pt !important; font-weight: 900 !important; text-transform: uppercase; border-bottom: 2px solid #000 !important; }
    
    /* Reliable Page Break */
    .page-break { 
        clear: both;
        page-break-before: always !important; 
        break-before: page !important;
        display: block;
        height: 1px;
    }
    
    /* Prevent breaking inside summary tables */
    .summary-section table, 
    .summary-section h5, 
    .summary-section .text-center {
        page-break-inside: avoid;
    }
}
</style>

<script>
function toggleFS() {
    const isChecked = document.getElementById('show_fs').checked;
    document.querySelectorAll('.fs-sig').forEach(el => {
        el.style.visibility = isChecked ? 'visible' : 'hidden'; 
    });
}
</script>

<?php include 'footer.php'; ?>

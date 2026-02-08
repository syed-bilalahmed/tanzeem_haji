<?php
include 'config.php';
// Use Frontend Header
include 'frontend_header.php';

// Use GET for simple year filtering matching year_report.php URL structure
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// DATA PREPARATION (Exact Copy from year_report.php)
$monthly_data = [];
$grand_total_inc = 0;
$grand_total_exp = 0;
$annual_funds_inc = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];
$annual_funds_exp = ['cash'=>0, 'dargah'=>0, 'qabristan'=>0, 'masjid'=>0, 'urs'=>0];

// Category wise andrun/berun/darbar
$annual_cat_col = ['darbar'=>0, 'indoor'=>0, 'outdoor'=>0];

for($m=1; $m<=12; $m++) {
    // Localization for public view can be added here if needed, but keeping exact match for now
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

<div class="container py-5" dir="<?php echo $dir; ?>">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h3 class="mb-0">سالانہ گوشوارہ رپورٹ (Annual Financial Report)</h3>
        
        <!-- Year Tabs as Robust Buttons (Single Line) -->
        <div class="d-flex flex-nowrap gap-2 justify-content-start justify-content-md-end bg-white p-2 rounded shadow-sm border overflow-auto" style="direction: ltr; min-width: 200px; white-space: nowrap;">
            <?php for($y=2024; $y<=2030; $y++): ?>
                <a href="?year=<?php echo $y; ?>" 
                   class="btn <?php echo ($year == $y) ? 'btn-success shadow text-white' : 'btn-light border text-dark'; ?> px-3 py-1 rounded-pill fw-bold"
                   style="min-width: 70px; font-size: 1rem;">
                   <?php echo $y; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered text-center align-middle">
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

    <!-- Page Break Placeholder/Summary Section -->
    <div class="summary-section mt-5">
        <!-- Fund Status Table -->
        <h5 class="mb-3 pb-2 border-bottom">فنڈز کی سالانہ صورتحال (Annual Fund Status)</h5>
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
        <h5 class="mb-3 pb-2 mt-4 border-bottom">چندہ کی تفصیل (Collections Breakdown)</h5>
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
        
        <!-- Net Performance -->
        <div class="text-center py-4 bg-light rounded mt-4">
            <h4 class="mb-2">کل سالانہ بچت / خسارہ</h4>
            <h2 class="mb-0 fw-bold text-success"><?php echo number_format($grand_total_inc - $grand_total_exp); ?></h2>
            <small class="text-muted">(Net Annual Performance)</small>
        </div>
    </div>
</div>

<?php include 'frontend_footer.php'; ?>

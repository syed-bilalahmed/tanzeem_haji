<?php
include 'config.php';
include 'header.php';

// 1. Filters
$month = isset($_GET['month']) ? $_GET['month'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Base query for current view (filtered or year-to-date)
$where_c = "YEAR(collection_date) = ?";
$params_c = [$year];
if($month) { $where_c .= " AND MONTH(collection_date) = ?"; $params_c[] = $month; }

$where_i = "YEAR(income_date) = ?";
$params_i = [$year];
if($month) { $where_i .= " AND MONTH(income_date) = ?"; $params_i[] = $month; }

$where_e = "YEAR(expense_date) = ?";
$params_e = [$year];
if($month) { $where_e .= " AND MONTH(expense_date) = ?"; $params_e[] = $month; }

// --- DATA FETCHING FOR STATS ---

// Collections
$stmt = $pdo->prepare("SELECT * FROM collections WHERE $where_c");
$stmt->execute($params_c);
$cols = $stmt->fetchAll();
$total_col = 0;
foreach($cols as $col) {
    // Calc from Denoms
    $calc = 0;
    $calc += ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
    $calc += ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
    $calc += ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
    
    // Manual
    $man = ($col['darbar_total']??0) + ($col['andron_total']??0) + ($col['beron_total']??0);
    
    $total_col += ($man > 0) ? $man : $calc;
}

// Income/Expense (Filtered)
$stmt = $pdo->prepare("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM incomes WHERE $where_i");
$stmt->execute($params_i);
$inc_val = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM expenses WHERE $where_e");
$stmt->execute($params_e);
$exp_val = $stmt->fetch()['total'] ?? 0;

// All Time Totals (For Capital)
// All Time Totals (For Capital) - UNFILTERED (Global)
// Formula: Income - Expense (Collections excluded from Capital as per user request)
$st = $pdo->query("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM incomes");
$all_inc = $st->fetch()['total'] ?: 0;

$st = $pdo->query("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM expenses");
$all_exp = $st->fetch()['total'] ?: 0;

$total_capital = $all_inc - $all_exp;

// --- CHART DATA (Annual Trend) ---
$labels = []; $data_col = []; $data_inc = []; $data_exp = []; $data_running = [];
$running_sum = 0;

// Get cumulative balance up to start of year
$st = $pdo->prepare("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM incomes WHERE YEAR(income_date) < ?");
$st->execute([$year]);
$pre_inc = $st->fetch()['total'] ?: 0;
$st = $pdo->prepare("SELECT SUM(amount + dargah_fund + qabristan_fund + masjid_fund + urs_fund) as total FROM expenses WHERE YEAR(expense_date) < ?");
$st->execute([$year]);
$pre_exp = $st->fetch()['total'] ?: 0;
$st = $pdo->prepare("SELECT * FROM collections WHERE YEAR(collection_date) < ?");
$st->execute([$year]);
$pre_col = 0;
while($col = $st->fetch()) {
    $calc = 0;
    $calc += ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
    $calc += ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
    $calc += ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
    
    $man = ($col['darbar_total']??0) + ($col['andron_total']??0) + ($col['beron_total']??0);
    $pre_col += ($man > 0) ? $man : $calc;
}
$running_sum = ($pre_col + $pre_inc) - $pre_exp;

for($m=1; $m<=12; $m++) {
    $labels[] = date('F', mktime(0, 0, 0, $m, 1));
    
    // Monthly Collection
    $st = $pdo->prepare("SELECT * FROM collections WHERE MONTH(collection_date) = ? AND YEAR(collection_date) = ?");
    $st->execute([$m, $year]);
    $m_col = 0;
    while($col = $st->fetch()) {
        $calc = 0;
        $calc += ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
        $calc += ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
        $calc += ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
        
        $man = ($col['darbar_total']??0) + ($col['andron_total']??0) + ($col['beron_total']??0);
        $m_col += ($man > 0) ? $man : $calc;
    }
    $data_col[] = $m_col;
    
    // Monthly Income/Expense
    $st = $pdo->prepare("SELECT SUM(amount) as total FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ?");
    $st->execute([$m, $year]);
    $m_inc = $st->fetch()['total'] ?: 0;
    $data_inc[] = $m_inc;
    
    $st = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $st->execute([$m, $year]);
    $m_exp = $st->fetch()['total'] ?: 0;
    $data_exp[] = $m_exp;
    
    $running_sum += ($m_col + $m_inc) - $m_exp;
    $data_running[] = $running_sum;
}
?>

<div class="row mb-4 align-items-center no-print">
    <div class="col-md-5">
        <h2 class="section-title mb-0">ڈیش بورڈ (Dashboard)</h2>
    </div>
    <div class="col-md-7 d-flex justify-content-end gap-2">
        <form class="d-flex gap-2">
            <select name="month" class="form-control form-control-sm">
                <option value="">پورے سال کا (Whole Year)</option>
                <?php for($m=1;$m<=12;$m++) echo "<option value='".str_pad($m,2,'0',STR_PAD_LEFT)."' ".($month==str_pad($m,2,'0',STR_PAD_LEFT)?'selected':'').">".date('M',mktime(0,0,0,$m,1))."</option>"; ?>
            </select>
            <select name="year" class="form-control form-control-sm">
                <?php for($y=2024;$y<=2030;$y++) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>"; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-dark">فلٹر</button>
        </form>
        <a href="year_report.php?year=<?php echo $year; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-invoice"></i> سالانہ رپورٹ</a>
        <a href="multi_year_report.php" class="btn btn-sm btn-outline-dark"><i class="fas fa-layer-group"></i> کثیر سالانہ (Multi-Year)</a>
    </div>
</div>

<div class="print-only mb-4">
    <?php if(function_exists('get_org_header')) get_org_header("ڈیش بورڈ رپورٹ", ($month ? date('F', mktime(0,0,0, (int)$month, 1)) . " " : "") . $year); ?>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card blue">
        <h3>کل چندہ (Total Collections)</h3>
        <div class="value"><?php echo number_format($total_col); ?></div>
        <div class="small text-white-50">منتخب مدت کے لئے</div>
    </div>
    <div class="stat-card green">
        <h3>کل آمدن (Total Income)</h3>
        <div class="value"><?php echo number_format($inc_val); ?></div>
        <div class="small text-white-50">بشمول تمام فنڈز</div>
    </div>
    <div class="stat-card red">
        <h3>کل اخراجات (Total Expenses)</h3>
        <div class="value"><?php echo number_format($exp_val); ?></div>
        <div class="small text-white-50">بشمول تمام فنڈز</div>
    </div>
    
    <!-- Period Balance Card -->
    <?php $period_balance = $inc_val - $exp_val; ?>
    <div class="stat-card" style="background: linear-gradient(135deg, #6610f2, #520dc2);">
        <h3>بچت / خسارہ (Balance)</h3>
        <div class="value"><?php echo number_format($period_balance); ?></div>
        <div class="small text-white-50">آمدنی - اخراجات (Selected Period)</div>
    </div>

    <div class="stat-card orange">
        <h3>میزان (Net Capital)</h3>
        <div class="value"><?php echo number_format($total_capital); ?></div>
        <div class="small text-white-50">مجموعی بینک و کیش بیلنس</div>
    </div>
    
    <!-- Funeral Stats -->
    <?php
    // Total Deaths
    $stmt_fun = $pdo->prepare("SELECT count(*) as total_deaths, SUM(digging + tea + truck + other) as total_expense FROM funeral_records WHERE year = ?");
    $stmt_fun->execute([$year]); 
    $fun_stats = $stmt_fun->fetch(PDO::FETCH_ASSOC);
    $fun_expense = $fun_stats['total_expense'] ?: 0;

    // Returned Amount
    $stmt_ret = $pdo->prepare("SELECT (ret_digging + ret_tea + ret_truck + ret_other) as total_return FROM funeral_year_summary WHERE year = ?");
    $stmt_ret->execute([$year]);
    $fun_return = $stmt_ret->fetchColumn() ?: 0;
    ?>
    <div class="stat-card" style="background: linear-gradient(135deg, #1f2937, #111827); color:white;">
        <h3 class="d-flex justify-content-between mb-3"><span>تجہیز و تکفین</span> <i class="fas fa-praying-hands opacity-50"></i></h3>
        
        <div class="d-flex justify-content-between align-items-end mb-2">
             <div>
                <div class="value"><?php echo $fun_stats['total_deaths']; ?></div>
                <div class="small text-white-50">اموات (Deaths)</div>
             </div>
             <div class="text-end">
                <div class="fs-5 fw-bold text-warning">Exp: <?php echo number_format($fun_expense); ?></div>
                <div class="small text-white-50">Net: <?php echo number_format($fun_expense - $fun_return); ?></div>
             </div>
        </div>
        
        <div class="small text-white-50 border-top pt-2 mt-1 border-secondary d-flex justify-content-between">
            <span>Return: <?php echo number_format($fun_return); ?></span>
            <a href="funeral_record.php" class="text-white text-decoration-none">تفصیل <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>



<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <h5 class="mb-3"><i class="fas fa-hand-holding-heart text-danger"></i> چندہ رجحان (Collections Graph)</h5>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="colChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <h5 class="mb-3"><i class="fas fa-chart-line text-primary"></i> جاری بیلنس (Continuous Net Balance)</h5>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="runningChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <h5 class="mb-3"><i class="fas fa-exchange-alt text-success"></i> ماہانہ آمدن و اخراجات (Monthly Flow)</h5>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="flowChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: false } }
};

// 1. Collections Chart
new Chart(document.getElementById('colChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'چندہ (Collections)',
            data: <?php echo json_encode($data_col); ?>,
            backgroundColor: '#dc3545'
        }]
    },
    options: commonOptions
});

// 2. Running Balance Chart
new Chart(document.getElementById('runningChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'کل سرمایہ (Running Balance)',
            data: <?php echo json_encode($data_running); ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: commonOptions
});

// 3. Monthly Flow Chart
new Chart(document.getElementById('flowChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            {
                label: 'آمدن (Income)',
                data: <?php echo json_encode($data_inc); ?>,
                borderColor: '#28a745',
                tension: 0.4
            },
            {
                label: 'اخراجات (Expense)',
                data: <?php echo json_encode($data_exp); ?>,
                borderColor: '#6c757d',
                tension: 0.4
            }
        ]
    },
    options: {
        ...commonOptions,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include 'footer.php'; ?>

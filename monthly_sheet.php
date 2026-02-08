<?php
include 'config.php';
include 'header.php';

// Auto Print if requested
if (isset($_GET['print'])) {
    echo "<script>window.onload = function() { window.print(); }</script>";
}

// Default to current month/year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_month_label = date('F', mktime(0, 0, 0, $month, 10));

// Fixed Lists
$fixed_income_sources = [
    "تندور کا کرایہ", "دوکاندار جان محمد", "دوکاندار واجد", "دوکاندار ملنگ شاد", 
    "اسلم خاکروب", "دربار صندوق", "مسجد اندرون صندوق", "مسجد بیرون صندوق"
];
$fixed_expense_sources = [
    "مسجد بجلی بل", "دربار گیس بل", "مسجد امام وظیفہ", "مسجد خا دم وظیفہ", "گدی نشین وظیفہ"
];

// Handle Saving
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_sheet'])) {
    // Save Incomes
    if (isset($_POST['income'])) {
        foreach ($_POST['income'] as $row_data) {
            $row_id = isset($row_data['id']) ? $row_data['id'] : null;
            $title = $row_data['title'];
            $date = $row_data['date'] ?: "$year-$month-01";
            $cash = $row_data['cash'] ?: 0;
            $dargah = $row_data['dargah'] ?: 0;
            $qabristan = $row_data['qabristan'] ?: 0;
            $masjid = $row_data['masjid'] ?: 0;
            $urs = $row_data['urs'] ?: 0;
            $is_fixed = in_array($title, $fixed_income_sources) ? 1 : 0;

            if ($row_id) {
                $stmt = $pdo->prepare("UPDATE incomes SET income_date = ?, title = ?, amount = ?, dargah_fund = ?, qabristan_fund = ?, masjid_fund = ?, urs_fund = ?, is_fixed = ? WHERE id = ?");
                $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed, $row_id]);
            } elseif ($cash > 0 || $dargah > 0 || $qabristan > 0 || $masjid > 0 || $urs > 0) {
                $stmt = $pdo->prepare("INSERT INTO incomes (income_date, title, amount, dargah_fund, qabristan_fund, masjid_fund, urs_fund, is_fixed, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Monthly Sheet')");
                $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed]);
            }
        }
    }

    // Save Expenses
    if (isset($_POST['expense'])) {
        foreach ($_POST['expense'] as $row_data) {
            $row_id = isset($row_data['id']) ? $row_data['id'] : null;
            $title = $row_data['title'];
            $date = $row_data['date'] ?: "$year-$month-01";
            $cash = $row_data['cash'] ?: 0;
            $dargah = $row_data['dargah'] ?: 0;
            $qabristan = $row_data['qabristan'] ?: 0;
            $masjid = $row_data['masjid'] ?: 0;
            $urs = $row_data['urs'] ?: 0;
            $is_fixed = in_array($title, $fixed_expense_sources) ? 1 : 0;
            $cat = in_array($title, $fixed_expense_sources) ? 'Fixed' : 'Monthly Sheet';

            if ($row_id) {
                $stmt = $pdo->prepare("UPDATE expenses SET expense_date = ?, title = ?, amount = ?, dargah_fund = ?, qabristan_fund = ?, masjid_fund = ?, urs_fund = ?, is_fixed = ? WHERE id = ?");
                $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed, $row_id]);
            } elseif ($cash > 0 || $dargah > 0 || $qabristan > 0 || $masjid > 0 || $urs > 0) {
                $stmt = $pdo->prepare("INSERT INTO expenses (expense_date, title, amount, dargah_fund, qabristan_fund, masjid_fund, urs_fund, is_fixed, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed, $cat]);
            }
        }
    }

    // Save Manual Summary Adjustments
    foreach (['income', 'expense'] as $type) {
        if (isset($_POST['manual_'.$type])) {
            $m_data = $_POST['manual_'.$type];
            $m_id = $m_data['id'] ?? null;
            $m_cash = $m_data['cash'] ?: 0;
            $m_dargah = $m_data['dargah'] ?: 0;
            $m_qabristan = $m_data['qabristan'] ?: 0;
            $m_masjid = $m_data['masjid'] ?: 0;
            $m_urs = $m_data['urs'] ?: 0;
            $table = $type.'s'; // incomes / expenses

            $m_date = $m_data['date'] ?: "$year-$month-01";
            $date_col = ($type == 'income') ? 'income_date' : 'expense_date';

            if ($m_id) {
                // Now also updating the date
                $sql = "UPDATE $table SET $date_col=?, amount=?, dargah_fund=?, qabristan_fund=?, masjid_fund=?, urs_fund=? WHERE id=?";
                $pdo->prepare($sql)->execute([$m_date, $m_cash, $m_dargah, $m_qabristan, $m_masjid, $m_urs, $m_id]);
            } elseif ($m_cash > 0 || $m_dargah > 0 || $m_qabristan > 0 || $m_masjid > 0 || $m_urs > 0) {
                $sql = "INSERT INTO $table ($date_col, title, amount, dargah_fund, qabristan_fund, masjid_fund, urs_fund, category, is_fixed) 
                        VALUES (?, 'Manual Adjustment', ?, ?, ?, ?, ?, 'Manual Summary', 0)";
                $pdo->prepare($sql)->execute([$m_date, $m_cash, $m_dargah, $m_qabristan, $m_masjid, $m_urs]);
            }
        }
    }

    echo "<script>alert('محفوظ ہو گیا'); window.location.href='monthly_sheet.php?month=$month&year=$year';</script>";
}

// Data Fetching - Exclude Manual Summary
$income_data = $pdo->prepare("SELECT * FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ? AND category != 'Manual Summary' ORDER BY is_fixed DESC, income_date ASC");
$income_data->execute([$month, $year]);
$all_incomes = $income_data->fetchAll();

$expense_data = $pdo->prepare("SELECT * FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? AND category != 'Manual Summary' ORDER BY is_fixed DESC, expense_date ASC");
$expense_data->execute([$month, $year]);
$all_expenses = $expense_data->fetchAll();

// Fetch Manual Summary Data
$man_inc_stmt = $pdo->prepare("SELECT * FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ? AND category = 'Manual Summary' LIMIT 1");
$man_inc_stmt->execute([$month, $year]);
$manual_inc = $man_inc_stmt->fetch() ?: [];

$man_exp_stmt = $pdo->prepare("SELECT * FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? AND category = 'Manual Summary' LIMIT 1");
$man_exp_stmt->execute([$month, $year]);
$manual_exp = $man_exp_stmt->fetch() ?: [];

// Distribution
$present_fixed_inc = []; $other_inc = [];
foreach ($all_incomes as $r) { if($r['is_fixed']) $present_fixed_inc[$r['title']] = $r; else $other_inc[] = $r; }

$present_fixed_exp = []; $other_exp = [];
foreach ($all_expenses as $r) { if($r['is_fixed']) $present_fixed_exp[$r['title']] = $r; else $other_exp[] = $r; }

$sum_inc = ['cash'=>0,'dargah'=>0,'qabristan'=>0,'masjid'=>0,'urs'=>0];
$sum_exp = ['cash'=>0,'dargah'=>0,'qabristan'=>0,'masjid'=>0,'urs'=>0];
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap');
    
    /* body font handled by style.css */
    .ledger-table { border: 2px solid #000 !important; width: 100%; margin-bottom: 20px; }
    .ledger-table th { background-color: #6c757d !important; color: #fff !important; border: 1px solid #444 !important; padding: 12px 8px !important; font-size: 15px; text-align: center; }
    .ledger-table td { border: 1px solid #222 !important; padding: 6px 8px !important; vertical-align: middle; font-size: 14px; }
    .amt-input { border: none; background: transparent; text-align: center; width: 100%; font-weight: bold; outline: none; }
    .amt-input:focus { background: #fff8e1; }
    .total-row td { background-color: #f8f9fa !important; border-top: 3px double #000 !important; font-weight: bold; }
    .grand-total-row td { background-color: #e3f2fd !important; border: 2px solid #000 !important; font-weight: bold; font-size: 1.2em; }
    
    .print-only-header { display: none; }
    .print-only { display: none; }
    .page-break { display: none; }
    
    @media print { 
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .no-print { display: none !important; } 
        .print-only-header { display: block !important; }
        .print-only { display: flex !important; }
        .page-break { display: block !important; page-break-before: always !important; height: 0; margin: 0; }
        
        @page { margin: 8mm; size: A4 portrait; }
        html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; background: #fff !important; font-size: 11pt; }
        .main-wrapper, .content-wrapper, .container, .card { 
            display: block !important; width: 100% !important; max-width: none !important; 
            margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; 
        }
        .table-responsive { overflow: visible !important; display: block !important; width: 100% !important; }
        
        .report-header { margin-bottom: 20px !important; width: 100% !important; }
        .ledger-table { width: 100% !important; border: 2.5px solid #000 !important; border-collapse: collapse !important; border-spacing: 0 !important; margin-top: 10px !important; }
        .ledger-table th, .ledger-table td { border: 1.5px solid #000 !important; padding: 10px 5px !important; font-size: 11pt !important; text-align: center !important; }
        .ledger-table th { background-color: #f0f0f0 !important; color: #000 !important; font-weight: 900 !important; font-size: 14pt !important; text-transform: uppercase; }
        
        .amt-input { width: 100% !important; border: none !important; background: transparent !important; font-size: 11pt !important; font-weight: bold !important; text-align: center !important; }
        select, input[type="date"], input[type="text"] { border: none !important; appearance: none !important; background: transparent !important; font-size: 11pt !important; }
        
        tr.data-row.empty-row { display: none !important; }
        .grand-total-row td { background-color: #e3f2fd !important; font-size: 12pt !important; border: 2.5px solid #000 !important; }
        .print-only { border-top: none !important; }
        
        /* Zoom to fill A4 better */
        body { zoom: 1.05; }
    }
</style>

<div class="card" dir="rtl">
    <div class="no-print">
        <?php get_org_header("ماہانہ گوشوارہ (Monthly Account Sheet)", $selected_month_label); ?>
    </div>

    <!-- Month Selection -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">ماہانہ اکاؤنٹ لیجر</h4>
        <form class="form-inline d-flex gap-2">
            <select name="month" id="filter_month" class="form-control">
                <?php for ($m=1; $m<=12; $m++) { 
                    $mp = str_pad($m, 2, '0', STR_PAD_LEFT);
                    echo "<option value='$mp' ".($mp==$month?'selected':'').">".date('F', mktime(0, 0, 0, $m, 10))."</option>";
                } ?>
            </select>
            <select name="year" id="filter_year" class="form-control">
                <?php for ($y=2024; $y<=2030; $y++) echo "<option value='$y' ".($y==$year?'selected':'').">$y</option>"; ?>
            </select>
            <button type="submit" class="btn btn-dark">منتخب کریں</button>
        </form>
        <div class="form-check form-switch ms-3">
             <input class="form-check-input" type="checkbox" id="show_fs" checked onchange="toggleFS()">
             <label class="form-check-label fw-bold" for="show_fs">فنانس سیکرٹری دستخط (Show Finance Sec)</label>
        </div>
    </div>

    <form method="POST">
        <!-- 1. INCOME TABLE -->
        <div class="print-only-header">
            <?php get_org_header("آمدن (Income)", "ماہانہ گوشوارہ - " . $selected_month_label); ?>
        </div>
        <h5 class="mb-2" style="border-right: 5px solid #198754; padding-right: 10px;">آمدن (Income)</h5>
        <div class="table-responsive">
            <table class="ledger-table" id="income_table">
                <thead>
                    <tr>
                        <th width="10%">تاریخ</th>
                        <th>تفصیل آمدن</th>
                        <th width="12%">کیش (Cash)</th>
                        <th width="12%">درگاہ فنڈ</th>
                        <th width="12%">قبرستان فنڈ</th>
                        <th width="12%">مسجد فنڈ</th>
                        <th width="12%">عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody id="income_body">
                    <?php 
                    $inc_idx = 0;
                    foreach ($fixed_income_sources as $src): 
                        $v = $present_fixed_inc[$src] ?? null;
                        $has_data = ($v && ($v['amount'] > 0 || $v['dargah_fund'] > 0 || $v['qabristan_fund'] > 0 || $v['masjid_fund'] > 0 || $v['urs_fund'] > 0));
                        if($v) { foreach($sum_inc as $k=>$null) $sum_inc[$k] += ($k=='cash'?$v['amount']:$v[$k.'_fund']); }
                    ?>
                    <tr class="data-row <?php echo $has_data ? '' : 'empty-row'; ?>">
                        <td><input type="date" name="income[<?php echo $inc_idx; ?>][date]" value="<?php echo $v ? $v['income_date'] : "$year-$month-01"; ?>" class="amt-input" style="font-weight:normal; font-size:small;">
                            <?php if($v): ?><input type="hidden" name="income[<?php echo $inc_idx; ?>][id]" value="<?php echo $v['id']; ?>"><?php endif; ?>
                        </td>
                        <td class="fw-bold"><input type="text" name="income[<?php echo $inc_idx; ?>][title]" value="<?php echo $src; ?>" readonly class="amt-input text-end px-2" style="text-align:right !important;"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][cash]" class="amt-input inc-amt" data-col="cash" value="<?php echo $v ? $v['amount'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][dargah]" class="amt-input inc-amt" data-col="dargah" value="<?php echo $v ? $v['dargah_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][qabristan]" class="amt-input inc-amt" data-col="qabristan" value="<?php echo $v ? $v['qabristan_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][masjid]" class="amt-input inc-amt" data-col="masjid" value="<?php echo $v ? $v['masjid_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][urs]" class="amt-input inc-amt" data-col="urs" value="<?php echo $v ? $v['urs_fund'] : ''; ?>"></td>
                    </tr>
                    <?php $inc_idx++; endforeach; ?>
                    <?php foreach ($other_inc as $v): 
                        foreach($sum_inc as $k=>$null) $sum_inc[$k] += ($k=='cash'?$v['amount']:$v[$k.'_fund']);
                    ?>
                    <tr class="data-row">
                        <td><input type="date" name="income[<?php echo $inc_idx; ?>][date]" value="<?php echo $v['income_date']; ?>" class="amt-input" style="font-weight:normal; font-size:small;">
                            <input type="hidden" name="income[<?php echo $inc_idx; ?>][id]" value="<?php echo $v['id']; ?>">
                        </td>
                        <td><input type="text" name="income[<?php echo $inc_idx; ?>][title]" value="<?php echo htmlspecialchars($v['title']); ?>" class="amt-input text-end px-2" style="text-align:right !important;"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][cash]" class="amt-input inc-amt" data-col="cash" value="<?php echo $v['amount']; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][dargah]" class="amt-input inc-amt" data-col="dargah" value="<?php echo $v['dargah_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][qabristan]" class="amt-input inc-amt" data-col="qabristan" value="<?php echo $v['qabristan_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][masjid]" class="amt-input inc-amt" data-col="masjid" value="<?php echo $v['masjid_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="income[<?php echo $inc_idx; ?>][urs]" class="amt-input inc-amt" data-col="urs" value="<?php echo $v['urs_fund']; ?>"></td>
                    </tr>
                    <?php $inc_idx++; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="no-print"><td colspan="7"><button type="button" class="btn btn-sm btn-outline-success" onclick="addRow('income')">+ آمدن درج کریں</button></td></tr>
                    <tr class="total-row text-center">
                        <td colspan="2">ٹوٹل آمدن</td>
                        <td id="tot_inc_cash"><?php echo number_format($sum_inc['cash']); ?></td>
                        <td id="tot_inc_dargah"><?php echo number_format($sum_inc['dargah']); ?></td>
                        <td id="tot_inc_qabristan"><?php echo number_format($sum_inc['qabristan']); ?></td>
                        <td id="tot_inc_masjid"><?php echo number_format($sum_inc['masjid']); ?></td>
                        <td id="tot_inc_urs"><?php echo number_format($sum_inc['urs']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between pt-4 pb-2 print-only" style="margin-top: 30px;">
            <div class="fs-sig" style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">فنانس سیکرٹری (Finance Secretary)<br>Syed Afiat Hussain Shah</div>
            <div style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">صدر تنظیم (President)<br>Col Kamil Shah ( R )</div>
        </div>

        <div class="page-break"></div>
        <div class="print-only-header">
            <?php get_org_header("اخراجات (Expenses)", "ماہانہ گوشوارہ - " . $selected_month_label); ?>
        </div>

        <!-- 2. EXPENSE TABLE -->
        <h5 class="mt-4 mb-2" style="border-right: 5px solid #dc3545; padding-right: 10px;">اخراجات (Expenses)</h5>
        <div class="table-responsive">
            <table class="ledger-table" id="expense_table">
                <thead>
                    <tr>
                        <th width="10%">تاریخ</th>
                        <th>تفصیل خرچہ</th>
                        <th width="12%">کیش (Cash)</th>
                        <th width="12%">درگاہ فنڈ</th>
                        <th width="12%">قبرستان فنڈ</th>
                        <th width="12%">مسجد فنڈ</th>
                        <th width="12%">عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody id="expense_body">
                    <?php 
                    $exp_idx = 0;
                    foreach ($fixed_expense_sources as $src): 
                        $v = $present_fixed_exp[$src] ?? null;
                        $has_data = ($v && ($v['amount'] > 0 || $v['dargah_fund'] > 0 || $v['qabristan_fund'] > 0 || $v['masjid_fund'] > 0 || $v['urs_fund'] > 0));
                        if($v) { foreach($sum_exp as $k=>$null) $sum_exp[$k] += ($k=='cash'?$v['amount']:$v[$k.'_fund']); }
                    ?>
                    <tr class="data-row <?php echo $has_data ? '' : 'empty-row'; ?>">
                        <td><input type="date" name="expense[<?php echo $exp_idx; ?>][date]" value="<?php echo $v ? $v['expense_date'] : "$year-$month-01"; ?>" class="amt-input" style="font-weight:normal; font-size:small;">
                            <?php if($v): ?><input type="hidden" name="expense[<?php echo $exp_idx; ?>][id]" value="<?php echo $v['id']; ?>"><?php endif; ?>
                        </td>
                        <td class="fw-bold"><input type="text" name="expense[<?php echo $exp_idx; ?>][title]" value="<?php echo $src; ?>" readonly class="amt-input text-end px-2" style="text-align:right !important;"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][cash]" class="amt-input exp-amt" data-col="cash" value="<?php echo $v ? $v['amount'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][dargah]" class="amt-input exp-amt" data-col="dargah" value="<?php echo $v ? $v['dargah_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][qabristan]" class="amt-input exp-amt" data-col="qabristan" value="<?php echo $v ? $v['qabristan_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][masjid]" class="amt-input exp-amt" data-col="masjid" value="<?php echo $v ? $v['masjid_fund'] : ''; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][urs]" class="amt-input exp-amt" data-col="urs" value="<?php echo $v ? $v['urs_fund'] : ''; ?>"></td>
                    </tr>
                    <?php $exp_idx++; endforeach; ?>
                    <?php foreach ($other_exp as $v): 
                        foreach($sum_exp as $k=>$null) $sum_exp[$k] += ($k=='cash'?$v['amount']:$v[$k.'_fund']);
                    ?>
                    <tr class="data-row">
                        <td><input type="date" name="expense[<?php echo $exp_idx; ?>][date]" value="<?php echo $v['expense_date']; ?>" class="amt-input" style="font-weight:normal; font-size:small;">
                            <input type="hidden" name="expense[<?php echo $exp_idx; ?>][id]" value="<?php echo $v['id']; ?>">
                        </td>
                        <td><input type="text" name="expense[<?php echo $exp_idx; ?>][title]" value="<?php echo htmlspecialchars($v['title']); ?>" class="amt-input text-end px-2" style="text-align:right !important;"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][cash]" class="amt-input exp-amt" data-col="cash" value="<?php echo $v['amount']; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][dargah]" class="amt-input exp-amt" data-col="dargah" value="<?php echo $v['dargah_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][qabristan]" class="amt-input exp-amt" data-col="qabristan" value="<?php echo $v['qabristan_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][masjid]" class="amt-input exp-amt" data-col="masjid" value="<?php echo $v['masjid_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="expense[<?php echo $exp_idx; ?>][urs]" class="amt-input exp-amt" data-col="urs" value="<?php echo $v['urs_fund']; ?>"></td>
                    </tr>
                    <?php $exp_idx++; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="no-print"><td colspan="7"><button type="button" class="btn btn-sm btn-outline-danger" onclick="addRow('expense')">+ خرچہ درج کریں</button></td></tr>
                    <tr class="total-row text-center" style="color:#d32f2f;">
                        <td colspan="2">ٹوٹل اخراجات</td>
                        <td id="tot_exp_cash"><?php echo number_format($sum_exp['cash']); ?></td>
                        <td id="tot_exp_dargah"><?php echo number_format($sum_exp['dargah']); ?></td>
                        <td id="tot_exp_qabristan"><?php echo number_format($sum_exp['qabristan']); ?></td>
                        <td id="tot_exp_masjid"><?php echo number_format($sum_exp['masjid']); ?></td>
                        <td id="tot_exp_urs"><?php echo number_format($sum_exp['urs']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between pt-4 pb-2 print-only" style="margin-top: 30px;">
            <div class="fs-sig" style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">فنانس سیکرٹری (Finance Secretary)<br>Syed Afiat Hussain Shah</div>
            <div style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">صدر تنظیم (President)<br>Col Kamil Shah ( R )</div>
        </div>

        <!-- summary calculations -->
        <div class="page-break"></div>
        <div class="print-only-header">
            <?php get_org_header("خلاصہ (Summary)", "ماہانہ گوشوارہ - " . $selected_month_label); ?>
        </div>

        <h5 class="mt-4 mb-2" style="border-right: 5px solid #007bff; padding-right: 10px;">خلاصہ (Final Summary)</h5>
        <div class="table-responsive">
            <table class="ledger-table mt-2">
                <thead>
                    <tr>
                        <th>تاریخ (Date)</th>
                        <th>تفصیل (Description)</th>
                        <th width="12%">کیش (Cash)</th>
                        <th width="12%">درگاہ فنڈ</th>
                        <th width="12%">قبرستان فنڈ</th>
                        <th width="12%">مسجد فنڈ</th>
                        <th width="12%">عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Total Income Row with Inputs -->
                    <tr class="text-center" style="border-top:2px solid #000; background-color:#f1f8e9;">
                        <input type="hidden" name="manual_income[id]" value="<?php echo $manual_inc['id'] ?? ''; ?>">
                        <td><input type="date" name="manual_income[date]" value="<?php echo $manual_inc['income_date'] ?? "$year-$month-01"; ?>" class="amt-input" style="font-weight:normal; font-size:small;"></td>
                        <td class="fw-bold">کل آمدن (Total Income)</td>
                        <td><input type="number" step="0.01" name="manual_income[cash]" value="<?php echo $manual_inc['amount'] ?? ''; ?>" class="amt-input man-inc" data-col="cash" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_income[dargah]" value="<?php echo $manual_inc['dargah_fund'] ?? ''; ?>" class="amt-input man-inc" data-col="dargah" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_income[qabristan]" value="<?php echo $manual_inc['qabristan_fund'] ?? ''; ?>" class="amt-input man-inc" data-col="qabristan" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_income[masjid]" value="<?php echo $manual_inc['masjid_fund'] ?? ''; ?>" class="amt-input man-inc" data-col="masjid" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_income[urs]" value="<?php echo $manual_inc['urs_fund'] ?? ''; ?>" class="amt-input man-inc" data-col="urs" placeholder="0"></td>
                    </tr>

                    <!-- Total Expense Row with Inputs -->
                    <tr class="text-center text-danger" style="border-top:2px solid #000; background-color:#ffebee;">
                        <input type="hidden" name="manual_expense[id]" value="<?php echo $manual_exp['id'] ?? ''; ?>">
                        <td><input type="date" name="manual_expense[date]" value="<?php echo $manual_exp['expense_date'] ?? "$year-$month-01"; ?>" class="amt-input" style="font-weight:normal; font-size:small;"></td>
                        <td class="fw-bold">کل اخراجات (Total Expenses)</td>
                        <td><input type="number" step="0.01" name="manual_expense[cash]" value="<?php echo $manual_exp['amount'] ?? ''; ?>" class="amt-input man-exp" data-col="cash" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_expense[dargah]" value="<?php echo $manual_exp['dargah_fund'] ?? ''; ?>" class="amt-input man-exp" data-col="dargah" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_expense[qabristan]" value="<?php echo $manual_exp['qabristan_fund'] ?? ''; ?>" class="amt-input man-exp" data-col="qabristan" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_expense[masjid]" value="<?php echo $manual_exp['masjid_fund'] ?? ''; ?>" class="amt-input man-exp" data-col="masjid" placeholder="0"></td>
                        <td><input type="number" step="0.01" name="manual_expense[urs]" value="<?php echo $manual_exp['urs_fund'] ?? ''; ?>" class="amt-input man-exp" data-col="urs" placeholder="0"></td>
                    </tr>

                    <!-- Final Balance -->
                    <tr class="grand-total-row text-center">
                        <td colspan="2" style="text-align:right !important; padding-right:20px !important;">میزان (Net Capital)</td>
                        
                        <!-- CASH COLUMN IS THE GRAND TOTAL -->
                        <td id="final_cash" class="fw-bold"><?php 
                            $f_inc = ($manual_inc['amount']??0) > 0 ? ($manual_inc['amount']??0) : $sum_inc['cash'];
                            $f_exp = ($manual_exp['amount']??0) > 0 ? ($manual_exp['amount']??0) : $sum_exp['cash'];
                            echo number_format($f_inc - $f_exp); ?></td>
                            
                        <!-- Remaining columns are just breakdowns, we show their net balance for info, but they DONT add to the grand total -->
                        <td id="final_dargah" class="fw-bold" style="color:#666; font-weight:normal;"><?php 
                            $f_inc = ($manual_inc['dargah_fund']??0) > 0 ? ($manual_inc['dargah_fund']??0) : $sum_inc['dargah'];
                            $f_exp = ($manual_exp['dargah_fund']??0) > 0 ? ($manual_exp['dargah_fund']??0) : $sum_exp['dargah'];
                            echo number_format($f_inc - $f_exp); ?></td>
                        <td id="final_qabristan" class="fw-bold" style="color:#666; font-weight:normal;"><?php 
                            $f_inc = ($manual_inc['qabristan_fund']??0) > 0 ? ($manual_inc['qabristan_fund']??0) : $sum_inc['qabristan'];
                            $f_exp = ($manual_exp['qabristan_fund']??0) > 0 ? ($manual_exp['qabristan_fund']??0) : $sum_exp['qabristan'];
                            echo number_format($f_inc - $f_exp); ?></td>
                        <td id="final_masjid" class="fw-bold" style="color:#666; font-weight:normal;"><?php 
                            $f_inc = ($manual_inc['masjid_fund']??0) > 0 ? ($manual_inc['masjid_fund']??0) : $sum_inc['masjid'];
                            $f_exp = ($manual_exp['masjid_fund']??0) > 0 ? ($manual_exp['masjid_fund']??0) : $sum_exp['masjid'];
                            echo number_format($f_inc - $f_exp); ?></td>
                        <td id="final_urs" class="fw-bold" style="color:#666; font-weight:normal;"><?php 
                            $f_inc = ($manual_inc['urs_fund']??0) > 0 ? ($manual_inc['urs_fund']??0) : $sum_inc['urs'];
                            $f_exp = ($manual_exp['urs_fund']??0) > 0 ? ($manual_exp['urs_fund']??0) : $sum_exp['urs'];
                            echo number_format($f_inc - $f_exp); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between pt-4 pb-2 print-only" style="margin-top: 30px;">
            <div class="fs-sig" style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">فنانس سیکرٹری (Finance Secretary)<br>Syed Afiat Hussain Shah</div>
            <div style="width:200px; text-align:center; border-top:2px solid #000; font-weight:bold;">صدر تنظیم (President)<br>Col Kamil Shah ( R )</div>
        </div>

        <div class="mt-4 text-center no-print">
            <button type="submit" name="save_sheet" class="btn btn-success">محفوظ کریں (Save All)</button>
            <button type="button" onclick="window.print()" class="btn btn-primary ms-3">پرنٹ (Print Sheet)</button>
        </div>
    </form>
</div>

<script>
let incCount = <?php echo $inc_idx; ?>;
let expCount = <?php echo $exp_idx; ?>;
const curDate = "<?php echo "$year-$month-01"; ?>";

    let currentDefaultDate = "<?php echo "$year-$month-01"; ?>";

    // ... (Existing variables: incCount, expCount, curDate) ...
    // Note: curDate above is PHP based, currentDefaultDate is for JS dynamic updates

    function addRow(type) {
        const tbody = document.getElementById(type + '_body');
        const idx = (type == 'income') ? incCount++ : expCount++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="date" name="${type}[${idx}][date]" value="${currentDefaultDate}" class="amt-input" style="font-weight:normal; font-size:small;"></td>
            <td><input type="text" name="${type}[${idx}][title]" placeholder="${type=='income'?'آمدن':'خرچہ'} کی تفصیل" class="amt-input text-end px-2" style="text-align:right !important;"></td>
            <td><input type="number" step="0.01" name="${type}[${idx}][cash]" class="amt-input ${type}-amt" data-col="cash"></td>
            <td><input type="number" step="0.01" name="${type}[${idx}][dargah]" class="amt-input ${type}-amt" data-col="dargah"></td>
            <td><input type="number" step="0.01" name="${type}[${idx}][qabristan]" class="amt-input ${type}-amt" data-col="qabristan"></td>
            <td><input type="number" step="0.01" name="${type}[${idx}][masjid]" class="amt-input ${type}-amt" data-col="masjid"></td>
            <td><input type="number" step="0.01" name="${type}[${idx}][urs]" class="amt-input ${type}-amt" data-col="urs"></td>
        `;
        tbody.appendChild(tr);
        tr.querySelectorAll('.amt-input').forEach(i => i.addEventListener('input', updateCalc));
    }

    function updateCalc(e) {
        let auto_inc = {cash:0, dargah:0, qabristan:0, masjid:0, urs:0};
        let auto_exp = {cash:0, dargah:0, qabristan:0, masjid:0, urs:0};
        let man_inc = {cash:0, dargah:0, qabristan:0, masjid:0, urs:0};
        let man_exp = {cash:0, dargah:0, qabristan:0, masjid:0, urs:0};
        
        // Sum Auto
        document.querySelectorAll('.inc-amt').forEach(i => { auto_inc[i.dataset.col] += parseFloat(i.value)||0; });
        document.querySelectorAll('.exp-amt').forEach(i => { auto_exp[i.dataset.col] += parseFloat(i.value)||0; });

        // Sum Manual
        document.querySelectorAll('.man-inc').forEach(i => { man_inc[i.dataset.col] += parseFloat(i.value)||0; });
        document.querySelectorAll('.man-exp').forEach(i => { man_exp[i.dataset.col] += parseFloat(i.value)||0; });

        for(let k in auto_inc) {
            // Update Auto Field displays (e.g. tot_inc_cash) in footer of main tables
            if(document.getElementById('tot_inc_'+k)) document.getElementById('tot_inc_'+k).innerText = auto_inc[k].toLocaleString();
            if(document.getElementById('tot_exp_'+k)) document.getElementById('tot_exp_'+k).innerText = auto_exp[k].toLocaleString();
            
            // Calculate Totals using Hybrid Logic: Manual overrides Auto
            const total_inc = (man_inc[k] > 0) ? man_inc[k] : auto_inc[k];
            const total_exp = (man_exp[k] > 0) ? man_exp[k] : auto_exp[k];

            // Update Summary Grand Total Fields (Row 2 in summary)
            const s_inc = document.getElementById('summary_inc_'+k);
            const s_exp = document.getElementById('summary_exp_'+k);
            if(s_inc) s_inc.innerText = total_inc.toLocaleString();
            if(s_exp) s_exp.innerText = total_exp.toLocaleString();
            
            // Final Balance
            const f = document.getElementById('final_'+k);
            if(f) f.innerText = (total_inc - total_exp).toLocaleString();
        }

        // Dynamic Header Year Update - Only if a DATE field was changed
        if (e && e.target && e.target.type === 'date' && e.target.value) {
            const d = new Date(e.target.value);
            if (!isNaN(d.getTime())) {
                const y = d.getFullYear();
                document.querySelectorAll('.header-subtitle-dynamic').forEach(el => {
                    const currentText = el.innerText;
                    if (currentText.match(/20\d{2}/)) {
                        if (!currentText.includes(y)) {
                             el.innerText = currentText.replace(/20\d{2}/g, y);
                        }
                    } else {
                        el.innerText = currentText + " " + y;
                    }
                });
            }
        }
    }

    // NEW: Listeners for Top Filters
    const f_month = document.getElementById('filter_month');
    const f_year = document.getElementById('filter_year');

    function updateHeaderFromFilters() {
        const m_text = f_month.options[f_month.selectedIndex].text;
        const y_val = f_year.value;
        const new_subtitle = m_text + " " + y_val;

        // Update Header
        document.querySelectorAll('.header-subtitle-dynamic').forEach(el => {
             el.innerText = new_subtitle;
        });
        
        // Update Default Date for new rows and existing empty/default rows
        const m_val = f_month.value; 
        currentDefaultDate = `${y_val}-${m_val}-01`;

        document.querySelectorAll('input[type="date"]').forEach(inp => {
             if(inp.classList.contains('amt-input')) {
                 inp.value = currentDefaultDate;
             }
        });
    }

    if(f_month && f_year) {
        f_month.addEventListener('change', updateHeaderFromFilters);
        f_year.addEventListener('change', updateHeaderFromFilters);
    }

    document.querySelectorAll('.amt-input').forEach(i => i.addEventListener('input', updateCalc));

    // Toggle Finance Secretary Signature
    function toggleFS() {
        const isChecked = document.getElementById('show_fs').checked;
        document.querySelectorAll('.fs-sig').forEach(el => {
            el.style.visibility = isChecked ? 'visible' : 'hidden'; // Use visibility to keep layout space if desired, or display for collapse
            // User requested "sometime need... sometimes both", implies presence. 
            // If we hide, alignment might shift depending on flex.
            // The layout is "justify-content-between". 
            // If FS is hidden (display:none), "Sadar" moves to right? Or stays left?
            // "justify-content-between" with 2 items: Left ------- Right.
            // If 1 item: Left (if flex-start) or it depends.
            // Let's use visibility:hidden so the Sadar stays on the Left/Right as it was.
            // Wait, standard Urdu doc: Start (Right) is FS, End (Left) is Sadar?
            // "justify-content-between":
            // Item 1 (FS) --- Item 2 (Sadar)
            // If direction is RTL: Item 1 (Right) --- Item 2 (Left).
            // Visbility hidden updates content but keeps space. Perfect.
        });
    }
</script>

<?php include 'footer.php'; ?>

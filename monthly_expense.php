<?php
include 'config.php';
include 'header.php';

// Default to current month/year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_month_label = date('F', mktime(0, 0, 0, $month, 10));

// Auto Print if requested
if (isset($_GET['print'])) {
    echo "<script>window.onload = function() { window.print(); }</script>";
}

// Fixed Expense Sources
$fixed_expenses = [
    "مسجد بجلی بل",
    "دربار گیس بل",
    "مسجد امام وظیفہ",
    "مسجد خا دم وظیفہ",
    "گدی نشین وظیفہ"
];

// Handle Saving
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_ledger'])) {
    if (isset($_POST['rows'])) {
        foreach ($_POST['rows'] as $row_data) {
            $row_id = isset($row_data['id']) ? $row_data['id'] : null;
            $title = $row_data['title'];
            $date = $row_data['date'] ?: "$year-$month-01";
            $cash = $row_data['cash'] ?: 0;
            $dargah = $row_data['dargah'] ?: 0;
            $qabristan = $row_data['qabristan'] ?: 0;
            $masjid = $row_data['masjid'] ?: 0;
            $urs = $row_data['urs'] ?: 0;
            $is_fixed = in_array($title, $fixed_expenses) ? 1 : 0;

            if ($row_id) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE expenses SET expense_date = ?, title = ?, amount = ?, dargah_fund = ?, qabristan_fund = ?, masjid_fund = ?, urs_fund = ?, is_fixed = ? WHERE id = ?");
                $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed, $row_id]);
            } else {
                // Insert new if any amount > 0
                if ($cash > 0 || $dargah > 0 || $qabristan > 0 || $masjid > 0 || $urs > 0) {
                    $stmt = $pdo->prepare("INSERT INTO expenses (expense_date, title, amount, dargah_fund, qabristan_fund, masjid_fund, urs_fund, is_fixed, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Monthly Ledger')");
                    $stmt->execute([$date, $title, $cash, $dargah, $qabristan, $masjid, $urs, $is_fixed]);
                }
            }
        }
    }
    echo "<script>alert('محفوظ ہو گیا'); window.location.href='monthly_expense.php?month=$month&year=$year';</script>";
}

// Fetch All Expenses for this Month
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? ORDER BY is_fixed DESC, expense_date ASC");
$stmt->execute([$month, $year]);
$all_expenses = $stmt->fetchAll();

$present_fixed = [];
$other_expenses = [];
foreach ($all_expenses as $row) {
    if ($row['is_fixed']) {
        $present_fixed[$row['title']] = $row;
    } else {
        $other_expenses[] = $row;
    }
}

// Fetch Income Totals for Summary
$stmt = $pdo->prepare("SELECT 
    SUM(amount) as cash, 
    SUM(dargah_fund) as dargah, 
    SUM(qabristan_fund) as qabristan, 
    SUM(masjid_fund) as masjid, 
    SUM(urs_fund) as urs 
    FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ?");
$stmt->execute([$month, $year]);
$income_totals = $stmt->fetch(PDO::FETCH_ASSOC);

$totals = ['cash' => 0, 'dargah' => 0, 'qabristan' => 0, 'masjid' => 0, 'urs' => 0];
?>

<style>
    .ledger-table { border: 2px solid #000 !important; width: 100%; margin-bottom: 20px; }
    .ledger-table th { background-color: #212529 !important; color: #fff !important; border: 1px solid #444 !important; padding: 12px 8px !important; font-size: 16px; text-align: center; }
    .ledger-table td { border: 1px solid #222 !important; padding: 8px !important; vertical-align: middle; font-size: 15px; }
    .amt-input { border: none; background: transparent; text-align: center; width: 100%; font-weight: bold; font-size: 16px; outline: none; }
    .amt-input:focus { background: #fff8e1; }
    .total-row td { background-color: #f8f9fa !important; border-top: 3px double #000 !important; font-weight: bold; }
    @media print {
        .no-print { display: none !important; }
        .amt-input { border: none !important; pointer-events: none; }
        @page { margin: 10mm; size: A4; }
    }
</style>

<div class="card" dir="rtl">
    <!-- Header -->
    <div class="report-header text-center mb-4" style="border-bottom: 2px solid #000; padding-bottom: 20px; position: relative;">
        <img src="logo.jpeg" style="width: 80px; position: absolute; right: 20px; top: 0;" class="header-logo">
        <h1 style="font-size: 28px; margin: 0; font-weight: bold;">تنظیم حضرت حاجی بہادرؒ، کوہاٹ</h1>
        <h3 style="margin: 10px 0; border-bottom: 1px solid #333; display: inline-block; padding: 0 30px;">اخراجات (Expenses)</h3>
        <div class="mt-2 text-muted no-print" style="font-size: 14px;">ماہانہ اخراجات گوشوارہ (Monthly Expense Ledger)</div>
    </div>

    <!-- Month Selection -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">ماہانہ لیجر (Monthly Expense) - <?php echo $selected_month_label . " " . $year; ?></h4>
        <form class="form-inline d-flex gap-2">
            <select name="month" class="form-control">
                <?php 
                for ($m=1; $m<=12; $m++) {
                    $m_padded = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = ($m_padded == $month) ? 'selected' : '';
                    echo "<option value='$m_padded' $selected>".date('F', mktime(0, 0, 0, $m, 10))."</option>";
                }
                ?>
            </select>
            <select name="year" class="form-control">
                <?php 
                for ($y=2024; $y<=2030; $y++) {
                    $selected = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-dark">منتخب کریں</button>
        </form>
    </div>

    <form method="POST" id="ledger_form">
        <div class="table-responsive">
            <table class="ledger-table" id="ledger_table">
                <thead>
                    <tr>
                        <th width="10%">تاریخ<br>(Date)</th>
                        <th>تفصیل خرچہ (Expense Detail)</th>
                        <th width="12%">کیش (Cash)</th>
                        <th width="12%">درگاہ فنڈ</th>
                        <th width="12%">قبرستان فنڈ</th>
                        <th width="12%">مسجد فنڈ</th>
                        <th width="12%">عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody id="ledger_body">
                    <!-- Fixed Rows -->
                    <?php 
                    $row_idx = 0;
                    foreach ($fixed_expenses as $source): 
                        $val = isset($present_fixed[$source]) ? $present_fixed[$source] : null;
                        if ($val) {
                            $totals['cash'] += $val['amount'];
                            $totals['dargah'] += $val['dargah_fund'];
                            $totals['qabristan'] += $val['qabristan_fund'];
                            $totals['masjid'] += $val['masjid_fund'];
                            $totals['urs'] += $val['urs_fund'];
                        }
                    ?>
                    <tr>
                        <td><input type="date" name="rows[<?php echo $row_idx; ?>][date]" class="form-control form-control-sm" value="<?php echo $val ? $val['expense_date'] : "$year-$month-01"; ?>" style="border:none; text-align:center;">
                            <?php if ($val): ?><input type="hidden" name="rows[<?php echo $row_idx; ?>][id]" value="<?php echo $val['id']; ?>"><?php endif; ?>
                        </td>
                        <td class="fw-bold">
                            <input type="text" name="rows[<?php echo $row_idx; ?>][title]" value="<?php echo $source; ?>" readonly style="border:none; width:100%; font-weight:bold; background:transparent;">
                        </td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][cash]" class="amt-input" data-col="cash" value="<?php echo $val ? $val['amount'] : ''; ?>" placeholder="-"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][dargah]" class="amt-input" data-col="dargah" value="<?php echo $val ? $val['dargah_fund'] : ''; ?>" placeholder="-"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][qabristan]" class="amt-input" data-col="qabristan" value="<?php echo $val ? $val['qabristan_fund'] : ''; ?>" placeholder="-"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][masjid]" class="amt-input" data-col="masjid" value="<?php echo $val ? $val['masjid_fund'] : ''; ?>" placeholder="-"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][urs]" class="amt-input" data-col="urs" value="<?php echo $val ? $val['urs_fund'] : ''; ?>" placeholder="-"></td>
                    </tr>
                    <?php $row_idx++; endforeach; ?>

                    <!-- Dynamic Rows -->
                    <?php foreach ($other_expenses as $de): 
                        $totals['cash'] += $de['amount'];
                        $totals['dargah'] += $de['dargah_fund'];
                        $totals['qabristan'] += $de['qabristan_fund'];
                        $totals['masjid'] += $de['masjid_fund'];
                        $totals['urs'] += $de['urs_fund'];
                    ?>
                    <tr>
                        <td><input type="date" name="rows[<?php echo $row_idx; ?>][date]" class="form-control form-control-sm" value="<?php echo $de['expense_date']; ?>" style="border:none; text-align:center;">
                            <input type="hidden" name="rows[<?php echo $row_idx; ?>][id]" value="<?php echo $de['id']; ?>">
                        </td>
                        <td><input type="text" name="rows[<?php echo $row_idx; ?>][title]" value="<?php echo htmlspecialchars($de['title']); ?>" style="border:none; width:100%; background:transparent;"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][cash]" class="amt-input" data-col="cash" value="<?php echo $de['amount']; ?>"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][dargah]" class="amt-input" data-col="dargah" value="<?php echo $de['dargah_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][qabristan]" class="amt-input" data-col="qabristan" value="<?php echo $de['qabristan_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][masjid]" class="amt-input" data-col="masjid" value="<?php echo $de['masjid_fund']; ?>"></td>
                        <td><input type="number" step="0.01" name="rows[<?php echo $row_idx; ?>][urs]" class="amt-input" data-col="urs" value="<?php echo $de['urs_fund']; ?>"></td>
                    </tr>
                    <?php $row_idx++; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="no-print">
                        <td colspan="7" class="text-right">
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addNewRow()"><i class="fas fa-plus"></i> مزید خرچہ درج کریں (Add New Row)</button>
                        </td>
                    </tr>
                    <tr class="total-row text-center">
                        <td colspan="2" class="text-right px-4">ٹوٹل (Total Expense)</td>
                        <td id="total_cash"><?php echo number_format($totals['cash']); ?></td>
                        <td id="total_dargah"><?php echo number_format($totals['dargah']); ?></td>
                        <td id="total_qabristan"><?php echo number_format($totals['qabristan']); ?></td>
                        <td id="total_masjid"><?php echo number_format($totals['masjid']); ?></td>
                        <td id="total_urs"><?php echo number_format($totals['urs']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary Table (Income vs Expense) -->
        <div class="mt-5">
            <h4 class="mb-3 text-center" style="text-decoration: underline;">میزان (Summary - Balance)</h4>
            <table class="ledger-table">
                <thead>
                    <tr class="table-dark">
                        <th>تفصیل (Description)</th>
                        <th>کیش (Cash)</th>
                        <th>درگاہ فنڈ</th>
                        <th>قبرستان فنڈ</th>
                        <th>مسجد فنڈ</th>
                        <th>عرس فنڈ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center" style="background: #f1f8e9;">
                        <td class="text-right px-4 fw-bold">کل آمدن (Total Income)</td>
                        <td><?php echo number_format($income_totals['cash'] ?: 0); ?></td>
                        <td><?php echo number_format($income_totals['dargah'] ?: 0); ?></td>
                        <td><?php echo number_format($income_totals['qabristan'] ?: 0); ?></td>
                        <td><?php echo number_format($income_totals['masjid'] ?: 0); ?></td>
                        <td><?php echo number_format($income_totals['urs'] ?: 0); ?></td>
                    </tr>
                    <tr class="text-center" style="background: #fbe9e7; color: #d32f2f;">
                        <td class="text-right px-4 fw-bold">کل خرچہ (Total Expense)</td>
                        <td id="summary_exp_cash"><?php echo number_format($totals['cash']); ?></td>
                        <td id="summary_exp_dargah"><?php echo number_format($totals['dargah']); ?></td>
                        <td id="summary_exp_qabristan"><?php echo number_format($totals['qabristan']); ?></td>
                        <td id="summary_exp_masjid"><?php echo number_format($totals['masjid']); ?></td>
                        <td id="summary_exp_urs"><?php echo number_format($totals['urs']); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="grand-total-row text-center">
                        <td class="text-right px-4">میزان (Net Balance)</td>
                        <td id="net_cash"><?php echo number_format(($income_totals['cash'] ?: 0) - $totals['cash']); ?></td>
                        <td id="net_dargah"><?php echo number_format(($income_totals['dargah'] ?: 0) - $totals['dargah']); ?></td>
                        <td id="net_qabristan"><?php echo number_format(($income_totals['qabristan'] ?: 0) - $totals['qabristan']); ?></td>
                        <td id="net_masjid"><?php echo number_format(($income_totals['masjid'] ?: 0) - $totals['masjid']); ?></td>
                        <td id="net_urs"><?php echo number_format(($income_totals['urs'] ?: 0) - $totals['urs']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 text-center no-print">
            <button type="submit" name="save_ledger" class="btn btn-success btn-lg px-5">محفوظ کریں (Save Ledger)</button>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-lg px-5 ml-2">پرنٹ نکالیں (Print Report)</button>
        </div>
    </form>

    <div class="mt-5 d-flex justify-content-between pt-4" style="border-top: 1px solid #eee;">
        <div style="width:250px; text-align:center;">
            <div style="border-top:2px solid #000; padding-top:5px;">فنانس سیکرٹری (Finance Secretary)<br>Syed Afiat Hussain Shah</div>
        </div>
        <div style="width:250px; text-align:center;">
            <div style="border-top:2px solid #000; padding-top:5px;">صدر تنظیم (President)<br>Col Kamil Shah ( R )</div>
        </div>
    </div>
</div>

<script>
let rowCount = <?php echo $row_idx; ?>;
const monthStr = "<?php echo $month; ?>";
const yearStr = "<?php echo $year; ?>";

function addNewRow() {
    const tbody = document.getElementById('ledger_body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="date" name="rows[${rowCount}][date]" class="form-control form-control-sm" value="${yearStr}-${monthStr}-01" style="border:none; text-align:center;"></td>
        <td><input type="text" name="rows[${rowCount}][title]" class="form-control form-control-sm" placeholder="خرچہ کی تفصیل" style="border:none; background:transparent;"></td>
        <td><input type="number" step="0.01" name="rows[${rowCount}][cash]" class="amt-input" data-col="cash"></td>
        <td><input type="number" step="0.01" name="rows[${rowCount}][dargah]" class="amt-input" data-col="dargah"></td>
        <td><input type="number" step="0.01" name="rows[${rowCount}][qabristan]" class="amt-input" data-col="qabristan"></td>
        <td><input type="number" step="0.01" name="rows[${rowCount}][masjid]" class="amt-input" data-col="masjid"></td>
        <td><input type="number" step="0.01" name="rows[${rowCount}][urs]" class="amt-input" data-col="urs"></td>
    `;
    tbody.appendChild(tr);
    rowCount++;
    tr.querySelectorAll('.amt-input').forEach(input => input.addEventListener('input', updateCalc));
}

function updateCalc() {
    let sums = { cash: 0, dargah: 0, qabristan: 0, masjid: 0, urs: 0 };
    document.querySelectorAll('.amt-input').forEach(input => {
        const val = parseFloat(input.value) || 0;
        const col = input.getAttribute('data-col');
        sums[col] += val;
    });

    // Main Table Total
    document.getElementById('total_cash').innerText = sums.cash.toLocaleString();
    document.getElementById('total_dargah').innerText = sums.dargah.toLocaleString();
    document.getElementById('total_qabristan').innerText = sums.qabristan.toLocaleString();
    document.getElementById('total_masjid').innerText = sums.masjid.toLocaleString();
    document.getElementById('total_urs').innerText = sums.urs.toLocaleString();

    // Summary Table Total
    document.getElementById('summary_exp_cash').innerText = sums.cash.toLocaleString();
    document.getElementById('summary_exp_dargah').innerText = sums.dargah.toLocaleString();
    document.getElementById('summary_exp_qabristan').innerText = sums.qabristan.toLocaleString();
    document.getElementById('summary_exp_masjid').innerText = sums.masjid.toLocaleString();
    document.getElementById('summary_exp_urs').innerText = sums.urs.toLocaleString();

    // Net Balance
    const incCash = <?php echo ($income_totals['cash'] ?: 0); ?>;
    const incDargah = <?php echo ($income_totals['dargah'] ?: 0); ?>;
    const incQabristan = <?php echo ($income_totals['qabristan'] ?: 0); ?>;
    const incMasjid = <?php echo ($income_totals['masjid'] ?: 0); ?>;
    const incUrs = <?php echo ($income_totals['urs'] ?: 0); ?>;

    document.getElementById('net_cash').innerText = (incCash - sums.cash).toLocaleString();
    document.getElementById('net_dargah').innerText = (incDargah - sums.dargah).toLocaleString();
    document.getElementById('net_qabristan').innerText = (incQabristan - sums.qabristan).toLocaleString();
    document.getElementById('net_masjid').innerText = (incMasjid - sums.masjid).toLocaleString();
    document.getElementById('net_urs').innerText = (incUrs - sums.urs).toLocaleString();
}

document.querySelectorAll('.amt-input').forEach(input => input.addEventListener('input', updateCalc));
</script>

<?php include 'footer.php'; ?>

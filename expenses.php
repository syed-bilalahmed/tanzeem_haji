<?php
include 'config.php';
include 'header.php';

// Handle Delete Month
if (isset($_GET['delete_month']) && isset($_GET['delete_year'])) {
    $m = $_GET['delete_month'];
    $y = $_GET['delete_year'];
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $stmt->execute([$m, $y]);
    echo "<script>window.location.href='expenses.php';</script>";
}

// Fetch Grouped Monthly Expenses
$stmt = $pdo->query("SELECT 
    MONTH(expense_date) as m, 
    YEAR(expense_date) as y, 
    SUM(amount) as total_cash,
    SUM(dargah_fund) as total_dargah,
    SUM(qabristan_fund) as total_qabristan,
    SUM(masjid_fund) as total_masjid,
    SUM(urs_fund) as total_urs
    FROM expenses 
    GROUP BY y, m 
    ORDER BY y DESC, m DESC");
$monthly_ledgers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grand Totals
$grand = ['cash' => 0, 'dargah' => 0, 'qabristan' => 0, 'masjid' => 0, 'urs' => 0];
foreach ($monthly_ledgers as $l) {
    $grand['cash'] += $l['total_cash'];
    $grand['dargah'] += $l['total_dargah'];
    $grand['qabristan'] += $l['total_qabristan'];
    $grand['masjid'] += $l['total_masjid'];
    $grand['urs'] += $l['total_urs'];
}
?>

<div class="card" dir="rtl">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom:10px;">
        <h3>اخراجات ریکارڈز - گوشوارہ جات (Expense Records - Ledgers)</h3>
        <div>
            <a href="monthly_expense.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> نیا گوشوارہ (New Ledger)</a>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="row text-center mb-4">
        <div class="col-md-2 mb-2">
            <div class="p-2 border rounded bg-light">
                <small class="text-muted d-block">کل کیش (Total Expense)</small>
                <strong><?php echo number_format($grand['cash']); ?>/-</strong>
            </div>
        </div>
        <!-- Funds summarized if needed -->
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center" style="border: 1px solid #000;">
            <thead class="table-dark">
                <tr>
                    <th width="8%">سیریل نمبر</th>
                    <th>مہینہ اور سال (Month & Year)</th>
                    <th>کیش اخراجات (Cash)</th>
                    <th>درگاہ فنڈ</th>
                    <th>قبرستان فنڈ</th>
                    <th>مسجد فنڈ</th>
                    <th>عرس فنڈ</th>
                    <th width="20%">ایکشن (Actions)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_ledgers as $index => $row): 
                    $m_label = date('F', mktime(0, 0, 0, $row['m'], 10));
                ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td class="fw-bold"><?php echo $m_label . " " . $row['y']; ?></td>
                    <td class="text-danger fw-bold"><?php echo number_format($row['total_cash']); ?>/-</td>
                    <td><?php echo number_format($row['total_dargah']); ?></td>
                    <td><?php echo number_format($row['total_qabristan']); ?></td>
                    <td><?php echo number_format($row['total_masjid']); ?></td>
                    <td><?php echo number_format($row['total_urs']); ?></td>
                    <td>
                        <a href="monthly_expense.php?month=<?php echo str_pad($row['m'],2,'0',STR_PAD_LEFT); ?>&year=<?php echo $row['y']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> دیکھیں/ترمیم</a>
                        <a href="monthly_expense.php?month=<?php echo str_pad($row['m'],2,'0',STR_PAD_LEFT); ?>&year=<?php echo $row['y']; ?>&print=1" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-print"></i> پرنٹ</a>
                        <a href="expenses.php?delete_month=<?php echo $row['m']; ?>&delete_year=<?php echo $row['y']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('کیا آپ اس مکمل مہینے کا ریکارڈ حذف کرنا چاہتے ہیں؟')"><i class="fas fa-trash"></i> حذف</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($monthly_ledgers)): ?>
                <tr><td colspan="8">کوئی ریکارڈ نہیں ملا</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

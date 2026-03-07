<?php
include 'config.php';
include_once 'auth_session.php';
if (!has_permission('salaries')) { die("<div style='text-align:center; margin-top:50px; font-size:20px; font-family:Arial;'>Access Denied. You do not have permission to view salaries.</div>"); }
include 'header.php';

// Handle Delete Request
if (isset($_GET['delete_month'])) {
    if(!has_permission('salaries_edit')) { die("Access Denied to Delete."); }
    $del_month = $_GET['delete_month'];
    // Delete all slips for this month
    $stmt_del = $pdo->prepare("DELETE FROM salary_payments WHERE payment_month = ?");
    $stmt_del->execute([$del_month]);
    echo "<script>alert('Record for $del_month deleted successfully!'); window.location.href='salaries.php';</script>";
}

// Fetch Distinct Months and Stats
// Group by payment_month to show one row per month
$query = "
    SELECT 
        payment_month, 
        COUNT(id) as total_slips, 
        SUM(amount) as total_amount, 
        MAX(created_at) as created_at 
    FROM salary_payments 
    GROUP BY payment_month 
    ORDER BY MAX(created_at) DESC
";
$stmt = $pdo->query($query);
$salary_months = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">تنخواہوں کا ریکارڈ (Salary Records)</h2>
        <?php if(has_permission('salaries_edit')): ?>
        <a href="generate_salary.php" class="btn btn-success"><i class="fas fa-plus"></i> نئی تنخواہ جاری کریں (New Entry)</a>
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-striped" dir="rtl" style="margin-top:20px;">
        <thead class="table-dark">
            <tr>
                <th>مہینہ (Month)</th>
                <th>کل رسیدیں (Total Slips)</th>
                <th>کل رقم (Total Amount)</th>
                <th>تاریخ اجراء (Date Generated)</th>
                <th>ایکشن (Actions)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($salary_months) > 0): ?>
                <?php foreach ($salary_months as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['payment_month']); ?></strong></td>
                    <td><?php echo $row['total_slips']; ?></td>
                    <td><?php echo number_format($row['total_amount']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="print_salary_month.php?month=<?php echo urlencode($row['payment_month']); ?>" target="_blank" class="btn btn-warning btn-sm">
                            <i class="fas fa-print"></i> پرنٹ (Print)
                        </a>
                        <?php if(has_permission('salaries_edit')): ?>
                        <a href="edit_salary_month.php?month=<?php echo urlencode($row['payment_month']); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> ترمیم (Edit)
                        </a>
                        <a href="salaries.php?delete_month=<?php echo urlencode($row['payment_month']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete all slips for this month? This cannot be undone.')">
                            <i class="fas fa-trash"></i> حذف (Delete)
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">کوئی ریکارڈ موجود نہیں (No records found)</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

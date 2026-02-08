<?php
include 'config.php';
include 'header.php';

// Fetch Active Employees
$stmt = $pdo->query("SELECT * FROM salary_employees WHERE status='active' ORDER BY id ASC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("UPDATE salary_employees SET status='inactive' WHERE id=?")->execute([$id]);
    echo "<script>window.location.href='employees.php';</script>";
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">ملازمین کی فہرست (Salary Recipients)</h2>
        <div>
            <a href="generate_salary.php" class="btn btn-warning"><i class="fas fa-magic"></i> تنخواہ جاری کریں (Generate Monthly)</a>
            <a href="add_employee.php" class="btn btn-success"><i class="fas fa-plus"></i> نیا ملازم (Add New)</a>
        </div>
    </div>

    <table class="table table-bordered table-striped" dir="rtl" style="margin-top:20px;">
        <thead class="table-dark">
            <tr>
                <th>شمار</th>
                <th>نام (Name)</th>
                <th>عہدہ (Designation)</th>
                <th>پہلے سے طے شدہ رقم (Amount)</th>
                <th>فون نمبر</th>
                <th>ایکشن</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?php echo $emp['id']; ?></td>
                <td><?php echo htmlspecialchars($emp['name']); ?></td>
                <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                <td><?php echo number_format($emp['default_amount']); ?></td>
                <td><?php echo htmlspecialchars($emp['phone']); ?></td>
                <td>
                    <a href="edit_employee.php?id=<?php echo $emp['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> ترمیم (Edit)</a>
                    <a href="employees.php?delete=<?php echo $emp['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Archive this employee?')">غیر فعال (Archive)</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

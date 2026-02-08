<?php
include 'config.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Invalid ID");
}
$id = $_GET['id'];

// Initial Fetch
$stmt = $pdo->prepare("SELECT * FROM salary_employees WHERE id=?");
$stmt->execute([$id]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$emp) {
    die("Employee not found.");
}

// Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    $update = $pdo->prepare("UPDATE salary_employees SET name=?, designation=?, default_amount=?, phone=?, status=? WHERE id=?");
    if ($update->execute([$name, $designation, $amount, $phone, $status, $id])) {
        echo "<script>alert('Employee updated successfully!'); window.location.href='employees.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error updating employee.</div>";
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">ملازم کی تفصیلات میں ترمیم کریں (Edit Employee)</h2>
        <a href="employees.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post">
        <div class="form-group">
            <label>نام (Name):</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($emp['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>عہدہ (Designation):</label>
            <input type="text" name="designation" value="<?php echo htmlspecialchars($emp['designation']); ?>" required>
        </div>

        <div class="form-group">
            <label>ماہانہ رقم (Monthly Amount):</label>
            <input type="number" name="amount" value="<?php echo $emp['default_amount']; ?>" required>
        </div>

        <div class="form-group">
            <label>موبائل نمبر (Phone):</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($emp['phone']); ?>">
        </div>

        <div class="form-group">
            <label>سٹیٹس (Status):</label>
            <select name="status" class="form-control">
                <option value="active" <?php if($emp['status']=='active') echo 'selected'; ?>>فعال (Active)</option>
                <option value="inactive" <?php if($emp['status']=='inactive') echo 'selected'; ?>>غیر فعال (Inactive)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">محفوظ کریں (Save Changes)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

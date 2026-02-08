<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("INSERT INTO salary_employees (name, designation, default_amount, phone) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $designation, $amount, $phone])) {
        echo "<script>alert('Employee added successfully!'); window.location.href='employees.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error adding employee.</div>";
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">نیا ملازم شامل کریں (Add Salary Recipient)</h2>
        <a href="employees.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post" id="employeeForm">
        <div class="form-group">
            <label>نام (Name):</label>
            <input type="text" name="name" required placeholder="مثال: شیر علی">
        </div>

        <div class="form-group">
            <label>عہدہ (Designation):</label>
            <input type="text" name="designation" required placeholder="مثال: امام مسجد">
        </div>

        <div class="form-group">
            <label>ماہانہ رقم (Monthly Amount):</label>
            <input type="number" name="amount" required placeholder="17000">
        </div>

        <div class="form-group">
            <label>موبائل نمبر (Phone):</label>
            <input type="text" name="phone" placeholder="0344-1234567">
        </div>

        <button type="submit" class="btn btn-success">محفوظ کریں (Save)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

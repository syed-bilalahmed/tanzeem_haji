<?php
include 'config.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO expenses (expense_date, title, amount, category) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$date, $title, $amount, $category])) {
        echo "<script>alert('محفوظ ہو گیا'); window.location.href='expenses.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>غلطی! محفوظ نہیں ہو سکا۔</div>";
    }
}
?>

<div class="card">
    <h3>نیا خرچہ (New Expense Entry)</h3>
    <form method="POST">
        <div class="form-group">
            <label>تاریخ (Date)</label>
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>تفصیل (Title)</label>
            <input type="text" name="title" required placeholder="اخراجات کی تفصیل">
        </div>
        <div class="form-group">
            <label>رقم (Amount)</label>
            <input type="number" name="amount" required placeholder="مثال: 500">
        </div>
        <div class="form-group">
            <label>کیٹیگری (Category)</label>
            <select name="category">
                <option value="General">General</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Salary">Salary</option>
                <option value="Utility Bills">Utility Bills</option>
                <option value="Langar">Langar</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">محفوظ کریں</button>
        <a href="expenses.php" class="btn btn-secondary">واپس</a>
    </form>
</div>

<?php include 'footer.php'; ?>

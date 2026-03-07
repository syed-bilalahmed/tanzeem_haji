<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_no = $_POST['shop_no'];
    $shop_name = $_POST['shop_name'];
    $shopkeeper_name = $_POST['shopkeeper_name'];
    $monthly_rent = $_POST['monthly_rent'];
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("INSERT INTO renters (shop_no, shop_name, shopkeeper_name, monthly_rent, phone) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$shop_no, $shop_name, $shopkeeper_name, $monthly_rent, $phone])) {
        echo "<script>alert('Shopkeeper added successfully!'); window.location.href='renters.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error adding shopkeeper.</div>";
    }
}
?>

<div class="card" style="max-width:800px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">نیا دکاندار شامل کریں (Add Shopkeeper)</h2>
        <a href="renters.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post" style="margin-top:20px;">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>دکان نمبر (Shop No):</label>
                <input type="text" name="shop_no" class="form-control" placeholder="12A">
            </div>
            <div class="col-md-6 form-group">
                <label>دکان کا نام (Shop Name):</label>
                <input type="text" name="shop_name" class="form-control" required placeholder="مثال: مدینہ سٹور">
            </div>
            <div class="col-md-6 form-group">
                <label>دکاندار کا نام (Shopkeeper Name):</label>
                <input type="text" name="shopkeeper_name" class="form-control" required placeholder="مثال: علی خان">
            </div>
            <div class="col-md-6 form-group">
                <label>ماہانہ کرایہ (Monthly Rent):</label>
                <input type="number" name="monthly_rent" class="form-control" required placeholder="5000">
            </div>
            <div class="col-md-6 form-group">
                <label>موبائل نمبر (Phone):</label>
                <input type="text" name="phone" class="form-control" placeholder="0344-1234567">
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block mt-3">محفوظ کریں (Save)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

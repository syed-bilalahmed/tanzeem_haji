<?php
include 'config.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Invalid access");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM renters WHERE id = ?");
$stmt->execute([$id]);
$renter = $stmt->fetch();

if (!$renter) {
    die("Shopkeeper not found / deleted.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_no = $_POST['shop_no'];
    $shop_name = $_POST['shop_name'];
    $shopkeeper_name = $_POST['shopkeeper_name'];
    $monthly_rent = $_POST['monthly_rent'];
    $phone = $_POST['phone'];

    $update = $pdo->prepare("UPDATE renters SET shop_no=?, shop_name=?, shopkeeper_name=?, monthly_rent=?, phone=? WHERE id=?");
    if ($update->execute([$shop_no, $shop_name, $shopkeeper_name, $monthly_rent, $phone, $id])) {
        echo "<script>alert('Updated successfully!'); window.location.href='renters.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error updating data.</div>";
    }
}
?>

<div class="card" style="max-width:800px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">دکاندار میں ترمیم (Edit Shopkeeper)</h2>
        <a href="renters.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post" style="margin-top:20px;">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>دکان نمبر (Shop No):</label>
                <input type="text" name="shop_no" class="form-control" value="<?php echo htmlspecialchars($renter['shop_no']); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label>دکان کا نام (Shop Name):</label>
                <input type="text" name="shop_name" class="form-control" required value="<?php echo htmlspecialchars($renter['shop_name']); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label>دکاندار کا نام (Shopkeeper Name):</label>
                <input type="text" name="shopkeeper_name" class="form-control" required value="<?php echo htmlspecialchars($renter['shopkeeper_name']); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label>ماہانہ کرایہ (Monthly Rent):</label>
                <input type="number" name="monthly_rent" class="form-control" required value="<?php echo htmlspecialchars($renter['monthly_rent']); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label>موبائل نمبر (Phone):</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($renter['phone']); ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-3">اپ ڈیٹ (Update)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

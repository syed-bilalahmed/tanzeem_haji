<?php
include 'config.php';
include_once 'auth_session.php';
// if (!has_permission('rents')) { die("<div style='text-align:center; margin-top:50px; font-size:20px; font-family:Arial;'>Access Denied.</div>"); }

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("UPDATE renters SET status='inactive' WHERE id=?")->execute([$id]);
    header("Location: renters.php");
    exit;
}

include 'header.php';

$stmt = $pdo->query("SELECT * FROM renters WHERE status='active' ORDER BY id ASC");
$renters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">دکانداروں کی فہرست (Shopkeepers)</h2>
        <div>
            <a href="add_renter.php" class="btn btn-success"><i class="fas fa-plus"></i> نیا دکاندار (Add New)</a>
        </div>
    </div>

    <table class="table table-bordered table-striped" dir="rtl" style="margin-top:20px;">
        <thead class="table-dark">
            <tr>
                <th>شمار</th>
                <th>دکان نمبر (Shop No)</th>
                <th>دکان کا نام (Shop Name)</th>
                <th>دکاندار کا نام (Shopkeeper)</th>
                <th>ماہانہ کرایہ (Monthly Rent)</th>
                <th>فون نمبر</th>
                <th>ایکشن</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($renters as $renter): ?>
            <tr>
                <td><?php echo $renter['id']; ?></td>
                <td><?php echo htmlspecialchars($renter['shop_no']); ?></td>
                <td><?php echo htmlspecialchars($renter['shop_name']); ?></td>
                <td><?php echo htmlspecialchars($renter['shopkeeper_name']); ?></td>
                <td><?php echo number_format($renter['monthly_rent']); ?></td>
                <td><?php echo htmlspecialchars($renter['phone']); ?></td>
                <td>
                    <a href="edit_renter.php?id=<?php echo $renter['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> ترمیم (Edit)</a>
                    <a href="renters.php?delete=<?php echo $renter['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Archive this shopkeeper?')">غیر فعال (Archive)</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

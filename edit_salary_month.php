<?php
include 'config.php';
include 'header.php';

if (!isset($_GET['month'])) {
    die("Invalid Request");
}
$month = $_GET['month'];

// Update Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ids = $_POST['id'];
    $texts = $_POST['details_text'];
    
    foreach ($ids as $index => $id) {
        $text = $texts[$index];
        $pdo->prepare("UPDATE salary_payments SET details_text=? WHERE id=?")->execute([$text, $id]);
    }
    echo "<script>alert('Updated successfully!'); window.location.href='salaries.php';</script>";
}

// Fetch Slips
$stmt = $pdo->prepare("SELECT * FROM salary_payments WHERE payment_month = ?");
$stmt->execute([$month]);
$slips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">ترمیم کریں (Edit Slips): <?php echo $month; ?></h2>
        <a href="salaries.php" class="btn btn-secondary">واپس (Back)</a>
    </div>
    
    <form method="post">
        <table class="table table-bordered" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th width="10%">ID</th>
                    <th width="80%">عبارت (Text Content)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slips as $slip): ?>
                <tr>
                    <td><?php echo $slip['id']; ?></td>
                    <td>
                        <input type="hidden" name="id[]" value="<?php echo $slip['id']; ?>">
                        <textarea name="details_text[]" class="form-control" rows="2" style="font-family:'Noto Nastaliq Urdu', serif; font-size:16px;"><?php echo htmlspecialchars($slip['details_text']); ?></textarea>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">محفوظ کریں (Save Changes)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

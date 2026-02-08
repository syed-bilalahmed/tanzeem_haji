<?php
include 'config.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}
$id = $_GET['id'];

// Fetch Template
$stmt = $pdo->prepare("SELECT * FROM salary_templates WHERE id=?");
$stmt->execute([$id]);
$tpl = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tpl) {
    die("Template not found.");
}

// Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $text = $_POST['template_text'];

    $update = $pdo->prepare("UPDATE salary_templates SET title=?, template_text=? WHERE id=?");
    if ($update->execute([$title, $text, $id])) {
        echo "<script>alert('Template updated!'); window.location.href='templates.php';</script>";
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">ٹیمپلیٹ میں ترمیم کریں (Edit Template)</h2>
        <a href="templates.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post">
        <div class="form-group">
            <label>عنوان (Title):</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($tpl['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>عبارت (Template Text):</label>
            <textarea name="template_text" class="form-control" rows="4" required style="font-family:'Noto Nastaliq Urdu', serif; font-size:18px;"><?php echo htmlspecialchars($tpl['template_text']); ?></textarea>
            <small class="text-muted d-block mt-2">
                Available Variables: <br>
                <code>{AMOUNT}</code> = رقم<br>
                <code>{MONTH}</code> = مہینہ<br>
                <code>{NAME}</code> = نام<br>
                <code>{DESIGNATION}</code> = عہدہ<br>
                <code>{YEAR}</code> = سال
            </small>
        </div>

        <button type="submit" class="btn btn-primary">محفوظ کریں (Save Changes)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

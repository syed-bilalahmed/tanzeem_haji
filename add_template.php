<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $text = $_POST['template_text'];

    $stmt = $pdo->prepare("INSERT INTO salary_templates (title, template_text) VALUES (?, ?)");
    if ($stmt->execute([$title, $text])) {
        echo "<script>alert('Template added!'); window.location.href='templates.php';</script>";
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">نیا ٹیمپلیٹ شامل کریں (Add Receipt Template)</h2>
        <a href="templates.php" class="btn btn-secondary">واپس (Back)</a>
    </div>

    <form method="post">
        <div class="form-group">
            <label>عنوان (Title):</label>
            <input type="text" name="title" required placeholder="مثال: عید بونس">
        </div>

        <div class="form-group">
            <label>عبارت (Template Text):</label>
            <textarea name="template_text" class="form-control" rows="4" required style="font-family:'Noto Nastaliq Urdu', serif; font-size:18px;">مبلغ {AMOUNT} روپے ماہ {MONTH} {NAME} ({DESIGNATION}) نے وصول پائے اور تحریر دی تاکہ سند رہے۔</textarea>
            <small class="text-muted d-block mt-2">
                Available Variables: <br>
                <code>{AMOUNT}</code> = رقم<br>
                <code>{MONTH}</code> = مہینہ<br>
                <code>{NAME}</code> = نام<br>
                <code>{DESIGNATION}</code> = عہدہ<br>
                <code>{YEAR}</code> = سال
            </small>
        </div>

        <button type="submit" class="btn btn-success">محفوظ کریں (Save)</button>
    </form>
</div>

<?php include 'footer.php'; ?>

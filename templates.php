<?php
include 'config.php';
include 'header.php';

$stmt = $pdo->query("SELECT * FROM salary_templates ORDER BY id ASC");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM salary_templates WHERE id=?")->execute([$id]);
    echo "<script>window.location.href='templates.php';</script>";
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">رسیدوں کے ٹیمپلیٹس (Receipt Templates)</h2>
        <div>
            <a href="generate_salary.php" class="btn btn-warning">تنخواہ جاری کریں (Generator)</a>
            <a href="add_template.php" class="btn btn-success"><i class="fas fa-plus"></i> نیا ٹیمپلیٹ (New Template)</a>
        </div>
    </div>

    <table class="table table-bordered table-striped" dir="rtl" style="margin-top:20px;">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>عنوان (Title)</th>
                <th>عبارت (Preview Text)</th>
                <th>ایکشن (Action)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $tpl): ?>
            <tr>
                <td><?php echo $tpl['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($tpl['title']); ?></strong></td>
                <td style="font-family:'Noto Nastaliq Urdu', serif;"><?php echo htmlspecialchars($tpl['template_text']); ?></td>
                <td>
                    <a href="edit_template.php?id=<?php echo $tpl['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> ترمیم (Edit)</a>
                    <?php if(!$tpl['is_default']): ?>
                    <a href="templates.php?delete=<?php echo $tpl['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template?')">ختم کریں</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

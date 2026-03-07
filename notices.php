<?php
include 'config.php';
include_once 'auth_session.php';
if (!has_permission('notices')) { die("<div style='text-align:center; margin-top:50px; font-size:20px; font-family:Arial;'>Access Denied. You do not have permission to view notices.</div>"); }
include 'header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    if(!has_permission('notices_edit')) { die("Access Denied to Delete Notice."); }
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='notices.php';</script>";
}

// Fetch All Notices
$stmt = $pdo->query("SELECT * FROM notices ORDER BY notice_date DESC");
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>نوٹیفکیشن / رسید ریکارڈز (All Notices)</h3>
        <div>
            <a href="print_all_notices.php" class="btn btn-warning" target="_blank" style="margin-right:5px;"><i class="fas fa-print"></i> تمام پرنٹ کریں (Print All)</a>
            <?php if(has_permission('notices_edit')): ?>
            <a href="add_notice.php" class="btn btn-success"><i class="fas fa-plus"></i> نیا اندراج (New Notice)</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($notices) > 0): ?>
        <div style="display:grid; gap:20px; margin-top:20px;">
            <?php foreach ($notices as $notice): ?>
            <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:8px; border-right:4px solid #1b5e20;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:10px;">
                    <h3 style="margin:0; color:#1b5e20;"><?php echo htmlspecialchars($notice['topic']); ?></h3>
                    <span style="color:#666; font-size:0.9em;">
                        <?php echo date('d-m-Y', strtotime($notice['notice_date'])); ?>
                    </span>
                </div>
                <?php
                $dir = ($notice['lang'] ?? 'ur') === 'en' ? 'ltr' : 'rtl';
                $align = ($notice['lang'] ?? 'ur') === 'en' ? 'left' : 'right';
                ?>
                <div style="font-size:1.1em; line-height:1.6; color:#333; margin-bottom:15px; overflow:hidden; max-height:100px; direction: <?php echo $dir; ?>; text-align: <?php echo $align; ?>;">
                    <?php echo $notice['details']; ?> <!-- Output Raw HTML -->
                </div>
                <div style="text-align:left;">
                    <a href="generate_notice_pdf.php?id=<?php echo $notice['id']; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-eye"></i> View PDF</a>
                    <?php if(has_permission('notices_edit')): ?>
                    <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <a href="notices.php?delete=<?php echo $notice['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this notice?')"><i class="fas fa-trash"></i> Delete</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#666;">No notices found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

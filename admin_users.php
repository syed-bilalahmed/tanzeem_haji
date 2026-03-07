<?php
include 'config.php';
include 'header.php'; // Secured internally with auth_session

// Only admin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div class='container mt-5'><div class='alert alert-danger'>اس صفحے تک رسائی صرف ایڈمن کے لیے ہے۔ (Admin Access Only)</div></div>");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prevent admin from deleting themselves
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>window.location.href='admin_users.php?msg=deleted';</script>";
    } else {
        echo "<script>alert('آپ خود کو حذف نہیں کر سکتے! (You cannot delete your own account!)'); window.location.href='admin_users.php';</script>";
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'access_helper.php';
$modules = get_all_modules(); // For mapping raw permission keys to nice labels
?>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if($_GET['msg'] == 'added') echo "<strong>کامیابی!</strong> نیا یوزر شامل کر لیا گیا (New User Added).";
        if($_GET['msg'] == 'updated') echo "<strong>کامیابی!</strong> یوزر اپ ڈیٹ ہو گیا (User Updated).";
        if($_GET['msg'] == 'deleted') echo "<strong>کامیابی!</strong> یوزر حذف کر دیا گیا (User Deleted).";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">یوزرز مینجمنٹ (User Management)</h2>
        <div>
            <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> نیا یوزر (Add New User)</a>
        </div>
    </div>

    <div class="table-responsive" style="margin-top:20px;">
        <table class="table table-bordered table-striped text-center" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>یوزر نیم (Username)</th>
                    <th>رول (Role)</th>
                    <th>اجازتیں (Permissions)</th>
                    <th>ایکشن (Action)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger">ایڈمن (Admin)</span>
                            <?php else: ?>
                                <span class="badge bg-primary">یوزر (User)</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; max-width: 300px;">
                            <?php 
                            if ($u['role'] === 'admin') {
                                echo "<i class='text-muted'>تمام ختیارات (All Access)</i>";
                            } else {
                                $perms = explode(',', $u['permissions']);
                                $badges = [];
                                foreach ($perms as $p) {
                                    $p = trim($p);
                                    if (empty($p)) continue;
                                    
                                    $is_edit = str_ends_with($p, '_edit');
                                    $base_module = $is_edit ? str_replace('_edit', '', $p) : $p;
                                    
                                    $label_prefix = $is_edit ? "Edit - " : "View - ";
                                    $bg_class = $is_edit ? "bg-success" : "bg-info text-dark";

                                    if (array_key_exists($base_module, $modules)) {
                                        $badges[] = "<span class='badge $bg_class mb-1' style='font-size: 0.85em;'>" . $label_prefix . $modules[$base_module]['label'] . "</span>";
                                    } else {
                                        $badges[] = "<span class='badge bg-secondary mb-1'>$p</span>";
                                    }
                                }
                                echo empty($badges) ? '<span class="text-muted">کوئی نہیں (None)</span>' : implode(' ', $badges);
                            }
                            ?>
                        </td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> ترمیم (Edit)</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('واقعی حذف کرنا چاہتے ہیں؟ (Are you sure you want to delete this user?)')"><i class="fas fa-trash"></i> حذف (Delete)</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

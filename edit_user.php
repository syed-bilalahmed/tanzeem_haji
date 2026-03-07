<?php
include 'config.php';
include 'header.php';
include_once 'access_helper.php';

// Only admin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div class='container mt-5'><div class='alert alert-danger'>اس صفحے تک رسائی صرف ایڈمن کے لیے ہے۔ (Admin Access Only)</div></div>");
}

if (!isset($_GET['id'])) {
    die('User ID not provided.');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$edit_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$edit_user) {
    die('User not found.');
}

$modules = get_all_modules();
$error = '';
$current_perms = explode(',', $edit_user['permissions']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    
    // Handled allowed permissions
    $permissions_array = [];
    if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
        foreach ($_POST['permissions'] as $mod_key => $mod_val) {
            if ($mod_val === 'edit') {
                $permissions_array[] = $mod_key . '_edit';
            } elseif ($mod_val === 'view') {
                $permissions_array[] = $mod_key;
            }
            // If 'none', we don't add it.
        }
    }
    $permissions_str = implode(',', $permissions_array);
    
    // Password reset Logic
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if username already taken by another user
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt_check->execute([$username, $id]);
    if ($stmt_check->fetchColumn() > 0) {
        $error = "یہ یوزر نیم پہلے سے موجود ہے۔ (Username already exists.)";
    } else {
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                $error = "پاس ورڈ آپس میں مماثل نہیں ہیں۔ (Passwords do not match.)";
            } else {
                // Update with new password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, permissions=?, password=? WHERE id=?");
                $stmt->execute([$username, $role, $permissions_str, $hashed, $id]);
                echo "<script>window.location.href='admin_users.php?msg=updated';</script>";
                exit;
            }
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, permissions=? WHERE id=?");
            $stmt->execute([$username, $role, $permissions_str, $id]);
            echo "<script>window.location.href='admin_users.php?msg=updated';</script>";
            exit;
        }
    }
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">یوزر میں ترمیم کریں (Edit User)</h2>
        <a href="admin_users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> واپس (Back)</a>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="" class="mt-4" dir="rtl" style="text-align: right;">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">یوزر نیم (Username)</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required style="direction: ltr; text-align: left;">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">رول (Role)</label>
                <select name="role" id="roleSelect" class="form-select">
                    <option value="user" <?php if($edit_user['role']=='user') echo 'selected'; ?>>یوزر (Standard User)</option>
                    <option value="admin" <?php if($edit_user['role']=='admin') echo 'selected'; ?>>ایڈمن (Administrator)</option>
                </select>
                <small class="text-muted">ایڈمن کو تمام اختیارات حاصل ہوتے ہیں۔</small>
            </div>
        </div>

        <div class="alert alert-info py-2 mt-3">
            <strong>نوٹ:</strong> اگر آپ پاس ورڈ تبدیل نہیں کرنا چاہتے تو ان خانوں کو خالی چھوڑ دیں۔ (Leave blank if you don't want to change the password.)
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">نیا پاس ورڈ (New Password)</label>
                <input type="password" name="password" class="form-control" style="direction: ltr; text-align: left;">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">پاس ورڈ کی تصدیق (Confirm Password)</label>
                <input type="password" name="confirm_password" class="form-control" style="direction: ltr; text-align: left;">
            </div>
        </div>

        <hr class="my-4">

        <div id="permissionsSection" style="<?php echo ($edit_user['role'] === 'admin') ? 'opacity:0.5; pointer-events:none;' : ''; ?>">
            <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-lock"></i> ماڈیولز کے حقوق (Module Permissions)</h5>
            <p class="text-muted small">جن حصوں تک رسائی دینا چاہتے ہیں ان کا انتخاب کریں۔</p>
            
            <div class="row">
                <?php foreach ($modules as $key => $module): ?>
                    <?php 
                        $val = 'none';
                        if (in_array($key . '_edit', $current_perms)) {
                            $val = 'edit';
                        } elseif (in_array($key, $current_perms)) {
                            $val = 'view';
                        }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="p-3 border rounded border-success" style="background:#f8fdf8;">
                            <div class="fw-bold mb-2"><?php echo $module['label']; ?></div>
                            <div class="d-flex justify-content-start gap-3">
                                <div class="form-check">
                                    <input class="form-check-input perm-radio" type="radio" name="permissions[<?php echo $key; ?>]" value="none" id="perm_<?php echo $key; ?>_none" <?php echo ($val=='none') ? 'checked' : ''; ?> style="float:none;">
                                    <label class="form-check-label" for="perm_<?php echo $key; ?>_none">کوئی نہیں (None)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input perm-radio" type="radio" name="permissions[<?php echo $key; ?>]" value="view" id="perm_<?php echo $key; ?>_view" <?php echo ($val=='view') ? 'checked' : ''; ?> style="float:none;">
                                    <label class="form-check-label text-primary" for="perm_<?php echo $key; ?>_view">صرف دیکھیں (View)</label>
                                </div>
                                <?php if ($module['has_edit']): ?>
                                <div class="form-check">
                                    <input class="form-check-input perm-radio" type="radio" name="permissions[<?php echo $key; ?>]" value="edit" id="perm_<?php echo $key; ?>_edit" <?php echo ($val=='edit') ? 'checked' : ''; ?> style="float:none;">
                                    <label class="form-check-label text-danger" for="perm_<?php echo $key; ?>_edit">مکمل رسائی (Edit)</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-warning btn-lg mt-4 w-100"><i class="fas fa-edit"></i> اپ ڈیٹ کریں (Update User)</button>
    </form>
</div>

<script>
// Hide permissions if Admin is selected
document.getElementById('roleSelect').addEventListener('change', function() {
    var pSec = document.getElementById('permissionsSection');
    if (this.value === 'admin') {
        pSec.style.opacity = '0.5';
        pSec.style.pointerEvents = 'none';
    } else {
        pSec.style.opacity = '1';
        pSec.style.pointerEvents = 'auto';
    }
});
</script>

<?php include 'footer.php'; ?>

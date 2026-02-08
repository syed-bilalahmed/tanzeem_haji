<?php
include 'config.php';
include 'header.php'; // Includes auth_session.php, so it's protected

$success = '';
$error = '';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings = [
        'org_name' => trim($_POST['org_name']),
        'org_name_en' => trim($_POST['org_name_en']),
        'hero_title' => trim($_POST['hero_title']),
        'hero_title_en' => trim($_POST['hero_title_en']),
        'hero_subtitle' => trim($_POST['hero_subtitle']),
        'hero_subtitle_en' => trim($_POST['hero_subtitle_en']),
        'about_text' => trim($_POST['about_text']),
        'about_text_en' => trim($_POST['about_text_en']),
        'contact_address' => trim($_POST['contact_address']),
        'contact_address_en' => trim($_POST['contact_address_en']),
        'contact_phone' => trim($_POST['contact_phone']),
        'contact_email' => trim($_POST['contact_email'])
    ];

    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = $pdo->prepare($sql);

    try {
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $success = "سیٹنگز کامیابی سے محفوظ ہو گئیں۔";
    } catch (PDOException $e) {
        $error = "خرابی: " . $e->getMessage();
    }
}

// Fetch Current Settings
$details = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $details[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm border-0 mt-4 rounded-3">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> مین پیج سیٹنگز (Website Settings)</h5>
            </div>
            <div class="card-body">
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Org Name -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Organization Name (English)</label>
                            <input type="text" name="org_name_en" class="form-control" value="<?php echo $details['org_name_en'] ?? ''; ?>" dir="ltr">
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label fw-bold">تنظیم کا نام (Urdu)</label>
                            <input type="text" name="org_name" class="form-control text-end" value="<?php echo $details['org_name'] ?? ''; ?>" required>
                        </div>
                    </div>

                    <!-- Hero Title -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hero Title (English)</label>
                            <input type="text" name="hero_title_en" class="form-control" value="<?php echo $details['hero_title_en'] ?? ''; ?>" dir="ltr">
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label fw-bold">ہیڈ لائن (Urdu)</label>
                            <input type="text" name="hero_title" class="form-control text-end" value="<?php echo $details['hero_title'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Hero Subtitle -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hero Subtitle (English)</label>
                            <textarea name="hero_subtitle_en" class="form-control" rows="2" dir="ltr"><?php echo $details['hero_subtitle_en'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label fw-bold">ذیلی ہیڈ لائن (Urdu)</label>
                            <textarea name="hero_subtitle" class="form-control text-end" rows="2"><?php echo $details['hero_subtitle'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <!-- About Text -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">About Us (English)</label>
                            <textarea name="about_text_en" class="form-control" rows="5" dir="ltr"><?php echo $details['about_text_en'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label fw-bold">تعارف (Urdu)</label>
                            <textarea name="about_text" class="form-control text-end" rows="5"><?php echo $details['about_text'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Contact & Address -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Address (English)</label>
                            <input type="text" name="contact_address_en" class="form-control" value="<?php echo $details['contact_address_en'] ?? ''; ?>" dir="ltr">
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="form-label fw-bold">ایڈریس (Urdu)</label>
                            <input type="text" name="contact_address" class="form-control text-end" value="<?php echo $details['contact_address'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 text-end">
                            <label class="form-label fw-bold">فون نمبر (Phone)</label>
                            <input type="text" name="contact_phone" class="form-control text-end" value="<?php echo $details['contact_phone'] ?? ''; ?>" dir="ltr">
                        </div>
                        <div class="col-md-6 mb-3 text-end">
                            <label class="form-label fw-bold">ای میل (Email)</label>
                            <input type="email" name="contact_email" class="form-control text-end" value="<?php echo $details['contact_email'] ?? ''; ?>" dir="ltr">
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-success px-5"><i class="fas fa-save"></i> محفوظ کریں (Save)</button>
                        <a href="index.php" target="_blank" class="btn btn-outline-primary ms-2"><i class="fas fa-external-link-alt"></i> ویب سائٹ دیکھیں (View Site)</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

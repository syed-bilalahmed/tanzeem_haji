<?php
include 'config.php';
include 'header.php';

// Fetch Templates
$tpl_stmt = $pdo->query("SELECT * FROM salary_templates");
$templates = $tpl_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Active Employees for Display
$emp_stmt = $pdo->query("SELECT * FROM salary_employees WHERE status='active' ORDER BY id ASC");
$active_employees = $emp_stmt->fetchAll(PDO::FETCH_ASSOC);

// If Form Submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month_start = $_POST['month_name'];
    $month_end = $_POST['month_to']; // Optional
    $year = $_POST['year'];
    
    // Construct Month String
    $month_display = $month_start;
    if (!empty($month_end)) {
        $month_display = $month_start . ' تا ' . $month_end;
    }
    
    $template_id = $_POST['template_id'];
    $selected_emps = $_POST['emp_ids'] ?? []; // Array of selected IDs
    
    if (empty($selected_emps)) {
        echo "<script>alert('Please select at least one employee!');</script>";
    } else {
        // Get Selected Template Text
        $sel_tpl = $pdo->prepare("SELECT template_text FROM salary_templates WHERE id=?");
        $sel_tpl->execute([$template_id]);
        $raw_text = $sel_tpl->fetchColumn();

        if (!$raw_text) die("Template not found.");

        $count = 0;
        
        // Loop Only Selected IDs
        $IN_placeholders = implode(',', array_fill(0, count($selected_emps), '?'));
        $stmt_sel = $pdo->prepare("SELECT * FROM salary_employees WHERE id IN ($IN_placeholders)");
        $stmt_sel->execute($selected_emps);
        $employees_to_pay = $stmt_sel->fetchAll(PDO::FETCH_ASSOC);

        foreach ($employees_to_pay as $emp) {
            $amount = $emp['default_amount'];
            $name = $emp['name'];
            $desig = $emp['designation'];
            
            // Dynamic Replacement
            // We use $month_display for {MONTH}
            $details = str_replace(
                ['{AMOUNT}', '{MONTH}', '{NAME}', '{DESIGNATION}', '{YEAR}'], 
                [number_format($amount, 0, '', ''), $month_display, $name, $desig, $year], 
                $raw_text
            );
            
            // Store the full range string in payment_month so it shows correctly in list
            $insert = $pdo->prepare("INSERT INTO salary_payments (employee_id, payment_month, payment_year, amount, details_text) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$emp['id'], $month_display . " " . $year, $year, $amount, $details]);
            $count++;
        }
        
        echo "<script>alert('$count salary slips generated successfully!'); window.location.href='salaries.php';</script>";
    }
}
?>

<div class="card" style="max-width:800px; margin:0 auto;">
    <h2 class="section-title">تنخواہ کی رسیدیں جاری کریں (Generate Salaries)</h2>
    <p class="text-muted text-center">Select Month Range, Template, and Employees.</p>
    
    <form method="post">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>مہینہ شروع (Start Month):</label>
                    <input type="text" name="month_name" required placeholder="مثال: ستمبر" value="<?php echo date('F'); ?>" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>مہینہ ختم (End Month - Optional):</label>
                    <input type="text" name="month_to" placeholder="مثال: دسمبر" class="form-control">
                    <small class="text-muted">Leave empty for single month.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>سال (Year):</label>
                    <input type="number" name="year" required value="<?php echo date('Y'); ?>" class="form-control">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>ٹیمپلیٹ منتخب کریں (Select Template):</label>
            <select name="template_id" class="form-control">
                <?php foreach($templates as $tpl): ?>
                <option value="<?php echo $tpl['id']; ?>">
                    <?php echo $tpl['title']; ?> 
                    (<?php echo mb_substr($tpl['template_text'], 0, 30); ?>...)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <h4 style="margin-top:20px; border-bottom:1px solid #ccc; padding-bottom:5px;">ملازمین منتخب کریں (Select People to Pay)</h4>
        
        <div style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-sm text-center" dir="rtl">
                <thead class="table-dark">
                    <tr>
                        <th width="10%"><input type="checkbox" id="selectAll" checked></th>
                        <th>نام (Name)</th>
                        <th>عہدہ (Designation)</th>
                        <th>رقم (Amount)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($active_employees as $emp): ?>
                    <tr>
                        <td><input type="checkbox" name="emp_ids[]" value="<?php echo $emp['id']; ?>" class="emp-checkbox" checked></td>
                        <td><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                        <td><?php echo number_format($emp['default_amount']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-3">جاری کریں (Generate)</button>
    </form>
    
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.emp-checkbox');
            for(var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
    </script>
</div>


<?php include 'footer.php'; ?>

<?php
include 'config.php';
include 'auth_session.php'; // Ensure user is logged in
include 'header.php';

function parse_month_year_value($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $value = preg_replace('/[\/_-]+/u', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', trim($value));
    $parts = preg_split('/\s+/u', $value);
    if (!$parts || count($parts) > 2) {
        return null;
    }

    $month_token_raw = $parts[0];
    $month_token = function_exists('mb_strtolower') ? mb_strtolower($month_token_raw, 'UTF-8') : strtolower($month_token_raw);
    $year = (count($parts) === 2 && preg_match('/^\d{4}$/', $parts[1])) ? (int) $parts[1] : (int) date('Y');

    $month_map = [
        'january' => 1, 'jan' => 1, 'jany' => 1,
        'february' => 2, 'feb' => 2, 'feburary' => 2, 'febuary' => 2,
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12, 'decmber' => 12,
        'جنوری' => 1,
        'فروری' => 2,
        'مارچ' => 3,
        'اپریل' => 4,
        'مئی' => 5,
        'جون' => 6,
        'جولائی' => 7,
        'اگست' => 8,
        'ستمبر' => 9,
        'اکتوبر' => 10,
        'نومبر' => 11,
        'دسمبر' => 12
    ];

    if (isset($month_map[$month_token])) {
        return ['year' => $year, 'month' => $month_map[$month_token]];
    }

    return null;
}

function get_month_span_inclusive($month_from, $month_to) {
    $start = parse_month_year_value($month_from);
    if (!$start) {
        return 1;
    }

    $end = trim((string) $month_to) === '' ? $start : parse_month_year_value($month_to);
    if (!$end) {
        $end = $start;
    }

    $start_index = ($start['year'] * 12) + $start['month'];
    $end_index = ($end['year'] * 12) + $end['month'];

    if ($end_index < $start_index) {
        [$start_index, $end_index] = [$end_index, $start_index];
    }

    return ($end_index - $start_index) + 1;
}

// Fetch all active renters
$stmt = $pdo->query("SELECT * FROM renters WHERE status = 'active' ORDER BY shop_no ASC");
$renters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receipt_date = $_POST['receipt_date'];
    $month_from   = trim($_POST['month_from']);
    $month_to     = trim($_POST['month_to']);
    $months_count = get_month_span_inclusive($month_from, $month_to);
    
    // Arrays from form
    $generate_flags   = isset($_POST['generate']) ? $_POST['generate'] : [];
    $renter_ids       = $_POST['renter_id'];
    $monthly_rents    = $_POST['monthly_rent'];
    $arrears_list     = $_POST['arrears'];
    $received_amounts = $_POST['received_amount'];
    $notes_list       = $_POST['notes'];

    if (empty($receipt_date) || empty($month_from)) {
        $error = "تاریخ اور مہینہ از (Month From) ضروری ہیں۔";
    } elseif (empty($generate_flags)) {
        $error = "براہ کرم رسید بنانے کے لئے کم از کم ایک دکاندار منتخب کریں۔ (Select at least one renter)";
    } else {
        try {
            $pdo->beginTransaction();
            $inserted_ids = [];
            
            // Generate next consecutive receipt_no
            $stmt_last = $pdo->query("SELECT MAX(CAST(receipt_no AS UNSIGNED)) as max_receipt FROM rent_collections");
            $last_receipt = $stmt_last->fetch();
            $next_receipt_no = ($last_receipt['max_receipt'] ? $last_receipt['max_receipt'] : 0);

            $insert_stmt = $pdo->prepare("
                INSERT INTO rent_collections (
                    renter_id, receipt_date, receipt_no, month_from, month_to, 
                    monthly_rent, arrears, total_amount, amount_received, remaining_balance, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            // Process each selected renter
            foreach ($generate_flags as $index => $val) {
                // $index is the key from the loop, matching the row in HTML
                // Check if this renter was actually submitted with basic data
                if (isset($renter_ids[$index])) {
                    $r_id = $renter_ids[$index];
                    $r_monthly = floatval($monthly_rents[$index]);
                    $r_arrears = floatval($arrears_list[$index]);
                    $r_received = floatval($received_amounts[$index]);
                    $r_notes = $notes_list[$index];

                    // Enforce backend total from month range.
                    $r_total = ($r_monthly * $months_count) + $r_arrears;
                    
                    $r_remaining = $r_total - $r_received;
                    
                    $next_receipt_no++;

                    $insert_stmt->execute([
                        $r_id,
                        $receipt_date,
                        $next_receipt_no, // Auto Assigning sequential receipt no
                        $month_from,
                        $month_to,
                        $r_monthly,
                        $r_arrears,
                        $r_total,
                        $r_received,
                        $r_remaining,
                        $r_notes
                    ]);
                    
                    $inserted_ids[] = $pdo->lastInsertId();
                }
            }

            $pdo->commit();
            
            // Redirect to print page with multi-IDs
            $ids_param = implode(',', $inserted_ids);
            echo "<script>window.location.href='print_rent_receipts_batch.php?ids=" . $ids_param . "';</script>";
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "ڈیٹا بیس کی خرابی (Database Error): " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid" style="padding: 20px;">
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">دکانوں کا ایک ساتھ کرایہ شامل کریں (Batch Rent Receipts)</h4>
        </div>
        <div class="card-body">
            <form action="" method="POST" id="batchForm">
                
                <!-- Global Settings -->
                <div class="row mb-4" style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
                    <div class="col-md-4 form-group">
                        <label>وصولی کی تاریخ (Receipt Date): <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>مہینہ از (Month From): <span class="text-danger">*</span></label>
                        <input type="text" name="month_from" id="month_from" class="form-control" placeholder="مثلاً: مارچ 2026" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>مہینہ تک (Month To):</label>
                        <input type="text" name="month_to" id="month_to" class="form-control" placeholder="مثلاً: مئی 2026 (اختیاری)">
                    </div>
                </div>

                <!-- Renters Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="min-width: 1100px;">
                        <thead class="table-dark">
                            <tr>
                                <th width="50" class="text-center">
                                    <input type="checkbox" id="selectAll" checked>
                                </th>
                                <th width="200">دکان / دکاندار</th>
                                <th>ماہانہ کرایہ</th>
                                <th width="100">مہینوں کی تعداد</th>
                                <th>پچھلا بقایا</th>
                                <th>کل رقم (Rs)</th>
                                <th>وصول شدہ (Rs)</th>
                                <th>باقی بقایا (Rs)</th>
                                <th width="120">نوٹ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($renters as $index => $r): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="generate[<?php echo $index; ?>]" value="1" class="row-checkbox" checked>
                                    <input type="hidden" name="renter_id[<?php echo $index; ?>]" value="<?php echo $r['id']; ?>">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($r['shop_no']); ?></strong> - <?php echo htmlspecialchars($r['shop_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($r['shopkeeper_name']); ?></small>
                                </td>
                                <td>
                                    <input type="number" name="monthly_rent[<?php echo $index; ?>]" id="rent_<?php echo $index; ?>" class="form-control input-sm row-calc" value="<?php echo floatval($r['monthly_rent']); ?>" onchange="calcRow(<?php echo $index; ?>)" onkeyup="calcRow(<?php echo $index; ?>)">
                                </td>
                                <td>
                                    <!-- Default to 1 month -->
                                    <input type="number" id="months_<?php echo $index; ?>" class="form-control input-sm row-calc" value="1" min="1" readonly>
                                </td>
                                <td>
                                    <input type="number" name="arrears[<?php echo $index; ?>]" id="arrears_<?php echo $index; ?>" class="form-control input-sm row-calc" value="0" onchange="calcRow(<?php echo $index; ?>)" onkeyup="calcRow(<?php echo $index; ?>)">
                                </td>
                                <td>
                                    <input type="number" name="total_amount[<?php echo $index; ?>]" id="total_<?php echo $index; ?>" class="form-control input-sm text-primary font-weight-bold row-calc" value="<?php echo floatval($r['monthly_rent']); ?>" onchange="calcBal(<?php echo $index; ?>)" onkeyup="calcBal(<?php echo $index; ?>)">
                                </td>
                                <td>
                                    <input type="number" name="received_amount[<?php echo $index; ?>]" id="received_<?php echo $index; ?>" class="form-control input-sm text-success font-weight-bold row-calc" value="<?php echo floatval($r['monthly_rent']); ?>" onchange="calcBal(<?php echo $index; ?>)" onkeyup="calcBal(<?php echo $index; ?>)">
                                </td>
                                <td>
                                    <input type="number" id="balance_<?php echo $index; ?>" class="form-control input-sm text-danger" value="0" readonly>
                                </td>
                                <td>
                                    <input type="text" name="notes[<?php echo $index; ?>]" class="form-control input-sm" placeholder="اختیاری">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5">سبمٹ کریں اور پرنٹ کریں (Save All & Print)</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const monthFromInput = document.getElementById('month_from');
    const monthToInput = document.getElementById('month_to');

    function parseMonthYear(text) {
        const value = (text || '').trim();
        if (!value) return null;

        const normalized = value.replace(/[\/_-]+/g, ' ').replace(/\s+/g, ' ').trim();
        const parts = normalized.split(' ');
        if (parts.length < 1 || parts.length > 2) return null;

        const token = parts[0].toLowerCase();
        const year = (parts.length === 2 && /^\d{4}$/.test(parts[1])) ? parseInt(parts[1], 10) : new Date().getFullYear();

        const monthMap = {
            'january': 1, 'jan': 1, 'jany': 1,
            'february': 2, 'feb': 2, 'feburary': 2, 'febuary': 2,
            'march': 3, 'mar': 3,
            'april': 4, 'apr': 4,
            'may': 5,
            'june': 6, 'jun': 6,
            'july': 7, 'jul': 7,
            'august': 8, 'aug': 8,
            'september': 9, 'sep': 9, 'sept': 9,
            'october': 10, 'oct': 10,
            'november': 11, 'nov': 11,
            'december': 12, 'dec': 12, 'decmber': 12,
            'جنوری': 1,
            'فروری': 2,
            'مارچ': 3,
            'اپریل': 4,
            'مئی': 5,
            'جون': 6,
            'جولائی': 7,
            'اگست': 8,
            'ستمبر': 9,
            'اکتوبر': 10,
            'نومبر': 11,
            'دسمبر': 12
        };

        if (monthMap[token]) {
            return { year: year, month: monthMap[token] };
        }

        return null;
    }

    function getMonthsCount() {
        const start = parseMonthYear(monthFromInput.value);
        if (!start) return 1;

        const end = parseMonthYear(monthToInput.value) || start;
        let startIndex = (start.year * 12) + start.month;
        let endIndex = (end.year * 12) + end.month;

        if (endIndex < startIndex) {
            [startIndex, endIndex] = [endIndex, startIndex];
        }

        return (endIndex - startIndex) + 1;
    }

    function recalcAllRows() {
        document.querySelectorAll('[id^="months_"]').forEach((el) => {
            const idx = el.id.replace('months_', '');
            el.value = getMonthsCount();
            calcRow(idx);
        });
    }

    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        for(let chk of checkboxes) {
            chk.checked = this.checked;
        }
    });

    // Calculate specific row total = (rent * months) + arrears
    function calcRow(index) {
        let rent = parseFloat(document.getElementById('rent_'+index).value) || 0;
        let months = getMonthsCount();
        document.getElementById('months_'+index).value = months;
        let arrears = parseFloat(document.getElementById('arrears_'+index).value) || 0;
        let total = (rent * months) + arrears;
        
        document.getElementById('total_'+index).value = total;
        
        // Auto-fill received amount assuming they are paying in full by default
        document.getElementById('received_'+index).value = total;
        calcBal(index);
    }

    // Calculate specific row balance = total - received
    function calcBal(index) {
        let total = parseFloat(document.getElementById('total_'+index).value) || 0;
        let received = parseFloat(document.getElementById('received_'+index).value) || 0;
        let balance = total - received;
        
        document.getElementById('balance_'+index).value = balance;
    }

    monthFromInput.addEventListener('input', recalcAllRows);
    monthToInput.addEventListener('input', recalcAllRows);
    recalcAllRows();
</script>

<style>
    .input-sm { padding: 4px 8px; font-size: 14px; height: 32px; }
    .table td { vertical-align: middle; }
</style>

<?php include 'footer.php'; ?>

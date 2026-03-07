<?php
include 'config.php';
include 'auth_session.php';
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

if (!isset($_GET['id'])) {
    die("Invalid request - ID not found.");
}

$id = $_GET['id'];

// Fetch the receipt data
$stmt = $pdo->prepare("SELECT * FROM rent_collections WHERE id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receipt) {
    die("Receipt not found.");
}

$initial_months_count = get_month_span_inclusive($receipt['month_from'], $receipt['month_to']);

// Fetch all renters for the dropdown
$stmt_renters = $pdo->query("SELECT * FROM renters WHERE status = 'active' ORDER BY shop_no ASC");
$renters = $stmt_renters->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $renter_id = $_POST['renter_id'];
    $receipt_date = $_POST['receipt_date'];
    $receipt_no = $_POST['receipt_no'];
    $month_from = trim($_POST['month_from']);
    $month_to = trim($_POST['month_to']);
    $monthly_rent = floatval($_POST['monthly_rent']);
    $arrears = floatval($_POST['arrears']);
    $amount_received = floatval($_POST['amount_received']);
    $months_count = get_month_span_inclusive($month_from, $month_to);
    $total_amount = ($monthly_rent * $months_count) + $arrears;
    $remaining_balance = $total_amount - $amount_received;
    $notes = $_POST['notes'];

    if (empty($renter_id) || empty($receipt_date) || empty($month_from) || empty($total_amount)) {
        $error = "تمام ضروری خانے پُر کریں۔ (Please fill all required fields)";
    } else {
        try {
            $update_stmt = $pdo->prepare("
                UPDATE rent_collections SET 
                    renter_id = ?, receipt_date = ?, receipt_no = ?, 
                    month_from = ?, month_to = ?, monthly_rent = ?, 
                    arrears = ?, total_amount = ?, amount_received = ?, 
                    remaining_balance = ?, notes = ?
                WHERE id = ?
            ");
            $update_stmt->execute([
                $renter_id, $receipt_date, $receipt_no, $month_from, $month_to,
                $monthly_rent, $arrears, $total_amount, $amount_received,
                $remaining_balance, $notes, $id
            ]);
            $message = "رسید کامیابی سے اپ ڈیٹ ہو گئی۔ (Receipt updated successfully)";
            // Refresh data
            $stmt->execute([$id]);
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "ڈیٹا بیس کی خرابی: " . $e->getMessage();
        }
    }
}
?>

<div class="card" style="margin:20px; max-width:800px; margin-left:auto; margin-right:auto;">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">رسید میں ترمیم کریں (Edit Rent Receipt)</h4>
    </div>
    <div class="card-body">
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>دکاندار منتخب کریں (Select Renter): <span class="text-danger">*</span></label>
                    <select name="renter_id" id="renter_id" class="form-control" required>
                        <option value="">-- منتخب کریں --</option>
                        <?php foreach($renters as $r): ?>
                            <option value="<?php echo $r['id']; ?>" data-rent="<?php echo $r['monthly_rent']; ?>" <?php echo $receipt['renter_id'] == $r['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['shop_no'] . " - " . $r['shop_name'] . " (" . $r['shopkeeper_name'] . ")"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>وصولی کی تاریخ (Receipt Date): <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="<?php echo $receipt['receipt_date']; ?>" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label>مہینہ از (Month From): <span class="text-danger">*</span></label>
                    <input type="text" name="month_from" id="month_from" class="form-control" value="<?php echo htmlspecialchars($receipt['month_from']); ?>" placeholder="مثلاً: مارچ 2026" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>مہینہ تک (Month To):</label>
                    <input type="text" name="month_to" id="month_to" class="form-control" value="<?php echo htmlspecialchars($receipt['month_to']); ?>" placeholder="مثلاً: مئی 2026 (اختیاری)">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label>ماہانہ کرایہ (Monthly Rent - Rs):</label>
                    <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" value="<?php echo $receipt['monthly_rent']; ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label>مہینوں کی تعداد (Number of Months):</label>
                    <input type="number" id="months" class="form-control" value="<?php echo $initial_months_count; ?>" min="1" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label>رسید نمبر (Receipt No):</label>
                    <input type="text" name="receipt_no" class="form-control" value="<?php echo htmlspecialchars($receipt['receipt_no']); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label>پچھلا بقایا (Arrears - Rs):</label>
                    <input type="number" name="arrears" id="arrears" class="form-control" value="<?php echo $receipt['arrears']; ?>">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label>کل رقم (Total Amount - Rs): <span class="text-danger">*</span></label>
                    <input type="number" name="total_amount" id="total_amount" class="form-control bg-light" value="<?php echo $receipt['total_amount']; ?>" required readonly>
                </div>
                <div class="col-md-6 form-group">
                    <label>وصول شدہ رقم (Amount Received - Rs): <span class="text-danger">*</span></label>
                    <input type="number" name="amount_received" id="amount_received" class="form-control font-weight-bold text-success" value="<?php echo $receipt['amount_received']; ?>" required>
                </div>
            </div>

            <div class="form-group mt-3">
                <label>باقی بقایا (Remaining Balance - Rs):</label>
                <input type="text" id="remaining_balance" class="form-control bg-light text-danger" value="<?php echo $receipt['remaining_balance']; ?>" readonly>
            </div>

            <div class="form-group mt-3">
                <label>نوٹ (Notes):</label>
                <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($receipt['notes']); ?></textarea>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success w-50">تبدیلی محفوظ کریں (Save Changes)</button>
                <a href="rents_detail.php" class="btn btn-secondary w-50">واپس جائیں (Back)</a>
            </div>
        </form>
    </div>
</div>

<script>
    const monthFromInput = document.getElementById('month_from');
    const monthToInput = document.getElementById('month_to');
    const monthlyRentInput = document.getElementById('monthly_rent');
    const arrearsInput = document.getElementById('arrears');
    const monthsInput = document.getElementById('months');
    const totalAmountInput = document.getElementById('total_amount');
    const amountReceivedInput = document.getElementById('amount_received');
    const remainingBalanceInput = document.getElementById('remaining_balance');

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

    function calculateTotal() {
        const rent = parseFloat(monthlyRentInput.value) || 0;
        const months = getMonthsCount();
        monthsInput.value = months;
        const arrears = parseFloat(arrearsInput.value) || 0;
        
        const total = (rent * months) + arrears;
        totalAmountInput.value = total;
        calculateBalance();
    }

    function calculateBalance() {
        const total = parseFloat(totalAmountInput.value) || 0;
        const received = parseFloat(amountReceivedInput.value) || 0;
        const balance = total - received;
        remainingBalanceInput.value = balance;
    }

    monthlyRentInput.addEventListener('input', calculateTotal);
    arrearsInput.addEventListener('input', calculateTotal);
    monthFromInput.addEventListener('input', calculateTotal);
    monthToInput.addEventListener('input', calculateTotal);
    amountReceivedInput.addEventListener('input', calculateBalance);

    calculateTotal();
</script>

<?php include 'footer.php'; ?>

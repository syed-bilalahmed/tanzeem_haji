<?php
include 'config.php';
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

// Fetch active renters
$stmt = $pdo->query("SELECT * FROM renters WHERE status='active' ORDER BY id ASC");
$renters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $renter_id = $_POST['renter_id'];
    $receipt_date = $_POST['receipt_date'];
    $receipt_no = $_POST['receipt_no'];
    $month_from = trim($_POST['month_from']);
    $month_to = trim($_POST['month_to']);
    $monthly_rent = floatval($_POST['monthly_rent']);
    $arrears = floatval($_POST['arrears']);
    $received_amount = floatval($_POST['amount_received']);
    $notes = $_POST['notes'];

    $months_count = get_month_span_inclusive($month_from, $month_to);
    $total_amount = ($monthly_rent * $months_count) + $arrears;
    $remaining_balance = $total_amount - $received_amount;
    
    // Insert into rent_collections
    $insert_stmt = $pdo->prepare("
        INSERT INTO rent_collections 
        (renter_id, receipt_date, receipt_no, month_from, month_to, monthly_rent, arrears, total_amount, amount_received, remaining_balance, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($insert_stmt->execute([$renter_id, $receipt_date, $receipt_no, $month_from, $month_to, $monthly_rent, $arrears, $total_amount, $received_amount, $remaining_balance, $notes])) {
        $last_id = $pdo->lastInsertId();
        // Redirect to print page with the ID
        echo "<script>window.location.href='print_rent_receipt.php?id=$last_id';</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error generating receipt.</div>";
    }
}
?>

<div class="card" style="max-width:800px; margin:0 auto; padding: 20px;">
    <h2 class="section-title text-center">دکان کرایہ کی رسید</h2>
    <p class="text-muted text-center">تفصیلات درج کر کے رسید تیار کریں۔</p>
    
    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>تاریخ:</label>
                <input type="date" name="receipt_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 form-group">
                <label>رسید نمبر (اختیاری):</label>
                <input type="text" name="receipt_no" class="form-control" placeholder="اختیاری">
            </div>
            
            <div class="col-md-12 form-group">
                <label>دکان منتخب کریں:</label>
                <select name="renter_id" id="renter_select" class="form-control" required style="font-family: inherit;">
                    <option value="">-- منتخب کریں --</option>
                    <?php foreach($renters as $r): ?>
                        <option value="<?php echo $r['id']; ?>" data-rent="<?php echo $r['monthly_rent']; ?>">
                            <?php echo htmlspecialchars($r['shop_no'] . ' - ' . $r['shop_name'] . ' (' . $r['shopkeeper_name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label>مہینہ از:</label>
                <input type="text" name="month_from" id="month_from" class="form-control" placeholder="مثلاً: مارچ 2026" required>
            </div>
            
            <div class="col-md-6 form-group">
                <label>مہینہ تک (اختیاری):</label>
                <input type="text" name="month_to" id="month_to" class="form-control" placeholder="مثلاً: مئی 2026">
                <small class="text-muted">اگر ایک مہینہ ہے تو خالی چھوڑ دیں۔</small>
            </div>

            <div class="col-md-6 form-group">
                <label>ماہانہ کرایہ:</label>
                <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" value="0" required>
                <small class="text-muted">یہاں آپ کرایہ خود شامل کر سکتے ہیں</small>
            </div>

            <div class="col-md-6 form-group">
                <label>مہینوں کی تعداد (Number of Months):</label>
                <input type="number" id="months" class="form-control" value="1" min="1" readonly>
            </div>
            
            <div class="col-md-12 form-group">
                <label>پچھلا بقایا (Arrears - Rs):</label>
                <input type="number" name="arrears" id="arrears" class="form-control" value="0" placeholder="عاریئر (Arrears)">
            </div>
            
            <div class="col-md-12"><hr></div>

            <div class="col-md-4 form-group">
                <label>کل رقم (Total Amount - Rs): <span class="text-danger">*</span></label>
                <input type="number" name="total_amount" id="total_amount" class="form-control text-primary font-weight-bold" style="font-size: 18px;" required readonly>
            </div>

            <div class="col-md-4 form-group">
                <label>وصول شدہ رقم (Amount Received - Rs): <span class="text-danger">*</span></label>
                <input type="number" name="amount_received" id="amount_received" class="form-control text-success font-weight-bold" style="font-size: 18px;" value="0" required>
            </div>
            <div class="col-md-4 form-group">
                <label>باقی بقایا (Remaining Balance - Rs):</label>
                <input type="number" name="remaining_balance" id="remaining_balance" class="form-control text-danger font-weight-bold" style="font-size: 18px;" readonly>
            </div>
            
            <div class="col-md-12 form-group">
                <label>تفصیل / نوٹ (اختیاری):</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="کرایہ کی تفصیل..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-3" style="font-size: 18px;">محفوظ کریں اور پرنٹ کریں</button>
    </form>
</div>

<script>
    const renterSelect = document.getElementById('renter_select');
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

    // Auto-fill monthly rent when a renter is selected
    renterSelect.addEventListener('change', function() {
        if(this.selectedIndex > 0) {
            const rent = this.options[this.selectedIndex].getAttribute('data-rent');
            monthlyRentInput.value = rent;
            calculateTotal();
        } else {
            monthlyRentInput.value = 0; // Set to 0 instead of empty string for calculations
            calculateTotal(); // Recalculate totals
        }
    });

    // Calculate Total = (Monthly Rent * Months) + Arrears
    function calculateTotal() {
        const rent = parseFloat(monthlyRentInput.value) || 0;
        const months = getMonthsCount();
        monthsInput.value = months;
        const arrears = parseFloat(arrearsInput.value) || 0;
        
        const total = (rent * months) + arrears;
        totalAmountInput.value = total;
        
        // Assume full payment by default when total changes, or keep current if it's not 0
        if (amountReceivedInput.value == 0 || amountReceivedInput.value == totalAmountInput.value) {
            amountReceivedInput.value = total;
        }
        calculateBalance();
    }

    // Calculate Remaining Balance = Total - Received
    function calculateBalance() {
        const total = parseFloat(totalAmountInput.value) || 0;
        const received = parseFloat(amountReceivedInput.value) || 0;
        
        const balance = total - received;
        remainingBalanceInput.value = balance;
    }

    // Add event listeners for dynamic recalculation
    monthlyRentInput.addEventListener('input', calculateTotal);
    arrearsInput.addEventListener('input', calculateTotal);
    monthFromInput.addEventListener('input', calculateTotal);
    monthToInput.addEventListener('input', calculateTotal);
    
    amountReceivedInput.addEventListener('input', calculateBalance);
    // totalAmountInput is readonly, so no input listener needed for it to trigger balance calculation
    
    // Initial calculation on page load
    calculateTotal();
</script>

<?php include 'footer.php'; ?>

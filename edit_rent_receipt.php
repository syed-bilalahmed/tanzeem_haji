<?php
include 'config.php';
include 'auth_session.php';
include 'header.php';

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

// Fetch all renters for the dropdown
$stmt_renters = $pdo->query("SELECT * FROM renters WHERE status = 'active' ORDER BY shop_no ASC");
$renters = $stmt_renters->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $renter_id = $_POST['renter_id'];
    $receipt_date = $_POST['receipt_date'];
    $receipt_no = $_POST['receipt_no'];
    $month_from = $_POST['month_from'];
    $month_to = $_POST['month_to'];
    $monthly_rent = $_POST['monthly_rent'];
    $arrears = $_POST['arrears'];
    $total_amount = $_POST['total_amount'];
    $amount_received = $_POST['amount_received'];
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
                    <input type="text" name="month_from" class="form-control" value="<?php echo htmlspecialchars($receipt['month_from']); ?>" placeholder="مثلاً: مارچ 2026" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>مہینہ تک (Month To):</label>
                    <input type="text" name="month_to" class="form-control" value="<?php echo htmlspecialchars($receipt['month_to']); ?>" placeholder="مثلاً: مئی 2026 (اختیاری)">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label>ماہانہ کرایہ (Monthly Rent - Rs):</label>
                    <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" value="<?php echo $receipt['monthly_rent']; ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label>مہینوں کی تعداد (Number of Months):</label>
                    <input type="number" id="months" class="form-control" value="1" min="1">
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
    const monthlyRentInput = document.getElementById('monthly_rent');
    const arrearsInput = document.getElementById('arrears');
    const monthsInput = document.getElementById('months');
    const totalAmountInput = document.getElementById('total_amount');
    const amountReceivedInput = document.getElementById('amount_received');
    const remainingBalanceInput = document.getElementById('remaining_balance');

    function calculateTotal() {
        const rent = parseFloat(monthlyRentInput.value) || 0;
        const months = parseFloat(monthsInput.value) || 1;
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
    monthsInput.addEventListener('input', calculateTotal);
    amountReceivedInput.addEventListener('input', calculateBalance);
</script>

<?php include 'footer.php'; ?>

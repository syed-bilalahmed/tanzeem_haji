<?php
include 'config.php';
include 'header.php';

// Fetch active renters
$stmt = $pdo->query("SELECT * FROM renters WHERE status='active' ORDER BY id ASC");
$renters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $renter_id = $_POST['renter_id'];
    $receipt_date = $_POST['receipt_date'];
    $receipt_no = $_POST['receipt_no'];
    $month_from = $_POST['month_from'];
    $month_to = $_POST['month_to'];
    $monthly_rent = $_POST['monthly_rent'];
    $arrears = $_POST['arrears'];
    $total_amount = $_POST['total_amount'];
    $received_amount = $_POST['received_amount'];
    $remaining_balance = $_POST['remaining_balance'];
    $notes = $_POST['notes'];
    
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
                <input type="text" name="month_from" class="form-control" placeholder="مثلاً: مارچ 2026" required>
            </div>
            
            <div class="col-md-6 form-group">
                <label>مہینہ تک (اختیاری):</label>
                <input type="text" name="month_to" class="form-control" placeholder="مثلاً: مئی 2026">
                <small class="text-muted">اگر ایک مہینہ ہے تو خالی چھوڑ دیں۔</small>
            </div>

            <div class="col-md-6 form-group">
                <label>ماہانہ کرایہ:</label>
                <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" value="0" required>
                <small class="text-muted">یہاں آپ کرایہ خود شامل کر سکتے ہیں</small>
            </div>

            <div class="col-md-6 form-group">
                <label>مہینوں کی تعداد (Number of Months):</label>
                <input type="number" id="months" class="form-control" value="1" min="1" placeholder="مثلاً: 5">
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
    const renterSelect = document.getElementById('renter_id');
    const monthlyRentInput = document.getElementById('monthly_rent');
    const arrearsInput = document.getElementById('arrears');
    const monthsInput = document.getElementById('months');
    const totalAmountInput = document.getElementById('total_amount');
    const amountReceivedInput = document.getElementById('amount_received');
    const remainingBalanceInput = document.getElementById('remaining_balance');

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
        const months = parseFloat(monthsInput.value) || 1;
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
    monthsInput.addEventListener('input', calculateTotal);
    
    amountReceivedInput.addEventListener('input', calculateBalance);
    // totalAmountInput is readonly, so no input listener needed for it to trigger balance calculation
    
    // Initial calculation on page load
    calculateTotal();
</script>

<?php include 'footer.php'; ?>

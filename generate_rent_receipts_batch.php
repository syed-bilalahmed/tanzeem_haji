<?php
include 'config.php';
include 'auth_session.php'; // Ensure user is logged in
include 'header.php';

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
    
    // Arrays from form
    $generate_flags   = isset($_POST['generate']) ? $_POST['generate'] : [];
    $renter_ids       = $_POST['renter_id'];
    $monthly_rents    = $_POST['monthly_rent'];
    $arrears_list     = $_POST['arrears'];
    $total_amounts    = $_POST['total_amount'];
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
                    $r_total = floatval($total_amounts[$index]);
                    $r_received = floatval($received_amounts[$index]);
                    $r_notes = $notes_list[$index];
                    
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
                        <input type="text" name="month_from" class="form-control" placeholder="مثلاً: مارچ 2026" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>مہینہ تک (Month To):</label>
                        <input type="text" name="month_to" class="form-control" placeholder="مثلاً: مئی 2026 (اختیاری)">
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
                                    <input type="number" id="months_<?php echo $index; ?>" class="form-control input-sm row-calc" value="1" min="1" onchange="calcRow(<?php echo $index; ?>)" onkeyup="calcRow(<?php echo $index; ?>)">
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
        let months = parseFloat(document.getElementById('months_'+index).value) || 1;
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
</script>

<style>
    .input-sm { padding: 4px 8px; font-size: 14px; height: 32px; }
    .table td { vertical-align: middle; }
</style>

<?php include 'footer.php'; ?>

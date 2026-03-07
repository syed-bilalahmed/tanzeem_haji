<?php
include 'config.php';
include 'auth_session.php';
include 'header.php';

if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("Invalid request - IDs not found.");
}

$ids_str = $_GET['ids'];
$ids_array = explode(',', $ids_str);
$clean_ids = array_filter($ids_array, 'is_numeric');

if (empty($clean_ids)) {
    die("No valid IDs provided.");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_batch'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['receipt_id'] as $index => $id) {
            $month_from = $_POST['month_from'][$index];
            $month_to = $_POST['month_to'][$index];
            $monthly_rent = $_POST['monthly_rent'][$index];
            $arrears = $_POST['arrears'][$index];
            $total_amount = $_POST['total_amount'][$index];
            $amount_received = $_POST['amount_received'][$index];
            $remaining_balance = $total_amount - $amount_received;
            $notes = $_POST['notes'][$index];

            $stmt = $pdo->prepare("
                UPDATE rent_collections SET 
                    month_from = ?, month_to = ?, monthly_rent = ?, 
                    arrears = ?, total_amount = ?, amount_received = ?, 
                    remaining_balance = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $month_from, $month_to, $monthly_rent, $arrears, 
                $total_amount, $amount_received, $remaining_balance, $notes, $id
            ]);
        }
        $pdo->commit();
        $message = "تمام رسیدیں کامیابی سے اپ ڈیٹ ہو گئیں۔ (All receipts updated successfully)";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "خرابی: " . $e->getMessage();
    }
}

// Fetch the receipts data
$placeholders = str_repeat('?,', count($clean_ids) - 1) . '?';
$stmt = $pdo->prepare("
    SELECT rc.*, r.shop_no, r.shop_name, r.shopkeeper_name
    FROM rent_collections rc
    JOIN renters r ON rc.renter_id = r.id
    WHERE rc.id IN ($placeholders)
    ORDER BY r.shop_no ASC
");
$stmt->execute($clean_ids);
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card" style="margin:20px;">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0">بیچ رسید ایڈیٹر (Batch Receipt Editor)</h4>
    </div>
    <div class="card-body">
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="update_batch" value="1">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>دکان / دکاندار</th>
                            <th width="150">مہینہ از (From)</th>
                            <th width="150">مہینہ تک (To)</th>
                            <th>کرایہ</th>
                            <th>بقایا</th>
                            <th>کل رقم</th>
                            <th>وصول شدہ</th>
                            <th>نوٹ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($receipts as $index => $r): ?>
                        <tr>
                            <td class="text-right">
                                <input type="hidden" name="receipt_id[<?php echo $index; ?>]" value="<?php echo $r['id']; ?>">
                                <strong><?php echo htmlspecialchars($r['shop_no']); ?></strong><br>
                                <small><?php echo htmlspecialchars($r['shopkeeper_name']); ?></small>
                            </td>
                            <td><input type="text" name="month_from[<?php echo $index; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['month_from']); ?>"></td>
                            <td><input type="text" name="month_to[<?php echo $index; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['month_to']); ?>"></td>
                            <td><input type="number" name="monthly_rent[<?php echo $index; ?>]" id="rent_<?php echo $index; ?>" class="form-control form-control-sm row-calc" value="<?php echo $r['monthly_rent']; ?>" onchange="calcRow(<?php echo $index; ?>)"></td>
                            <td><input type="number" name="arrears[<?php echo $index; ?>]" id="arrears_<?php echo $index; ?>" class="form-control form-control-sm row-calc" value="<?php echo $r['arrears']; ?>" onchange="calcRow(<?php echo $index; ?>)"></td>
                            <td><input type="number" name="total_amount[<?php echo $index; ?>]" id="total_<?php echo $index; ?>" class="form-control form-control-sm bg-light" value="<?php echo $r['total_amount']; ?>" readonly></td>
                            <td><input type="number" name="amount_received[<?php echo $index; ?>]" id="received_<?php echo $index; ?>" class="form-control form-control-sm" value="<?php echo $r['amount_received']; ?>"></td>
                            <td><input type="text" name="notes[<?php echo $index; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['notes']); ?>"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-warning px-5 font-weight-bold">تبدیلیاں محفوظ کریں (Update All Selected)</button>
                <a href="rents_detail.php" class="btn btn-secondary px-5">واپس جائیں (Back)</a>
            </div>
        </form>
    </div>
</div>

<script>
function calcRow(index) {
    let rent = parseFloat(document.getElementById('rent_'+index).value) || 0;
    let arrears = parseFloat(document.getElementById('arrears_'+index).value) || 0;
    let total = rent + arrears; // Note: simplified for batch edit as multiplier is complex to track per row here unless added UI
    document.getElementById('total_'+index).value = total;
    document.getElementById('received_'+index).value = total;
}
</script>

<?php include 'footer.php'; ?>

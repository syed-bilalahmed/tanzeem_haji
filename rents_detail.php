<?php
include 'config.php';
include_once 'auth_session.php';
include 'header.php';

// Default Tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'monthly';

// --- For Monthly Tab ---
// Default month is current month, formatted like "March 2026"
$current_month_str = date('F Y');
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month_str;

// Fetch Monthly Collections
$monthly_stmt = $pdo->prepare("
    SELECT rc.*, r.shop_no, r.shop_name, r.shopkeeper_name
    FROM rent_collections rc
    JOIN renters r ON rc.renter_id = r.id
    WHERE rc.month_from LIKE ? OR rc.month_to LIKE ?
    ORDER BY rc.id DESC
");
// Broad search for the month in either 'from' or 'to' fields
$like_month = "%$selected_month%";
$monthly_stmt->execute([$like_month, $like_month]);
$monthly_records = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_monthly_received = 0;

// --- For Yearly Tab ---
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? $_GET['year'] : $current_year;

// Fetch active renters
$renters_stmt = $pdo->query("SELECT * FROM renters WHERE status='active' ORDER BY id ASC");
$all_renters = $renters_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all collections for the selected year
$yearly_stmt = $pdo->prepare("
    SELECT renter_id, SUM(amount_received) as total_received 
    FROM rent_collections 
    WHERE (month_from LIKE ? OR month_to LIKE ? OR receipt_date LIKE ?)
    GROUP BY renter_id
");
$like_year = "%$selected_year%";
$yearly_stmt->execute([$like_year, $like_year, "$selected_year-%"]);
$yearly_collections = $yearly_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // returns [renter_id => total_received]

$grand_total_year = 0;
?>

<div class="card" style="margin:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h2 class="section-title">کرایہ کی تفصیلات (Rents Detail)</h2>
        <div class="no-print">
            <a href="generate_rent_receipt.php" class="btn btn-warning"><i class="fas fa-receipt"></i> نئی رسید (New Receipt)</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> پرنٹ (Print)</button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs no-print">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'monthly' ? 'active font-weight-bold' : ''; ?>" href="rents_detail.php?tab=monthly">ماہانہ تفصیل (Monthly)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'yearly' ? 'active font-weight-bold' : ''; ?>" href="rents_detail.php?tab=yearly">سالانہ تفصیل (Yearly)</a>
        </li>
    </ul>

    <div class="tab-content" style="padding-top: 20px;">
        
        <?php if ($tab == 'monthly'): ?>
        <!-- === MONTHLY TAB === -->
        <div class="no-print mb-4" style="background:#f8f9fa; padding:15px; border-radius:5px; border:1px solid #ddd;">
            <form method="GET" class="row align-items-end">
                <input type="hidden" name="tab" value="monthly">
                <div class="col-md-4">
                    <label>مہینہ منتخب کریں (Select Month):</label>
                    <input type="text" name="month" class="form-control" value="<?php echo htmlspecialchars($selected_month); ?>" placeholder="e.g. March 2026">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">تلاش (Search)</button>
                </div>
            </form>
        </div>

        <h4 class="text-center print-only-heading">ماہانہ کرایہ تفصیل - <?php echo htmlspecialchars($selected_month); ?></h4>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success mt-2">رسید کامیابی سے ڈیلیٹ ہو گئی۔ (Receipt deleted successfully)</div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'batch_deleted'): ?>
            <div class="alert alert-success mt-2">منتخب کردہ رسیدیں کامیابی سے ڈیلیٹ ہو گئیں۔ (Selected receipts deleted successfully)</div>
        <?php endif; ?>

        <!-- Batch Actions -->
        <div class="no-print d-flex justify-content-start gap-2 mb-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="handleBatchPrint()"><i class="fas fa-print"></i> منتخب کردہ پرنٹ کریں (Print Selected)</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="handleBatchEdit()"><i class="fas fa-edit"></i> منتخب کردہ ایڈٹ کریں (Edit Selected)</button>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="handleBatchDelete()"><i class="fas fa-trash-alt"></i> منتخب کردہ ڈیلیٹ کریں (Delete Selected)</button>
            <?php endif; ?>
        </div>

        <form id="batchActionForm" method="POST" action="batch_delete_rent_receipts.php">
        <table class="table table-bordered table-sm text-center" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th class="no-print"><input type="checkbox" id="selectAll"></th>
                    <th>رسید نمبر</th>
                    <th>تاریخ (Date)</th>
                    <th>دکان نمبر</th>
                    <th>دکان کا نام</th>
                    <th>دکاندار</th>
                    <th>مہینہ (Month)</th>
                    <th>وصول شدہ (Received)</th>
                    <th>بقایا (Balance)</th>
                    <th class="no-print">ایکشن (Actions)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($monthly_records) > 0): ?>
                    <?php foreach ($monthly_records as $rec): ?>
                        <?php $total_monthly_received += $rec['amount_received']; ?>
                        <tr>
                            <td class="no-print"><input type="checkbox" name="ids[]" value="<?php echo $rec['id']; ?>" class="row-checkbox"></td>
                            <td><?php echo htmlspecialchars($rec['receipt_no']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($rec['receipt_date'])); ?></td>
                            <td><?php echo htmlspecialchars($rec['shop_no']); ?></td>
                            <td><?php echo htmlspecialchars($rec['shop_name']); ?></td>
                            <td><?php echo htmlspecialchars($rec['shopkeeper_name']); ?></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($rec['month_from']); 
                                if(!empty($rec['month_to'])) echo " تا " . htmlspecialchars($rec['month_to']);
                                ?>
                            </td>
                            <td class="table-success font-weight-bold"><?php echo number_format($rec['amount_received']); ?></td>
                            <td class="<?php echo $rec['remaining_balance'] > 0 ? 'table-danger' : ''; ?>"><?php echo number_format($rec['remaining_balance']); ?></td>
                            <td class="no-print">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="print_rent_receipt.php?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-info" target="_blank" title="View/Print"><i class="fas fa-eye"></i></a>
                                    <a href="edit_rent_receipt.php?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a href="delete_rent_receipt.php?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('کیا آپ واقعی یہ رسید ڈیلیٹ کرنا چاہتے ہیں؟');" title="Delete"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-dark" style="font-size:18px;">
                        <td class="no-print"></td>
                        <td colspan="6" style="text-align:left;">کل وصولی (Total Received):</td>
                        <td colspan="3" style="text-align:right;"><?php echo number_format($total_monthly_received); ?> روپے</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No records found for <?php echo htmlspecialchars($selected_month); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </form>

        <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(chk => chk.checked = this.checked);
        });

        function getSelectedIds() {
            let selected = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(chk => selected.push(chk.value));
            return selected;
        }

        function handleBatchPrint() {
            let ids = getSelectedIds();
            if (ids.length === 0) {
                alert("براہ کرم کم از کم ایک رسید منتخب کریں۔ (Select at least one receipt)");
                return;
            }
            window.open('print_rent_receipts_batch.php?ids=' + ids.join(','), '_blank');
        }

        function handleBatchEdit() {
            let ids = getSelectedIds();
            if (ids.length === 0) {
                alert("براہ کرم ایڈٹ کرنے کے لئے کم از کم ایک رسید منتخب کریں۔");
                return;
            }
            window.location.href = 'edit_rent_receipts_batch.php?ids=' + ids.join(',');
        }

        function handleBatchDelete() {
            let ids = getSelectedIds();
            if (ids.length === 0) {
                alert("براہ کرم ڈیلیٹ کرنے کے لئے کم از کم ایک رسید منتخب کریں۔");
                return;
            }
            if (confirm('کیا آپ واقعی منتخب کردہ ' + ids.length + ' رسیدیں ڈیلیٹ کرنا چاہتے ہیں؟')) {
                document.getElementById('batchActionForm').submit();
            }
        }
        </script>

        <?php elseif ($tab == 'yearly'): ?>
        <!-- === YEARLY TAB === -->
        <div class="no-print mb-4" style="background:#f8f9fa; padding:15px; border-radius:5px; border:1px solid #ddd;">
            <form method="GET" class="row align-items-end">
                <input type="hidden" name="tab" value="yearly">
                <div class="col-md-3">
                    <label>سال (Year):</label>
                    <input type="number" name="year" class="form-control" value="<?php echo htmlspecialchars($selected_year); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">تلاش (Search)</button>
                </div>
            </form>
        </div>

        <h4 class="text-center print-only-heading">سالانہ کرایہ رپورٹ - <?php echo htmlspecialchars($selected_year); ?></h4>

        <table class="table table-bordered table-sm text-center" dir="rtl">
            <thead class="table-dark">
                <tr>
                    <th>شمار</th>
                    <th>دکان نمبر</th>
                    <th>دکان کا نام</th>
                    <th>دکاندار</th>
                    <th>طے شدہ ماہانہ کرایہ</th>
                    <th>سالانہ وصولی (Total Received in <?php echo htmlspecialchars($selected_year); ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php $k = 1; foreach ($all_renters as $r): ?>
                    <?php 
                    $received = isset($yearly_collections[$r['id']]) ? $yearly_collections[$r['id']] : 0;
                    $grand_total_year += $received;
                    ?>
                    <tr>
                        <td><?php echo $k++; ?></td>
                        <td><?php echo htmlspecialchars($r['shop_no']); ?></td>
                        <td><?php echo htmlspecialchars($r['shop_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['shopkeeper_name']); ?></td>
                        <td><?php echo number_format($r['monthly_rent']); ?></td>
                        <td class="table-success font-weight-bold"><?php echo number_format($received); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-dark" style="font-size:18px;">
                    <td colspan="5" style="text-align:left;">مجموعی وصولی (Grand Total):</td>
                    <td style="text-align:center;"><?php echo number_format($grand_total_year); ?> روپے</td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
</div>

<style>
    .print-only-heading { display: none; margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 10px; }
    @media print {
        body { background: white; margin: 0; padding: 0; }
        .card { border: none; box-shadow: none; margin: 0; padding: 0; }
        .no-print { display: none !important; }
        .print-only-heading { display: block; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table th, .table td { border: 1px solid #000 !important; padding: 4px; }
        .table-dark th { background-color: #f2f2f2 !important; color: #000 !important; -webkit-print-color-adjust: exact; }
    }
</style>

<?php include 'footer.php'; ?>

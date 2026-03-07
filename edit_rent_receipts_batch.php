<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_session.php';

if (!has_permission('salaries_edit')) {
    die("<div style='text-align:center; margin-top:50px; font-size:20px; font-family:Arial;'>Access Denied. You do not have permission to edit rent receipts.</div>");
}

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
    $has_year = (count($parts) === 2 && preg_match('/^\d{4}$/', $parts[1]));
    $year = $has_year ? (int) $parts[1] : (int) date('Y');

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
        return ['year' => $year, 'month' => $month_map[$month_token], 'has_year' => $has_year];
    }

    return null;
}

function get_month_span_inclusive($month_from, $month_to, &$error = null) {
    $start = parse_month_year_value($month_from);
    if (!$start) {
        $error = "مہینہ از (Month From) غلط ہے۔ مثال: January 2026 یا جنوری 2026";
        return null;
    }

    $end = trim((string) $month_to) === '' ? $start : parse_month_year_value($month_to);
    if (!$end) {
        $error = "مہینہ تک (Month To) غلط ہے۔ مثال: June 2026 یا جون 2026";
        return null;
    }

    if (empty($start['has_year']) && !empty($end['has_year'])) {
        $start['year'] = $end['year'];
    }
    if (!empty($start['has_year']) && empty($end['has_year'])) {
        $end['year'] = $start['year'];
    }

    $start_index = ($start['year'] * 12) + $start['month'];
    $end_index = ($end['year'] * 12) + $end['month'];

    if ($end_index < $start_index) {
        [$start_index, $end_index] = [$end_index, $start_index];
    }

    return ($end_index - $start_index) + 1;
}

include __DIR__ . '/header.php';

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
            $month_from = trim($_POST['month_from'][$index]);
            $month_to = trim($_POST['month_to'][$index]);
            $monthly_rent = floatval($_POST['monthly_rent'][$index]);
            $arrears = floatval($_POST['arrears'][$index]);
            $months_count = get_month_span_inclusive($month_from, $month_to, $error);
            if ($months_count === null) {
                throw new Exception($error);
            }
            $total_amount = ($monthly_rent * $months_count) + $arrears;
            $amount_received = floatval($_POST['amount_received'][$index]);
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
                            <td><input type="text" name="month_from[<?php echo $index; ?>]" id="month_from_<?php echo $index; ?>" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['month_from']); ?>" onchange="calcRow(<?php echo $index; ?>)" onkeyup="calcRow(<?php echo $index; ?>)"></td>
                            <td><input type="text" name="month_to[<?php echo $index; ?>]" id="month_to_<?php echo $index; ?>" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['month_to']); ?>" onchange="calcRow(<?php echo $index; ?>)" onkeyup="calcRow(<?php echo $index; ?>)"></td>
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
function parseMonthYear(text) {
    const value = (text || '').trim();
    if (!value) return null;

    const normalized = value.replace(/[\/_-]+/g, ' ').replace(/\s+/g, ' ').trim();
    const parts = normalized.split(' ');
    if (parts.length < 1 || parts.length > 2) return null;

    const token = parts[0].toLowerCase();
    const hasYear = (parts.length === 2 && /^\d{4}$/.test(parts[1]));
    const year = hasYear ? parseInt(parts[1], 10) : new Date().getFullYear();

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
        return { year: year, month: monthMap[token], hasYear: hasYear };
    }

    return null;
}

function getMonthsCount(index) {
    const start = parseMonthYear(document.getElementById('month_from_'+index).value);
    if (!start) return null;

    let end = parseMonthYear(document.getElementById('month_to_'+index).value);
    if (!end) {
        if ((document.getElementById('month_to_'+index).value || '').trim() !== '') return null;
        end = start;
    }
    if (!start.hasYear && end.hasYear) {
        start.year = end.year;
    }
    if (start.hasYear && !end.hasYear) {
        end.year = start.year;
    }
    let startIndex = (start.year * 12) + start.month;
    let endIndex = (end.year * 12) + end.month;

    if (endIndex < startIndex) {
        [startIndex, endIndex] = [endIndex, startIndex];
    }

    return (endIndex - startIndex) + 1;
}

function calcRow(index) {
    let rent = parseFloat(document.getElementById('rent_'+index).value) || 0;
    let months = getMonthsCount(index);
    if (!months) {
        document.getElementById('total_'+index).value = '';
        return;
    }
    let arrears = parseFloat(document.getElementById('arrears_'+index).value) || 0;
    let total = (rent * months) + arrears;
    document.getElementById('total_'+index).value = total;
}

document.querySelectorAll('input[id^="rent_"], input[id^="arrears_"], input[id^="month_from_"], input[id^="month_to_"]').forEach((el) => {
    const idx = el.id.split('_').pop();
    calcRow(idx);
});
</script>

<?php include 'footer.php'; ?>

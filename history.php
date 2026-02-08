<?php
include 'config.php';

// AJAX Handler for history
if (isset($_POST['year'])) {
    $year = $_POST['year'];
    
    // Fetch Unified History for specific year
    $stmt = $pdo->prepare("SELECT DISTINCT MONTH(date_col) as m, YEAR(date_col) as y 
        FROM (
            SELECT income_date as date_col FROM incomes 
            UNION 
            SELECT expense_date as date_col FROM expenses
        ) combined 
        WHERE YEAR(date_col) = ?
        ORDER BY m DESC"); // No need to order by Y since it's single year
    $stmt->execute([$year]);
    $months = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($months as $row) {
        $m = $row['m'];
        $y = $row['y']; // should match $year
        
        // Income
        $si = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ?");
        $si->execute([$m, $y]);
        $inc = $si->fetch(PDO::FETCH_ASSOC);
        
        // Expense
        $se = $pdo->prepare("SELECT SUM(amount) as cash, SUM(dargah_fund) as dargah, SUM(qabristan_fund) as qabristan, SUM(masjid_fund) as masjid, SUM(urs_fund) as urs FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
        $se->execute([$m, $y]);
        $exp = $se->fetch(PDO::FETCH_ASSOC);
        
        $m_label = date('F', mktime(0, 0, 0, $m, 10));
        
        $data[] = [
            'm_label' => $m_label . " " . $y,
            'cash' => number_format(($inc['cash'] ?? 0) - ($exp['cash'] ?? 0)),
            'cash_cls' => (($inc['cash'] ?? 0) - ($exp['cash'] ?? 0)) >= 0 ? 'text-success' : 'text-danger',
            'dargah' => number_format(($inc['dargah'] ?? 0) - ($exp['dargah'] ?? 0)),
            'dargah_cls' => (($inc['dargah'] ?? 0) - ($exp['dargah'] ?? 0)) >= 0 ? 'text-success' : 'text-danger',
            'qabristan' => number_format(($inc['qabristan'] ?? 0) - ($exp['qabristan'] ?? 0)),
            'qabristan_cls' => (($inc['qabristan'] ?? 0) - ($exp['qabristan'] ?? 0)) >= 0 ? 'text-success' : 'text-danger',
            'masjid' => number_format(($inc['masjid'] ?? 0) - ($exp['masjid'] ?? 0)),
            'masjid_cls' => (($inc['masjid'] ?? 0) - ($exp['masjid'] ?? 0)) >= 0 ? 'text-success' : 'text-danger',
            'urs' => number_format(($inc['urs'] ?? 0) - ($exp['urs'] ?? 0)),
            'urs_cls' => (($inc['urs'] ?? 0) - ($exp['urs'] ?? 0)) >= 0 ? 'text-success' : 'text-danger',
            'm' => str_pad($m, 2, '0', STR_PAD_LEFT),
            'y' => $y,
            'raw_m' => $m
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

include 'header.php';

// Handle Delete Month
if (isset($_GET['delete_month']) && isset($_GET['delete_year'])) {
    $m = $_GET['delete_month'];
    $y = $_GET['delete_year'];
    $pdo->prepare("DELETE FROM incomes WHERE MONTH(income_date) = ? AND YEAR(income_date) = ?")->execute([$m, $y]);
    $pdo->prepare("DELETE FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?")->execute([$m, $y]);
    echo "<script>window.location.href='history.php';</script>";
}
?>

<div class="card" dir="rtl">
    <?php get_org_header("ماہانہ ریکارڈز (Monthly History)"); ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;" class="no-print">
        <h4 class="mb-0">گوشوارہ جات کی فہرست</h4>
        <div>
            <a href="monthly_sheet.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> نیا اندراج (New Ledger)</a>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="year-filters mb-3 text-center no-print">
        <label class="fw-bold me-2">سال منتخب کریں (Select Year):</label>
        <div class="btn-group" role="group">
            <?php 
            $current_year = date('Y');
            for($y = 2024; $y <= 2030; $y++): ?>
                <button type="button" class="btn btn-outline-primary year-btn <?php echo $y == $current_year ? 'active' : ''; ?>" data-year="<?php echo $y; ?>" onclick="loadHistory(<?php echo $y; ?>)">
                    <?php echo $y; ?>
                </button>
            <?php endfor; ?>
        </div>
    </div>
    
    <!-- Loading Spinner -->
    <div id="loader" class="text-center py-5" style="display:none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">ڈیٹا لوڈ ہو رہا ہے...</p>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center" style="border: 2px solid #000;" id="histTable">
            <thead class="table-dark">
                <tr>
                    <th rowspan="2" style="vertical-align:middle;">سیریل نمبر</th>
                    <th rowspan="2" style="vertical-align:middle;">مہینہ اور سال</th>
                    <th colspan="5">خالص میزان (Fund-wise Net Balance)</th>
                    <th rowspan="2" style="vertical-align:middle;" width="25%" class="no-print">ایکشن (Actions)</th>
                </tr>
                <tr>
                    <th>کیش (Cash)</th>
                    <th>درگاہ فنڈ</th>
                    <th>قبرستان فنڈ</th>
                    <th>مسجد فنڈ</th>
                    <th>عرس فنڈ</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- AJAX Content -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load current year by default
    const currentYear = new Date().getFullYear();
    const defaultYear = (currentYear < 2024) ? 2024 : currentYear; 
    
    // Check if button exists
    if(document.querySelector(`.year-btn[data-year='${defaultYear}']`)) {
        loadHistory(defaultYear);
    } else {
        loadHistory(2024);
    }
});

function loadHistory(year) {
    document.querySelectorAll('.year-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.year-btn[data-year='${year}']`)?.classList.add('active');
    
    const tbody = document.getElementById('tableBody');
    const loader = document.getElementById('loader');
    
    tbody.innerHTML = '';
    loader.style.display = 'block';
    
    const formData = new FormData();
    formData.append('year', year);
    
    fetch('history.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(resp => {
        loader.style.display = 'none';
        
        if (resp.status === 'success') {
            const data = resp.data;
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-muted">اس سال کا کوئی ریکارڈ موجود نہیں</td></tr>';
            } else {
                data.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td class="fw-bold">${row.m_label}</td>
                        <td class="${row.cash_cls} fw-bold">${row.cash}</td>
                        <td class="${row.dargah_cls}">${row.dargah}</td>
                        <td class="${row.qabristan_cls}">${row.qabristan}</td>
                        <td class="${row.masjid_cls}">${row.masjid}</td>
                        <td class="${row.urs_cls}">${row.urs}</td>
                        <td class="no-print">
                            <a href="monthly_sheet.php?month=${row.m}&year=${row.y}" class="btn btn-sm btn-primary">دیکھیں/ترمیم</a>
                            <a href="monthly_sheet.php?month=${row.m}&year=${row.y}&print=1" class="btn btn-sm btn-info" target="_blank">پرنٹ</a>
                            <a href="history.php?delete_month=${row.raw_m}&delete_year=${row.y}" class="btn btn-sm btn-danger" onclick="return confirm('کیا آپ اس مکمل مہینے کا ریکارڈ حذف کرنا چاہتے ہیں؟')">حذف</a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
    })
    .catch(error => {
        console.error(error);
        loader.style.display = 'none';
        tbody.innerHTML = '<tr><td colspan="8" class="text-danger">Error loading data</td></tr>';
    });
}
</script>

<?php include 'footer.php'; ?>

<?php
include 'config.php';

// AJAX Handler for fetching collections by year
if (isset($_POST['year'])) {
    $year = $_POST['year'];
    
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE YEAR(collection_date) = ? ORDER BY collection_date DESC");
    $stmt->execute([$year]);
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $data = [];
    foreach($cols as $index => $col) {
        // Calculation Logic (Same as before)
        $calc_total = 0;
        $calc_total += ($col['darbar_5000']*5000) + ($col['darbar_1000']*1000) + ($col['darbar_500']*500) + ($col['darbar_100']*100) + ($col['darbar_50']*50) + ($col['darbar_20']*20) + ($col['darbar_10']*10);
        $calc_total += ($col['andron_5000']*5000) + ($col['andron_1000']*1000) + ($col['andron_500']*500) + ($col['andron_100']*100) + ($col['andron_50']*50) + ($col['andron_20']*20) + ($col['andron_10']*10);
        $calc_total += ($col['beron_5000']*5000) + ($col['beron_1000']*1000) + ($col['beron_500']*500) + ($col['beron_100']*100) + ($col['beron_50']*50) + ($col['beron_20']*20) + ($col['beron_10']*10);
        
        $man_total = ($col['darbar_total'] ?? 0) + ($col['andron_total'] ?? 0) + ($col['beron_total'] ?? 0);
        $grand_total = ($calc_total > 0 && $calc_total >= $man_total) ? $calc_total : $man_total;
        
        $data[] = [
            'index' => $index + 1,
            'date_raw' => $col['collection_date'],
            'date' => date('d-m-Y', strtotime($col['collection_date'])),
            'amount' => number_format($grand_total),
            'id' => $col['id']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

include 'header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM collections WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='collections.php?msg=deleted';</script>";
}
?>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if($_GET['msg'] == 'added') echo "<strong>کامیابی!</strong> نیا ریکارڈ شامل کر لیا گیا (New Record Added).";
        if($_GET['msg'] == 'updated') echo "<strong>کامیابی!</strong> ریکارڈ اپ ڈیٹ ہو گیا (Record Updated).";
        if($_GET['msg'] == 'deleted') echo "<strong>کامیابی!</strong> ریکارڈ حذف کر دیا گیا (Record Deleted).";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
        <h3>چندہ ریکارڈز (Collections)</h3>
        <div>
            <a href="print_all_collections.php" class="btn btn-warning" target="_blank" style="margin-right:5px;"><i class="fas fa-print"></i> تمام پرنٹ کریں (Print All)</a>
            <a href="add_collection.php" class="btn btn-success"><i class="fas fa-plus"></i> نیا اندراج (New Collection)</a>
        </div>
    </div>
    
    <!-- Year Filter -->
    <div class="year-filters mb-3 text-center">
        <label class="fw-bold me-2">سال منتخب کریں (Select Year):</label>
        <div class="btn-group" role="group">
            <?php 
            $current_year = date('Y');
            for($y = 2024; $y <= 2030; $y++): ?>
                <button type="button" class="btn btn-outline-primary year-btn <?php echo $y == $current_year ? 'active' : ''; ?>" data-year="<?php echo $y; ?>" onclick="loadYear(<?php echo $y; ?>)">
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
        <p class="mt-2">ریکارڈ لوڈ ہو رہا ہے...</p>
    </div>

    <!-- Data Table -->
    <table class="table table-bordered table-striped text-center" id="colTable">
        <thead class="table-dark">
            <tr>
                <th>نمبر شمار</th>
                <th>تاریخ (Date)</th>
                <th>کل رقم (Total Amount)</th>
                <th>ایکشن (Action)</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Data will be loaded here via AJAX -->
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load current year by default
    const currentYear = new Date().getFullYear();
    // Use 2024 as fallback if system date is wrong or out of range for demo
    const defaultYear = (currentYear < 2024) ? 2024 : currentYear; 
    
    // Check if we have a button for this year
    if(document.querySelector(`.year-btn[data-year='${defaultYear}']`)) {
        loadYear(defaultYear);
    } else {
        loadYear(2024); // Fallback
    }
});

function loadYear(year) {
    // UI Updates
    document.querySelectorAll('.year-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.year-btn[data-year='${year}']`)?.classList.add('active');
    
    const tbody = document.getElementById('tableBody');
    const loader = document.getElementById('loader');
    const table = document.getElementById('colTable');
    
    // Show Loader
    tbody.innerHTML = '';
    loader.style.display = 'block';
    
    // AJAX Request
    const formData = new FormData();
    formData.append('year', year);
    
    fetch('collections.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(resp => {
        loader.style.display = 'none';
        
        if (resp.status === 'success') {
            const data = resp.data;
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted">اس سال کا کوئی ریکارڈ موجود نہیں (No Records Found)</td></tr>';
            } else {
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.index}</td>
                        <td>${row.date}</td>
                        <td>${row.amount}/-</td>
                        <td>
                            <a href="edit_collection.php?id=${row.id}" class="btn btn-sm btn-warning me-1">ترمیم (Edit)</a>
                            <a href="view_report.php?id=${row.id}" class="btn btn-sm btn-info text-white me-1" target="_blank">رپورٹ (View)</a>
                            <a href="collections.php?delete=${row.id}" class="btn btn-sm btn-danger" onclick="return confirm('واقعی حذف کرنا چاہتے ہیں؟')">حذف</a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } else {
            alert('Error loading data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loader.style.display = 'none';
        tbody.innerHTML = '<tr><td colspan="4" class="text-danger">ڈیٹا لوڈ کرنے میں خرابی پیش آئی (Error Loading Data)</td></tr>';
    });
}
</script>

<?php include 'footer.php'; ?>

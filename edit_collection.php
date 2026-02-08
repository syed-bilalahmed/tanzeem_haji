<?php 
include 'config.php';
include 'header.php'; 

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID not provided.</div>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
$stmt->execute([$id]);
$col = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$col) {
    echo "<div class='alert alert-danger'>Record not found.</div>";
    exit;
}
?>

<div class="card">
    <h3>ریکارڈ ترمیم کریں (Edit Collection)</h3>
    <form action="update_collection.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $col['id']; ?>">
        
        <div style="margin-bottom: 20px;">
            <label>تاریخ (Date):</label>
            <input type="date" name="collection_date" required value="<?php echo $col['collection_date']; ?>" style="font-size:1.2em; padding:5px;">
        </div>

        <!-- Main Grid -->
        <div class="collection-grid">
            
            <!-- Masjid Beron (Outer) -->
             <div class="location-column">
                <div class="location-header">مسجد بیرون (Outer Mosque)</div>
                <div style="display:flex; background:#eee; font-weight:bold; padding:5px;">
                    <span style="flex:1; text-align:center">تعداد (Count)</span>
                    <span style="width:60px; text-align:center">نوٹ</span>
                </div>
                <?php 
                $denominations = [5000, 1000, 500, 100, 50, 20, 10];
                foreach($denominations as $denom): 
                    $key = 'beron_'.$denom;
                    $val = $col[$key] ?? 0;
                ?>
                <div class="denomination-row">
                    <div class="input-group">
                        <input type="number" name="<?php echo $key; ?>" value="<?php echo $val; ?>" placeholder="0" oninput="calculateTotal()" class="count-input" data-loc="beron" data-val="<?php echo $denom; ?>">
                    </div>
                    <div class="denom-label"><?php echo $denom; ?></div>
                </div>
                <?php endforeach; ?>
                <div class="total-row" style="padding:10px; text-align:center;">
                    کل: <input type="number" name="beron_total" id="total_beron" value="<?php echo $col['beron_total'] ?? 0; ?>" style="width:100px; font-weight:bold;">
                </div>
            </div>

            <!-- Masjid Andron (Inner) -->
             <div class="location-column">
                <div class="location-header">مسجد اندرون (Inner Mosque)</div>
                <div style="display:flex; background:#eee; font-weight:bold; padding:5px;">
                    <span style="flex:1; text-align:center">تعداد (Count)</span>
                    <span style="width:60px; text-align:center">نوٹ</span>
                </div>
                <?php foreach($denominations as $denom): 
                    $key = 'andron_'.$denom;
                    $val = $col[$key] ?? 0;
                ?>
                <div class="denomination-row">
                    <div class="input-group">
                        <input type="number" name="<?php echo $key; ?>" value="<?php echo $val; ?>" placeholder="0" oninput="calculateTotal()" class="count-input" data-loc="andron" data-val="<?php echo $denom; ?>">
                    </div>
                    <div class="denom-label"><?php echo $denom; ?></div>
                </div>
                <?php endforeach; ?>
                <div class="total-row" style="padding:10px; text-align:center;">
                    کل: <input type="number" name="andron_total" id="total_andron" value="<?php echo $col['andron_total'] ?? 0; ?>" style="width:100px; font-weight:bold;">
                </div>
            </div>

            <!-- Darbar Masrooq -->
            <div class="location-column">
                <div class="location-header">دربار معہ (Darbar)</div>
                <div style="display:flex; background:#eee; font-weight:bold; padding:5px;">
                    <span style="flex:1; text-align:center">تعداد (Count)</span>
                    <span style="width:60px; text-align:center">نوٹ</span>
                </div>
                <?php foreach($denominations as $denom): 
                    $key = 'darbar_'.$denom;
                    $val = $col[$key] ?? 0;
                ?>
                <div class="denomination-row">
                    <div class="input-group">
                        <input type="number" name="<?php echo $key; ?>" value="<?php echo $val; ?>" placeholder="0" oninput="calculateTotal()" class="count-input" data-loc="darbar" data-val="<?php echo $denom; ?>">
                    </div>
                    <div class="denom-label"><?php echo $denom; ?></div>
                </div>
                <?php endforeach; ?>
                <div class="total-row" style="padding:10px; text-align:center;">
                    کل: <input type="number" name="darbar_total" id="total_darbar" value="<?php echo $col['darbar_total'] ?? 0; ?>" style="width:100px; font-weight:bold;">
                </div>
            </div>
        
        </div>

        <!-- Officials -->
        <h4 style="margin-top:20px;">عہدیداران (Officials)</h4>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
            <div>
                <label>نائب صدر (Vice President):</label>
                <input type="text" name="naib_saddar" value="<?php echo htmlspecialchars($col['naib_saddar'] ?? ''); ?>" style="width:100%; padding:5px;">
            </div>
            <div>
                <label>جنرل سیکرٹری (General Secretary):</label>
                <input type="text" name="general_secretary" value="<?php echo htmlspecialchars($col['general_secretary'] ?? ''); ?>" style="width:100%; padding:5px;">
            </div>
            <div>
                <label>جوائنٹ سیکرٹری (Joint Secretary):</label>
                <input type="text" name="joint_secretary" value="<?php echo htmlspecialchars($col['joint_secretary'] ?? ''); ?>" style="width:100%; padding:5px;">
            </div>
             <div>
                <label>انفارمیشن سیکرٹری (Information Secretary):</label>
                <input type="text" name="information_secretary" value="<?php echo htmlspecialchars($col['information_secretary'] ?? ''); ?>" style="width:100%; padding:5px;">
            </div>
        </div>

        <div style="margin-top:20px; text-align:center;">
            <button type="submit" class="btn btn-primary" style="width:200px;">اپ ڈیٹ کریں (Update)</button>
        </div>
    </form>
</div>

<script>
function calculateTotal() {
    let inputs = document.querySelectorAll('.count-input');
    let totals = { darbar: 0, andron: 0, beron: 0 };
    
    inputs.forEach(input => {
        let loc = input.dataset.loc;
        let val = parseInt(input.dataset.val);
        let count = parseInt(input.value) || 0;
        
        totals[loc] += (count * val);
    });
    
    document.getElementById('total_darbar').value = totals.darbar;
    document.getElementById('total_andron').value = totals.andron;
    document.getElementById('total_beron').value = totals.beron;
}
// Run on load to show initial totals
window.onload = calculateTotal;
</script>

<?php include 'footer.php'; ?>

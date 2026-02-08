<?php
include 'config.php';
include 'header.php';

$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// --- AUTO-SETUP TABLES (Fix for missing table error) ---
$pdo->exec("CREATE TABLE IF NOT EXISTS funeral_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    name VARCHAR(255),
    death_date VARCHAR(20),
    place VARCHAR(255),
    kafan_kit VARCHAR(50),
    digging DECIMAL(10,2) DEFAULT 0,
    tea DECIMAL(10,2) DEFAULT 0,
    truck DECIMAL(10,2) DEFAULT 0,
    other DECIMAL(10,2) DEFAULT 0,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS funeral_year_summary (
    year INT PRIMARY KEY,
    ret_digging DECIMAL(10,2) DEFAULT 0,
    ret_tea DECIMAL(10,2) DEFAULT 0,
    ret_truck DECIMAL(10,2) DEFAULT 0,
    ret_other DECIMAL(10,2) DEFAULT 0
)");

// --- ACTIONS ---

// DELETE
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM funeral_records WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    echo "<script>window.location='funeral_record.php?year=$current_year';</script>";
    exit;
}

// SAVE ALL
if (isset($_POST['save_funeral'])) {
    // 1. Save Records
    if (isset($_POST['records'])) {
        foreach ($_POST['records'] as $r) {
            $name = trim($r['name']);
            if (empty($name) && empty($r['id'])) continue; // Skip empty new rows

            if (!empty($r['id'])) {
                // Update
                if (empty($name)) {
                    // Delete if cleared? No, let's keep it safe or manual delete.
                    // Actually, if name is now empty, maybe nothing? User can use delete button.
                }
                $stmt = $pdo->prepare("UPDATE funeral_records SET year=?, name=?, death_date=?, place=?, kafan_kit=?, digging=?, tea=?, truck=?, other=?, remarks=? WHERE id=?");
                $stmt->execute([$current_year, $name, $r['death_date'], $r['place'], $r['kafan_kit'], $r['digging'], $r['tea'], $r['truck'], $r['other'], $r['remarks'], $r['id']]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO funeral_records (year, name, death_date, place, kafan_kit, digging, tea, truck, other, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$current_year, $name, $r['death_date'], $r['place'], $r['kafan_kit'], $r['digging'], $r['tea'], $r['truck'], $r['other'], $r['remarks']]);
            }
        }
    }

    // 2. Save Summary (Returns)
    $stmt = $pdo->prepare("INSERT INTO funeral_year_summary (year, ret_digging, ret_tea, ret_truck, ret_other) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ret_digging=?, ret_tea=?, ret_truck=?, ret_other=?");
    $stmt->execute([
        $current_year, 
        $_POST['ret_digging'], $_POST['ret_tea'], $_POST['ret_truck'], $_POST['ret_other'],
        $_POST['ret_digging'], $_POST['ret_tea'], $_POST['ret_truck'], $_POST['ret_other']
    ]);

    echo "<script>window.location='funeral_record.php?year=$current_year';</script>";
    exit;
}

// --- FETCH DATA ---
$stmt = $pdo->prepare("SELECT * FROM funeral_records WHERE year = ? ORDER BY id ASC");
$stmt->execute([$current_year]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM funeral_year_summary WHERE year = ?");
$stmt->execute([$current_year]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$summary) $summary = ['ret_digging'=>0, 'ret_tea'=>0, 'ret_truck'=>0, 'ret_other'=>0];

?>

<div class="card shadow-sm">
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Funeral & Burial Record (<?php echo $current_year; ?>)</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                    <?php for($y=2024; $y<=2030; $y++) echo "<option value='$y' ".($current_year==$y?'selected':'').">$y</option>"; ?>
                </select>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>

    <form method="POST">
        <!-- PRINT CONTAINER -->
        <div class="print-container">
            
            <!-- HEADER -->
            <div class="text-center mb-4 header-section">
                <div class="d-flex justify-content-center align-items-center">
                    <img src="logo.jpeg" alt="Logo" style="height: 80px; margin-right: 15px;"> 
                    <div class="text-start">
                        <h2 style="font-weight: 900; font-family: 'Arial', sans-serif; text-transform: uppercase; margin: 0; letter-spacing: 1px;">TANZEEM-E-AULAAD HAZRAT HAJI BAHADAR, KOHAT</h2>
                        <h4 style="font-weight: bold; text-decoration: underline; margin-top: 5px; font-family: 'Times New Roman', serif;">Details of Fund for Funeral and Burial Process (<?php echo $current_year; ?>)</h4>
                    </div>
                </div>
            </div>

            <!-- MAIN TABLE -->
            <div class="table-responsive">
                <table class="table custom-table" id="recordTable">
                    <thead>
                        <tr>
                            <th width="3%">S.No</th>
                            <th width="15%">Name of Deceased Person</th>
                            <th width="8%">Date of Death</th>
                            <th width="12%">Place of Death</th>
                            <th width="5%">Kafan Kit</th>
                            <th width="8%">Grave Digging <br><span class="urdu-text">قبر کی کھدائی</span></th>
                            <th width="8%">Workers Tea & Juice</th>
                            <th width="8%">Shazar Truck</th>
                            <th width="8%">Other <br><span class="urdu-text">دیگر</span></th>
                            <th width="8%">Total</th>
                            <th width="15%">Remarks</th>
                            <th width="3%" class="no-print">X</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rowCount = 0;
                        // 1. Render Existing Records
                        foreach($records as $idx => $rec): 
                            $rowCount++;
                            // Sanitize output
                            $s_name = htmlspecialchars($rec['name']);
                            $s_death_date = htmlspecialchars($rec['death_date']);
                            $s_place = htmlspecialchars($rec['place']);
                            $s_kafan = htmlspecialchars($rec['kafan_kit']);
                            $s_remarks = htmlspecialchars($rec['remarks']);
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $rowCount; ?></td>
                            <td>
                                <input type="hidden" name="records[<?php echo $idx; ?>][id]" value="<?php echo $rec['id']; ?>">
                                <input type="text" name="records[<?php echo $idx; ?>][name]" value="<?php echo $s_name; ?>" class="form-control-plaintext table-input" placeholder="">
                            </td>
                            <td><input type="text" name="records[<?php echo $idx; ?>][death_date]" value="<?php echo $s_death_date; ?>" class="form-control-plaintext table-input text-center" placeholder="YYYY-MM-DD"></td>
                            <td><input type="text" name="records[<?php echo $idx; ?>][place]" value="<?php echo $s_place; ?>" class="form-control-plaintext table-input" placeholder=""></td>
                            <td><input type="text" name="records[<?php echo $idx; ?>][kafan_kit]" value="<?php echo $s_kafan; ?>" class="form-control-plaintext table-input text-center" placeholder="-"></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $idx; ?>][digging]" value="<?php echo $rec['digging']; ?>" class="form-control-plaintext table-input text-center amt-field" data-col="digging" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $idx; ?>][tea]" value="<?php echo $rec['tea']; ?>" class="form-control-plaintext table-input text-center amt-field" data-col="tea" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $idx; ?>][truck]" value="<?php echo $rec['truck']; ?>" class="form-control-plaintext table-input text-center amt-field" data-col="truck" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $idx; ?>][other]" value="<?php echo $rec['other']; ?>" class="form-control-plaintext table-input text-center amt-field" data-col="other" placeholder=""></td>
                            <td><input type="text" class="form-control-plaintext table-input text-center fw-bold total-field" readonly tabIndex="-1"></td>
                            <td><input type="text" name="records[<?php echo $idx; ?>][remarks]" value="<?php echo $s_remarks; ?>" class="form-control-plaintext table-input" placeholder=""></td>
                            <td class="no-print text-center"><a href="?delete_id=<?php echo $rec['id']; ?>&year=<?php echo $current_year; ?>" class="text-danger" onclick="return confirm('Delete this record?')"><i class="fas fa-times"></i></a></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php 
                        // 2. Render Empty Rows to reach minimum 5 or just add 1 empty row at end
                        // Requested: "Default 5 rows"
                        $minRows = 5;
                        $rowsToAdd = max($minRows - $rowCount, 1); // Ensure at least 5 total, or add 1 if already 5
                        if ($rowCount >= 5) $rowsToAdd = 1; // If we have 15 records, add 1 empty new one

                        for($i=0; $i<$rowsToAdd; $i++): 
                            $newIdx = 9999 + $i; // Temp logic for index
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo ++$rowCount; ?></td>
                            <td>
                                <input type="text" name="records[<?php echo $newIdx; ?>][name]" class="form-control-plaintext table-input" placeholder="">
                            </td>
                            <td><input type="text" name="records[<?php echo $newIdx; ?>][death_date]" class="form-control-plaintext table-input text-center" placeholder="YYYY-MM-DD"></td>
                            <td><input type="text" name="records[<?php echo $newIdx; ?>][place]" class="form-control-plaintext table-input" placeholder=""></td>
                            <td><input type="text" name="records[<?php echo $newIdx; ?>][kafan_kit]" class="form-control-plaintext table-input text-center" placeholder="-"></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $newIdx; ?>][digging]" class="form-control-plaintext table-input text-center amt-field" data-col="digging" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $newIdx; ?>][tea]" class="form-control-plaintext table-input text-center amt-field" data-col="tea" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $newIdx; ?>][truck]" class="form-control-plaintext table-input text-center amt-field" data-col="truck" placeholder=""></td>
                            <td><input type="number" step="0.01" name="records[<?php echo $newIdx; ?>][other]" class="form-control-plaintext table-input text-center amt-field" data-col="other" placeholder=""></td>
                            <td><input type="text" class="form-control-plaintext table-input text-center fw-bold total-field" readonly tabIndex="-1"></td>
                            <td><input type="text" name="records[<?php echo $newIdx; ?>][remarks]" class="form-control-plaintext table-input" placeholder=""></td>
                            <td class="no-print"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr class="no-print">
                            <td colspan="12" class="text-start">
                                <button type="button" class="btn btn-sm btn-success" onclick="addRecordRow()">+ Add Row</button>
                                <button type="submit" name="save_funeral" class="btn btn-sm btn-primary ms-2">Save All Data</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SUMMARY TABLE -->
            <div class="mt-4">
                <h4 style="font-weight: bold; font-family: 'Arial', sans-serif; text-decoration: underline; margin-bottom: 15px;">Summary of Funeral and Burial (<?php echo $current_year; ?>) :</h4>
                <table class="table custom-table text-center" id="summaryTable">
                    <thead>
                        <tr class="bg-white">
                            <th width="15%" style="border:none;"></th>
                            <th width="10%">No. of death</th>
                            <th width="10%">Kafan kit</th>
                            <th width="13%">Grave Digging</th>
                            <th width="13%">Tea & Juice</th>
                            <th width="13%">Shazor</th>
                            <th width="13%">Other <span class="urdu-text">دیگر</span></th>
                            <th width="13%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fw-bold">
                            <td class="text-start">Total Amount</td>
                            <td><input type="number" class="form-control-plaintext text-center fw-bold" id="sum_death" value="0" readonly></td>
                            <td><input type="number" class="form-control-plaintext text-center fw-bold" id="sum_kit" value="0" readonly></td>
                            <td id="sum_digging">Rs.0/-</td>
                            <td id="sum_tea">Rs.0/-</td>
                            <td id="sum_truck">Rs.0/-</td>
                            <td id="sum_other">Rs.0/-</td>
                            <td id="sum_grand_total">Rs.0/-</td>
                        </tr>
                        <tr class="fw-bold text-danger">
                            <td class="text-start">Returned Amount</td>
                            <td>-</td>
                            <td>-</td>
                            <td><input type="number" step="0.01" name="ret_digging" value="<?php echo $summary['ret_digging']; ?>" class="form-control-plaintext text-center fw-bold text-danger ret-field" id="ret_digging" placeholder="0"></td>
                            <td><input type="number" step="0.01" name="ret_tea" value="<?php echo $summary['ret_tea']; ?>" class="form-control-plaintext text-center fw-bold text-danger ret-field" id="ret_tea" placeholder="0"></td>
                            <td><input type="number" step="0.01" name="ret_truck" value="<?php echo $summary['ret_truck']; ?>" class="form-control-plaintext text-center fw-bold text-danger ret-field" id="ret_truck" placeholder="0"></td>
                            <td><input type="number" step="0.01" name="ret_other" value="<?php echo $summary['ret_other']; ?>" class="form-control-plaintext text-center fw-bold text-danger ret-field" id="ret_other" placeholder="0"></td>
                            <td id="ret_total">Rs.0/-</td>
                        </tr>
                        <tr class="fw-bold" style="background-color: #f8f9fa;">
                            <td class="text-start" style="font-size: 1.1em;">Total Expense</td>
                            <td id="fin_death">0</td>
                            <td id="fin_kit">0</td>
                            <td id="fin_digging">Rs.0/-</td>
                            <td id="fin_tea">Rs.0/-</td>
                            <td id="fin_truck">Rs.0/-</td>
                            <td id="fin_other">Rs.0/-</td>
                            <td id="fin_total">Rs.0/-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SIGNATURES -->
            <div class="mt-5 pt-5">
                <div class="d-flex justify-content-between align-items-end" style="font-weight: bold; font-family: 'Times New Roman', serif;">
                    <div style="text-align: left;">
                        Fawad Hussain: <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                    </div>
                    <div style="text-align: center;">
                        Syed Haris Shah <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                    </div>
                    <div style="text-align: center;">
                        M Kamil Shah: <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                    </div>
                    <div style="text-align: right;">
                         Syed AfiatHussain shah: <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<style>
    /* Font Imports */
    @import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap');

    .urdu-text {
        font-family: 'Noto Nastaliq Urdu', serif;
        font-weight: normal;
        font-size: 0.9em;
    }

    .print-container {
        font-family: 'Times New Roman', serif;
        color: #000;
        background: #fff;
        padding: 20px;
    }

    .custom-table {
        border: 2px solid #000 !important;
        width: 100%;
        margin-bottom: 0;
    }

    .custom-table th, .custom-table td {
        border: 1px solid #000 !important;
        padding: 4px 6px; /* Tight padding for compact rows */
        vertical-align: middle;
        font-size: 13px;
    }

    .custom-table thead th {
        border-bottom: 2px solid #000 !important;
        font-weight: bold;
        text-align: center;
        background-color: #fff; /* White bg for print */
    }

    .table-input {
        width: 100%;
        padding: 0;
        margin: 0;
        border: none;
        outline: none;
        font-size: 13px;
        background: transparent;
    }
    .table-input:focus {
        background-color: #eef;
    }

    /* Column Width Adjustments */
    #summaryTable th, #summaryTable td {
        padding: 8px;
        font-size: 14px;
    }

    @media print {
        @page { size: A4 landscape; margin: 5mm; }
        body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; background: white !important; }
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important; }
        .navbar, .sidebar { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        
        /* Ensure inputs print their values */
        input { border: none !important; box-shadow: none !important; }
        /* Hide placeholders on print */
        input::placeholder { color: transparent !important; }
        .text-danger { color: #000 !important; } /* Clean print */
    }
</style>

<script>
    function updateTotals() {
        let totals = { digging: 0, tea: 0, truck: 0, other: 0, row_total: 0 };
        let count_death = 0; 
        let count_kit = 0; 

        // 1. Process Rows
        document.querySelectorAll('#recordTable tbody tr').forEach(tr => {
            const digging = parseFloat(tr.querySelector('[data-col="digging"]').value) || 0;
            const tea = parseFloat(tr.querySelector('[data-col="tea"]').value) || 0;
            const truck = parseFloat(tr.querySelector('[data-col="truck"]').value) || 0;
            const other = parseFloat(tr.querySelector('[data-col="other"]').value) || 0;

            const rowSum = digging + tea + truck + other;
            tr.querySelector('.total-field').value = rowSum > 0 ? rowSum : '';

            totals.digging += digging;
            totals.tea += tea;
            totals.truck += truck;
            totals.other += other;
            totals.row_total += rowSum;

            // Counts logic
            const nameInp = tr.cells[1].querySelector('input');
            if(nameInp && nameInp.value.trim() !== '') {
                count_death++;
            }
            
            const kitVal = tr.cells[4].querySelector('input').value.toLowerCase();
            if((kitVal.includes('yes') || kitVal.includes('y') || (!isNaN(parseFloat(kitVal)) && parseFloat(kitVal) > 0))) {
                count_kit++;
            }
        });

        // 2. Update Total Row in Summary
        document.getElementById('sum_death').value = count_death;
        document.getElementById('sum_kit').value = count_kit;
        
        document.getElementById('sum_digging').innerText = formatRs(totals.digging);
        document.getElementById('sum_tea').innerText = formatRs(totals.tea);
        document.getElementById('sum_truck').innerText = formatRs(totals.truck);
        document.getElementById('sum_other').innerText = formatRs(totals.other);
        document.getElementById('sum_grand_total').innerText = formatRs(totals.row_total);

        // 3. Process Returns & Net Expense
        const ret_digging = parseFloat(document.getElementById('ret_digging').value) || 0;
        const ret_tea = parseFloat(document.getElementById('ret_tea').value) || 0;
        const ret_truck = parseFloat(document.getElementById('ret_truck').value) || 0;
        const ret_other = parseFloat(document.getElementById('ret_other').value) || 0;
        
        const ret_total = ret_digging + ret_tea + ret_truck + ret_other;
        document.getElementById('ret_total').innerText = formatRs(ret_total);

        // Net
        document.getElementById('fin_death').innerText = count_death;
        document.getElementById('fin_kit').innerText = count_kit; 
        
        document.getElementById('fin_digging').innerText = formatRs(totals.digging - ret_digging);
        document.getElementById('fin_tea').innerText = formatRs(totals.tea - ret_tea);
        document.getElementById('fin_truck').innerText = formatRs(totals.truck - ret_truck);
        document.getElementById('fin_other').innerText = formatRs(totals.other - ret_other);
        document.getElementById('fin_total').innerText = formatRs(totals.row_total - ret_total);
    }

    function formatRs(num) {
        return "Rs." + num.toLocaleString() + "/-";
    }

    function addRecordRow() {
        const tbody = document.querySelector('#recordTable tbody');
        const rowCount = tbody.rows.length + 1;
        const tr = document.createElement('tr');
        const uniqueIdx = Date.now(); // Simple unique ID for array key
        tr.innerHTML = `
            <td class="text-center fw-bold">${rowCount}</td>
            <td><input type="text" name="records[new_${uniqueIdx}][name]" class="form-control-plaintext table-input" placeholder=""></td>
            <td><input type="text" name="records[new_${uniqueIdx}][death_date]" class="form-control-plaintext table-input text-center" placeholder="YYYY-MM-DD"></td>
            <td><input type="text" name="records[new_${uniqueIdx}][place]" class="form-control-plaintext table-input" placeholder=""></td>
            <td><input type="text" name="records[new_${uniqueIdx}][kafan_kit]" class="form-control-plaintext table-input text-center" placeholder="-"></td>
            <td><input type="number" step="0.01" name="records[new_${uniqueIdx}][digging]" class="form-control-plaintext table-input text-center amt-field" data-col="digging" placeholder=""></td>
            <td><input type="number" step="0.01" name="records[new_${uniqueIdx}][tea]" class="form-control-plaintext table-input text-center amt-field" data-col="tea" placeholder=""></td>
            <td><input type="number" step="0.01" name="records[new_${uniqueIdx}][truck]" class="form-control-plaintext table-input text-center amt-field" data-col="truck" placeholder=""></td>
            <td><input type="number" step="0.01" name="records[new_${uniqueIdx}][other]" class="form-control-plaintext table-input text-center amt-field" data-col="other" placeholder=""></td>
            <td><input type="text" class="form-control-plaintext table-input text-center fw-bold total-field" readonly tabIndex="-1"></td>
            <td><input type="text" name="records[new_${uniqueIdx}][remarks]" class="form-control-plaintext table-input" placeholder=""></td>
            <td class="no-print"></td>
        `;
        tbody.appendChild(tr);
        
        // Attach listener only to new inputs
        tr.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', updateTotals);
        });
    }

    // Event Listeners
    document.querySelectorAll('input').forEach(inp => {
        inp.addEventListener('input', updateTotals);
    });

    // Run on load to set defaults
    window.addEventListener('DOMContentLoaded', updateTotals);
</script>

<?php include 'footer.php'; ?>

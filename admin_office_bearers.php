<?php
include 'config.php';
include 'header.php';

// Handle Add/Edit/Delete
$msg = '';
if (isset($_POST['add_member'])) {
    $name_ur = $_POST['name_ur'];
    $name_en = $_POST['name_en'];
    $khail_ur = $_POST['khail_ur'];
    $khail_en = $_POST['khail_en'];
    $role_ur = $_POST['role_ur'];
    $role_en = $_POST['role_en'];
    $term_start = $_POST['term_start'] ?: null;
    $term_end = $_POST['term_end'] ?: null;
    $status = $_POST['status'];
    $sort = (int)$_POST['sort_order'];

    $stmt = $pdo->prepare("INSERT INTO office_bearers (name_ur, name_en, khail_ur, khail_en, role_ur, role_en, term_start, term_end, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name_ur, $name_en, $khail_ur, $khail_en, $role_ur, $role_en, $term_start, $term_end, $status, $sort]);
    $msg = "عہدیدار شامل کر لیا گیا (Member Added).";
}

if (isset($_POST['update_member'])) {
    $id = (int)$_POST['id'];
    $name_ur = $_POST['name_ur'];
    $name_en = $_POST['name_en'];
    $khail_ur = $_POST['khail_ur'];
    $khail_en = $_POST['khail_en'];
    $role_ur = $_POST['role_ur'];
    $role_en = $_POST['role_en'];
    $term_start = $_POST['term_start'] ?: null;
    $term_end = $_POST['term_end'] ?: null;
    $status = $_POST['status'];
    $sort = (int)$_POST['sort_order'];

    $stmt = $pdo->prepare("UPDATE office_bearers SET name_ur=?, name_en=?, khail_ur=?, khail_en=?, role_ur=?, role_en=?, term_start=?, term_end=?, status=?, sort_order=? WHERE id=?");
    $stmt->execute([$name_ur, $name_en, $khail_ur, $khail_en, $role_ur, $role_en, $term_start, $term_end, $status, $sort, $id]);
    $msg = "عہدیدار اپ ڈیٹ ہو گیا (Member Updated).";
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM office_bearers WHERE id = ?")->execute([$id]);
    $msg = "عہدیدار حذف کر دیا گیا (Member Deleted).";
}

// Fetch all members
$members = $pdo->query("SELECT * FROM office_bearers ORDER BY sort_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>انتظامیہ / عہدیداران (Office Bearers)</h3>
        <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> نیا اندراج (Add New)</button>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- Inline Add/Edit Form -->
    <div id="memberForm" class="card mb-4 shadow-sm" style="display:none; background:#f0f8ff; border:2px solid #007bff;">
        <div class="card-header text-white" id="formHeader" style="background:#007bff;">
            <h5 class="mb-0" id="formTitle">نیا عہدیدار شامل کریں (Add Member)</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" id="mem_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name (Urdu):</label>
                        <input type="text" name="name_ur" id="mem_name_ur" class="form-control" required dir="rtl">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name (English):</label>
                        <input type="text" name="name_en" id="mem_name_en" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Khail/Clan (Urdu):</label>
                        <input type="text" name="khail_ur" id="mem_khail_ur" class="form-control" dir="rtl" placeholder="مثلاً: یوسف خیل">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Khail/Clan (English):</label>
                        <input type="text" name="khail_en" id="mem_khail_en" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Role/Designation (Urdu):</label>
                        <input type="text" name="role_ur" id="mem_role_ur" class="form-control" dir="rtl" placeholder="مثلاً: صدر">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role/Designation (English):</label>
                        <input type="text" name="role_en" id="mem_role_en" class="form-control" placeholder="e.g. President">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date:</label>
                        <input type="date" name="term_start" id="mem_start" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date (Optional):</label>
                        <input type="date" name="term_end" id="mem_end" class="form-control">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Status:</label>
                        <select name="status" id="mem_status" class="form-select">
                            <option value="active">Active (موجودہ)</option>
                            <option value="past">Past (سابقہ)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order:</label>
                        <input type="number" name="sort_order" id="mem_sort" class="form-control" value="0">
                        <small class="text-muted d-block mt-1">1=President, 2=VP, 3=Gen Sec, etc.</small>
                    </div>

                    <div class="col-12 mt-2">
                         <div class="alert alert-info py-2 small mb-0">
                            <strong>Sorting Guide:</strong> 
                            1: President (صدر), 
                            2: Vice President (نائب صدر), 
                            3: General Secretary (جنرل سیکرٹری), 
                            4: Finance Secretary (فنانس سیکرٹری), 
                            5: Info Secretary (سیکرٹری نشر و اشاعت), 
                            6: Joint Secretary (جوائنٹ سیکرٹری), 
                            7+: Members
                        </div>
                    </div>

                    <div class="col-12 text-end mt-3">
                        <button type="button" class="btn btn-secondary" onclick="hideForm()">Cancel</button>
                        <button type="submit" name="add_member" id="btn_add" class="btn btn-success px-4">Save Record</button>
                        <button type="submit" name="update_member" id="btn_update" class="btn btn-primary px-4" style="display:none;">Update Record</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-hover align-middle mt-3">
        <thead class="table-light">
            <tr>
                <th>Sort</th>
                <th>نام (Name)</th>
                <th>عہدہ (Role)</th>
                <th>خیل (Khail)</th>
                <th>دورانیہ (Term)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?php echo $m['sort_order']; ?></td>
                <td>
                    <span class="fw-bold"><?php echo $m['name_ur']; ?></span><br>
                    <small class="text-muted"><?php echo $m['name_en']; ?></small>
                </td>
                <td>
                    <span class="badge bg-info text-dark"><?php echo $m['role_ur']; ?></span><br>
                    <small><?php echo $m['role_en']; ?></small>
                </td>
                <td>
                    <?php echo $m['khail_ur']; ?>
                </td>
                <td>
                    <small>
                        <?php echo $m['term_start'] ? date('M Y', strtotime($m['term_start'])) : '...'; ?> 
                        - 
                        <?php echo $m['term_end'] ? date('M Y', strtotime($m['term_end'])) : 'Present'; ?>
                    </small>
                </td>
                <td>
                    <?php if($m['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Past</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick='editMember(<?php echo htmlspecialchars(json_encode($m), ENT_QUOTES); ?>)'><i class="fas fa-edit"></i></button>
                    <a href="admin_office_bearers.php?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('واقعی حذف کرنا چاہتے ہیں؟')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function resetForm() {
    document.getElementById('mem_id').value = '';
    document.getElementById('mem_name_ur').value = '';
    document.getElementById('mem_name_en').value = '';
    document.getElementById('mem_khail_ur').value = '';
    document.getElementById('mem_khail_en').value = '';
    document.getElementById('mem_role_ur').value = '';
    document.getElementById('mem_role_en').value = '';
    document.getElementById('mem_start').value = '';
    document.getElementById('mem_end').value = '';
    document.getElementById('mem_status').value = 'active';
    document.getElementById('mem_sort').value = '0';
}

function showAddForm() {
    resetForm();
    document.getElementById('formTitle').innerText = 'نیا عہدیدار شامل کریں (Add Member)';
    document.getElementById('formHeader').style.background = '#28a745';
    document.getElementById('btn_add').style.display = 'inline-block';
    document.getElementById('btn_update').style.display = 'none';
    document.getElementById('memberForm').style.display = 'block';
    document.getElementById('memberForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function hideForm() {
    document.getElementById('memberForm').style.display = 'none';
}

function editMember(m) {
    resetForm();
    document.getElementById('formTitle').innerText = 'ترمیم کریں (Edit Member)';
    document.getElementById('formHeader').style.background = '#0d6efd';
    document.getElementById('btn_add').style.display = 'none';
    document.getElementById('btn_update').style.display = 'inline-block';
    
    document.getElementById('mem_id').value = m.id;
    document.getElementById('mem_name_ur').value = m.name_ur;
    document.getElementById('mem_name_en').value = m.name_en;
    document.getElementById('mem_khail_ur').value = m.khail_ur;
    document.getElementById('mem_khail_en').value = m.khail_en;
    document.getElementById('mem_role_ur').value = m.role_ur;
    document.getElementById('mem_role_en').value = m.role_en;
    document.getElementById('mem_start').value = m.term_start;
    document.getElementById('mem_end').value = m.term_end;
    document.getElementById('mem_status').value = m.status;
    document.getElementById('mem_sort').value = m.sort_order;
    
    document.getElementById('memberForm').style.display = 'block';
    document.getElementById('memberForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php include 'footer.php'; ?>

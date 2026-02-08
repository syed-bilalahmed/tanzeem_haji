<?php
include 'config.php';
include 'header.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<style>
    body, .modal-title, .form-control, .form-select, .table {
        font-family: 'Inter', 'Noto Nastaliq Urdu', serif !important;
    }
    .fw-bold { font-family: 'Noto Nastaliq Urdu', serif !important; }
    
    /* Eliminate ALL modal animations to prevent blinking */
    .modal {
        transition: none !important;
        animation: none !important;
    }
    .modal.show {
        transition: none !important;
        animation: none !important;
    }
    .modal-backdrop {
        transition: none !important;
        animation: none !important;
    }
    .modal-backdrop.show {
        transition: none !important;
        animation: none !important;
    }
    .modal-dialog {
        transition: none !important;
        animation: none !important;
        transform: none !important;
    }
    .modal-content {
        transition: none !important;
        animation: none !important;
    }
    /* Prevent any blur effects */
    .modal-backdrop {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
</style>
<?php

// Handle Add/Edit/Delete
$msg = '';
if (isset($_POST['add_node'])) {
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $name_ur = $_POST['name_ur'];
    $name_en = $_POST['name_en'];
    $title_ur = $_POST['title_ur'];
    $title_en = $_POST['title_en'];
    $khail_ur = $_POST['khail_ur'];
    $khail_en = $_POST['khail_en'];
    $sort = (int)$_POST['sort_order'];
    $is_main = isset($_POST['is_main_node']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO shajra_nodes (parent_id, name_ur, name_en, title_ur, title_en, khail_ur, khail_en, sort_order, is_main_node) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$parent_id, $name_ur, $name_en, $title_ur, $title_en, $khail_ur, $khail_en, $sort, $is_main]);
    $msg = "نیا رکن شامل کر لیا گیا (Node Added).";
}

if (isset($_POST['update_node'])) {
    $id = (int)$_POST['id'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $name_ur = $_POST['name_ur'];
    $name_en = $_POST['name_en'];
    $title_ur = $_POST['title_ur'];
    $title_en = $_POST['title_en'];
    $khail_ur = $_POST['khail_ur'];
    $khail_en = $_POST['khail_en'];
    $sort = (int)$_POST['sort_order'];
    $is_main = isset($_POST['is_main_node']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE shajra_nodes SET parent_id=?, name_ur=?, name_en=?, title_ur=?, title_en=?, khail_ur=?, khail_en=?, sort_order=?, is_main_node=? WHERE id=?");
    $stmt->execute([$parent_id, $name_ur, $name_en, $title_ur, $title_en, $khail_ur, $khail_en, $sort, $is_main, $id]);
    $msg = "رکن اپ ڈیٹ ہو گیا (Node Updated).";
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM shajra_nodes WHERE id = ?")->execute([$id]);
    $msg = "رکن حذف کر دیا گیا (Node Deleted).";
}

// Pagination Logic
$limit = 20; // Nodes per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total count for pagination
$total_stmt = $pdo->query("SELECT COUNT(*) FROM shajra_nodes");
$total_nodes = $total_stmt->fetchColumn();
$total_pages = ceil($total_nodes / $limit);

// Fetch nodes for listing with limit and offset
$nodes_stmt = $pdo->prepare("SELECT n.*, p.name_ur as parent_name FROM shajra_nodes n LEFT JOIN shajra_nodes p ON n.parent_id = p.id ORDER BY n.id ASC LIMIT ? OFFSET ?");
$nodes_stmt->bindValue(1, $limit, PDO::PARAM_INT);
$nodes_stmt->bindValue(2, $offset, PDO::PARAM_INT);
$nodes_stmt->execute();
$nodes = $nodes_stmt->fetchAll(PDO::FETCH_ASSOC);

// For dropdown (Show all for parent selection)
$parent_options = $pdo->query("SELECT id, name_ur, name_en FROM shajra_nodes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>شجرہ نسب مینجمنٹ (Family Tree CRUD)</h3>
        <div>
            <a href="family_tree.php" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye"></i> شجرہ دیکھیں (View Shajra)</a>
            <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> نیا رکن (Add New)</button>
        </div>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- Inline Add/Edit Form -->
    <div id="shajraForm" class="card mb-4 shadow-sm" style="display:none; background:#f0f8f0; border:2px solid #28a745;">
        <div class="card-header text-white" id="formHeader" style="background:#28a745;">
            <h5 class="mb-0" id="formTitle">نیا رکن شامل کریں (Add Member)</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" id="node_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">والد / سرپرست (Parent Member):</label>
                        <select name="parent_id" id="node_parent" class="form-select border-success">
                            <option value="">-- کوئی نہیں (Root) --</option>
                            <?php foreach($parent_options as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo $p['name_ur']; ?> (<?php echo $p['name_en']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">نام (اردو):</label>
                        <input type="text" name="name_ur" id="node_name_ur" class="form-control border-success" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name (English):</label>
                        <input type="text" name="name_en" id="node_name_en" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">لقب / خطاب (Title Ur):</label>
                        <input type="text" name="title_ur" id="node_title_ur" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title (En):</label>
                        <input type="text" name="title_en" id="node_title_en" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">خیل / شاخ (Khail Ur):</label>
                        <input type="text" name="khail_ur" id="node_khail_ur" class="form-control" placeholder="مثلاً: یوسف خیل">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Khail / Branch (En):</label>
                        <input type="text" name="khail_en" id="node_khail_en" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ترتیب (Sort):</label>
                        <input type="number" name="sort_order" id="node_sort" class="form-control" value="0">
                    </div>
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_main_node" id="node_main_check">
                            <label class="form-check-label fw-bold" for="node_main_check">نمایاں رکن (Main Historical Node)</label>
                        </div>
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button type="button" class="btn btn-secondary" onclick="hideForm()">کینسل</button>
                        <button type="submit" name="add_node" id="btn_add" class="btn btn-success px-4">محفوظ کریں</button>
                        <button type="submit" name="update_node" id="btn_update" class="btn btn-primary px-4" style="display:none;">اپ ڈیٹ کریں</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-hover align-middle mt-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>نام (Name)</th>
                <th>والد (Parent)</th>
                <th>خیل (Khail)</th>
                <th>ایکشن (Action)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($nodes as $node): ?>
            <tr>
                <td><?php echo $node['id']; ?></td>
                <td>
                    <span class="fw-bold text-dark"><?php echo $node['name_ur']; ?></span><br>
                    <small class="text-muted"><?php echo $node['name_en']; ?></small>
                </td>
                <td><?php echo $node['parent_name'] ?? '<span class="badge bg-secondary">Root Node</span>'; ?></td>
                <td><span class="badge bg-outline-warning text-dark border border-warning px-3"><?php echo $node['khail_ur'] ?: '---'; ?></span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-success" title="صاحبزادہ شامل کریں" onclick="addSon(<?php echo $node['id']; ?>)"><i class="fas fa-plus"></i> بیٹا</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick='editNode(<?php echo htmlspecialchars(json_encode($node), ENT_QUOTES); ?>)'><i class="fas fa-edit"></i></button>
                    <a href="admin_shajra.php?delete=<?php echo $node['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('واقعی حذف کرنا چاہتے ہیں؟')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">پچھلا (Previous)</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">اگلا (Next)</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function resetForm() {
    document.getElementById('node_id').value = '';
    document.getElementById('node_parent').value = '';
    document.getElementById('node_name_ur').value = '';
    document.getElementById('node_name_en').value = '';
    document.getElementById('node_title_ur').value = '';
    document.getElementById('node_title_en').value = '';
    document.getElementById('node_khail_ur').value = '';
    document.getElementById('node_khail_en').value = '';
    document.getElementById('node_sort').value = '0';
    document.getElementById('node_main_check').checked = false;
}

function showAddForm() {
    resetForm();
    document.getElementById('formTitle').innerText = 'نیا رکن شامل کریں (Add Member)';
    document.getElementById('formHeader').style.background = '#28a745';
    document.getElementById('btn_add').style.display = 'inline-block';
    document.getElementById('btn_update').style.display = 'none';
    document.getElementById('shajraForm').style.display = 'block';
    document.getElementById('shajraForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function hideForm() {
    document.getElementById('shajraForm').style.display = 'none';
}

function addSon(parentId) {
    resetForm();
    document.getElementById('node_parent').value = parentId;
    document.getElementById('formTitle').innerText = 'صاحبزادہ شامل کریں (Add Son)';
    document.getElementById('formHeader').style.background = '#28a745';
    document.getElementById('btn_add').style.display = 'inline-block';
    document.getElementById('btn_update').style.display = 'none';
    document.getElementById('shajraForm').style.display = 'block';
    document.getElementById('shajraForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function editNode(node) {
    resetForm();
    document.getElementById('formTitle').innerText = 'ترمیم کریں (Edit Member)';
    document.getElementById('formHeader').style.background = '#0d6efd';
    document.getElementById('btn_add').style.display = 'none';
    document.getElementById('btn_update').style.display = 'inline-block';
    
    document.getElementById('node_id').value = node.id;
    document.getElementById('node_parent').value = node.parent_id;
    document.getElementById('node_name_ur').value = node.name_ur;
    document.getElementById('node_name_en').value = node.name_en;
    document.getElementById('node_title_ur').value = node.title_ur;
    document.getElementById('node_title_en').value = node.title_en;
    document.getElementById('node_khail_ur').value = node.khail_ur;
    document.getElementById('node_khail_en').value = node.khail_en;
    document.getElementById('node_sort').value = node.sort_order;
    document.getElementById('node_main_check').checked = (node.is_main_node == 1);
    
    document.getElementById('shajraForm').style.display = 'block';
    document.getElementById('shajraForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php include 'footer.php'; ?>

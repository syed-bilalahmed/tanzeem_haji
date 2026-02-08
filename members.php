<?php
include 'frontend_header.php';

// --- DATA FETCHING ---
// Fetch ALL data once and let JS handle the filtering/display.
// This provides the "No Reload" experience requested.

// 1. Get all office bearers sorted by date descending (latest first)
$stmt = $pdo->query("SELECT * FROM office_bearers WHERE status = 'active' OR status = 'past' ORDER BY term_start DESC, sort_order ASC");
$all_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Prepare standardized JSON data for JS
$data_members = [];
$distinct_years = [];
$distinct_roles = [];

foreach ($all_members as $m) {
    // Determine the year(s) this member belongs to
    // For simplicity, we assign them to the year their term started, 
    // AND if they have a term_end, we might want to logic that (but simple start year is standard for cabinets).
    // Actually, following previous logic: "Cabinet of Year X" includes anyone active in Year X.
    
    $start_date = $m['term_start'];
    $end_date = ($m['term_end'] && $m['term_end'] != '0000-00-00') ? $m['term_end'] : date('Y-m-d'); // If active, use current date for range check
    
    $start_year = (int)date('Y', strtotime($start_date));
    $end_year = (int)date('Y', strtotime($end_date));
    
    // Add to distinct years list (all years they touched)
    for ($y = $start_year; $y <= $end_year; $y++) {
        $distinct_years[$y] = true;
    }

    // Add to distinct roles list
    $r_key = strtolower(trim($m['role_en']));
    if (!isset($distinct_roles[$r_key])) {
        $distinct_roles[$r_key] = [
            'en' => $m['role_en'],
            'ur' => $m['role_ur'],
            'sort' => $m['sort_order']
        ];
    }
    
    // Process display data
    $m['display_name_en'] = $m['name_en'] ?: $m['name_ur'];
    $m['display_name_ur'] = $m['name_ur'];
    
    $m['display_khail_en'] = $m['khail_en'] ?: $m['khail_ur'];
    $m['display_khail_ur'] = $m['khail_ur'];
    
    $m['term_start_formatted'] = date('M Y', strtotime($m['term_start']));
    $m['term_end_formatted'] = ($m['term_end'] && $m['term_end'] != '0000-00-00') ? date('M Y', strtotime($m['term_end'])) : (($lang=='en')?'Present':'تا حال');
    
    // Robust status check
    $m['is_active'] = (strtolower($m['status']) === 'active');
    
    $data_members[] = $m;
}

// Sort years descending
krsort($distinct_years);
$years_list = array_keys($distinct_years);

// Sort roles by sort_order
usort($distinct_roles, function($a, $b) {
    return $a['sort'] - $b['sort'];
});

// Page Titles
$page_title_ur = "انتظامیہ / عہدیداران";
$page_title_en = "Office Bearers / Management";
$title = ($lang == 'en') ? $page_title_en : $page_title_ur;
?>

<!-- Pass Data to JS -->
<script>
    const membersData = <?php echo json_encode($data_members); ?>;
    const yearsList = <?php echo json_encode($years_list); ?>;
    const rolesList = <?php echo json_encode(array_values($distinct_roles)); ?>;
    const currentLang = "<?php echo $lang; ?>";
    const serverYear = <?php echo date('Y'); ?>; // Sync time with server
</script>

<div class="page-header">
    <div class="container">
        <h1 class="page-title" data-aos="fade-down"><?php echo $title; ?></h1>
    </div>
</div>

<div class="container mb-5">
    
    <!-- View Switcher Tabs -->
    <div class="d-flex justify-content-center mb-4">
        <div class="btn-group" role="group">
            <button onclick="switchView('year')" id="btn-view-year" class="btn btn-primary px-4 active">
                <i class="fas fa-calendar-alt me-2"></i> <?php echo ($lang=='en') ? 'By Year (Sessions)' : 'سیشن وائز (سالانہ)'; ?>
            </button>
            <button onclick="switchView('role')" id="btn-view-role" class="btn btn-outline-primary px-4">
                <i class="fas fa-list-ul me-2"></i> <?php echo ($lang=='en') ? 'By Designation (History)' : 'عہدہ وائز (تاریخ)'; ?>
            </button>
        </div>
    </div>

    <!-- Filters Area -->
    <div class="row mb-5">
        <div class="col-12 text-center" id="filters-container">
            <!-- Injeected by JS -->
        </div>
    </div>

    <!-- Section Title -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h3 class="fw-bold border-bottom d-inline-block pb-2 px-4" id="section-title">...</h3>
        </div>
    </div>

    <!-- Content Area -->
    <div id="content-container">
        
        <!-- YEAR VIEW CONTAINER -->
        <div id="year-view-container">
            <!-- Cards injected here -->
        </div>

        <!-- ROLE VIEW CONTAINER (Hidden by default) -->
        <div id="role-view-container" style="display:none;">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0 text-center">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="py-3"><?php echo ($lang=='en')?'Name':'نام'; ?></th>
                                            <th class="py-3"><?php echo ($lang=='en')?'Term':'دورانیہ'; ?></th>
                                            <th class="py-3"><?php echo ($lang=='en')?'Status':'حیثیت'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="role-table-body">
                                        <!-- Rows injected here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State for Role View -->
                    <div id="role-empty-state" class="text-center py-5" style="display:none;">
                        <div class="alert alert-light border shadow-sm d-inline-block px-5">
                            <i class="fas fa-folder-open fa-2x mb-3 text-muted"></i>
                            <p class="text-muted fs-5 mb-0"><?php echo ($lang=='en') ? "No records found." : "کوئی ریکارڈ موجود نہیں ہے۔"; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .cursor-pointer { cursor: pointer; }

    /* TREE / ORGANIZATION CHART CSS */
    .org-tree {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        gap: 25px; /* Reduced from 40px */
    }
    .org-level {
        display: flex;
        justify-content: center;
        gap: 15px; /* Reduced from 20px */
        flex-wrap: wrap;
        width: 100%;
        position: relative;
    }
    .org-node {
        width: 220px; /* Reduced from 280px */
        position: relative;
        z-index: 2;
    }
    
    /* Connectors logic - Simple vertical lines for now */
    .org-level + .org-level::before {
        content: '';
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        width: 2px;
        height: 40px;
        background-color: #ccc;
        z-index: 1;
    }
    /* Horizontal connector for multiple items in a level */
    .org-level.multi-item::after {
        content: '';
        position: absolute;
        top: -20px;
        left: 20%;
        right: 20%;
        height: 2px;
        background-color: #ccc;
        z-index: 1;
    }
    .org-level.multi-item .org-node::before {
        content: '';
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 2px;
        height: 20px;
        background-color: #ccc;
    }
    
    /* Special styling for President */
    .org-node.rank-1 .card {
        border: 4px solid var(--secondary-color);
        transform: scale(1.1);
    }
    
    /* COMPACT HEADER STYLES */
    .page-title {
        font-size: 2rem !important; /* Smaller from default 2.5/3rem */
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    .page-header {
        padding: 20px 0 10px 0 !important; /* Reduced padding */
        min-height: auto !important;
    }
    .mb-5 { margin-bottom: 1.5rem !important; } /* Reduce generic spacing */
    .mb-4 { margin-bottom: 1rem !important; }
    h3.fw-bold { font-size: 1.5rem !important; } /* Section title smaller */
</style>

<script>
// Get initial view from URL
const urlParams = new URLSearchParams(window.location.search);
const initialView = urlParams.get('view') || 'year';

let currentView = initialView; // 'year' or 'role'
let selectedYear = yearsList.length > 0 ? yearsList[0] : serverYear;
let selectedRole = rolesList.length > 0 ? rolesList[0].en : '';

// Init
document.addEventListener('DOMContentLoaded', () => {
    // Sync UI with currentView (if it's not 'year')
    if (currentView !== 'year') {
        switchView(currentView);
    } else {
        // Initial UI Setup for default view
        if (yearsList.length === 0) {
            document.getElementById('filters-container').innerHTML = '<p class="text-muted">No data available.</p>';
            return;
        }
        renderFilters();
        renderContent();
    }
});

function switchView(view) {
    currentView = view;
    
    // Update Tab UI
    document.getElementById('btn-view-year').className = view === 'year' ? 'btn btn-primary px-4' : 'btn btn-outline-primary px-4';
    document.getElementById('btn-view-role').className = view === 'role' ? 'btn btn-primary px-4' : 'btn btn-outline-primary px-4';
    
    // Toggle Containers
    document.getElementById('year-view-container').style.display = (view === 'year') ? 'block' : 'none';
    document.getElementById('role-view-container').style.display = (view === 'role') ? 'block' : 'none';
    
    renderFilters();
    renderContent();
}

function selectYear(yr) {
    selectedYear = yr;
    renderFilters();
    renderContent();
}

function selectRole(roleEn) {
    selectedRole = roleEn;
    renderFilters();
    renderContent();
}

function renderFilters() {
    const container = document.getElementById('filters-container');
    let html = '';

    if (currentView === 'year') {
        // User requested to hide year buttons and just show Current Cabinet
        html = '';
    } else {
        html += '<div class="d-flex flex-wrap justify-content-center gap-2">';
        rolesList.forEach(r => {
            const label = (currentLang === 'en') ? r.en : r.ur;
            const isSelected = (r.en === selectedRole);
            const btnClass = isSelected ? 'btn-info text-white' : 'btn-outline-info';
            html += `<button onclick="selectRole('${r.en}')" class="btn ${btnClass} rounded-pill">${label}</button>`;
        });
        html += '</div>';
    }

    container.innerHTML = html;
}

function renderContent() {
    const titleEl = document.getElementById('section-title');
    
    let filteredData = [];
    
    if (currentView === 'year') {
        const yearContainer = document.getElementById('year-view-container');
        
        // Title
        // Find the LATEST year available in the data
        const latestYear = Math.max(...yearsList);
        const isLatest = (selectedYear == latestYear);
        
        let labelEn = '', labelUr = '';
        
        // Always show "Current Cabinet" for the default view (which is latest year)
        // User requested "just show heading current cabinet"
        if (isLatest) {
            labelEn = `Current Cabinet`;
            labelUr = `موجودہ کابینہ`;
        } else {
            // Fallback if somehow they access old years (though buttons are hidden)
            labelEn = `Cabinet (${selectedYear})`;
            labelUr = `کابینہ (${selectedYear})`;
        }

        titleEl.innerText = (currentLang === 'en') ? labelEn : labelUr;
        
        // Filter Data
        // Filter Data
        // Filter Data - Primary Pass
        function filterPrimary(m) {
             // Robust check for Current Cabinet
            if (isLatest) {
                if (m.is_active) return true;
                if (!m.term_end || m.term_end === '0000-00-00') return true;
            }

            const startYear = new Date(m.term_start).getFullYear();
            let endYear = serverYear;
            if (m.term_end && m.term_end !== '0000-00-00') {
                endYear = new Date(m.term_end).getFullYear();
            }
            const sY = parseInt(selectedYear);
            return (sY >= startYear && sY <= endYear);
        }

        filteredData = membersData.filter(filterPrimary);

        // FALLBACK
        if (filteredData.length === 0 && isLatest) {
            filteredData = membersData.filter(m => m.is_active);
        }
        
        filteredData.sort((a,b) => a.sort_order - b.sort_order);

        // Render Tree / Org Chart
        if (filteredData.length === 0) {
            yearContainer.innerHTML = getEmptyState();
            return;
        }

        // Group by Levels
        // Level 1: Sort Order 1 (President)
        // Level 2: Sort Order 2 (VP)
        // Level 3: Sort Order 3-6 (All Secretaries) - User Request: "vc then childs all certrayryes"
        // Level 4: Others (Members)
        
        const levels = { 1: [], 2: [], 3: [], 4: [] };
        
        filteredData.forEach(m => {
            const s = parseInt(m.sort_order);
            if (s === 1) levels[1].push(m);
            else if (s === 2) levels[2].push(m);
            else if (s >= 3 && s <= 6) levels[3].push(m); // All Secretaries together
            else levels[4].push(m);
        });

        let html = '<div class="org-tree">';

        // Helper to render a node (unchanged template)
        const renderNode = (m, rankClass) => {
            const name = (currentLang === 'en') ? m.display_name_en : m.display_name_ur;
            const khail = (currentLang === 'en') ? m.display_khail_en : m.display_khail_ur;
            const role = (currentLang === 'en') ? m.role_en : m.role_ur;
            const displayName = khail ? `<span class='text-primary small d-block mb-1'>[${khail}]</span> ${name}` : name;
            
            const isPresident = (m.role_en.toLowerCase().includes('president'));
            const icon = isPresident ? '<i class="fas fa-crown fa-lg text-warning mb-2"></i>' : '<i class="fas fa-user-tie fa-lg text-secondary mb-2"></i>';
            
            return `
            <div class="org-node ${rankClass}">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body text-center p-2">
                        ${icon}
                        <h6 class="card-title fw-bold mb-1" style="font-size:0.95rem;">${displayName}</h6>
                        <div class="badge bg-success mb-1" style="font-size:0.7rem;">${role}</div>
                        <p class="text-muted small mb-0" style="font-size:0.7rem;">${m.term_start_formatted} - ${m.term_end_formatted}</p>
                    </div>
                </div>
            </div>`;
        };

        // Render Level 1 (President)
        if (levels[1].length > 0) {
            html += '<div class="org-level">';
            levels[1].forEach(m => html += renderNode(m, 'rank-1'));
            html += '</div>';
        }

        // Render Level 2 (VP)
        if (levels[2].length > 0) {
            const multiClass = levels[2].length > 1 ? 'multi-item' : '';
            html += `<div class="org-level ${multiClass}">`;
            levels[2].forEach(m => html += renderNode(m, 'rank-2'));
            html += '</div>';
        }

        // Render Level 3 (All Secretaries)
        if (levels[3].length > 0) {
            const multiClass = levels[3].length > 1 ? 'multi-item' : '';
            html += `<div class="org-level ${multiClass}" style="flex-wrap:wrap;">`;
            levels[3].forEach(m => html += renderNode(m, 'rank-3'));
            html += '</div>';
        }

        // Render Level 4 (Members)
        if (levels[4].length > 0) {
            html += '<div class="org-level multi-item" style="gap:15px; flex-wrap:wrap;">';
            levels[4].forEach(m => html += renderNode(m, 'rank-4'));
            html += '</div>';
        }

        html += '</div>'; // End org-tree
        
        yearContainer.innerHTML = html;

    } else {
        // --- ROLE VIEW ---
        const tbody = document.getElementById('role-table-body');
        const tableCard = document.querySelector('#role-view-container .card');
        const emptyState = document.getElementById('role-empty-state');

        // Title
        const currentRoleObj = rolesList.find(r => r.en === selectedRole);
        const roleLabel = currentRoleObj ? ((currentLang === 'en') ? currentRoleObj.en : currentRoleObj.ur) : selectedRole;
        titleEl.innerText = (currentLang === 'en') ? `History of ${selectedRole}` : `${roleLabel} (تاریخ)`;

        // Filter Data
        filteredData = membersData.filter(m => m.role_en === selectedRole);
        filteredData.sort((a,b) => new Date(b.term_start) - new Date(a.term_start));

        if (filteredData.length === 0) {
            tableCard.style.display = 'none';
            emptyState.style.display = 'block';
            tbody.innerHTML = '';
            return;
        }
        
        tableCard.style.display = 'block';
        emptyState.style.display = 'none';

        let html = '';
        filteredData.forEach(m => {
            const name = (currentLang === 'en') ? m.display_name_en : m.display_name_ur;
            const khail = (currentLang === 'en') ? m.display_khail_en : m.display_khail_ur;
            const khailHtml = khail ? `<span class='text-primary fw-bold'>[${khail}]</span> ` : '';
            const displayName = khailHtml + name;
            
            const statusBadge = m.is_active 
                ? `<span class="badge bg-success">${(currentLang=='en')?'Current':'موجودہ'}</span>`
                : `<span class="badge bg-secondary">${(currentLang=='en')?'Past':'سابقہ'}</span>`;

            html += `
            <tr>
                <td class="fw-bold py-3">${displayName}</td>
                <td>${m.term_start_formatted} - ${m.term_end_formatted}</td>
                <td>${statusBadge}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }
}

function getEmptyState() {
    const msg = (currentLang === 'en') ? "No records found." : "کوئی ریکارڈ موجود نہیں ہے۔";
    return `
    <div class="col-12 text-center py-5">
        <div class="alert alert-light border shadow-sm d-inline-block px-5">
            <i class="fas fa-folder-open fa-2x mb-3 text-muted"></i>
            <p class="text-muted fs-5 mb-0">${msg}</p>
        </div>
    </div>`;
}
</script>

<?php include 'frontend_footer.php'; ?>

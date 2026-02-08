<?php include 'frontend_header.php'; ?>
<?php include 'config.php'; ?>

<!-- Treant.js Dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.css">
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.min.js"></script>

<!-- Page Header -->
<header class="page-header py-5 mb-0" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('logo.jpeg'); background-size: cover; background-position: center;">
    <div class="container text-center" data-aos="zoom-in">
        <h1 class="display-4 fw-bold text-white"><?php echo ($lang == 'en') ? 'Shajra-e-Nasab' : 'شجرہ نسب مبارک'; ?></h1>
        <div class="motto-badge d-inline-block bg-dark text-white border border-white border-opacity-25 px-4 py-2 rounded-pill fw-bold shadow mt-3" style="background: rgba(0,0,0,0.4) !important;">
             <?php echo ($lang == 'en') ? 'Unity • Respect • Management' : 'اتحاد • احترام • انصرام'; ?>
        </div>
    </div>
</header>

<div class="container-fluid py-5 bg-light" style="min-height: 800px; background: #fdfcf3 !important; position: relative;">
    <div class="text-center mb-5">
        <p class="text-muted fst-italic">
            <?php echo ($lang == 'en') ? 'Tracing through the blessed Sadat lineage through centuries from Medina to Kohat.' : 'مدینہ منورہ سے کوہاٹ تک صدیوں پر محیط سادات کا پاکیزہ سلسلہ۔'; ?>
        </p>
    </div>

    <!-- Tree Container -->
    <div id="shajra-tree" style="width: 100%; height: auto; min-height: 1000px; margin: 0 auto; overflow: auto; background: url('https://www.transparenttextures.com/patterns/handmade-paper.png'); padding: 50px;"></div>
</div>

<?php
// Fetch all nodes from database
$stmt = $pdo->query("SELECT * FROM shajra_nodes ORDER BY parent_id ASC, sort_order ASC");
$all_nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build Treant-compatible JSON
function buildTree(array $elements, $parentId = null) {
    $branch = array();
    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = buildTree($elements, $element['id']);
            
            $node_data = [
                'text' => [
                    /* Always Urdu text as per user request */
                    'name' => !empty($element['name_ur']) ? $element['name_ur'] : $element['name_en'],
                    'title' => !empty($element['title_ur']) ? "({$element['title_ur']})" : "",
                    'desc' => !empty($element['khail_ur']) ? $element['khail_ur'] : ""
                ],
                'HTMLclass' => $element['is_main_node'] ? 'main-node' : 'child-node'
            ];

            if ($children) {
                $node_data['children'] = $children;
            }
            $branch[] = $node_data;
        }
    }
    return $branch;
}

$tree_data = buildTree($all_nodes);
?>

<script>
    var tree_structure = {
        chart: {
            container: "#shajra-tree",
            levelSeparation: 40, /* Tightened vertical gap */
            siblingSeparation: 15, /* Minimum horizontal gap for compact row */
            subTeeSeparation: 30,
            nodeAlign: "CENTER",
            connectors: {
                type: "step",
                style: {
                    "stroke-width": 2,
                    "stroke": "#B8860B",
                    "stroke-dasharray": ". ",
                    "arrow-end": "classic-wide-long"
                }
            },
            node: {
                HTMLclass: "shajra-node"
            }
        },
        nodeStructure: <?php echo json_encode($tree_data[0] ?? []); ?>
    };

    document.addEventListener("DOMContentLoaded", function() {
        new Treant(tree_structure);
    });
</script>

<style>
    /* High-Readability Compact Urdu Shajra */
    #shajra-tree {
        border: 12px double #06402B;
        background: #fdfaf1;
        background-image: url('https://www.transparenttextures.com/patterns/old-mathematics.png');
        box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        border-radius: 12px;
        padding: 40px;
        margin: 20px auto;
        max-width: 98%;
        overflow: auto;
        min-height: 800px;
    }

    /* Node Styling - Larger Text, Tighter Cards */
    .shajra-node {
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #D4AF37;
        border-radius: 6px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        min-width: 140px; /* Slightly wider for large Urdu text */
        /* Target specific properties to prevent blinking/flickering */
        transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .shajra-node:hover {
        /* Removed translateY as it causes blinking (hover flutter) */
        box-shadow: 0 8px 24px rgba(184, 134, 11, 0.4);
        border-color: #06402B;
        background-color: #fdfaf1;
    }

    /* Historical Nodes */
    .main-node {
        background: linear-gradient(135deg, #06402B 0%, #0a5d40 100%) !important;
        border: 2px solid #D4AF37 !important;
        color: #fff !important;
        min-width: 160px !important;
        padding: 10px 20px !important;
    }

    .main-node .node-name {
        color: #D4AF37 !important;
        font-size: 1.25rem !important; /* Prominent historical names */
    }

    /* Large Urdu Typography - Easily Viewable */
    .node-name {
        font-weight: bold;
        font-size: 1.15rem; /* Significantly larger for "easy view" */
        display: block;
        color: #1a1a1a;
        line-height: 1.8; /* Increased for Nastaliq descenders */
        font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    }

    .node-title {
        font-size: 0.8rem;
        color: #5d4037;
        font-style: italic;
        display: block;
        margin-top: 1px;
    }

    .main-node .node-title { color: #e0e0e0 !important; }

    /* Branch / Khail Badge */
    .node-desc {
        display: inline-block;
        margin-top: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background: #B8860B;
        padding: 2px 10px;
        border-radius: 30px;
        border: 1px solid #06402B;
    }

    /* Treant Layout Adjustments */
    .Treant .node { margin: auto; }
    
    /* Elegant Gold Scrollbar */
    #shajra-tree::-webkit-scrollbar { width: 10px; height: 10px; }
    #shajra-tree::-webkit-scrollbar-thumb { background: #B8860B; border-radius: 5px; }
    #shajra-tree::-webkit-scrollbar-track { background: #efebe9; }

    @media (max-width: 768px) {
        .shajra-node { min-width: 120px; padding: 5px; }
        .node-name { font-size: 1rem; }
    }
</style>

<?php include 'frontend_footer.php'; ?>

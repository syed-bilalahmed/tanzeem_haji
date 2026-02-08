<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!isset($_POST['id'])) { die("ID Missing"); }
    
    $id = $_POST['id'];
    $date = $_POST['collection_date'];
    
    // Build SQL for Update
    $sql_parts = ["collection_date = ?"];
    $values = [$date];
    
    // Denominations
    $denoms = [5000, 1000, 500, 100, 50, 20, 10];
    $locations = ['darbar', 'andron', 'beron'];
    
    foreach ($locations as $loc) {
        // Collect Manual Total
        $total_key = $loc . '_total';
        $manual_total = isset($_POST[$total_key]) && $_POST[$total_key] !== '' ? $_POST[$total_key] : 0;
        $sql_parts[] = "$total_key = ?";
        $values[] = $manual_total;

        foreach ($denoms as $d) {
            $key = $loc . '_' . $d;
            $val = isset($_POST[$key]) && $_POST[$key] !== '' ? $_POST[$key] : 0;
            
            $sql_parts[] = "$key = ?";
            $values[] = $val;
        }
    }
    
    // Officials
    $officials = ['naib_saddar', 'general_secretary', 'joint_secretary', 'information_secretary'];
    foreach ($officials as $off) {
        $sql_parts[] = "$off = ?";
        $values[] = $_POST[$off] ?? '';
    }
    
    // Add ID to values for WHERE clause
    $values[] = $id;
    
    $sql = "UPDATE collections SET " . implode(', ', $sql_parts) . " WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        header("Location: collections.php?msg=updated");
        exit;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

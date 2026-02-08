<?php
include 'config.php';

try {
    // Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM notices LIKE 'lang'");
    if($check->rowCount() == 0) {
        // Add column
        $sql = "ALTER TABLE notices ADD COLUMN lang VARCHAR(10) DEFAULT 'ur' AFTER notice_date";
        $pdo->exec($sql);
        echo "Successfully added 'lang' column to notices table.";
    } else {
        echo "Column 'lang' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

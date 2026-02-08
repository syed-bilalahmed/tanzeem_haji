<?php
include 'config.php';

$alters = [
    "ALTER TABLE collections ADD COLUMN IF NOT EXISTS darbar_total INT DEFAULT 0",
    "ALTER TABLE collections ADD COLUMN IF NOT EXISTS andron_total INT DEFAULT 0",
    "ALTER TABLE collections ADD COLUMN IF NOT EXISTS beron_total INT DEFAULT 0"
];

foreach ($alters as $sql) {
    try {
        $pdo->exec($sql);
        echo "Executed: $sql <br>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
echo "Database schema updated successfully.";
?>

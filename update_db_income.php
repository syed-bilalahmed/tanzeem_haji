<?php
require_once 'config.php';

try {
    // Add fund columns to incomes table if they don't exist
    $columns = [
        'dargah_fund' => "DECIMAL(10, 2) DEFAULT 0",
        'qabristan_fund' => "DECIMAL(10, 2) DEFAULT 0",
        'masjid_fund' => "DECIMAL(10, 2) DEFAULT 0",
        'urs_fund' => "DECIMAL(10, 2) DEFAULT 0"
    ];

    foreach ($columns as $column => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM incomes LIKE '$column'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE incomes ADD COLUMN $column $type AFTER amount");
            echo "Added column $column.<br>";
        }
    }

    // Add is_fixed column
    $check = $pdo->query("SHOW COLUMNS FROM incomes LIKE 'is_fixed'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE incomes ADD COLUMN is_fixed TINYINT DEFAULT 0");
        echo "Added column is_fixed.<br>";
    }

    echo "Database update complete. <a href='income.php'>Go back to Income</a>";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

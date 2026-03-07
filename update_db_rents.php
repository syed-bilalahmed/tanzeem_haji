<?php
include 'config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS renters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            shop_no VARCHAR(50) DEFAULT '',
            shop_name VARCHAR(255) DEFAULT '',
            shopkeeper_name VARCHAR(255) DEFAULT '',
            monthly_rent DECIMAL(10,2) DEFAULT 0,
            phone VARCHAR(50) DEFAULT '',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rent_collections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            renter_id INT NOT NULL,
            receipt_date DATE NOT NULL,
            receipt_no VARCHAR(100) DEFAULT '',
            month_from VARCHAR(100) NOT NULL,
            month_to VARCHAR(100) DEFAULT '',
            monthly_rent DECIMAL(10,2) DEFAULT 0,
            arrears DECIMAL(10,2) DEFAULT 0,
            total_amount DECIMAL(10,2) DEFAULT 0,
            amount_received DECIMAL(10,2) DEFAULT 0,
            remaining_balance DECIMAL(10,2) DEFAULT 0,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (renter_id) REFERENCES renters(id) ON DELETE CASCADE
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
    ");
    
    echo "Tables 'renters' and 'rent_collections' created successfully.";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

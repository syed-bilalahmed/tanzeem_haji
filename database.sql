CREATE DATABASE IF NOT EXISTS tanzeem_db;
USE tanzeem_db;

CREATE TABLE IF NOT EXISTS collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_date DATE NOT NULL,
    -- Darbar
    darbar_5000 INT DEFAULT 0, darbar_1000 INT DEFAULT 0, darbar_500 INT DEFAULT 0,
    darbar_100 INT DEFAULT 0, darbar_50 INT DEFAULT 0, darbar_20 INT DEFAULT 0, darbar_10 INT DEFAULT 0,
    darbar_total INT DEFAULT 0,
    -- Masjid Andron
    andron_5000 INT DEFAULT 0, andron_1000 INT DEFAULT 0, andron_500 INT DEFAULT 0,
    andron_100 INT DEFAULT 0, andron_50 INT DEFAULT 0, andron_20 INT DEFAULT 0, andron_10 INT DEFAULT 0,
    andron_total INT DEFAULT 0,
    -- Masjid Beron
    beron_5000 INT DEFAULT 0, beron_1000 INT DEFAULT 0, beron_500 INT DEFAULT 0,
    beron_100 INT DEFAULT 0, beron_50 INT DEFAULT 0, beron_20 INT DEFAULT 0, beron_10 INT DEFAULT 0,
    beron_total INT DEFAULT 0,
    -- Officials
    naib_saddar VARCHAR(255) DEFAULT '',
    general_secretary VARCHAR(255) DEFAULT '',
    joint_secretary VARCHAR(255) DEFAULT '',
    information_secretary VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS incomes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    income_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

<?php
$host = getenv('DB_HOST') ?: 'localhost';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbname = getenv('DB_NAME') ?: 'tanzeem_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");

    // Auto-Setup Check (For Plug & Play)
    // We check if 'users' table exists. If not, we assume it's a fresh install.
    try {
        $check = $pdo->query("SELECT 1 FROM users LIMIT 1");
    } catch (PDOException $e) {
        // Table doesn't exist, run setup scripts
        include 'setup_tables.php'; // Creates tables + admin user
        include 'setup_settings.php'; // Creates settings + default content
    }

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<?php
include 'config.php';
include 'auth_session.php'; // Ensure user is logged in

// Check if admin/authorized user
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

if (!isset($_GET['id'])) {
    die("Invalid request - ID not found.");
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM rent_collections WHERE id = ?");
    $stmt->execute([$id]);
    
    // Redirect back to rents detail
    header("Location: rents_detail.php?tab=monthly&msg=deleted");
    exit;
} catch (PDOException $e) {
    die("Error deleting receipt: " . $e->getMessage());
}
?>

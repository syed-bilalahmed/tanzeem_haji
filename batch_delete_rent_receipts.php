<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_session.php';

if (!has_permission('salaries_edit') || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
    $ids = $_POST['ids']; // Expecting an array of IDs
    
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        try {
            $stmt = $pdo->prepare("DELETE FROM rent_collections WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            header("Location: rents_detail.php?tab=monthly&msg=batch_deleted");
            exit;
        } catch (PDOException $e) {
            die("Error deleting receipts: " . $e->getMessage());
        }
    }
}
header("Location: rents_detail.php?tab=monthly");
exit;
?>

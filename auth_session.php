<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Dynamically refresh permissions to ensure instant updates
global $pdo;
if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT role, permissions FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['role'] = $u['role'];
        $_SESSION['permissions'] = $u['permissions'];
    } else {
        // User was deleted
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

require_once 'access_helper.php';
?>

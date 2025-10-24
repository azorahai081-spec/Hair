<?php
require_once 'includes/auth-check.php'; // $csrf_token and functions are here
require_once '../db-config.php';

// --- CSRF Protection & Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?error=invalid_method');
    exit;
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token.');
}
// --- End CSRF Protection ---

// --- Permission Check: Only SuperAdmins can delete orders ---
if (!is_superadmin()) {
    header('Location: index.php?error=permission_denied');
    exit;
}

$order_id = $_POST['id'] ?? null;

if ($order_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
    } catch (PDOException $e) {
        error_log("Error deleting order ID $order_id: " . $e->getMessage());
        header('Location: index.php?error=db_delete');
        exit;
    }
}

header('Location: index.php?success=deleted');
exit;


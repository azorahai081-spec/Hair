<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// --- UPDATED PERMISSION CHECK ---
if (!can_change_status()) {
    header('Location: index.php?error=permission_denied');
    exit;
}
// --- END UPDATED CHECK ---

require_once '../db-config.php';

// --- CSRF Protection & Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token.');
}
// --- End CSRF Protection ---

$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;
$allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

if ($order_id && $status && in_array($status, $allowed_statuses)) {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
    } catch (PDOException $e) {
        error_log("Error updating status for order ID $order_id: " . $e->getMessage());
        header('Location: index.php?error=db_status_update');
        exit;
    }
} else {
     header('Location: index.php?error=invalid_status_data');
     exit;
}

header('Location: index.php?success=status_updated');
exit;


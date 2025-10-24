<?php
require_once 'includes/auth-check.php'; // $csrf_token and functions are here
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

// Security checks: User must be a superadmin
if (!is_superadmin()) {
    header('Location: index.php?error=permission_denied');
    exit;
}

$order_ids = $_POST['order_ids'] ?? [];

if (!empty($order_ids) && is_array($order_ids)) {
    $sanitized_ids = array_filter($order_ids, fn($id) => filter_var($id, FILTER_VALIDATE_INT));

    if (!empty($sanitized_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
            $sql = "DELETE FROM orders WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($sanitized_ids);
            header('Location: index.php?success=bulk_deleted');
            exit;

        } catch (PDOException $e) {
            error_log("Error bulk deleting orders: " . $e->getMessage());
            header('Location: index.php?error=db_bulk_delete');
            exit;
        }
    }
}

header('Location: index.php?error=invalid_bulk_delete_data');
exit;


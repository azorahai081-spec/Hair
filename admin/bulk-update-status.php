<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here
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

// --- UPDATED PERMISSION CHECK ---
if (!can_change_status()) {
    header('Location: index.php?error=permission_denied');
    exit;
}
// --- END UPDATED CHECK ---

$order_ids = $_POST['order_ids'] ?? [];
$bulk_status = $_POST['bulk_status'] ?? '';
$status_options = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

// Validate inputs
if (!empty($order_ids) && is_array($order_ids) && in_array($bulk_status, $status_options)) {
    // Sanitize IDs (ensure they are integers)
    $sanitized_ids = array_filter($order_ids, fn($id) => filter_var($id, FILTER_VALIDATE_INT));

    if(!empty($sanitized_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
            $sql = "UPDATE orders SET status = ? WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$bulk_status], $sanitized_ids);
            $stmt->execute($params);
             header('Location: index.php?success=bulk_status_updated');
             exit;

        } catch (PDOException $e) {
            error_log("Error bulk updating status: " . $e->getMessage());
            header('Location: index.php?error=db_bulk_status_update');
            exit;
        }
    }
}

header('Location: index.php?error=invalid_bulk_data');
exit;


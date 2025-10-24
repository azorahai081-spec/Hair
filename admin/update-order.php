<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// --- UPDATED PERMISSION CHECK ---
if (!can_edit_orders()) {
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

// Get data from the form
$order_id = $_POST['order_id'];
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$total_price = $_POST['total_price'] ?? 0;
$status = $_POST['status'] ?? 'Pending';

// Basic validation
if (empty($order_id) || empty($customer_name) || empty($customer_phone) || empty($customer_address) || !is_numeric($total_price) || $total_price < 0) {
    header('Location: edit-order.php?id=' . $order_id . '&error=invalid_data');
    exit;
}
if (!in_array($status, ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])) {
     header('Location: edit-order.php?id=' . $order_id . '&error=invalid_status');
    exit;
}

// Update the order in the database
try {
    $sql = "UPDATE orders SET customer_name = ?, customer_phone = ?, customer_address = ?, total_price = ?, status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_name, $customer_phone, $customer_address, $total_price, $status, $order_id]);
} catch (PDOException $e) {
    error_log("Error updating order ID $order_id: " . $e->getMessage());
    header('Location: index.php?error=db_update');
    exit;
}

header('Location: index.php?success=updated');
exit;


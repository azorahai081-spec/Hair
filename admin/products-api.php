<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions

// This API is for superadmins only
if (!is_superadmin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
    exit;
}

require_once '../db-config.php';
header('Content-Type: application/json');

function send_json($data) {
    echo json_encode($data);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_REQUEST['action'] ?? null;

    if ($method === 'GET' && $action === 'get_single' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch();
        send_json($product);
    }

    if ($method !== 'POST') {
        http_response_code(405);
        send_json(['status' => 'error', 'message' => 'Method not allowed.']);
    }

    // CSRF Token Validation for all POST requests
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
         http_response_code(403);
         send_json(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    }

    // Sanitize common fields
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $product_key = trim($_POST['product_key'] ?? '');
    $price = $_POST['price'] ?? 0;
    $original_price = $_POST['original_price'] ?? null;
    $image = trim($_POST['image'] ?? 'haircode.webp');
    $is_active = $_POST['is_active'] ?? 1;
    $sort_order = $_POST['sort_order'] ?? 0;
    
    switch ($action) {
        case 'create':
            if (empty($name) || empty($product_key) || $price <= 0) {
                send_json(['status' => 'error', 'message' => 'Name, Key, and Price are required.']);
            }
            $stmt = $pdo->prepare("INSERT INTO products (name, product_key, price, original_price, image, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $product_key, $price, $original_price, $image, $is_active, $sort_order]);
            send_json(['status' => 'success', 'message' => 'Product created successfully.']);
            break;

        case 'update':
            if (empty($id) || empty($name) || empty($product_key) || $price <= 0) {
                send_json(['status' => 'error', 'message' => 'ID, Name, Key, and Price are required.']);
            }
            $stmt = $pdo->prepare("UPDATE products SET name = ?, product_key = ?, price = ?, original_price = ?, image = ?, is_active = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $product_key, $price, $original_price, $image, $is_active, $sort_order, $id]);
            send_json(['status' => 'success', 'message' => 'Product updated successfully.']);
            break;

        case 'delete':
            if (empty($id)) {
                send_json(['status' => 'error', 'message' => 'Product ID is required.']);
            }
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            send_json(['status' => 'success', 'message' => 'Product deleted successfully.']);
            break;

        default:
            http_response_code(400);
            send_json(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate key)
        send_json(['status' => 'error', 'message' => 'That "Product Key" is already in use. It must be unique.']);
    } else {
        send_json(['status' => 'error', 'message' => 'Database operation failed: ' . $e->getMessage()]);
    }
}
?>

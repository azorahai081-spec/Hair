<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// This API is for superadmins only
if (!is_superadmin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
    exit;
}

// --- FIX: Add CSRF Protection Check ---
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    exit;
}
// --- End CSRF Protection ---

require_once '../db-config.php';

header('Content-Type: application/json');

function send_json($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['status' => 'error', 'message' => 'Invalid request method.']);
}

$action = $_POST['action'] ?? null;
$id = $_POST['id'] ?? null;
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;
$role = $_POST['role'] ?? null;

// --- FIX: Add validation for your new roles ---
$allowed_roles = ['viewer', 'status_updater', 'order_manager', 'superadmin'];

try {
    switch ($action) {
        case 'create':
            if (empty($username) || empty($password) || empty($role)) {
                send_json(['status' => 'error', 'message' => 'All fields are required.']);
            }
            if (!in_array($role, $allowed_roles)) {
                 send_json(['status' => 'error', 'message' => 'Invalid role selected.']);
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password_hash, $role]);
            send_json(['status' => 'success', 'message' => 'User created successfully.']);
            break;

        case 'update':
            if (empty($id) || empty($username) || empty($role)) {
                send_json(['status' => 'error', 'message' => 'Missing required fields for update.']);
            }
            if (!in_array($role, $allowed_roles)) {
                 send_json(['status' => 'error', 'message' => 'Invalid role selected.']);
            }
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password_hash = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $password_hash, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $id]);
            }
            send_json(['status' => 'success', 'message' => 'User updated successfully.']);
            break;

        case 'delete':
            if (empty($id)) {
                send_json(['status' => 'error', 'message' => 'User ID is required.']);
            }
            if ($id == $_SESSION['user_id']) {
                 send_json(['status' => 'error', 'message' => 'You cannot delete your own account.']);
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            send_json(['status' => 'success', 'message' => 'User deleted successfully.']);
            break;

        default:
            send_json(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate username)
        send_json(['status' => 'error', 'message' => 'Username already exists.']);
    }
    send_json(['status' => 'error', 'message' => 'A database error occurred.']);
}


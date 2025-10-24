<?php
session_start();
require_once '../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Username and password are required.';
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, start the session
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role']; // Store the role name

        // --- NEW PERMISSION SYSTEM ---
        // Create a list of permissions based on the role
        $permissions = [];
        $role = $user['role'];

        if ($role === 'superadmin') {
            $permissions['all'] = true; // Wildcard for all permissions
        }
        
        // This role can edit orders and manage reviews (but not delete reviews)
        if ($role === 'order_manager') {
            $permissions['can_edit_orders'] = true;
            $permissions['can_change_status'] = true;
            $permissions['can_manage_reviews'] = true;
            $permissions['can_delete_reviews'] = false; 
        }
        
        // This role can ONLY change order statuses
        if ($role === 'status_updater') {
            $permissions['can_change_status'] = true;
        }

        // 'viewer' gets no specific permissions, can only look
        
        $_SESSION['permissions'] = $permissions;
        // --- END NEW SYSTEM ---
        
        header('Location: dashboard.php');
        exit;
    } else {
        // Invalid credentials
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: login.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['login_error'] = 'A database error occurred. Please try again later.';
    header('Location: login.php');
    exit;
}


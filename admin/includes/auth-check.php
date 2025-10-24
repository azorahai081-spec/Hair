<?php
// --- CSRF Protection & Session Start ---
session_set_cookie_params([
    'httponly' => true,  // Prevents JavaScript from reading the cookie
    'secure' => true,    // Only sends the cookie over HTTPS (Requires SSL)
    'samesite' => 'Lax' // Mitigates most CSRF attacks
]);

session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


// --- NEW PERMISSION SYSTEM ---

/**
 * The new, primary permission checker.
 * Reads the $_SESSION['permissions'] array set by auth.php
 */
function can($permission) {
    // Superadmin (with 'all' permission) gets a wildcard
    if (isset($_SESSION['permissions']['all']) && $_SESSION['permissions']['all'] === true) {
        return true;
    }
    // Check for a specific permission
    return isset($_SESSION['permissions'][$permission]) && $_SESSION['permissions'][$permission] === true;
}

/**
 * Helper function for superadmin, used in manage-users.php
 * This checks the 'all' permission.
 */
function is_superadmin() {
    return can('all');
}

/**
 * Checks if a user can edit order details.
 */
function can_edit_orders() {
    return can('can_edit_orders');
}

/**
 * Checks if a user can change order status.
 */
function can_change_status() {
    return can('can_change_status');
}

/**
 * Checks if a user can add/edit/feature reviews.
 */
function can_manage_reviews() {
    return can('can_manage_reviews');
}

/**
 * Checks if a user can delete reviews.
 */
function can_delete_reviews() {
    return can('can_delete_reviews');
}


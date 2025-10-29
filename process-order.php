<?php
// Start the session to manage redirects and error messages.
session_start();
require_once 'db-config.php';

// --- Security: Only allow POST requests ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If accessed directly, redirect to the homepage.
    header('Location: index.php');
    exit;
}

// --- Get and Sanitize Form Data ---
$name = trim($_POST['customer_name'] ?? '');
$address = trim($_POST['customer_address'] ?? '');
$phone = trim($_POST['customer_phone'] ?? '');
$shipping_cost_value = $_POST['shipping_cost'] ?? '60'; // Default to Dhaka shipping cost if not set

// --- Get selected product details ---
$selected_product_name = trim($_POST['selected_product_name'] ?? '');
$selected_product_price = filter_var($_POST['selected_product_price'] ?? 0, FILTER_VALIDATE_FLOAT); // Get price from hidden input

// --- Server-Side Validation ---
$errors = [];
if (empty($name) || strlen($name) < 3) {
    $errors[] = "Please enter a valid name (at least 3 characters).";
}
if (empty($address) || strlen($address) < 5) {
    $errors[] = "Please enter a valid address (at least 5 characters).";
}
// This regex checks for a valid Bangladeshi phone number format.
if (!preg_match('/^(\+8801|01)[3-9]\d{8}$/', $phone)) {
    $errors[] = "Please enter a valid 11-digit Bangladeshi mobile number.";
}
// Validate selected product
if (empty($selected_product_name)) {
    $errors[] = "Please select a product option.";
}
if ($selected_product_price === false || $selected_product_price <= 0) {
    $errors[] = "Invalid product price selected.";
}
// Validate shipping cost
if (!in_array($shipping_cost_value, ['60', '100'])) {
    $errors[] = "Invalid shipping option selected.";
}


// --- Handle Validation Errors ---
if (!empty($errors)) {
    // If there are errors, store the first error in the session.
    $_SESSION['error_message'] = $errors[0];
    // Redirect back to the form page to display the error.
    header('Location: index.php#order-form');
    exit;
}

// --- If Validation Passes, Process the Order ---

// Determine shipping location text and cost based on the value.
$shipping_cost = intval($shipping_cost_value);
$shipping_location = ($shipping_cost === 100) ? ' ঢাকার বাইরে:' : ' ঢাকার মধ্যে:';

// --- Server-side recalculation of total price ---
$total_price = $selected_product_price + $shipping_cost;

// --- Set the current date and time ---
$current_date = date('Y-m-d H:i:s');

try {
    // Begin a transaction for safety.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO orders (customer_name, customer_address, customer_phone, product_name, shipping_location, shipping_cost, total_price, order_number, order_date, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
    );
    
    // Pass a temporary order number, which we will update next.
    // Use the selected product name and recalculated total price
    $stmt->execute([$name, $address, $phone, $selected_product_name, $shipping_location, $shipping_cost, $total_price, 'TEMP', $current_date]);
    
    // Get the unique ID of the order we just inserted.
    $last_insert_id = $pdo->lastInsertId();
    
    // Create the final, unique order number.
    $order_number_prefix = date('Ym'); 
    $final_order_number = $order_number_prefix . '-' . $last_insert_id;
    
    // Update the order with the final order number.
    $update_stmt = $pdo->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
    $update_stmt->execute([$final_order_number, $last_insert_id]);
    
    // Commit the transaction to finalize the changes.
    $pdo->commit();

} catch (PDOException $e) {
    // If anything goes wrong, roll back the transaction.
    $pdo->rollBack();
    // For a real app, you would log this error.
    $_SESSION['error_message'] = "A database error occurred. Please try again.";
    error_log("Order processing error: " . $e->getMessage()); // Log the actual error
    header('Location: index.php#order-form');
    exit;
}

// --- Redirect to Thank You Page on Success ---
if ($last_insert_id > 0) {
    $_SESSION['last_order_id'] = $last_insert_id;
    header('Location: thank-you.php');
    exit;
} else {
    // Fallback error if something went wrong after commit (unlikely but possible).
    $_SESSION['error_message'] = "Could not save the order. Please try again.";
    header('Location: index.php#order-form');
    exit;
}

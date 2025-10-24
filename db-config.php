<?php
// Set the default timezone to ensure date and time are handled correctly.
date_default_timezone_set('Asia/Dhaka');

// --- Database Credentials ---
// Replace with your actual database details.
$db_host = 'localhost';
$db_name = 'feg_orders';
$db_user = 'root';
$db_pass = ''; // Use your database password here. For XAMPP/WAMP, it might be empty.
$charset = 'utf8mb4';

// --- Data Source Name (DSN) ---
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// --- PDO Connection Options ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays.
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements.
];

// --- Create a PDO instance (connect to the database) ---
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // If connection fails, stop the script and show an error.
    // In a real production environment, you would log this error instead of showing it to the user.
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}


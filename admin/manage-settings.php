<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// This page is for superadmins only
if (!is_superadmin()) {
    header('Location: dashboard.php?error=access_denied');
    exit;
}
require_once '../db-config.php';

$current_page = basename($_SERVER['PHP_SELF']); // Get current page name

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid CSRF token. Please try again.';
    } else {
        // Get and sanitize data
        $shipping_dhaka = filter_var(trim($_POST['shipping_dhaka']), FILTER_SANITIZE_NUMBER_INT);
        $shipping_outside = filter_var(trim($_POST['shipping_outside']), FILTER_SANITIZE_NUMBER_INT);

        if ($shipping_dhaka > 0 && $shipping_outside > 0) {
            try {
                $pdo->beginTransaction();
                
                $stmt_dhaka = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'shipping_dhaka'");
                $stmt_dhaka->execute([$shipping_dhaka]);
                
                $stmt_outside = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'shipping_outside'");
                $stmt_outside->execute([$shipping_outside]);
                
                $pdo->commit();
                $success_message = 'Settings updated successfully!';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = 'Failed to update settings: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Please enter valid, positive numbers for shipping costs.';
        }
    }
}

// Fetch current settings to display in the form
try {
    $settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings_raw = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $settings = [
        'shipping_dhaka' => $settings_raw['shipping_dhaka'] ?? 60,
        'shipping_outside' => $settings_raw['shipping_outside'] ?? 100,
    ];
} catch (PDOException $e) {
    $error_message = 'Failed to load settings: ' . $e->getMessage();
    $settings = ['shipping_dhaka' => 60, 'shipping_outside' => 100]; // Fallback
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="font-bold text-xl text-gray-800">Admin Panel</span>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex md:items-center md:space-x-2">
                    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == 'edit-order.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    <?php if (can_manage_reviews()): ?>
                        <a href="manage-reviews.php" class="<?php echo ($current_page == 'manage-reviews.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Manage Reviews</a>
                    <?php endif; ?>
                    <?php if (is_superadmin()): ?>
                        <a href="manage-products.php" class="<?php echo ($current_page == 'manage-products.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Manage Products</a>
                        <a href="manage-settings.php" class="<?php echo ($current_page == 'manage-settings.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                        <a href="manage-users.php" class="<?php echo ($current_page == 'manage-users.php') ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'; ?> px-3 py-2 rounded-md text-sm font-medium">Manage Users</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == 'edit-order.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Manage Orders</a>
                <?php if (can_manage_reviews()): ?>
                    <a href="manage-reviews.php" class="<?php echo ($current_page == 'manage-reviews.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Manage Reviews</a>
                <?php endif; ?>
                <?php if (is_superadmin()): ?>
                    <a href="manage-products.php" class="<?php echo ($current_page == 'manage-products.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Manage Products</a>
                    <a href="manage-settings.php" class="<?php echo ($current_page == 'manage-settings.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <a href="manage-users.php" class="<?php echo ($current_page == 'manage-users.php') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Manage Users</a>
                <?php endif; ?>
                <a href="logout.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
            </div>
        </div>
    </nav>
    <!-- End Navigation -->

    <main class="max-w-4xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Manage Site Settings</h1>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <form action="manage-settings.php" method="POST" class="bg-white p-8 rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-3">Shipping Costs</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="shipping_dhaka" class="block text-sm font-medium text-gray-700">Shipping (Dhaka)</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">৳</span>
                        <input type="number" id="shipping_dhaka" name="shipping_dhaka" value="<?php echo htmlspecialchars($settings['shipping_dhaka']); ?>" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                 <div>
                    <label for="shipping_outside" class="block text-sm font-medium text-gray-700">Shipping (Outside Dhaka)</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">৳</span>
                        <input type="number" id="shipping_outside" name="shipping_outside" value="<?php echo htmlspecialchars($settings['shipping_outside']); ?>" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700">Save Settings</button>
            </div>
        </form>
    </main>
    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            var menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>

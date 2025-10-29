<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// --- UPDATED PERMISSION CHECK ---
if (!can_edit_orders()) {
    header('Location: index.php?error=permission_denied');
    exit;
}
// --- END UPDATED CHECK ---

require_once '../db-config.php';

$order_id = $_GET['id'] ?? null;
if (!$order_id || !filter_var($order_id, FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit;
}

// Fetch the order from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {
        header('Location: index.php?error=not_found');
        exit;
    }
} catch (PDOException $e) {
     error_log("Error fetching order ID $order_id: " . $e->getMessage());
     die("A database error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
     <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="flex items-center justify-between h-16">
                 <div class="flex items-center">
                    <span class="font-bold text-xl text-gray-800">Admin Panel</span>
                </div>
                <div>
                    <!-- UPDATED NAV -->
                    <a href="dashboard.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <a href="index.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    <?php if (can_manage_reviews()): ?>
                        <a href="manage-reviews.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Reviews</a>
                    <?php endif; ?>
                    <?php if (is_superadmin()): ?>
                        <a href="manage-products.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Products</a>
                        <a href="manage-settings.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                        <a href="manage-users.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Users</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-4xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>

        <form action="update-order.php" method="POST" class="bg-white p-8 rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order['customer_name']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                 <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                    <input type="text" id="customer_phone" name="customer_phone" value="<?php echo htmlspecialchars($order['customer_phone']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                <div class="md:col-span-2">
                    <label for="customer_address" class="block text-sm font-medium text-gray-700">Customer Address</label>
                    <textarea id="customer_address" name="customer_address" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"><?php echo htmlspecialchars($order['customer_address']); ?></textarea>
                </div>
                <div>
                    <label for="total_price" class="block text-sm font-medium text-gray-700">Total Price</label>
                    <input type="number" step="0.01" id="total_price" name="total_price" value="<?php echo htmlspecialchars($order['total_price']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                     <select id="status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">
                        <?php foreach(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $option): ?>
                            <option value="<?php echo $option; ?>" <?php echo ($order['status'] == $option) ? 'selected' : ''; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="index.php" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </main>
</body>
</html>


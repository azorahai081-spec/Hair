<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here
require_once '../db-config.php';

// --- Search and Filtering Logic ---
$search_term = $_GET['search'] ?? '';
$sql = "SELECT * FROM orders";
$params = [];
if (!empty($search_term)) {
    $sql .= " WHERE customer_name LIKE ? OR customer_phone LIKE ? OR order_number LIKE ? OR customer_address LIKE ?";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}
$sql .= " ORDER BY id DESC";

// --- Fetch Orders ---
$orders = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Status options for the dropdown
$status_options = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
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
                    <a href="dashboard.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <a href="index.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    
                    <!-- UPDATED: Use can_manage_reviews() -->
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

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="md:flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Orders</h1>
                <form method="GET" action="index.php" class="mt-4 md:mt-0 flex items-center">
                    <input type="text" name="search" placeholder="Search orders..."
                           class="border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md ml-2">Search</button>
                </form>
            </div>

            <!-- Bulk Action Form -->
            <form id="bulk-action-form" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- UPDATED: Show if user can change status OR is superadmin (for delete) -->
                <?php if (can_change_status() || is_superadmin()): ?>
                <div class="bg-gray-50 p-3 mb-4 rounded-md border flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700">With selected:</span>
                    
                    <!-- UPDATED: Only show if user can change status -->
                    <?php if (can_change_status()): ?>
                        <select id="bulk-status-select" name="bulk_status" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Change status to...</option>
                            <?php foreach($status_options as $option): ?>
                                <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="apply-status-btn" class="bg-indigo-600 text-white py-1 px-3 rounded-md text-sm font-semibold hover:bg-indigo-700">Apply</button>
                    <?php endif; ?>

                    <?php if (is_superadmin()): // Only superadmins can bulk delete ?>
                        <button type="button" id="bulk-delete-btn" class="bg-red-600 text-white py-1 px-3 rounded-md text-sm font-semibold hover:bg-red-700">Delete Selected</button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <?php if (isset($error_message)): ?>
                            <div class="p-4 bg-red-100 text-red-700"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php elseif (empty($orders)): ?>
                            <div class="p-6 text-center text-gray-500">No orders found.</div>
                        <?php else: ?>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <!-- UPDATED: Show if user can change status OR is superadmin -->
                                        <?php if (can_change_status() || is_superadmin()): ?>
                                        <th class="px-6 py-3 text-left">
                                            <input type="checkbox" id="select-all" class="rounded">
                                        </th>
                                        <?php endif; ?>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shipping Address</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <!-- UPDATED: Show if user can change status OR is superadmin -->
                                            <?php if (can_change_status() || is_superadmin()): ?>
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" class="order-checkbox rounded">
                                            </td>
                                            <?php endif; ?>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars((new DateTime($order['order_date']))->format("d M, Y")); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div class="text-gray-500"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_address']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($order['product_name']); ?></td>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">৳ <?php echo htmlspecialchars($order['total_price']); ?></td>
                                            <td class="px-6 py-4 text-sm">
                                                <!-- UPDATED: Use can_change_status() -->
                                                <?php if (can_change_status()): ?>
                                                <select class="single-status-select rounded-md border-gray-300 shadow-sm text-sm" data-order-id="<?php echo $order['id']; ?>">
                                                    <?php foreach($status_options as $option): ?>
                                                        <option value="<?php echo $option; ?>" <?php echo ($order['status'] == $option) ? 'selected' : ''; ?>>
                                                            <?php echo $option; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php else: // For Viewer role, just display the status ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        <?php
                                                            switch ($order['status']) {
                                                                case 'Pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                                case 'Processing': echo 'bg-blue-100 text-blue-800'; break;
                                                                case 'Shipped': echo 'bg-purple-100 text-purple-800'; break;
                                                                case 'Delivered': echo 'bg-green-100 text-green-800'; break;
                                                                case 'Cancelled': echo 'bg-red-100 text-red-800'; break;
                                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                                            }
                                                        ?>
                                                    ">
                                                        <?php echo htmlspecialchars($order['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                <!-- UPDATED: Use can_edit_orders() -->
                                                <?php if (can_edit_orders()): ?>
                                                    <a href="edit-order.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                                                <?php endif; ?>
                                                <?php if (is_superadmin()): // Only superadmins can delete ?>
                                                    <a href="#" class="text-red-600 hover:text-red-900" 
                                                       onclick="handleDelete(event, <?php echo $order['id']; ?>)">Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </form> <!-- End Bulk Action Form -->
        </div>
    </main>

    <!-- Hidden form for single status updates -->
    <form id="single-status-form" action="update-status.php" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="order_id" id="single-order-id">
        <input type="hidden" name="status" id="single-status-value">
    </form>

    <script>
        const CSRF_TOKEN = '<?php echo $csrf_token; ?>';

        function handleDelete(event, orderId) {
            event.preventDefault(); 
            if (confirm('Are you sure you want to delete this order?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-order.php';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = orderId;
                form.appendChild(idInput);

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = CSRF_TOKEN;
                form.appendChild(tokenInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // --- UPDATED JAVASCRIPT PERMISSIONS ---
            const userCanChangeStatus = <?php echo can_change_status() ? 'true' : 'false'; ?>;
            const userIsSuperAdmin = <?php echo is_superadmin() ? 'true' : 'false'; ?>;

            // Logic for users who can change status OR delete (for checkboxes)
            if (userCanChangeStatus || userIsSuperAdmin) {
                const selectAllCheckbox = document.getElementById('select-all');
                const bulkForm = document.getElementById('bulk-action-form');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function(e) {
                        const checkboxes = bulkForm.querySelectorAll('.order-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = e.target.checked;
                        });
                    });
                }
            }

            // Logic *only* for users who can change status
            if (userCanChangeStatus) {
                const bulkForm = document.getElementById('bulk-action-form');
                const statusBtn = document.getElementById('apply-status-btn');
                const statusSelect = document.getElementById('bulk-status-select');
                
                if (statusBtn && statusSelect) {
                    statusBtn.addEventListener('click', () => {
                        const selectedStatus = statusSelect.value;
                        const checkedBoxes = bulkForm.querySelectorAll('.order-checkbox:checked').length;

                        if (!selectedStatus) {
                            alert('Please select a status to apply.');
                            return;
                        }
                        if (checkedBoxes === 0) {
                            alert('Please select at least one order to update.');
                            return;
                        }
                        bulkForm.action = 'bulk-update-status.php';
                        bulkForm.submit();
                    });
                }

                // Single Status Update Logic
                const singleStatusForm = document.getElementById('single-status-form');
                const singleOrderIdInput = document.getElementById('single-order-id');
                const singleStatusValueInput = document.getElementById('single-status-value');

                if (singleStatusForm && singleOrderIdInput && singleStatusValueInput) {
                    document.querySelectorAll('.single-status-select').forEach(select => {
                        select.addEventListener('change', (e) => {
                            const orderId = e.target.dataset.orderId;
                            const newStatus = e.target.value;
                            singleOrderIdInput.value = orderId;
                            singleStatusValueInput.value = newStatus;
                            singleStatusForm.submit();
                        });
                    });
                }
            } // end userCanChangeStatus check

            // Logic *only* for super admins (bulk delete)
            if (userIsSuperAdmin) {
                const bulkForm = document.getElementById('bulk-action-form');
                const deleteBtn = document.getElementById('bulk-delete-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', () => {
                        const checkedBoxes = bulkForm.querySelectorAll('.order-checkbox:checked').length;
                        if (checkedBoxes > 0) {
                            if (confirm(`Are you sure you want to delete ${checkedBoxes} selected order(s)?`)) {
                                bulkForm.action = 'bulk-delete.php';
                                bulkForm.submit();
                            }
                        } else {
                            alert('Please select at least one order to delete.');
                        }
                    });
                }
            } // end userIsSuperAdmin check
        });
    </script>

</body>
</html>


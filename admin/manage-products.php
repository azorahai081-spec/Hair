<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// This page is for superadmins only
if (!is_superadmin()) {
    header('Location: dashboard.php?error=access_denied');
    exit;
}
require_once '../db-config.php';

// Fetch all products to display
$products = $pdo->query("SELECT * FROM products ORDER BY sort_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                    <a href="index.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    <?php if (can_manage_reviews()): ?>
                        <a href="manage-reviews.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Reviews</a>
                    <?php endif; ?>
                    <?php if (is_superadmin()): ?>
                        <a href="manage-products.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Manage Products</a>
                        <a href="manage-settings.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                        <a href="manage-users.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Users</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 px-4 mb-6">Manage Products</h1>
        
        <div class="px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Product List -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Existing Products</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Original Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="product-list" class="bg-white divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                                <tr id="product-row-<?php echo $product['id']; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">৳ <?php echo htmlspecialchars($product['price']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><del>৳ <?php echo htmlspecialchars($product['original_price']); ?></del></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $product['is_active'] ? 'Yes' : 'No'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="edit-btn text-blue-600 hover:text-blue-900" data-id="<?php echo $product['id']; ?>">Edit</button>
                                        <button class="delete-btn text-red-600 hover:text-red-900 ml-4" data-id="<?php echo $product['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Form -->
            <div class="bg-white p-6 rounded-lg shadow-md h-fit">
                <h2 id="form-title" class="text-xl font-bold mb-4">Add New Product</h2>
                <form id="product-form">
                    <input type="hidden" name="action" id="form-action" value="create">
                    <input type="hidden" name="id" id="product-id">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <div>
                            <label for="product_key" class="block text-sm font-medium text-gray-700">Product Key (e.g., '100ml')</label>
                            <input type="text" name="product_key" id="product_key" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Price (e.g., 800)</label>
                                <input type="number" step="0.01" name="price" id="price" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                            </div>
                            <div>
                                <label for="original_price" class="block text-sm font-medium text-gray-700">Original Price (e.g., 990)</label>
                                <input type="number" step="0.01" name="original_price" id="original_price" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                            </div>
                        </div>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">Image Filename</label>
                            <input type="text" name="image" id="image" value="haircode.webp" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" value="0" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                            </div>
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="is_active" id="is_active" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-4 pt-2">
                            <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add Product</button>
                            <button type="button" id="cancel-btn" class="w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 hidden">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('product-form');
        const productList = document.getElementById('product-list');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const CSRF_TOKEN = '<?php echo $csrf_token; ?>';

        const resetForm = () => {
            form.reset();
            form.action.value = 'create';
            form.id.value = '';
            formTitle.textContent = 'Add New Product';
            submitBtn.textContent = 'Add Product';
            cancelBtn.classList.add('hidden');
            form.image.value = 'haircode.webp';
            form.sort_order.value = '0';
            form.is_active.value = '1';
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('csrf_token', CSRF_TOKEN);

            const response = await fetch('products-api.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Error: ' (result.message || 'An unknown error occurred.'));
            }
        });

        productList.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-btn')) {
                const id = e.target.dataset.id;
                const response = await fetch(`products-api.php?action=get_single&id=${id}`);
                const product = await response.json();

                if(product) {
                    form.action.value = 'update';
                    form.id.value = product.id;
                    form.name.value = product.name;
                    form.product_key.value = product.product_key;
                    form.price.value = product.price;
                    form.original_price.value = product.original_price;
                    form.image.value = product.image;
                    form.sort_order.value = product.sort_order;
                    form.is_active.value = product.is_active;
                    
                    formTitle.textContent = 'Edit Product';
                    submitBtn.textContent = 'Update Product';
                    cancelBtn.classList.remove('hidden');
                    window.scrollTo(0, 0);
                }
            }

            if (e.target.classList.contains('delete-btn')) {
                if (confirm('Are you sure you want to delete this product?')) {
                    const id = e.target.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    formData.append('csrf_token', CSRF_TOKEN);

                    fetch('products-api.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(result => {
                            if (result.status === 'success') {
                                document.getElementById(`product-row-${id}`).remove();
                            } else {
                                alert('Error: ' (result.message || 'Could not delete product.'));
                            }
                        });
                }
            }
        });

        cancelBtn.addEventListener('click', resetForm);
        resetForm();
    });
    </script>
</body>
</html>

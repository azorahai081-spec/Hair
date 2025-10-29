<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here

// This page is for superadmins only
if (!is_superadmin()) {
    header('Location: dashboard.php?error=access_denied');
    exit;
}
require_once '../db-config.php';

// Fetch all users to display
$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY username")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                    <a href="index.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    <?php if (can('can_manage_reviews')): ?>
                        <a href="manage-reviews.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Reviews</a>
                    <?php endif; ?>
                    <?php if (is_superadmin()): ?>
                        <a href="manage-products.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Products</a>
                        <a href="manage-settings.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                        <a href="manage-users.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Manage Users</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 px-4 mb-6">Manage Users</h1>
        
        <div class="px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- User List -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Existing Users</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user-list" class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr id="user-row-<?php echo $user['id']; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="edit-btn text-blue-600 hover:text-blue-900" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-role="<?php echo $user['role']; ?>">Edit</button>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): // Can't delete yourself ?>
                                            <button class="delete-btn text-red-600 hover:text-red-900 ml-4" data-id="<?php echo $user['id']; ?>">Delete</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Form -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 id="form-title" class="text-xl font-bold mb-4">Add New User</h2>
                <form id="user-form">
                    <input type="hidden" name="action" id="form-action" value="create">
                    <input type="hidden" name="id" id="user-id">
                    <div class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" id="username" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password when editing.</p>
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">
                                <option value="viewer">Viewer</option>
                                <option value="status_updater">Status Updater</option>
                                <option value="order_manager">Order Manager</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="flex gap-4 pt-2">
                            <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add User</button>
                            <button type="button" id="cancel-btn" class="w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 hidden">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('user-form');
        const userList = document.getElementById('user-list');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const passwordInput = document.getElementById('password');
        const CSRF_TOKEN = '<?php echo $csrf_token; ?>'; // Get CSRF token

        const resetForm = () => {
            form.reset();
            form.action.value = 'create';
            form.id.value = '';
            formTitle.textContent = 'Add New User';
            submitBtn.textContent = 'Add User';
            cancelBtn.classList.add('hidden');
            passwordInput.setAttribute('required', 'required');
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('csrf_token', CSRF_TOKEN); // Add token

            const response = await fetch('users-api.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                alert(result.message);
                window.location.reload();
            } else {
                // --- THIS IS THE FIX ---
                alert('Error: ' + result.message);
            }
        });

        userList.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-btn')) {
                const button = e.target;
                form.action.value = 'update';
                form.id.value = button.dataset.id;
                form.username.value = button.dataset.username;
                form.role.value = button.dataset.role;
                formTitle.textContent = 'Edit User';
                submitBtn.textContent = 'Update User';
                cancelBtn.classList.remove('hidden');
                passwordInput.removeAttribute('required');
                window.scrollTo(0, 0);
            }

            if (e.target.classList.contains('delete-btn')) {
                if (confirm('Are you sure you want to delete this user?')) {
                    const id = e.target.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    formData.append('csrf_token', CSRF_TOKEN); // Add token

                    fetch('users-api.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(result => {
                            if (result.status === 'success') {
                                document.getElementById(`user-row-${id}`).remove();
                            } else {
                                // --- THIS IS THE FIX ---
                                alert('Error: ' + result.message);
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


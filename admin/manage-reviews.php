<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions are here
require_once '../db-config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style> 
        body { font-family: 'Inter', sans-serif; } 
        .featured-star { color: #f59e0b; }
    </style>
</head>
<body class="bg-gray-100 p-4 sm:p-8">
    <nav class="bg-white shadow-md mb-8 -mt-4 -mx-4 sm:-mx-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="font-bold text-xl text-gray-800">Admin Panel</span>
                </div>
                <div>
                    <a href="dashboard.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <a href="index.php" class="text-gray-500 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Manage Orders</a>
                    <?php if (can_manage_reviews()): ?>
                        <a href="manage-reviews.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Manage Reviews</a>
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

    <div class="max-w-5xl mx-auto">
        <?php if (can_manage_reviews()): ?>
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 id="form-title" class="text-2xl font-bold mb-4">Add New Review</h2>
            <form id="review-form">
                <!-- --- CSRF Protection --- -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <!-- --- End CSRF Protection --- -->
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="review-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="name" placeholder="Customer Name" required class="w-full p-2 border border-gray-300 rounded-md">
                    <input type="text" name="image_initials" placeholder="Initials (e.g., JD)" required maxlength="2" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="grid grid-cols-3 gap-2">
                        <select id="month-select" class="w-full p-2 border border-gray-300 rounded-md bg-white" required></select>
                        <select id="day-select" class="w-full p-2 border border-gray-300 rounded-md bg-white" required></select>
                        <select id="year-select" class="w-full p-2 border border-gray-300 rounded-md bg-white" required></select>
                    </div>
                    <input type="hidden" name="date" id="hidden-date-input">
                     <select name="rating" required class="w-full p-2 border border-gray-300 rounded-md bg-white">
                        <option value="">Select Rating</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <textarea name="review_text" placeholder="Review text..." required class="w-full p-2 border border-gray-300 rounded-md mb-4" rows="4"></textarea>
                 <div class="flex items-center mb-4">
                     <label for="status" class="block text-sm font-medium text-gray-700 mr-4">Status</label>
                     <select id="status" name="status" class="w-full max-w-xs p-2 border border-gray-300 rounded-md bg-white">
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Add Review</button>
                    <button type="button" id="cancel-btn" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 hidden">Cancel Edit</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <header class="flex justify-between items-center mb-4">
                 <h2 class="text-2xl font-bold">Existing Reviews</h2>
                 <input type="search" id="search-input" placeholder="Search reviews..." class="w-full md:w-1/2 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </header>
            <div id="reviews-list" class="space-y-4"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const reviewsList = document.getElementById('reviews-list');
        const searchInput = document.getElementById('search-input');
        let allReviews = [];
        
        // --- THIS IS THE FIX ---
        // Use the new permission functions
        const canManageReviews = <?php echo can_manage_reviews() ? 'true' : 'false'; ?>;
        const canDeleteReviews = <?php echo can_delete_reviews() ? 'true' : 'false'; ?>;
        // --- END FIX ---
        
        const CSRF_TOKEN = '<?php echo $csrf_token; ?>';

        // Form elements
        const form = document.getElementById('review-form');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const nameInput = form ? form.querySelector('input[name="name"]') : null;
        const initialsInput = form ? form.querySelector('input[name="image_initials"]') : null;
        const daySelect = document.getElementById('day-select');
        const monthSelect = document.getElementById('month-select');
        const yearSelect = document.getElementById('year-select');
        const hiddenDateInput = document.getElementById('hidden-date-input');

        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        const displayReviews = (filter = '') => {
            const searchTerm = filter.toLowerCase();
            const filteredReviews = allReviews.filter(review =>
                review.name.toLowerCase().includes(searchTerm) ||
                review.review_text.toLowerCase().includes(searchTerm)
            );

            reviewsList.innerHTML = '';
            if (filteredReviews.length === 0) {
                 reviewsList.innerHTML = '<p class="text-gray-500">No reviews to display.</p>'; // Changed message
                 return;
            }

            filteredReviews.forEach(review => {
                const reviewElement = document.createElement('div');
                reviewElement.className = 'p-4 border rounded-lg flex justify-between items-start';
                const isFeaturedClass = review.is_featured == 1 ? 'featured-star' : 'text-gray-400';
                
                let actionButtons = '';
                // --- THIS IS THE FIX ---
                if(canManageReviews){
                    actionButtons += `<button class="feature-btn text-sm font-semibold ${review.is_featured == 1 ? 'text-amber-600' : 'text-gray-500'} hover:text-amber-500" data-id="${review.id}">${review.is_featured == 1 ? 'Unfeature' : 'Feature'}</button>`;
                
                    // Add Edit button (part of "manage")
                    actionButtons += `
                        <div class="mt-2">
                            <button class="edit-btn text-blue-500 hover:text-blue-700 mr-2" data-id="${review.id}"><i class="fas fa-edit"></i></button>`;
                    
                    // Add Delete button (only if user has delete permission)
                    if(canDeleteReviews){
                         actionButtons += `<button class="delete-btn text-red-500 hover:text-red-700" data-id="${review.id}"><i class="fas fa-trash"></i></button>`;
                    }
                    actionButtons += `</div>`;
                }
                // --- END FIX ---


                reviewElement.innerHTML = `
                    <div class="flex items-start">
                         ${canManageReviews ? `<i class="fas fa-star ${isFeaturedClass} mt-1 mr-4"></i>` : ''}
                        <div>
                            <h3 class="font-bold">${escapeHTML(review.name)} <span class="text-yellow-400">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</span></h3>
                            <p class="text-sm text-gray-500">${escapeHTML(review.date)}</p>
                            <p class="mt-2">${escapeHTML(review.review_text)}</p>
                            <p class="text-xs mt-2 font-semibold uppercase text-gray-400">Status: ${review.status}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-4 flex flex-col items-end gap-2">
                       ${actionButtons}
                    </div>
                `;
                reviewsList.appendChild(reviewElement);
            });
        };
        
        const fetchReviews = async () => {
            try {
                const response = await fetch('reviews-api.php');
                allReviews = await response.json();
                displayReviews(searchInput.value);
            } catch (e) {
                reviewsList.innerHTML = '<p class="text-red-500">Failed to load reviews.</p>';
            }
        };

        function escapeHTML(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/[&<>"']/g, match => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[match]);
        }

        searchInput.addEventListener('input', () => displayReviews(searchInput.value));
        
        // --- THIS IS THE FIX ---
        // Replaced 'isEditor' with 'canManageReviews'
        if(canManageReviews) {
            // Populate Date Dropdowns
            months.forEach((month, index) => { monthSelect.options[monthSelect.options.length] = new Option(month, index); });
            for (let i = 1; i <= 31; i++) { daySelect.options[daySelect.options.length] = new Option(i, i); }
            const currentYear = new Date().getFullYear();
            for (let i = currentYear + 2; i >= currentYear - 5; i--) { yearSelect.options[yearSelect.options.length] = new Option(i, i); }

            const updateHiddenDate = () => {
                if (!monthSelect.value || !daySelect.value || !yearSelect.value) return;
                hiddenDateInput.value = `${months[parseInt(monthSelect.value)]} ${daySelect.value}, ${yearSelect.value}`;
            };

            const setDefaultDate = () => {
                const today = new Date();
                yearSelect.value = today.getFullYear();
                monthSelect.value = today.getMonth();
                daySelect.value = today.getDate();
                updateHiddenDate();
            };

            daySelect.addEventListener('change', updateHiddenDate);
            monthSelect.addEventListener('change', updateHiddenDate);
            yearSelect.addEventListener('change', updateHiddenDate);

            if (nameInput) {
                nameInput.addEventListener('input', () => {
                    const name = nameInput.value.trim();
                    if (initialsInput) {
                        initialsInput.value = name.split(' ').filter(p=>p).map(p=>p[0]).join('').toUpperCase();
                    }
                });
            }

            const resetForm = () => {
                form.reset();
                formTitle.textContent = 'Add New Review';
                submitBtn.textContent = 'Add Review';
                form.action.value = 'create';
                form.id.value = '';
                cancelBtn.classList.add('hidden');
                setDefaultDate();
            };

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                updateHiddenDate();
                const formData = new FormData(form);
                formData.append('csrf_token', CSRF_TOKEN); // Already correct

                const response = await fetch('reviews-api.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    resetForm();
                    fetchReviews();
                } else {
                    alert('Error: ' + result.message);
                }
            });

            cancelBtn.addEventListener('click', resetForm);
            setDefaultDate();
        }
        // --- END FIX ---

        reviewsList.addEventListener('click', async (e) => {
            const target = e.target.closest('button');
            if (!target) return;
            const id = target.dataset.id;
            
            // --- THIS IS THE FIX ---
            if (canManageReviews && target.classList.contains('feature-btn')) {
                const formData = new FormData();
                formData.append('action', 'toggle_feature');
                formData.append('id', id);
                formData.append('csrf_token', CSRF_TOKEN); // Already correct

                const response = await fetch('reviews-api.php', { method: 'POST', body: formData });
                if (response.ok) fetchReviews();
            }

            // --- THIS IS THE FIX ---
            if (canManageReviews && target.classList.contains('edit-btn')) {
                 const response = await fetch(`reviews-api.php?action=get_single&id=${id}`);
                 const review = await response.json();
                 
                 form.name.value = review.name;
                 form.rating.value = review.rating;
                 form.review_text.value = review.review_text;
                 form.image_initials.value = review.image_initials;
                 form.status.value = review.status;
                 
                 const dateParts = review.date.replace(',', '').split(' ');
                 if(dateParts.length === 3) {
                    monthSelect.value = months.indexOf(dateParts[0]);
                    daySelect.value = parseInt(dateParts[1]);
                    yearSelect.value = parseInt(dateParts[2]);
                 }
                 
                 formTitle.textContent = 'Edit Review';
                 submitBtn.textContent = 'Update Review';
                 form.action.value = 'update';
                 form.id.value = id;
                 cancelBtn.classList.remove('hidden');
                 window.scrollTo(0,0);
            }

            // --- THIS IS THE FIX ---
            if (canDeleteReviews && target.classList.contains('delete-btn')) {
                if (confirm('Are you sure you want to delete this review?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    formData.append('csrf_token', CSRF_TOKEN); // Already correct

                    const response = await fetch('reviews-api.php', { method: 'POST', body: formData });
                    if(response.ok) fetchReviews();
                }
            }
            // --- END FIX ---
        });
        
        fetchReviews();
    });
    </script>
</body>
</html>


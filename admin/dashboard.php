<?php
require_once 'includes/auth-check.php';
require_once '../db-config.php';

$current_page = basename($_SERVER['PHP_SELF']); // Get current page name

// --- Date Filter Logic ---
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$end_date_for_query = $end_date . ' 23:59:59';


// --- Fetch Analytics Data ---
try {
    // High-level stats for the selected period
    $total_orders_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ?");
    $total_orders_stmt->execute([$start_date, $end_date_for_query]);
    $total_orders = $total_orders_stmt->fetchColumn();

    $total_revenue_stmt = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE order_date BETWEEN ? AND ?");
    $total_revenue_stmt->execute([$start_date, $end_date_for_query]);
    $total_revenue = $total_revenue_stmt->fetchColumn() ?: 0;
    
    $orders_today_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = ?");
    $orders_today_stmt->execute([date('Y-m-d')]);
    $orders_today = $orders_today_stmt->fetchColumn();
    
    $total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

    // Order status breakdown for the selected period
    $status_counts = ['Pending' => 0, 'Processing' => 0, 'Shipped' => 0, 'Delivered' => 0, 'Cancelled' => 0];
    $status_results_stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE order_date BETWEEN ? AND ? GROUP BY status");
    $status_results_stmt->execute([$start_date, $end_date_for_query]);
    $status_results = $status_results_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $status_counts = array_merge($status_counts, $status_results);

    // --- Monthly Sales Comparison (only for default view) ---
    if (!isset($_GET['start_date'])) {
        $sales_this_month = $total_revenue;
        $previous_month_start = date('Y-m-01', strtotime('-1 month'));
        $previous_month_end = date('Y-m-t', strtotime('-1 month'));
        $sales_last_month_stmt = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE order_date BETWEEN ? AND ?");
        $sales_last_month_stmt->execute([$previous_month_start, $previous_month_end . ' 23:59:59']);
        $sales_last_month = $sales_last_month_stmt->fetchColumn() ?: 0;
        if ($sales_last_month > 0) {
            $percentage_change = (($sales_this_month - $sales_last_month) / $sales_last_month) * 100;
        } else {
            $percentage_change = $sales_this_month > 0 ? 100 : 0;
        }
    }

    // Daily sales for the selected period chart
    $daily_sales = [];
    $period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), (new DateTime($end_date))->modify('+1 day'));
    foreach ($period as $date) {
        $daily_sales[$date->format('Y-m-d')] = 0;
    }
    $daily_sales_stmt = $pdo->prepare("SELECT DATE(order_date) as day, SUM(total_price) as total FROM orders WHERE order_date BETWEEN ? AND ? GROUP BY DATE(order_date)");
    $daily_sales_stmt->execute([$start_date, $end_date_for_query]);
    $daily_sales_results = $daily_sales_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($daily_sales_results as $day => $total) {
        if (isset($daily_sales[$day])) {
            $daily_sales[$day] = (float)$total;
        }
    }
    $chart_labels = json_encode(array_keys($daily_sales));
    $chart_data = json_encode(array_values($daily_sales));

    // --- Yearly Sales Overview ---
    $selected_year = date('Y', strtotime($start_date));
    $yearly_sales = array_fill(1, 12, 0);
    $yearly_sales_stmt = $pdo->prepare("SELECT MONTH(order_date) as month, SUM(total_price) as total FROM orders WHERE YEAR(order_date) = ? GROUP BY MONTH(order_date)");
    $yearly_sales_stmt->execute([$selected_year]);
    $yearly_sales_results = $yearly_sales_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach($yearly_sales_results as $month => $total) {
        $yearly_sales[$month] = (float)$total;
    }
    $yearly_chart_data = json_encode(array_values($yearly_sales));
    
    // --- Sales by Location ---
    $location_sales_stmt = $pdo->prepare("SELECT customer_address, SUM(total_price) as total FROM orders WHERE order_date BETWEEN ? AND ? GROUP BY customer_address ORDER BY total DESC LIMIT 5");
    $location_sales_stmt->execute([$start_date, $end_date_for_query]);
    $location_sales = $location_sales_stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Top Selling Products ---
    $product_sales_stmt = $pdo->prepare(
        "SELECT product_name, COUNT(*) as units_sold 
         FROM orders 
         WHERE order_date BETWEEN ? AND ? 
         GROUP BY product_name 
         ORDER BY units_sold DESC"
    );
    $product_sales_stmt->execute([$start_date, $end_date_for_query]);
    $product_sales = $product_sales_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Data for Pie Chart - with shortened names
    $product_chart_labels = [];
    $product_chart_data = [];
    $product_name_prefix_to_remove = 'FEG Hair Growth Serum usa 50ml × '; // Adjust this if product names change
    
    foreach ($product_sales as $product) {
        // Attempt to shorten the name for better chart display
        $short_name = $product['product_name'];
        if (strpos($short_name, $product_name_prefix_to_remove) === 0) {
             $short_name = str_replace($product_name_prefix_to_remove, '', $short_name);
        }
        // Fallback for other long names
        if (strlen($short_name) > 20) { 
            $short_name = substr($short_name, 0, 20) . '...';
        }
        
        $product_chart_labels[] = $short_name;
        $product_chart_data[] = $product['units_sold'];
    }
    $product_chart_labels_json = json_encode($product_chart_labels);
    $product_chart_data_json = json_encode($product_chart_data);


} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 flex flex-col md:flex-row justify-between md:items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <form method="GET" class="flex flex-col sm:flex-row items-center gap-2 mt-4 md:mt-0">
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="w-full sm:w-auto border-gray-300 rounded-md shadow-sm">
                <span class="text-gray-500 hidden sm:inline">to</span>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="w-full sm:w-auto border-gray-300 rounded-md shadow-sm">
                <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
            </form>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="m-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php else: ?>
            <!-- Top Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-4 py-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Total Revenue (Period)</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">৳ <?php echo number_format($total_revenue, 2); ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Total Orders (Period)</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $total_orders; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Orders Today</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $orders_today; ?></p>
                </div>
                 <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500">Total Reviews</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $total_reviews; ?></p>
                </div>
            </div>

            <!-- Order Status Breakdown -->
            <div class="px-4 py-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Status Breakdown</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-400">
                        <h3 class="text-sm font-medium text-gray-500">Pending</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $status_counts['Pending']; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-400">
                        <h3 class="text-sm font-medium text-gray-500">Processing</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $status_counts['Processing']; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-400">
                        <h3 class="text-sm font-medium text-gray-500">Shipped</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $status_counts['Shipped']; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-400">
                        <h3 class="text-sm font-medium text-gray-500">Delivered</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $status_counts['Delivered']; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-400">
                        <h3 class="text-sm font-medium text-gray-500">Cancelled</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900"><?php echo $status_counts['Cancelled']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Daily and Yearly Sales Report -->
            <div class="px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                 <!-- Daily Sales Report -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Sales Report (<?php echo date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)); ?>)</h2>
                    <?php if (isset($sales_last_month)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-center">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">This Month's Sales</h4>
                            <p class="text-2xl font-bold text-blue-600 mt-1">৳ <?php echo number_format($sales_this_month, 2); ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Last Month's Sales</h4>
                            <p class="text-2xl font-bold text-gray-700 mt-1">৳ <?php echo number_format($sales_last_month, 2); ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Monthly Change</h4>
                            <p class="text-2xl font-bold mt-1 <?php echo $percentage_change >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                <?php echo number_format($percentage_change, 1); ?>%
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                 <!-- Yearly Sales & Top Locations -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                         <h2 class="text-xl font-semibold text-gray-800 mb-4">Yearly Sales (<?php echo $selected_year; ?>)</h2>
                         <div>
                            <canvas id="yearlySalesChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Products Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Top Selling Products (Units)</h2>
                        <?php if (empty($product_sales)): ?>
                            <p class="text-gray-500">No product sales data for this period.</p>
                        <?php else: ?>
                            <div class="max-w-xs mx-auto">
                                <canvas id="productPieChart"></canvas>
                            </div>
                            <ul class="space-y-2 mt-4">
                               <?php foreach ($product_sales as $product): ?>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 truncate pr-2" title="<?php echo htmlspecialchars($product['product_name']); ?>">
                                            <?php 
                                                $display_name = $product['product_name'];
                                                if (strpos($display_name, $product_name_prefix_to_remove) === 0) {
                                                    $display_name = str_replace($product_name_prefix_to_remove, '', $display_name);
                                                }
                                                echo htmlspecialchars($display_name);
                                            ?>
                                        </span>
                                        <span class="font-semibold text-gray-800 whitespace-nowrap"><?php echo $product['units_sold']; ?> units</span>
                                    </li>
                               <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Top 5 Locations</h2>
                        <ul class="space-y-2">
                           <?php foreach ($location_sales as $location): ?>
                                <li class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 truncate pr-2" title="<?php echo htmlspecialchars($location['customer_address']); ?>"><?php echo htmlspecialchars($location['customer_address']); ?></span>
                                    <span class="font-semibold text-gray-800 whitespace-nowrap">৳ <?php echo number_format($location['total']); ?></span>
                                </li>
                           <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>


        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile Menu Toggle
            document.getElementById('mobile-menu-button').addEventListener('click', function() {
                var menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });

            // Daily Sales Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chart_labels; ?>,
                    datasets: [{
                        label: 'Daily Sales (৳)',
                        data: <?php echo $chart_data; ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: value => '৳ ' + value } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: context => `Sales: ${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'BDT' }).format(context.parsed.y)}` } }
                    }
                }
            });

            // Yearly Sales Chart
            const yearlyCtx = document.getElementById('yearlySalesChart').getContext('2d');
            const yearlySalesChart = new Chart(yearlyCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Monthly Sales (৳)',
                        data: <?php echo $yearly_chart_data; ?>,
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                 options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: value => '৳ ' + (value/1000) + 'k' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: context => `Sales: ${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'BDT' }).format(context.parsed.y)}` } }
                    }
                }
            });

            // Product Sales Pie Chart
            const productCtx = document.getElementById('productPieChart');
            if (productCtx) {
                const productPieChart = new Chart(productCtx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?php echo $product_chart_labels_json; ?>,
                        datasets: [{
                            label: 'Units Sold',
                            data: <?php echo $product_chart_data_json; ?>,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(239, 68, 68, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(34, 197, 94, 0.7)',
                                'rgba(139, 92, 246, 0.7)'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += context.parsed + ' units';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

        });
    </script>
</body>
</html>


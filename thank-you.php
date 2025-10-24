<?php
// Start the session to access session variables.
session_start();
require_once 'db-config.php';

// --- Security Check ---
// If 'last_order_id' is not in the session, redirect to the homepage.
if (!isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

// Get the order ID from the session.
$order_id = $_SESSION['last_order_id'];

// --- Clean up the session ---
// This prevents the user from seeing the same confirmation on refresh.
unset($_SESSION['last_order_id']);


// --- Fetch Order Details from Database ---
$order = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
} catch (PDOException $e) {
    // For a real-world app, you might log this error instead of showing it.
    die("Error: Could not retrieve order details. " . $e->getMessage());
}

// If the order ID from the session doesn't match an order in the database, redirect.
if (!$order) {
    header('Location: index.php');
    exit;
}

// --- FIX: Use DateTime object for reliable date formatting ---
$date_obj = new DateTime($order['order_date']);
$display_date = $date_obj->format("F j, Y");
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container mx-auto max-w-3xl p-4 md:p-8">
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-10">
            <div class="text-center mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">আপনার অর্ডার টি কনফার্ম করা হয়েছে।</h1>
                <p class="text-gray-600 mt-2 text-lg">১ থেকে ৩ দিনের মধ্যে প্রোডাক্ট টি হাতে পাবেন।</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-center mb-8">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500">Order number</h2>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($order['order_number']); ?></p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-500">Date</h2>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($display_date); ?></p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-500">Total</h2>
                    <p class="text-lg font-bold text-gray-800">৳ <?php echo htmlspecialchars($order['total_price']); ?></p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-500">Payment method</h2>
                    <p class="text-lg font-bold text-gray-800">Cash on delivery</p>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-bold mb-4">Order details</h2>

                <div class="flow-root">
                    <dl class="text-sm">
                        <div class="flex justify-between py-3 border-b">
                            <dt class="text-gray-600"><?php echo htmlspecialchars($order['product_name']); ?></dt>
                            <dd class="font-medium text-gray-800">৳ <?php echo htmlspecialchars($order['total_price'] - $order['shipping_cost']); ?></dd>
                        </div>
                        <div class="flex justify-between py-3 border-b">
                            <dt class="text-gray-600">Subtotal</dt>
                            <dd class="font-medium text-gray-800">৳ <?php echo htmlspecialchars($order['total_price'] - $order['shipping_cost']); ?></dd>
                        </div>
                        <div class="flex justify-between py-3 border-b">
                            <dt class="text-gray-600">Shipping</dt>
                            <dd class="text-gray-600">৳ <?php echo htmlspecialchars($order['shipping_cost']); ?> via <?php echo htmlspecialchars($order['shipping_location']); ?></dd>
                        </div>
                        <div class="flex justify-between py-3 border-b">
                            <dt class="text-gray-600">Payment method</dt>
                            <dd class="text-gray-600">Cash on delivery</dd>
                        </div>
                        <div class="flex justify-between py-4 text-base font-bold">
                            <dt>Total</dt>
                            <dd>৳ <?php echo htmlspecialchars($order['total_price']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 text-sm">
                <div>
                    <h3 class="text-lg font-bold mb-2">Billing address</h3>
                    <address class="not-italic text-gray-600">
                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                        <?php echo htmlspecialchars($order['customer_address']); ?><br>
                        <?php echo htmlspecialchars($order['customer_phone']); ?>
                    </address>
                </div>
                <div class="text-left md:text-right">
                    <h3 class="text-lg font-bold mb-2">Shipping address</h3>
                    <p class="text-gray-600">N/A</p>
                </div>
            </div>
            
            <!-- Return to Home Button -->
            <div class="text-center mt-10">
                <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                    ← Return to Home
                </a>
            </div>

        </div>
        <footer class="text-center py-6">
            <p class="text-gray-500 text-sm">&copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
        </footer>
    </div>
</body>
</html>


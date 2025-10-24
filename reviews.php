<?php
require_once 'db-config.php';

// Fetch all approved reviews
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY id DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
    $error_message = "Could not load reviews at this time.";
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto px-4 py-8 md:py-12">
        <header class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800">আমাদের কাস্টমারদের মতামত</h1>
            <p class="text-lg text-gray-600 mt-2">আমাদের সম্মানিত কাস্টমারদের থেকে পাওয়া বাস্তব রিভিউ।</p>
             <a href="index.php#order-form" class="mt-4 inline-block bg-blue-600 text-white font-bold py-3 px-8 rounded-lg text-lg">
                এখনই অর্ডার করুন
            </a>
        </header>

        <main class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php if (isset($error_message)): ?>
                <p class="text-center text-red-500 md:col-span-2"><?php echo $error_message; ?></p>
            <?php elseif (empty($reviews)): ?>
                <p class="text-center text-gray-500 md:col-span-2">এখনো কোনো রিভিউ যোগ করা হয়নি।</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col">
                        <div class="flex items-start mb-4">
                             <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xl mr-4 flex-shrink-0">
                                <?php echo htmlspecialchars($review['image_initials']); ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($review['name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($review['date']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center mb-3 text-yellow-400">
                             <?php echo str_repeat('<i class="fas fa-star"></i>', $review['rating']) . str_repeat('<i class="far fa-star text-gray-300"></i>', 5 - $review['rating']); ?>
                        </div>
                        <p class="text-gray-800 mb-4 flex-grow">
                            <span class="font-semibold"><?php echo htmlspecialchars($review['name']); ?> এটি ব্যবহারের পরামর্শ দিয়েছেন।</span><br>
                            "<?php echo htmlspecialchars($review['review_text']); ?>"
                        </p>
                         <div class="mt-auto pt-4 border-t border-gray-200 flex items-center text-sm text-gray-500">
                            <i class="fas fa-check-circle text-green-500 mr-2 text-lg"></i>
                            <span>Verified Customer</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
     <footer class="text-center py-6 mt-4">
        <p class="text-gray-500 text-sm">&copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
    </footer>
</body>
</html>

<?php
session_start();
require_once 'db-config.php';

// This code retrieves any error messages from the session to display them.
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']); // Clear the message after displaying it once.

// --- Fetch reviews for display ---
try {
    // Fetch featured reviews (up to 3)
    $stmt_featured = $pdo->query("SELECT name, rating, review_text, image_initials FROM reviews WHERE status = 'approved' AND is_featured = 1 ORDER BY id DESC LIMIT 3");
    $featured_reviews = $stmt_featured->fetchAll();

    $reviews = $featured_reviews;
    $limit = 3 - count($featured_reviews);

    if ($limit > 0) {
        // Fetch latest non-featured reviews to fill the remaining spots
        $stmt_latest = $pdo->prepare("SELECT name, rating, review_text, image_initials FROM reviews WHERE status = 'approved' AND is_featured = 0 ORDER BY id DESC LIMIT ?");
        $stmt_latest->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt_latest->execute();
        $latest_reviews = $stmt_latest->fetchAll();
        // Merge the arrays
        $reviews = array_merge($reviews, $latest_reviews);
    }
} catch (PDOException $e) {
    $reviews = []; // In case of error, show no reviews.
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hair Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@500;600;700&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- SwiperJS for Image Slider --><link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Hind Siliguri', 'Anek Bangla', sans-serif;
            background-color: #f9fafb; /* Light gray background */
        }
        .font-anek { font-family: 'Anek Bangla', sans-serif; }
        .font-hind { font-family: 'Hind Siliguri', sans-serif; }

        .gradient-text {
            background: linear-gradient(to right, #1e3a8a, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .cta-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 20px;
            right: 20px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        .whatsapp-float:hover {
            transform: scale(1.1);
        }
        /* Swiper Custom Styles */
        .swiper { border-radius: 0.75rem; } /* Slightly more rounded */
        .swiper-pagination-bullet-active { background: #1e3a8a; }

        .section-title {
            font-family: 'Anek Bangla', sans-serif;
            font-weight: 700;
            font-size: 2.5rem; /* 40px */
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-subtitle-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            border: 1px solid #e5e7eb;
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .section-subtitle {
            font-family: 'Hind Siliguri', sans-serif;
            font-size: 1.5rem; /* 24px */
            font-weight: 600;
            color: #111827;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <main class="w-full">
        
        <!-- Header Section --><header class="text-center py-8 bg-white shadow-md">
             <h1 class="font-anek font-semibold text-2xl md:text-3xl text-gray-900 flex items-center justify-center">
                 <img src="https://placehold.co/24x18/DE281E/ffffff?text=US" alt="US Flag" class="mr-3">
                 Hair Code
             </h1>
             <p class="font-hind font-semibold text-xl mt-3 text-blue-700">আমেরিকার বেস্ট সেলার, যা এখন থেকে বাংলাদেশে।</p>
             <p class="font-hind font-semibold text-lg mt-1 text-gray-600">বিশেষজ্ঞদের মতে চুলের ৯০% সমস্যার সমাধান মাত্র ৩০ দিনে।</p>
        </header>


        <!-- Hero Section --><section class="bg-white">
            <div class="container mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16 items-center">
                <!-- Left Column: Image Slider --><div class="rounded-lg shadow-2xl">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <!-- Slides - UPDATED with local images --><div class="swiper-slide"><img src="FEG-1-1.jpg" alt="Hair growth result 1" class="w-full rounded-xl"></div>
                            <div class="swiper-slide"><img src="FEG-2.jpg" alt="Hair growth result 2" class="w-full rounded-xl"></div>
                            <div class="swiper-slide"><img src="FEG-3.jpg" alt="Hair growth result 3" class="w-full rounded-xl"></div>
                            <div class="swiper-slide"><img src="FEG-4-1-800x800.jpg" alt="Hair growth result 4" class="w-full rounded-xl"></div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                
                 <!-- Right Column: Info and Benefits --><div class="flex flex-col justify-center">
                     <h2 class="hero-title my-4 text-center md:text-left gradient-text font-hind" style="font-size: 42px;">
                         কেন ব্যবহার করবেন <strong class="font-semibold">Hair Code</strong>?
                     </h2>
                     <div class="space-y-4 text-gray-600 text-lg mt-4 font-hind">
                         <p class="flex items-start"><span>✅</span><span class="ml-3 font-semibold">চুল পড়া পুরোপুরি বন্ধ করে।</span></p>
                         <p class="flex items-start"><span>✅</span><span class="ml-3 font-semibold">মাথার ত্বকের রক্ত সঞ্চালন বৃদ্ধি করে।</span></p>
                         <p class="flex items-start"><span>✅</span><span class="ml-3 font-semibold">নতুন চুল গজাতে সাহায্য করে।</span></p>
                         <p class="flex items-start"><span>✅</span><span class="ml-3 font-semibold">চুলের গোড়া মজবুত করে।</span></p>
                         <p class="flex items-start"><span>✅</span><span class="ml-3 font-semibold">নারী ও পুরুষ উভয়ের জন্য কার্যকর</span></p>
                     </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                         <a href="#order-form" class="cta-button w-full text-center bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 px-8 rounded-lg text-xl transition-all font-anek">
                             অর্ডার করুন
                         </a>
                         <a href="tel:+8801234567890" class="cta-button w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-lg text-xl transition-all font-anek">
                             কল করুন
                         </a>
                    </div>
                </div>
        </section>

        <!-- Ingredients Section --><section class="py-16 md:py-24 bg-gray-50">
            <div class="container mx-auto px-4">
                 <div class="section-subtitle-container">
                     <span class="text-2xl">🧪</span>
                    <h3 class="section-subtitle font-hind">প্রডাক্টের কার্যকারী উপাদান ও উপকারিতা</h3>
                 </div>

                <!-- NEW Updated 2-Column Layout --><div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                    <!-- Column 1: Ingredients --><div>
                        <h4 class="text-2xl font-semibold mb-4 font-anek text-blue-700 text-left">প্রধান উপাদান</h4>
                        <ul class="mt-4 text-gray-600 list-disc list-inside text-left text-lg font-hind space-y-2">
                             <li class="font-semibold">ভিটামিন এ - সি</li>
                             <li class="font-semibold">ওমেগা - থ্রি</li>
                             <li class="font-semibold">ফ্যাটি এসিড</li> <!-- Corrected spelling --><li class="font-semibold">পটাসিয়াম</li>
                             <li class="font-semibold">ম্যাগনেসিয়াম</li>
                             <li class="font-semibold">ক্যালসিয়াম</li>
                             <li class="font-semibold">আমলকি</li>
                             <li class="font-semibold">নিম</li>
                        </ul>
                    </div>
                    
                    <!-- Column 2: Benefits --><div>
                        <h4 class="text-2xl font-semibold mb-4 font-anek text-blue-700 text-left">উপকারিতা</h4>
                        <ul class="mt-4 text-gray-600 list-disc list-inside text-left text-lg font-hind space-y-2">
                            <li class="font-semibold">চুলের অকালপক্কতা রোধ করে।</li>
                            <li class="font-semibold">চুলের খুশকি দূর করে।</li>
                            <li class="font-semibold">চুলকে সিল্কি ও মসৃণ করে।</li>
                            <li class="font-semibold">চুলকে ঘন ও স্বাস্থ্যজ্জ্বল করে তোলে।</li>
                            <li class="font-semibold">প্রাকৃতিক উপাদান দিয়ে তৈরি।</li>
                        </ul>
                    </div>
                </div>
                 
                 <div class="text-center mt-12">
                     <!-- UPDATED with local image --><img src="FEGG.webp" alt="Hair Code Bottle" class="w-full max-w-md mx-auto rounded-lg shadow-lg">
                 </div>
            </div>
        </section>
        
        <!-- How to Use Section --><section class="py-16 md:py-24 bg-white">
            <div class="container mx-auto px-4">
                <div class="section-subtitle-container">
                    <span class="text-2xl">💡</span>
                    <h3 class="section-subtitle font-hind">Hair Code ব্যবহারের নিয়ম:</h3>
                </div>
                <div class="max-w-4xl mx-auto text-lg text-gray-700 space-y-3 font-hind">
                    <p><strong class="font-semibold">১. শুষ্ক পরিষ্কার চুল:</strong> নিশ্চিত করুন যে আপনার চুল এবং মাথার ত্বক সম্পূর্ণ শুকনো এবং পরিষ্কার।</p>
                    <p><strong class="font-semibold">২. সরাসরি স্প্রে করুন:</strong> চুলের ঘনত্ব যেখানে কম, সেখানে সরাসরি মাথার ত্বকে স্প্রে করুন।</p>
                    <p><strong class="font-semibold">৩. ম্যাসাজ দিয়ে শোষণ করান:</strong> হালকাভাবে আঙ্গুল দিয়ে ২-৩ মিনিট ম্যাসাজ করুন যাতে সিরামটি ভালোভাবে শোষিত হয়।</p>
                    <p><strong class="font-semibold">৪. ধুয়ে ফেলার প্রয়োজন নেই:</strong> সিরামটি চুলে রেখে দিন। এটি নন-স্টিকি এবং দ্রুত শুকিয়ে যায়।</p>
                    <p><strong class="font-semibold">৫. নিয়মিত ব্যবহার করুন:</strong> সেরা ফলাফলের জন্য প্রতিদিন ২ বার (সকালে এবং রাতে) ব্যবহার করুন।</p>
                </div>
            </div>
        </section>

        <!-- NEW: Problem & Solution Section --><section class="py-16 md:py-24 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="section-title gradient-text font-anek">চুল পড়ার আসল কারণ ও তার সমাধান</h2>
                <div class="max-w-4xl mx-auto text-lg text-gray-700 space-y-8 font-hind">
                    <div>
                        <h3 class="text-2xl font-semibold mb-3 font-anek text-gray-900">চুল পড়ার কারণ :</h3>
                        <p>যাদের শরীরে DHT তথা DIHYDROTESTOSTERON নামক এক প্রকার নেগেটিভ পদার্থের পরিমাণ বেড়ে যায় তাদের চুল অকালে ঝরে যায়। এই পদার্থটি চুলের কোষগুলোকে ডেমেজ করে দেয়, কোষে রক্ত সঞ্চালন এবং পুষ্টির যোগানকে ব্যাহত করে এবং কোষকে বেড়ে উঠতে বাধা প্রদান করে।ফলে চুলের সচল কোষগুলো মারা যায়।ধীরে ধীরে চুল পড়ার পরিমাণ বাড়লেও সেই হারে চুল গজায় না। ফলে একসময় চুলের পরিমাণ কমে যাওয়া, চুল পাতলা হয়ে যাওয়া, মাথার তালু গরম থাকা এবং মাথায় খুশকি ইত্যাদি সমস্যা গুলো দেখা দেয়।</p>
                    </div>
                     <div>
                        <h3 class="text-2xl font-semibold mb-3 font-anek text-gray-900">Hair Code যেভাবে কাজ করে :</h3>
                        <p>স্থায়ীভাবে চুল পড়া বন্ধ এবং নতুন চুল গজানোর জন্য Hair Code ব্যাবহার করুন। এটিতে রয়েছে DHT Anti Blocker System যা নেগেটিভ পদার্থকে পুরোপুরি দূর করে দিয়ে চুলের কোষকে ডেমেজ হওয়া থেকে রক্ষা করে,ফলে চুল পড়া স্থায়ীভাবে বন্ধ করে এবং নতুন চুল গজায়। মাথা ঠান্ডা রাখে ও খুশকি দূর করে।</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- NEW: FAQ Section --><section class="py-16 md:py-24 bg-white">
            <div class="container mx-auto px-4">
                 <h2 class="section-title gradient-text font-anek">আপনার মনে প্রশ্ন থাকতে পারে</h2>
                 <div class="max-w-4xl mx-auto space-y-8 text-lg font-hind">
                    <!-- FAQ 1 --><div class="bg-gray-50 p-6 rounded-lg border-l-4 border-blue-600 shadow-sm">
                        <h3 class="text-xl font-semibold mb-3 font-anek text-gray-900">কোনো সাইড ইফেক্ট আছে কিনা?</h3>
                        <p class="text-gray-700">সাধারণত সাইড ইফেক্ট থাকে ক্যামিকেল বা ড্রাগ জাতীয় ঔষধে।এটি সম্পুর্ন প্রাকৃতিক উপাদানে তৈরি। কোন মেডিসিন বা ক্যামিকেল নেই। এটি সম্পূর্ণ সাইড ইফেক্ট মুক্ত,কোনো ধরনের ক্ষতি হবেনা, শতভাগ রিস্ক ফ্রি।</p>
                    </div>
                    <!-- FAQ 2 --><div class="bg-gray-50 p-6 rounded-lg border-l-4 border-blue-600 shadow-sm">
                        <h3 class="text-xl font-semibold mb-3 font-anek text-gray-900">স্থায়ীভাবে সমাধানের জন্য কতোদিন ব্যাবহার করতে হবে?</h3>
                        <p class="text-gray-700">এটি দুই মাসের কোর্স। ১৫ দিনের মধ্যে DHT কে ব্লক করে চুল পড়া বন্ধ করে, এবং এক মাসের মধ্যে নতুন চুল গজানো শুরু হয়। স্থায়িত্বের জন্য দুই মাস ব্যাবহার করতে হবে।</p>
                    </div>
                 </div>
            </div>
        </section>

        <!-- Customer Reviews Section --><section class="py-16 md:py-24 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="section-title gradient-text font-anek">আমাদের কাস্টমাররা যা বলছেন</h2>
                <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if (empty($reviews)): ?>
                        <p class="text-center text-gray-500 col-span-3">এখনো কোনো রিভিউ যোগ করা হয়নি।</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col border-t-4 border-blue-600">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-xl mr-4 flex-shrink-0">
                                        <?php echo htmlspecialchars($review['image_initials']); ?>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 font-anek"><?php echo htmlspecialchars($review['name']); ?></h3>
                                        <div class="text-yellow-400 text-sm">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <?php if ($i < $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 text-sm flex-grow font-hind font-semibold">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                                 <div class="mt-4 pt-4 border-t border-gray-200 flex items-center text-xs text-green-600 font-semibold">
                                     <i class="fas fa-check-circle mr-2"></i>
                                     <span>Verified Customer</span>
                                 </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-12">
                    <a href="reviews.php" class="cta-button inline-block bg-white hover:bg-gray-100 text-blue-700 border border-blue-700 font-bold py-3 px-8 rounded-lg text-lg transition-all font-anek">
                        সকল রিভিউ দেখুন
                    </a>
                </div>
            </div>
        </section>

        <!-- Order Form Section --><section id="order-form" class="bg-white py-16 md:py-24">
             <div class="container mx-auto px-4">
                 <div class="max-w-3xl mx-auto text-center mb-12">
                      <h2 class="section-title gradient-text font-anek">আজই অর্ডার করুন!</h2>
                      <p class="text-xl text-gray-600 -mt-8 font-hind">বিশেষ মূল্য <del class="text-red-500">৳2500</del> এখন মাত্র <span class="text-blue-700 font-bold text-2xl">৳1460</span></p>
                 </div>
                
                 <?php if ($error_message): ?>
                     <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 max-w-4xl mx-auto" role="alert">
                         <strong class="font-semibold">Error!</strong>
                         <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                     </div>
                 <?php endif; ?>

                <form id="billing-form" action="process-order.php" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <input type="hidden" name="product_name" value="Hair Code usa 50ml × 1">

                    <!-- Left Column: Billing Details --><div class="bg-gray-50 p-6 md:p-8 rounded-lg shadow-lg border border-gray-200">
                        <h3 class="text-2xl font-semibold mb-6 text-gray-900 font-anek">আপনার ঠিকানা দিন</h3>
                        <div class="space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 font-hind font-semibold">আপনার নাম *</label>
                                <input type="text" id="name" name="customer_name" required class="block w-full border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:ring-blue-500 focus:border-blue-500 font-hind">
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1 font-hind font-semibold">আপনার সম্পূর্ণ ঠিকানা *</label>
                                <input type="text" id="address" name="customer_address" required class="block w-full border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:ring-blue-500 focus:border-blue-500 font-hind">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 font-hind font-semibold">আপনার মোবাইল নাম্বার *</label>
                                <input type="tel" id="phone" name="customer_phone" required class="block w-full border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:ring-blue-500 focus:border-blue-500 font-hind">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Your Order --><div class="bg-gray-50 p-6 md:p-8 rounded-lg shadow-lg border border-gray-200">
                        <h3 class="text-2xl font-semibold mb-6 text-gray-900 font-anek">আপনার অর্ডার</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-4">
                                <div class="flex items-center">
                                    <!-- UPDATED with local image --><img src="FEGG.webp" alt="Product Image" class="rounded-lg mr-4 border w-16 h-16 object-cover">
                                    <div>
                                        <p class="font-semibold font-anek">Hair Code</p>
                                        <p class="text-sm text-gray-500 font-hind">Quantity: 1</p>
                                    </div>
                                </div>
                                <span id="product-price" class="font-semibold text-lg font-hind">৳ 1460</span>
                            </div>

                            <div class="pt-4 space-y-2 font-hind">
                                <div class="flex justify-between items-center text-gray-600">
                                    <span class="font-semibold">Subtotal</span>
                                    <span id="subtotal-price" class="font-semibold">৳ 1460</span>
                                </div>
                                 <div class="flex items-center justify-between text-gray-600">
                                      <label for="shipping-dhaka" class="flex items-center cursor-pointer font-semibold">
                                           <input type="radio" id="shipping-dhaka" name="shipping_cost" value="70" class="h-4 w-4 text-blue-600 focus:ring-blue-500" checked>
                                           <span class="ml-2">ডেলিভারি চার্জ (ঢাকার মধ্যে)</span>
                                      </label>
                                      <span class="font-semibold">৳ 70</span>
                                 </div>
                                 <div class="flex items-center justify-between text-gray-600">
                                      <label for="shipping-outside-dhaka" class="flex items-center cursor-pointer font-semibold">
                                           <input type="radio" id="shipping-outside-dhaka" name="shipping_cost" value="120" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                           <span class="ml-2">ডেলিভারি চার্জ (ঢাকার বাইরে)</span>
                                      </label>
                                      <span class="font-semibold">৳ 120</span>
                                 </div>
                            </div>
                             <div class="border-b border-gray-200 pt-4"></div>
                            <div class="flex justify-between items-center pt-4 text-xl font-semibold text-gray-900 font-hind">
                                <span class="font-semibold">সর্বমোট</span>
                                <span id="total-price" class="text-blue-700 font-semibold">৳ 1530</span>
                            </div>
                        </div>
                        
                        <button type="submit" id="submit-button" class="cta-button w-full bg-blue-700 text-white font-bold py-4 px-6 rounded-lg mt-8 text-xl flex items-center justify-center shadow-lg hover:bg-blue-800 font-anek h-[60px]">
                            <span id="button-text">অর্ডার কনফার্ম করুন</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

    <!-- Footer --><footer class="text-center py-8 bg-gray-800 text-gray-300">
        <p>&copy; <?php echo date("Y"); ?> Hair Code. All Rights Reserved.</p>
    </footer>
    
    <!-- Floating WhatsApp button --><a href="https://wa.me/8801234567890?text=I'm%20interested%20in%20the%20Hair%20Code" class="whatsapp-float" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
    </a>
    
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Initialize Swiper ---
            const swiper = new Swiper('.swiper', {
                loop: true,
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });

            // --- Smooth Scrolling for Anchor Links ---
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // --- Order Form Price Calculation ---
            const productPrice = 1460;
            const shippingOptions = document.querySelectorAll('input[name="shipping_cost"]');
            const subtotalPriceEl = document.getElementById('subtotal-price');
            const totalPriceEl = document.getElementById('total-price');

            function updateTotalPrice() {
                const selectedShipping = document.querySelector('input[name="shipping_cost"]:checked');
                const shippingCost = parseInt(selectedShipping.value, 10);
                const total = productPrice + shippingCost;
                
                subtotalPriceEl.textContent = `৳ ${productPrice}`;
                totalPriceEl.textContent = `৳ ${total}`;
            }
            
            updateTotalPrice(); // Initial calculation

            shippingOptions.forEach(option => {
                option.addEventListener('change', updateTotalPrice);
            });
        });
    </script>
</body>
</html>


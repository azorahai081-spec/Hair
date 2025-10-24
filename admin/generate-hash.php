<?php
$hash_to_display = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_password'])) {
    // A password was submitted, so we generate a new hash.
    // PASSWORD_DEFAULT is the recommended modern and secure algorithm.
    $new_password = $_POST['new_password'];
    $hash_to_display = password_hash($new_password, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password Hash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hash-output {
            word-break: break-all;
            -webkit-user-select: all; /* Chrome, Safari, Opera */
            -moz-user-select: all;    /* Firefox */
            -ms-user-select: all;     /* Internet Explorer/Edge */
            user-select: all;         /* Standard */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Password Hash Generator</h2>
        <p class="text-center text-gray-500 mb-6">Use this tool to create a secure hash for your `config.php` file.</p>
        
        <form method="POST" action="generate-hash.php" class="mb-6">
            <div class="mb-4">
                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">Enter New Password:</label>
                <input type="text" id="new_password" name="new_password" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                Generate Hash
            </button>
        </form>

        <?php if (!empty($hash_to_display)): ?>
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800">Generated Hash:</h3>
                <p class="text-sm text-gray-600">Copy the text below and paste it into your `admin/config.php` file as the `ADMIN_PASS_HASH` value.</p>
                <div class="mt-2 p-4 bg-gray-100 rounded border border-gray-300">
                    <code class="text-gray-800 hash-output"><?php echo htmlspecialchars($hash_to_display); ?></code>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="login.php" class="text-blue-600 hover:underline">&larr; Back to Login</a>
        </div>
    </div>

</body>
</html>

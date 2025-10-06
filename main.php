<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Fetch wallet and spent balance
$stmt = $pdo->prepare("SELECT wallet_balance, spent_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$wallet = $user['wallet_balance'] ?? 0;
$spent = $user['spent_balance'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instaboost Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Top Navbar -->
<nav class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white shadow-lg p-4 flex justify-between items-center">
  <h1 class="text-2xl font-bold tracking-wide">🚀 Instaboost</h1>
  <div class="flex items-center gap-4">
    <span class="text-lg font-medium">👋 <?= htmlspecialchars($user_name) ?></span>
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md text-sm font-semibold transition">Logout</a>
  </div>
</nav>

<!-- Dashboard Cards -->
<section class="p-6 grid md:grid-cols-3 gap-6 max-w-6xl mx-auto mt-8">

  <!-- Welcome Card -->
  <div class="bg-white p-6 rounded-2xl shadow-md text-center border border-gray-100">
    <div class="text-blue-600 text-4xl mb-2">👤</div>
    <h2 class="text-lg font-semibold"><?= htmlspecialchars($user_name) ?></h2>
    <p class="text-gray-500">Welcome to Instaboost Panel</p>
  </div>

  <!-- Spent Balance -->
  <div class="bg-white p-6 rounded-2xl shadow-md text-center border border-gray-100">
    <div class="text-green-600 text-4xl mb-2">💸</div>
    <h2 class="text-lg font-semibold">₹<?= number_format($spent, 2) ?></h2>
    <p class="text-gray-500">Spent Balance</p>
  </div>

  <!-- Wallet Balance -->
  <div class="bg-white p-6 rounded-2xl shadow-md text-center border border-gray-100 relative">
    <div class="text-indigo-600 text-4xl mb-2">💰</div>
    <h2 class="text-lg font-semibold">₹<?= number_format($wallet, 2) ?></h2>
    <p class="text-gray-500">My Wallet</p>
    <a href="add_funds.php" class="absolute top-4 right-4 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm">+ Add Funds</a>
  </div>
</section>

<!-- Instagram Order Form -->
<section class="max-w-3xl mx-auto bg-white p-8 mt-8 rounded-2xl shadow-lg border border-gray-100">
  <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">📱 Instagram Services</h2>

  <form method="POST" action="place_order.php" class="space-y-5">
    <div>
      <label class="block text-gray-700 font-semibold mb-1">Category</label>
      <select name="category" class="w-full border-gray-300 rounded-lg p-3 focus:ring focus:ring-indigo-200">
        <option selected>INSTAGRAM</option>
      </select>
    </div>

    <div>
      <label class="block text-gray-700 font-semibold mb-1">Service</label>
      <select name="service_id" class="w-full border-gray-300 rounded-lg p-3 focus:ring focus:ring-indigo-200" required>
        <option value="497">497 - Instagram Likes (Post & Reels)</option>
        <option value="501">501 - Instagram Followers (Real)</option>
        <option value="509">509 - Instagram Views (Instant)</option>
      </select>
    </div>

    <div>
      <label class="block text-gray-700 font-semibold mb-1">Link</label>
      <input type="url" name="link" placeholder="https://www.instagram.com/p/..." required
        class="w-full border border-gray-300 rounded-lg p-3 focus:ring focus:ring-indigo-200">
    </div>

    <div>
      <label class="block text-gray-700 font-semibold mb-1">Quantity</label>
      <input type="number" name="quantity" min="10" max="100000" required
        class="w-full border border-gray-300 rounded-lg p-3 focus:ring focus:ring-indigo-200">
      <p class="text-sm text-gray-500 mt-1">Min: 10 - Max: 100,000</p>
    </div>

    <div>
      <label class="block text-gray-700 font-semibold mb-1">Charge</label>
      <input type="text" name="charge" value="₹0.50" readonly
        class="w-full border border-gray-300 rounded-lg p-3 bg-gray-100 cursor-not-allowed">
    </div>

    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl text-lg font-semibold hover:opacity-90 transition">
      Submit Order
    </button>
  </form>
</section>

<footer class="text-center text-gray-500 text-sm mt-10 mb-6">
  © <?= date('Y') ?> Instaboost — All Rights Reserved.
</footer>

</body>
</html>
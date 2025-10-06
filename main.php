<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT full_name, wallet_balance, spent_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

$name = htmlspecialchars($user['full_name']);
$wallet_balance = number_format($user['wallet_balance'], 2);
$spent_balance = number_format($user['spent_balance'], 2);

// Fetch TNT SMM API key
$stmt = $pdo->query("SELECT api_key FROM api_keys WHERE status='active' ORDER BY id DESC LIMIT 1");
$tnt_api_key = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InstaBoost Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
}
.card {
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<!-- Header -->
<header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
  <h1 class="text-2xl font-bold text-indigo-700">Insta<span class="text-pink-500">Boost</span></h1>
  <form method="POST" action="logout.php">
    <button class="text-red-500 hover:text-red-600 font-semibold">Logout</button>
  </form>
</header>

<!-- Dashboard -->
<main class="max-w-6xl mx-auto mt-10 px-4">
  <div class="grid md:grid-cols-3 gap-6 mb-8">

    <!-- Profile -->
    <div class="card bg-white p-6 rounded-xl shadow text-center">
      <div class="bg-indigo-100 p-3 w-12 h-12 mx-auto rounded-full mb-3 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 6.195M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
      <h2 class="font-bold text-lg"><?= $name ?></h2>
      <p class="text-gray-500 text-sm">Welcome to InstaBoost!</p>
    </div>

    <!-- Spent Balance -->
    <div class="card bg-white p-6 rounded-xl shadow text-center">
      <div class="bg-red-100 p-3 w-12 h-12 mx-auto rounded-full mb-3 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m12 7H9" />
        </svg>
      </div>
      <h2 class="font-bold text-lg text-red-500">₹<?= $spent_balance ?></h2>
      <p class="text-gray-500 text-sm">Total Spent</p>
    </div>

    <!-- Wallet Balance -->
    <div class="card bg-white p-6 rounded-xl shadow text-center relative">
      <div class="bg-green-100 p-3 w-12 h-12 mx-auto rounded-full mb-3 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="font-bold text-lg text-green-600">₹<?= $wallet_balance ?></h2>
      <p class="text-gray-500 text-sm">Wallet Balance</p>
      <a href="add_funds.php" class="absolute top-4 right-4 bg-orange-500 hover:bg-orange-600 text-white text-sm px-3 py-1 rounded-lg transition">
        + Add Fund
      </a>
    </div>

  </div>

  <!-- Instagram Order Section -->
  <div class="bg-white p-8 rounded-2xl shadow-lg mb-12">
    <h2 class="text-xl font-bold mb-6 text-indigo-600">📸 Instagram Orders</h2>
    
    <form method="POST" action="create_order.php" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Service Type</label>
        <select name="service" class="w-full border rounded p-3 bg-gray-50" required>
          <option value="1">Instagram Followers</option>
          <option value="2">Instagram Likes</option>
          <option value="3">Instagram Comments</option>
        </select>
      </div>

      <div>
        <label class="block font-semibold mb-1">Post/Profile Link</label>
        <input type="url" name="link" placeholder="https://www.instagram.com/username/" required class="w-full border p-3 rounded bg-gray-50">
      </div>

      <div>
        <label class="block font-semibold mb-1">Quantity</label>
        <input type="number" name="quantity" min="50" max="10000" placeholder="Enter Quantity" required class="w-full border p-3 rounded bg-gray-50">
      </div>

      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition">
        Place Order
      </button>
    </form>
  </div>

  <!-- Footer -->
  <footer class="text-center text-gray-500 text-sm py-6">
    © <?= date('Y') ?> <span class="font-semibold text-indigo-600">InstaBoost</span>. All rights reserved.
  </footer>
</main>

</body>
</html>
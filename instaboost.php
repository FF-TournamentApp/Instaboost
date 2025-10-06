<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT full_name, wallet_balance, spent_balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found!");

$wallet = number_format($user['wallet_balance'], 2);
$spent = number_format($user['spent_balance'], 2);
$name = htmlspecialchars($user['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instaboost</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  background: linear-gradient(135deg, #ffffff, #e0e7ff);
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}
.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
</head>
<body class="text-gray-800">

<!-- Navbar -->
<header class="flex items-center justify-between bg-white px-6 py-4 shadow-md">
  <h1 class="text-2xl font-bold text-blue-700">Instaboost<span class="text-black">PANNEL</span></h1>
  <form method="POST" action="logout.php">
    <button class="text-red-500 font-semibold hover:text-red-600 transition">Logout</button>
  </form>
</header>

<!-- Dashboard Container -->
<main class="max-w-5xl mx-auto mt-10 px-4">
  
  <!-- User Info -->
  <div class="grid md:grid-cols-3 gap-6">
    
    <!-- Profile Card -->
    <div class="card bg-white rounded-xl p-5 shadow text-center">
      <div class="flex justify-center mb-2">
        <div class="bg-blue-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 6.195M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
      </div>
      <h2 class="font-bold text-lg"><?= $name ?></h2>
      <p class="text-sm text-gray-500">We Welcome You Instaboost</p>
    </div>

    <!-- Spent Balance Card -->
    <div class="card bg-white rounded-xl p-5 shadow text-center">
      <div class="flex justify-center mb-2">
        <div class="bg-blue-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m12 7H9" />
          </svg>
        </div>
      </div>
      <h2 class="font-bold text-lg">₹<?= $spent ?></h2>
      <p class="text-sm text-gray-500">Spent Balance</p>
    </div>

    <!-- My Balance Card -->
    <div class="card bg-white rounded-xl p-5 shadow text-center relative">
      <div class="flex justify-center mb-2">
        <div class="bg-blue-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <h2 class="font-bold text-lg">₹<?= $wallet ?></h2>
      <p class="text-sm text-gray-500">My Balance</p>
      <a href="add_funds.php" class="absolute top-4 right-4 bg-orange-500 hover:bg-orange-600 text-white text-sm px-3 py-1 rounded-lg transition">Add Fund</a>
    </div>

  </div>

  <!-- Instagram Section -->
  <section class="mt-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4">📸 Instagram Orders</h2>
    <div class="bg-white p-6 rounded-2xl shadow-md">
      <form method="POST" action="create_order.php" class="space-y-4">
        <div>
          <label class="block font-semibold mb-1">Category</label>
          <select class="w-full border rounded p-3 bg-gray-50" required>
            <option value="INSTAGRAM">📷 Instagram</option>
          </select>
        </div>

        <div>
          <label class="block font-semibold mb-1">Service</label>
          <select class="w-full border rounded p-3 bg-gray-50" name="service" required>
            <option value="497">497 - INSTAGRAM LIKES FOR POST AND REEL [ACTIVE USERS]</option>
          </select>
        </div>

        <div>
          <label class="block font-semibold mb-1">Link</label>
          <input type="url" name="link" placeholder="https://www.instagram.com/post/..." required class="w-full border p-3 rounded bg-gray-50">
        </div>

        <div>
          <label class="block font-semibold mb-1">Quantity</label>
          <input type="number" name="quantity" min="100" max="1000000" placeholder="Enter Quantity" required class="w-full border p-3 rounded bg-gray-50">
        </div>

        <div>
          <label class="block font-semibold mb-1">Charge</label>
          <input type="text" name="charge" value="₹0.5" readonly class="w-full border p-3 rounded bg-gray-100">
        </div>

        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-semibold transition">Submit Order</button>
      </form>
    </div>
  </section>

</main>

</body>
</html>
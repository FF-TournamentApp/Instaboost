<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Failed | Instaboost</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { background: linear-gradient(135deg, #dc2626, #7f1d1d); font-family: 'Poppins', sans-serif; }
  .popup { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; color: #fff; text-align: center; }
  .crossmark { width: 100px; height: 100px; border-radius: 50%; border: 6px solid #ef4444; display: flex; justify-content: center; align-items: center; position: relative; margin-bottom: 20px; background: white; box-shadow: 0 0 30px rgba(239,68,68,0.5); }
  .crossmark::before, .crossmark::after { content: ""; position: absolute; width: 60px; height: 6px; background: #ef4444; border-radius: 5px; transform: scale(0); animation: crossDraw 0.4s 0.3s ease forwards; }
  .crossmark::before { transform: rotate(45deg) scale(0); }
  .crossmark::after { transform: rotate(-45deg) scale(0); }
  @keyframes crossDraw { to { transform: rotate(45deg) scale(1); } }
</style>
</head>
<body>
  <div class="popup">
    <div class="crossmark"></div>
    <h1 class="text-3xl font-bold mb-2">Payment Failed ❌</h1>
    <p class="text-lg text-gray-200">Something went wrong. Your payment was not successful.</p>
    <a href="add_funds.php" class="mt-6 bg-white text-red-600 hover:bg-gray-100 px-6 py-3 rounded-xl font-semibold shadow-md transition">Try Again</a>
  </div>
</body>
</html>
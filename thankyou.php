<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die("❌ Invalid payment callback - no order ID received.");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        die("❌ Transaction not found for this user.");
    }

    if ($txn['status'] !== 'success') {
        $amount = (float)$txn['amount'];

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?")
            ->execute([$amount, $user_id]);
        $pdo->prepare("UPDATE transactions SET status = 'success' WHERE id = ?")
            ->execute([$txn['id']]);
        $pdo->commit();
    } else {
        $amount = $txn['amount'];
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Success | Instaboost</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { background: linear-gradient(135deg, #4f46e5, #9333ea); font-family: 'Poppins', sans-serif; }
  .popup { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; color: #fff; text-align: center; }
  .checkmark { width: 100px; height: 100px; border-radius: 50%; border: 6px solid #22c55e; display: flex; justify-content: center; align-items: center; position: relative; margin-bottom: 20px; animation: pop 0.5s ease forwards; background: white; box-shadow: 0 0 30px rgba(34, 197, 94, 0.5); }
  .checkmark::after { content: ""; position: absolute; width: 30px; height: 60px; border-right: 6px solid #22c55e; border-bottom: 6px solid #22c55e; transform: rotate(45deg) scale(0); animation: drawCheck 0.4s 0.3s ease forwards; }
  @keyframes drawCheck { to { transform: rotate(45deg) scale(1); } } @keyframes pop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
</head>
<body>
  <div class="popup">
    <div class="checkmark"></div>
    <h1 class="text-3xl font-bold mb-2">Payment Successful 🎉</h1>
    <p class="text-lg text-gray-200">Thank you for your payment of <span class="font-semibold text-green-300">₹<?= number_format($amount, 2) ?></span></p>
    <p class="text-gray-300 mt-2">Your wallet has been credited successfully!</p>
    <a href="main.php" class="mt-6 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold shadow-md transition">Go to Dashboard</a>
  </div>
</body>
</html>
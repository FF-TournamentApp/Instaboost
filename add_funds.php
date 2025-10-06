<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// ✅ Fetch latest active API key dynamically
$stmt = $pdo->query("SELECT api_key FROM api_keys WHERE status='active' ORDER BY id DESC LIMIT 1");
$active_key = $stmt->fetchColumn();

if (!$active_key) {
    die("⚠️ No active KhilaadixPro API key found in database. Please add one from admin panel.");
}

// 💳 When user submits amount
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    if ($amount <= 0) {
        $errors[] = "Please enter a valid amount.";
    } else {
        $order_id = uniqid("ORD");
        
        // ✅ Correct redirect URL for your InfinityFree site
        $redirect_url = "https://tournamentapp.infinityfree.me/smm/fund_callback.php";

        $customer_mobile = "9999999999"; // optional user phone
        $remark1 = "Add Fund for User: $user_id";
        $remark2 = "Panel Recharge";

        // Prepare payload for KhilaadixPro API
        $payload = [
            "customer_mobile" => $customer_mobile,
            "user_token" => $active_key,
            "amount" => $amount,
            "order_id" => $order_id,
            "redirect_url" => $redirect_url,
            "remark1" => $remark1,
            "remark2" => $remark2,
            "route" => "1"
        ];

        // cURL request
        $ch = curl_init("https://khilaadixpro.shop/api/create-order");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (!$result) {
            $errors[] = "Payment server not responding. Please try again later.";
        } elseif ($result['status'] === true) {
            $payment_url = $result['result']['payment_url'];

            // 🧾 Store pending transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, order_id, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $order_id, $amount]);

            // Redirect user to payment page
            header("Location: " . $payment_url);
            exit;
        } else {
            $errors[] = "Payment creation failed: " . ($result['message'] ?? 'Unknown error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Funds</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 flex items-center justify-center min-h-screen">

<div class="bg-white text-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-md">
  <h2 class="text-2xl font-bold text-center text-indigo-600 mb-4">💸 Add Funds to Wallet</h2>

  <?php if ($errors): ?>
  <div class="bg-red-100 text-red-700 p-3 mb-3 rounded">
    <?php foreach ($errors as $e): ?>
      <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-semibold mb-1">Enter Amount (₹)</label>
      <input 
        type="number" 
        step="1" 
        name="amount" 
        placeholder="e.g. 100" 
        required
        class="w-full p-3 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-400"
      >
    </div>

    <button 
      type="submit" 
      class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition">
      Proceed to Pay
    </button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-4">
    You’ll be redirected to KhilaadixPro to complete payment.
  </p>
</div>

</body>
</html>
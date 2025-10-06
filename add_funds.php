<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$debug_mode = false; // ✅ set true to view debug info

try {
    // ✅ Fetch latest active KhilaadixPro API key
    $stmt = $pdo->query("SELECT api_key FROM api_keys WHERE status='active' ORDER BY id DESC LIMIT 1");
    $active_key = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("❌ Database error while fetching API key: " . $e->getMessage());
}

if (!$active_key) {
    die("⚠️ No active KhilaadixPro API key found in database. Please add one from admin panel.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $errors[] = "Please enter a valid amount.";
    } else {
        $order_id = uniqid("ORD");
        $redirect_url = "https://instaboost-59k0.onrender.com/thankyou.php?order_id=$order_id";
        $customer_mobile = "9876543210"; // ✅ Use valid 10-digit Indian mobile
        $remark1 = "Add Fund for User: $user_id";
        $remark2 = "Instaboost Wallet Recharge";

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

        // ✅ cURL setup
        $ch = curl_init("https://khilaadixpro.shop/api/create-order");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: InstaboostBot/1.0'
            ]
        ]);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_error) {
            $errors[] = "❌ cURL Error: " . $curl_error;
        } else {
            $result = json_decode($response, true);

            if (empty($response)) {
                $errors[] = "⚠️ No response received from API. Try again later.";
            } elseif (!$result) {
                $errors[] = "⚠️ Unable to decode API response: " . htmlspecialchars($response);
            } elseif (isset($result['status']) && $result['status'] === true) {
                // ✅ Success: Payment order created
                $payment_url = $result['result']['payment_url'];

                try {
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, order_id, amount, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->execute([$user_id, $order_id, $amount]);
                } catch (PDOException $e) {
                    $errors[] = "❌ Database error while saving transaction: " . $e->getMessage();
                }

                header("Location: " . $payment_url);
                exit;
            } else {
                $errors[] = "🚫 Payment creation failed: " . ($result['message'] ?? 'Unknown error');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Funds | Instaboost</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 flex items-center justify-center min-h-screen">

<div class="bg-white text-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-md">
  <h2 class="text-2xl font-bold text-center text-indigo-600 mb-4">💸 Add Funds to Wallet</h2>

  <?php if ($errors): ?>
  <div class="bg-red-100 text-red-700 p-3 mb-3 rounded">
    <?php foreach ($errors as $e): ?>
      <div>⚠️ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-semibold mb-1">Enter Amount (₹)</label>
      <input type="number" step="1" name="amount" placeholder="e.g. 100" required
        class="w-full p-3 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-400">
    </div>

    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition">
      Proceed to Pay
    </button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-4">You’ll be redirected to the payment gateway to complete your transaction.</p>

  <?php if ($debug_mode && isset($response)): ?>
  <div class="mt-6 bg-gray-50 p-3 rounded text-xs text-gray-700 overflow-auto">
    <h3 class="font-semibold mb-1">🔍 Debug Info</h3>
    <pre><?php
    echo "HTTP Code: " . ($http_code ?? 'N/A') . "\n";
    echo "Response:\n" . htmlspecialchars($response) . "\n\n";
    echo "Payload:\n";
    print_r($payload);
    ?></pre>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
<?php
require 'config.php';
session_start();

// 1️⃣ Try to get order ID from redirect or POST
$order_id = $_GET['order_id'] ?? ($_POST['order_id'] ?? '');
$user_id  = $_SESSION['user_id'] ?? null;

// If missing, try to find last pending transaction
if (!$order_id && $user_id) {
    $stmt = $pdo->prepare("SELECT order_id FROM transactions WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $order_id = $stmt->fetchColumn();
}

if (!$order_id) {
    die("<h3 style='color:red;text-align:center;margin-top:30px;'>❌ Invalid payment callback - no order ID found.</h3>");
}

// 2️⃣ Get active KhilaadixPro API key
$stmt = $pdo->query("SELECT api_key FROM api_keys WHERE status='active' ORDER BY id DESC LIMIT 1");
$user_token = $stmt->fetchColumn();

if (!$user_token) {
    die("<h3 style='color:red;text-align:center;'>❌ No active API key found.</h3>");
}

// 3️⃣ Check payment status via API
$payload = [
    "user_token" => $user_token,
    "order_id"   => $order_id
];

$ch = curl_init("https://khilaadixpro.shop/api/check-order-status");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// 4️⃣ Validate API response
if (!$result || $result['status'] !== true) {
    die("<h3 style='color:red;text-align:center;'>❌ Payment verification failed.<br>" . htmlspecialchars($result['message'] ?? "Unknown error") . "</h3>");
}

$txnStatus = strtoupper($result['result']['txnStatus']);
$amount    = floatval($result['result']['amount'] ?? 0);

// 5️⃣ Check if transaction exists
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
$stmt->execute([$order_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    die("<h3 style='color:red;text-align:center;'>❌ Transaction not found in your database.</h3>");
}

// 6️⃣ Update wallet on success
if ($txnStatus === 'SUCCESS' && $transaction['status'] !== 'success') {
    $pdo->beginTransaction();
    try {
        // Mark transaction successful
        $pdo->prepare("UPDATE transactions SET status = 'success' WHERE order_id = ?")->execute([$order_id]);

        // Add balance to wallet
        $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?")
            ->execute([$transaction['amount'], $transaction['user_id']]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("<h3 style='color:red;text-align:center;'>❌ Database update failed: {$e->getMessage()}</h3>");
    }

    echo "
    <div style='text-align:center;margin-top:50px;font-family:sans-serif;'>
        <h2 style='color:green;'>✅ Payment Successful!</h2>
        <p style='font-size:18px;'>₹{$transaction['amount']} has been added to your wallet.</p>
        <a href='main.php' style='
            display:inline-block;
            margin-top:20px;
            background:#4f46e5;
            color:white;
            padding:10px 20px;
            border-radius:8px;
            text-decoration:none;'>Return to Dashboard</a>
    </div>";
} else {
    echo "
    <div style='text-align:center;margin-top:50px;font-family:sans-serif;'>
        <h2 style='color:orange;'>⚠️ Payment Pending or Already Credited</h2>
        <p>Status: $txnStatus</p>
        <a href='main.php' style='
            display:inline-block;
            margin-top:20px;
            background:#4f46e5;
            color:white;
            padding:10px 20px;
            border-radius:8px;
            text-decoration:none;'>Go Back to Dashboard</a>
    </div>";
}
?>
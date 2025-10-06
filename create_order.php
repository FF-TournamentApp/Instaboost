<?php
session_start();
require 'config.php';
require 'tntsmm_api.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors  = [];

// Fetch user wallet
$stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// Fetch active TNT SMM API key
$stmt = $pdo->query("SELECT api_key FROM api_keys WHERE name='tntsmm' AND status='active' ORDER BY id DESC LIMIT 1");
$api_key = $stmt->fetchColumn();
if (!$api_key) die("No active TNT SMM API key found.");

// Validate form
$service  = (int)($_POST['service'] ?? 0);
$link     = trim($_POST['link'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 0);

if ($service <= 0 || !$link || $quantity <= 0) {
    die("<h3 style='color:red;text-align:center;'>❌ Invalid order data</h3>");
}

// ---- You can fetch the real service rate via API if you wish ----
$rate_per_1k = 1.0; // fallback rate
$cost = ($rate_per_1k / 1000) * $quantity;

if ($user['wallet_balance'] < $cost) {
    die("<h3 style='color:red;text-align:center;'>❌ Insufficient wallet balance</h3>");
}

// Create TNT SMM order
$api = new Api();
$api->api_key = $api_key;

$response = $api->order([
    'service'  => $service,
    'link'     => $link,
    'quantity' => $quantity
]);

if (!$response || empty($response->order)) {
    die("<h3 style='color:red;text-align:center;'>❌ Order failed<br>Check service ID or API key.</h3>");
}

$order_id = $response->order;

// Deduct wallet & record order
$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?")
        ->execute([$cost, $user_id]);
    $pdo->prepare("INSERT INTO smm_orders (user_id, tnt_order_id, service_id, link, quantity, cost, status)
                   VALUES (?, ?, ?, ?, ?, ?, 'Processing')")
        ->execute([$user_id, $order_id, $service, $link, $quantity, $cost]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("<h3 style='color:red;text-align:center;'>DB Error: {$e->getMessage()}</h3>");
}

echo "
<div style='text-align:center;margin-top:50px;font-family:sans-serif;'>
  <h2 style='color:green;'>✅ Order Placed Successfully!</h2>
  <p>Order ID: $order_id<br>Amount Charged: ₹" . number_format($cost,2) . "</p>
  <a href='main.php' style='display:inline-block;margin-top:20px;background:#4f46e5;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Return to Dashboard</a>
</div>";
?>
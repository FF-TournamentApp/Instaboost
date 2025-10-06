<?php
session_start();
require 'config.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Get user wallet
$stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$wallet_balance = (float)$user['wallet_balance'];

// ✅ Fetch active TNT SMM API key
$stmt = $pdo->query("SELECT api_key FROM api_keys WHERE status='active' ORDER BY id DESC LIMIT 1");
$tnt_api_key = $stmt->fetchColumn();

if (!$tnt_api_key) {
    die("⚠️ No active TNT SMM API key found in database. Please add one in admin panel.");
}

// ✅ Validate form input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = intval($_POST['service_id']);
    $link = trim($_POST['link']);
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0 || !$service_id || !$link) {
        die("❌ Invalid input.");
    }

    // 🔹 Calculate cost per 1000 (example rate)
    $rate_per_1000 = 0.90; // ₹ per 1000 units (example, update if you want dynamic)
    $cost = ($quantity / 1000) * $rate_per_1000;
    $cost = round($cost, 2);

    // Check wallet
    if ($wallet_balance < $cost) {
        die("<script>alert('❌ Insufficient balance! Please add funds.'); window.location.href='add_funds.php';</script>");
    }

    // ✅ Prepare order payload for TNT SMM API
    $postData = [
        'key' => $tnt_api_key,
        'action' => 'add',
        'service' => $service_id,
        'link' => $link,
        'quantity' => $quantity
    ];

    $ch = curl_init("https://tntsmm.in/api/v2");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        die("❌ cURL Error: $error");
    }

    $result = json_decode($response, true);

    // ✅ If order placed successfully
    if (isset($result['order'])) {
        $tnt_order_id = $result['order'];

        // Deduct wallet
        $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ?, spent_balance = spent_balance + ? WHERE id = ?")
            ->execute([$cost, $cost, $user_id]);

        // Save order record
        $stmt = $pdo->prepare("INSERT INTO smm_orders (user_id, tnt_order_id, service_id, link, quantity, cost, status)
                               VALUES (?, ?, ?, ?, ?, ?, 'Processing')");
        $stmt->execute([$user_id, $tnt_order_id, $service_id, $link, $quantity, $cost]);

        echo "
        <script>
          alert('✅ Order placed successfully! Order ID: $tnt_order_id');
          window.location.href='main.php';
        </script>";
    } else {
        $msg = $result['error'] ?? 'Unknown API error';
        echo "
        <script>
          alert('❌ Order failed: " . addslashes($msg) . "');
          window.location.href='main.php';
        </script>";
    }
} else {
    header("Location: main.php");
    exit;
}
?>
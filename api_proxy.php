<?php
// ======================================================
// Instaboost Secure Payment Proxy for InfinityFree
// Fix: Added SSL bypass to allow connection to KhilaadixPro
// ======================================================

header("Content-Type: application/json");

// ✅ Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Security secret (must match your InfinityFree add_funds.php)
$SECRET_KEY = "INSTA_PROXY_2025";
if (empty($_POST['secret']) || $_POST['secret'] !== $SECRET_KEY) {
    echo json_encode(["status" => false, "message" => "Unauthorized access"]);
    exit;
}

// ✅ Forward the request to KhilaadixPro API
$apiUrl = "https://khilaadixpro.shop/api/create-order";

$payload = $_POST;
unset($payload['secret']); // don't send proxy secret to API

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false, // 🚫 disable SSL verification
    CURLOPT_SSL_VERIFYHOST => false  // 🚫 ignore host mismatch
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ Return the API response or error
if ($response) {
    echo json_encode([
        "status" => true,
        "http_code" => $httpCode,
        "response" => json_decode($response, true)
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Proxy error: " . ($error ?: "Unknown error"),
        "http_code" => $httpCode
    ]);
}
?>
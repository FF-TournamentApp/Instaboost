<?php
// ======================================================
// Instaboost Payment Proxy for InfinityFree (KhilaadixPro)
// ======================================================

header("Content-Type: application/json");

// ✅ Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Optional: Security secret (same as in your InfinityFree)
$SECRET_KEY = "INSTA_PROXY_2025";

if (empty($_POST['secret']) || $_POST['secret'] !== $SECRET_KEY) {
    echo json_encode(["status" => false, "message" => "Unauthorized access"]);
    exit;
}

// ✅ Forward payload to KhilaadixPro API
$apiUrl = "https://khilaadixpro.shop/api/create-order";

$payload = $_POST;
unset($payload['secret']); // don’t send the proxy secret

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_TIMEOUT => 30
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
<?php
/**
 * Instaboost | KhilaadixPro API Tester
 * ------------------------------------
 * This script checks your API key, mobile number, and route.
 * It will tell you exactly what's failing: key, route, or connection.
 */

header('Content-Type: text/plain');

// ✅ CONFIGURATION
$api_key = "5341e4ffaa59ffffb61b635e398a6cea"; // your KhilaadixPro API key
$mobile  = "9759892238"; // put your real 10-digit number (no +91)
$amount  = "1"; // ₹1 test
$order_id = "TEST_" . uniqid();
$route = "1"; // try 1 or 2 if 1 fails

$redirect_url = "https://instaboost-59k0.onrender.com/thankyou.php?order_id=$order_id";

// ✅ Prepare payload
$payload = [
    "customer_mobile" => $mobile,
    "user_token" => $api_key,
    "amount" => $amount,
    "order_id" => $order_id,
    "redirect_url" => $redirect_url,
    "remark1" => "API Tester",
    "remark2" => "Instaboost Test",
    "route" => $route
];

// ✅ Setup cURL
$ch = curl_init("https://khilaadixpro.shop/api/create-order");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HEADER => true, // get headers
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: InstaboostTester/1.0'
    ]
]);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ Analyze
echo "==== Instaboost KhilaadixPro API Tester ====\n";
echo "Order ID: $order_id\n";
echo "HTTP Code: $http_code\n\n";

if ($curl_error) {
    echo "❌ cURL Error: $curl_error\n";
    exit;
}

if (!$response) {
    echo "⚠️ Empty response received. API might be blocking Render server or rejecting your IP.\n";
    exit;
}

list($headers, $body) = explode("\r\n\r\n", $response, 2);

echo "---- Response Headers ----\n";
echo trim($headers) . "\n\n";

echo "---- Raw Response ----\n";
echo trim($body) . "\n\n";

$json = json_decode($body, true);

if ($json) {
    echo "---- JSON Decoded ----\n";
    print_r($json);

    if (isset($json['status'])) {
        if ($json['status'] === true) {
            echo "\n✅ SUCCESS! Your API key and route are valid.\n";
            echo "Payment URL: " . $json['result']['payment_url'] . "\n";
        } else {
            echo "\n🚫 ERROR: " . $json['message'] . "\n";
            echo "💡 Hint: Check your API key, plan status, or route (1/2).\n";
        }
    }
} else {
    echo "⚠️ Unable to decode JSON. The API may be returning HTML or blank response.\n";
}
?>
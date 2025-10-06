<?php
$ch = curl_init("https://khilaadixpro.shop/api/create-order");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "test=1");
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if (!$response) {
    echo "❌ cURL not working: $err";
} else {
    echo "✅ cURL working. Response: " . htmlspecialchars($response);
}
?>
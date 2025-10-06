<?php
$ch = curl_init("https://khilaadixpro.shop/api/create-order");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "❌ cURL Error: " . curl_error($ch);
} else {
    echo "✅ cURL Working. Response: <pre>" . htmlspecialchars($response) . "</pre>";
}
curl_close($ch);
<?php
$host = "sql113.infinityfree.com";
$dbname = "if0_39868152_tnt_smm";
$username = "if0_39868152";
$password = "gwdevff88";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
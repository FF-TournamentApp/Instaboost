<?php
// Database configuration for FreeSQLDatabase.com

$host = "sql12.freesqldatabase.com";
$dbname = "sql12801557";
$username = "sql12801557";
$password = "ciujgR5HFV";
$port = 3306;

try {
    // ✅ Establish PDO connection
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // ✅ Set PDO attributes for error reporting
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Uncomment for connection test
    // echo "✅ Connected successfully to FreeSQLDatabase!";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
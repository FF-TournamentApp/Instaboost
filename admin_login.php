<?php
session_start();
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['username'];
    header("Location: admin_dashboard.php");
    exit;
  } else {
    $error = "Invalid username or password.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
  <form method="POST" class="bg-white text-gray-800 p-8 rounded-2xl shadow-lg w-full max-w-sm">
    <h2 class="text-2xl font-bold mb-4 text-center text-indigo-600">Admin Login</h2>
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-600 p-2 mb-3 rounded"><?= $error ?></div>
    <?php endif; ?>
    <input type="text" name="username" placeholder="Username" required class="w-full p-3 mb-3 border rounded">
    <input type="password" name="password" placeholder="Password" required class="w-full p-3 mb-4 border rounded">
    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg">Login</button>
  </form>
</body>
</html>
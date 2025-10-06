<?php
/*****************************************************
 * 🔐 Admin Reset Tool for TNT SMM System
 * Use only when locked out — delete after success!
 *****************************************************/

require 'config.php';

$secret_key = 'tntsmm#ishant';
$message = '';
$success = false;

// When the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_key = trim($_POST['secret']);
    if ($entered_key !== $secret_key) {
        $message = "❌ Invalid secret key!";
    } else {
        $username = 'Ishantadmin';
        $email = 'gwdevff88@gmail.com';
        $password_hash = password_hash('admin123', PASSWORD_BCRYPT);

        // Check if the email already exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing
            $update = $pdo->prepare("UPDATE admins SET username=?, password=? WHERE email=?");
            $update->execute([$username, $password_hash, $email]);
            $message = "✅ Admin updated successfully!<br>Username: <b>$username</b><br>Password: <b>admin123</b>";
            $success = true;
        } else {
            // Create new
            $create = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            $create->execute([$username, $email, $password_hash]);
            $message = "✅ New admin created successfully!<br>Username: <b>$username</b><br>Password: <b>admin123</b>";
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Admin - TNT SMM</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center min-h-screen text-white">

  <div class="bg-white text-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-indigo-600 mb-4">Reset Admin Access</h2>

    <?php if ($message): ?>
      <div class="<?= $success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?> p-3 mb-3 rounded text-center">
        <?= $message ?>
      </div>
      <?php if ($success): ?>
        <div class="text-center">
          <a href="admin_login.php" class="text-indigo-600 font-semibold hover:underline">Go to Login</a>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <p class="text-center text-gray-500 text-sm mb-2">Enter your secret key to reset admin credentials.</p>
        <input type="password" name="secret" placeholder="Secret Key" required class="w-full p-3 border rounded-lg">
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold transition">Reset Admin</button>
      </form>
    <?php endif; ?>
  </div>

</body>
</html>
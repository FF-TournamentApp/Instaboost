<?php
session_start();
require 'config.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin_login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_key'])) {
    $name = $_POST['name'];
    $api_key = $_POST['api_key'];
    $stmt = $pdo->prepare("INSERT INTO api_keys (name, api_key) VALUES (?, ?)");
    $stmt->execute([$name, $api_key]);
  }

  if (isset($_POST['activate'])) {
    $id = $_POST['id'];
    $pdo->query("UPDATE api_keys SET status='inactive'");
    $stmt = $pdo->prepare("UPDATE api_keys SET status='active' WHERE id=?");
    $stmt->execute([$id]);
  }

  if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM api_keys WHERE id=?");
    $stmt->execute([$id]);
  }
}

$keys = $pdo->query("SELECT * FROM api_keys ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - API Keys</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-indigo-600">🔑 API Key Manager</h1>
      <a href="logout_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Logout</a>
    </div>

    <form method="POST" class="mb-6">
      <h2 class="text-xl font-semibold mb-2">Add New API Key</h2>
      <div class="grid md:grid-cols-2 gap-4">
        <input type="text" name="name" placeholder="API Name (e.g., KhilaadixPro)" required class="border p-3 rounded">
        <input type="text" name="api_key" placeholder="API Key" required class="border p-3 rounded">
      </div>
      <button name="add_key" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">Add API Key</button>
    </form>

    <table class="w-full border text-sm">
      <thead class="bg-indigo-600 text-white">
        <tr>
          <th class="p-2">#</th>
          <th class="p-2 text-left">Name</th>
          <th class="p-2 text-left">Key</th>
          <th class="p-2">Status</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($keys as $k): ?>
        <tr class="border-b">
          <td class="p-2"><?= $k['id'] ?></td>
          <td class="p-2"><?= htmlspecialchars($k['name']) ?></td>
          <td class="p-2"><?= substr($k['api_key'], 0, 20) ?>...</td>
          <td class="p-2 text-center">
            <span class="px-3 py-1 rounded text-white <?= $k['status'] == 'active' ? 'bg-green-500' : 'bg-gray-400' ?>">
              <?= ucfirst($k['status']) ?>
            </span>
          </td>
          <td class="p-2 text-center flex gap-2 justify-center">
            <form method="POST">
              <input type="hidden" name="id" value="<?= $k['id'] ?>">
              <button name="activate" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Activate</button>
            </form>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $k['id'] ?>">
              <button name="delete" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
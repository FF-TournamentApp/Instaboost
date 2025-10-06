<?php
session_start();
require 'config.php';

$errors = [];
$mode = $_GET['mode'] ?? 'login'; // login or register
$registered = isset($_GET['registered']);
$success = isset($_GET['success']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['mode'] === 'register') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $pass = $_POST['password'];
        $cpass = $_POST['confirm_password'];
        if (!$name || !$email || !$pass || !$cpass) {
            $errors[] = "All fields are required.";
        } elseif ($pass !== $cpass) {
            $errors[] = "Passwords do not match.";
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$name, $email, $hash]);
                header("Location: auth.php?mode=login&registered=1");
                exit;
            } catch (PDOException $e) {
                $errors[] = "Error or email already registered.";
            }
        }
    } elseif ($_POST['mode'] === 'login') {
        $email = trim($_POST['email']);
        $pass = $_POST['password'];
        if (!$email || !$pass) {
            $errors[] = "Email & password required.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                header("Location: auth.php?success=1");
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        }
    }
}

$loginError = in_array("Invalid credentials.", $errors);
$showSuccessPopup = ($registered || $success) && empty($errors);
$showErrorPopup = $loginError;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Auth</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .tab-content {
      transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .hidden-tab {
      opacity: 0;
      transform: translateX(20px);
      pointer-events: none;
      position: absolute;
      left: 0; top: 0;
    }
    .visible-tab {
      opacity: 1;
      transform: translateX(0);
      position: relative;
    }

    /* ===== Universal Popup Styles ===== */
    .popup {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 50;
      backdrop-filter: blur(4px);
    }
    .popup.active {
      display: flex;
      animation: fadeIn 0.3s ease forwards;
    }
    .popup-content {
      background: white;
      padding: 2.5rem 3rem;
      border-radius: 20px;
      text-align: center;
      transform: scale(0.8);
      opacity: 0;
      animation: popIn 0.4s ease forwards;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
    }

    /* ===== Checkmark (Success) ===== */
    .checkmark {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 4px solid #22c55e;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 1rem;
      position: relative;
      animation: circlePop 0.5s ease forwards;
      box-shadow: 0 0 15px rgba(34, 197, 94, 0.3);
    }
    .checkmark::after {
      content: "";
      position: absolute;
      width: 25px;
      height: 45px;
      border-right: 5px solid #22c55e;
      border-bottom: 5px solid #22c55e;
      transform: rotate(45deg) scale(0);
      animation: checkDraw 0.4s 0.3s ease forwards;
    }

    /* ===== Crossmark (Error) ===== */
    .crossmark {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 4px solid #ef4444;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 1rem;
      position: relative;
      animation: circlePop 0.5s ease forwards;
      box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
    }
    .crossmark::before,
    .crossmark::after {
      content: "";
      position: absolute;
      width: 45px;
      height: 5px;
      background: #ef4444;
      border-radius: 5px;
      transform: scale(0);
      animation: crossDraw 0.4s 0.3s ease forwards;
    }
    .crossmark::before {
      transform: rotate(45deg) scale(0);
    }
    .crossmark::after {
      transform: rotate(-45deg) scale(0);
    }

    @keyframes popIn {
      from { transform: scale(0.8); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    @keyframes circlePop {
      from { transform: scale(0.5); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    @keyframes checkDraw {
      to { transform: rotate(45deg) scale(1); }
    }
    @keyframes crossDraw {
      to { transform: scale(1); }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .popup.active {
      animation: fadeIn 0.3s ease forwards, hidePopup 3s 2.5s forwards;
    }
    @keyframes hidePopup {
      to { opacity: 0; visibility: hidden; }
    }
  </style>
</head>
<body class="bg-blue-50 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-4xl w-full grid md:grid-cols-2">
    <div class="hidden md:flex items-center justify-center bg-blue-100">
      <img src="https://via.placeholder.com/300x300.png?text=Welcome" alt="Welcome">
    </div>
    <div class="p-8 relative">
      <div class="flex mb-6">
        <button onclick="switchMode('login')" id="btn-login"
          class="px-4 py-2 flex-1 font-semibold border-b-2">Login</button>
        <button onclick="switchMode('register')" id="btn-register"
          class="px-4 py-2 flex-1 font-semibold border-b-2">Register</button>
      </div>

      <?php if ($errors): ?>
        <div class="mb-4 text-red-600">
          <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <div id="form-login" class="tab-content <?= $mode === 'login' ? 'visible-tab' : 'hidden-tab' ?>">
        <form method="POST">
          <input type="hidden" name="mode" value="login">
          <div class="mb-4">
            <label class="block text-gray-700">Email</label>
            <input name="email" type="email" required class="w-full p-2 border rounded" />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Password</label>
            <input name="password" type="password" required class="w-full p-2 border rounded" />
          </div>
          <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Login</button>
        </form>
      </div>

      <!-- Register Form -->
      <div id="form-register" class="tab-content <?= $mode === 'register' ? 'visible-tab' : 'hidden-tab' ?>">
        <form method="POST">
          <input type="hidden" name="mode" value="register">
          <div class="mb-4">
            <label class="block text-gray-700">Full Name</label>
            <input name="name" type="text" required class="w-full p-2 border rounded" />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Email</label>
            <input name="email" type="email" required class="w-full p-2 border rounded" />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Password</label>
            <input name="password" type="password" required class="w-full p-2 border rounded" />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Confirm Password</label>
            <input name="confirm_password" type="password" required class="w-full p-2 border rounded" />
          </div>
          <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">Register</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ✅ Success Popup -->
  <div class="popup <?php if($showSuccessPopup) echo 'active'; ?>">
    <div class="popup-content">
      <div class="checkmark"></div>
      <h2 class="text-2xl font-semibold text-green-600 mb-2">Success!</h2>
      <p class="text-gray-600">
        <?= $registered ? 'Account created successfully. You can now login!' : 'Welcome back! Login successful.' ?>
      </p>
    </div>
  </div>

  <!-- ❌ Error Popup -->
  <div class="popup <?php if($showErrorPopup) echo 'active'; ?>">
    <div class="popup-content">
      <div class="crossmark"></div>
      <h2 class="text-2xl font-semibold text-red-600 mb-2">Error!</h2>
      <p class="text-gray-600">Invalid email or password. Please try again.</p>
    </div>
  </div>

  <script>
    function switchMode(mode) {
      const loginBtn = document.getElementById('btn-login');
      const registerBtn = document.getElementById('btn-register');
      const formLogin = document.getElementById('form-login');
      const formReg = document.getElementById('form-register');

      if (mode === 'login') {
        loginBtn.classList.add('border-blue-500');
        registerBtn.classList.remove('border-blue-500');
        formLogin.classList.remove('hidden-tab');
        formLogin.classList.add('visible-tab');
        formReg.classList.remove('visible-tab');
        formReg.classList.add('hidden-tab');
      } else {
        registerBtn.classList.add('border-green-500');
        loginBtn.classList.remove('border-green-500');
        formReg.classList.remove('hidden-tab');
        formReg.classList.add('visible-tab');
        formLogin.classList.remove('visible-tab');
        formLogin.classList.add('hidden-tab');
      }
    }

    window.addEventListener('DOMContentLoaded', () => {
      switchMode('<?= $mode ?>');
    });
  </script>
</body>
</html>
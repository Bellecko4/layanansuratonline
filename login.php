<?php
session_start();
require_once 'includes/db.php'; // koneksi database

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows == 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      if ($user['role'] == 'admin') {
        header("Location: dashboard_admin.php");
      } else {
        header("Location: dashboard_user.php");
      }
      exit;
    } else {
      $error = "Password salah.";
    }
  } else {
    $error = "Username tidak ditemukan.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Layanan Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-r from-green-100 to-green-200 dark:from-gray-800 dark:to-gray-900 text-gray-900 dark:text-white min-h-screen">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md animate-fade-in">
      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-green-700 dark:text-green-400">Selamat Datang</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">Masuk untuk mengakses layanan surat Kalurahan</p>
      </div>

      <?php if ($error): ?>
        <div class="alert-error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="" class="space-y-4">
        <div>
          <label for="username" class="block mb-1 font-medium">Username</label>
          <input type="text" name="username" id="username" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <label for="password" class="block mb-1 font-medium">Password</label>
          <input type="password" name="password" id="password" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <button type="submit"
                  class="w-full button-primary hover:scale-105">
            Masuk
          </button>
        </div>
      </form>

      <p class="mt-4 text-center text-sm">
        <a href="forgot_password.php" class="text-green-600 hover:underline">Lupa Password?</a>
      </p>
      
      <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        Belum punya akun? <a href="register.php" class="text-green-600 hover:underline">Daftar di sini</a>
      </p>
    </div>
  </div>
</body>
</html>

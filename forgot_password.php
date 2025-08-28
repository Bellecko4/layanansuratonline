<?php
require_once 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm'];

  // Validasi input
  if (empty($username) || empty($password) || empty($confirm)) {
    $error = "Semua field wajib diisi.";
  } elseif ($password !== $confirm) {
    $error = "Konfirmasi password tidak cocok.";
  } else {
    // Cari pengguna berdasarkan username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      // Username ditemukan, update password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
      $update_stmt->bind_param("ss", $hashed_password, $username);

      if ($update_stmt->execute()) {
        $success = "Password berhasil diperbarui. Silakan login.";
      } else {
        $error = "Terjadi kesalahan saat memperbarui password.";
      }
    } else {
      $error = "Username tidak ditemukan.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lupa Password</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-r from-green-100 to-green-200 dark:from-gray-800 dark:to-gray-900 text-gray-900 dark:text-white min-h-screen">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md animate-fade-in">
      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-green-700 dark:text-green-400">Lupa Password</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">Masukkan username dan password baru Anda</p>
      </div>

      <?php if ($error): ?>
        <div class="alert-error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert-success text-center">
          <?= htmlspecialchars($success) ?> <a href="login.php" class="underline text-green-800">Login</a>
        </div>
      <?php endif; ?>

      <form method="post" action="" class="space-y-4">
        <div>
          <label for="username" class="block mb-1 font-medium">Username</label>
          <input type="text" name="username" id="username" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <label for="password" class="block mb-1 font-medium">Password Baru</label>
          <input type="password" name="password" id="password" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <label for="confirm" class="block mb-1 font-medium">Ulangi Password Baru</label>
          <input type="password" name="confirm" id="confirm" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <button type="submit"
                  class="w-full button-primary hover:scale-105">
            Reset Password
          </button>
        </div>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        <a href="login.php" class="text-green-600 hover:underline">&larr; Kembali ke Login</a>
      </p>
    </div>
  </div>
</body>
</html>
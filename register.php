<?php
require_once 'includes/db.php';
session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $confirm  = $_POST['confirm'];

  if (empty($username) || empty($password) || empty($confirm)) {
    $errors[] = "Semua field wajib diisi.";
  } elseif ($password !== $confirm) {
    $errors[] = "Konfirmasi password tidak cocok.";
  } else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $errors[] = "Username sudah digunakan.";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $role = 'user';
      $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $username, $hashed_password, $role);

      if ($stmt->execute()) {
        $success = "Pendaftaran berhasil. Silakan login.";
      } else {
        $errors[] = "Terjadi kesalahan saat menyimpan data.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Akun Baru</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-r from-green-100 to-green-200 dark:from-gray-800 dark:to-gray-900 text-gray-900 dark:text-white min-h-screen">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md animate-fade-in">
      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-green-700 dark:text-green-400">Buat Akun</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">Daftar untuk mengakses layanan surat Kalurahan</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert-error">
          <ul class="list-disc ml-5">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
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
          <label for="password" class="block mb-1 font-medium">Password</label>
          <input type="password" name="password" id="password" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <label for="confirm" class="block mb-1 font-medium">Ulangi Password</label>
          <input type="password" name="confirm" id="confirm" required
                 class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500" />
        </div>
        <div>
          <button type="submit"
                  class="w-full button-primary hover:scale-105">
            Daftar
          </button>
        </div>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        Sudah punya akun? <a href="login.php" class="text-green-600 hover:underline">Login di sini</a>
      </p>
    </div>
  </div>
</body>
</html>

<?php
session_start();
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] == 'admin') {
    header('Location: dashboard_admin.php');
    exit;
  } else {
    header('Location: dashboard_user.php');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Beranda - Layanan Surat Kalurahan Banguntapan</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<header class="flex items-center gap-3 bg-green-700 text-white p-4">
  <img src="assets/icons/logo.png" alt="Logo Kalurahan" class="w-10 h-10 rounded-full shadow">
  <h1 class="text-xl font-bold">Kalurahan Banguntapan</h1>
</header>

<body class="bg-gradient-to-br from-green-100 to-white dark:from-gray-800 dark:to-gray-900 text-gray-800 dark:text-white transition duration-300 min-h-screen">

 <header class="shadow bg-green-700 text-white py-4">
  <div class="max-w-6xl mx-auto px-4 flex flex-col items-center text-center">
    <h1 class="text-xl md:text-2xl font-bold">Layanan Surat Kalurahan Banguntapan</h1>
    
    <!-- Tombol login & daftar -->
    <div class="flex gap-4 mt-3">
      <a href="login.php" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-green-100 transition">Login</a>
      <a href="register.php" class="bg-green-100 text-green-900 px-4 py-2 rounded hover:bg-white transition">Daftar</a>
    </div>
  </div>
</header>


  <main class="max-w-6xl mx-auto px-4 py-12 text-center">
    <section class="mb-12">
      <h1 class="text-lg md:text-2xl lg:text-3xl font-bold text-center mb-6">
        Ajukan Permohonan Surat Online
      </h1>

      <p class="text-lg text-gray-600 dark:text-gray-300 max-w-xl mx-auto">
        Praktis, cepat, dan tanpa antri. Kini masyarakat Kalurahan Banguntapan dapat mengajukan surat secara digital dari rumah.
      </p>
    </section>

    <section class="grid md:grid-cols-2 gap-8 text-left mt-10">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold mb-2">Jenis Surat yang Tersedia</h3>
        <ul class="list-disc ml-5 text-sm text-gray-700 dark:text-gray-300">
          <li>Surat Permohonan Nikah</li>
          <li>Surat Permohonan Domisili Tempat Tinggal</li>
          <li>Surat Permohonan Domisili Perusahaan/Usaha</li>
          <li>Surat Permohonan SKCK</li>
          <li>Surat Keterangan Usaha</li>
          <li>Surat Keterangan Tidak Mampu</li>
          <li>Surat Keterangan Kependudukan</li>
          <li>Surat Akta Kelahiran</li>
          <li>Surat Akta Kematian</li>
          <li>Surat KTP</li>
          <li>Surat Permohonan Status Pekerjaan</li>
          <li>Surat Permohonan Perubahan Status Perkawinan</li>
          <li>Surat Permohonan Status Pendidikan</li>
          <li>Surat Permohonan Keterangan Pensiun</li>
          <li>Surat Permohonan Cerai</li>
          <li>Surat Keterangan Penghasilan Orang Tua</li>
          <li>Surat Izin Perjalanan</li>
          <li>Surat Permohonan Pindah Kependudukan</li>
          <li>Surat Permohonan Masuk Kependudukan</li>
        </ul>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold mb-2">Prosedur Pengajuan Surat</h3>
        <ol class="list-decimal ml-5 text-sm text-gray-700 dark:text-gray-300">
          <li>Login ke akun Anda</li>
          <li>Pilih jenis surat yang dibutuhkan</li>
          <li>Isi formulir dan unggah dokumen</li>
          <li>Tunggu verifikasi petugas</li>
        </ol>
      </div>
    </section>
  </main>

  <section class="bg-white dark:bg-gray-800 py-10 mt-10 border-t">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-8 text-left">
      <div>
        <h3 class="text-xl font-semibold mb-2">🔒 Aman & Tertib</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300">Data pemohon dienkripsi dan diproses langsung oleh petugas kalurahan.</p>
      </div>
      <div>
        <h3 class="text-xl font-semibold mb-2">⚡ Cepat & Mudah</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300">Hanya perlu login, pilih jenis surat, unggah dokumen, dan selesai.</p>
      </div>
      <div>
        <h3 class="text-xl font-semibold mb-2">📱 Responsif</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300">Bisa diakses dari smartphone, tablet, atau laptop kapan pun dibutuhkan.</p>
      </div>
    </div>
  </section>

  <footer class="text-center text-sm py-4 text-gray-500 dark:text-gray-400 border-t">
    &copy; 2025 Kalurahan Banguntapan. Semua Hak Dilindungi.
  </footer>
</body>
</html>

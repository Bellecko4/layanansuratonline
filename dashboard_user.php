<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil semua permohonan user
$stmt = $conn->prepare("SELECT * FROM permohonan WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Ambil permohonan terakhir untuk notifikasi
$notifStmt = $conn->prepare("SELECT jenis_surat, status FROM permohonan WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$notifStmt->bind_param("i", $user_id);
$notifStmt->execute();
$notifResult = $notifStmt->get_result();
$notif = $notifResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pengguna</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-white dark:from-gray-800 dark:to-gray-900 text-gray-900 dark:text-white min-h-screen">

  <header class="flex items-center gap-3 bg-green-700 text-white p-4">
    <img src="assets/icons/logo.png" alt="Logo Kalurahan" class="w-10 h-10 rounded-full shadow">
    <h1 class="text-xl font-bold">Kalurahan Banguntapan</h1>
  </header>

  <header class="bg-green-700 text-white py-4 shadow">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">Dashboard</h1>
      <div class="flex items-center gap-4">
        <span class="text-sm">👋 Halo, <strong><?= htmlspecialchars($username) ?></strong></span>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto py-10 px-4">

    <?php if ($notif && $notif['status'] === 'ditolak'): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded mb-4">
        ❌ Permohonan <strong><?= htmlspecialchars($notif['jenis_surat']) ?></strong> telah <strong>DITOLAK</strong>.
      </div>
    <?php elseif ($notif && $notif['status'] === 'diterima'): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded mb-4">
        ✅ Permohonan <strong><?= htmlspecialchars($notif['jenis_surat']) ?></strong> telah <strong>DITERIMA</strong>.
      </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
      <div>
        <h2 class="text-2xl font-semibold">Riwayat Permohonan Surat</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Lihat status dan riwayat permohonan yang pernah Anda ajukan.</p>
      </div>
      <a href="buat_permohonan.php" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow-md">+ Buat Permohonan</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 border rounded shadow-md">
          <thead>
            <tr class="bg-gray-200 dark:bg-gray-700 text-sm uppercase text-gray-700 dark:text-gray-200">
              <th class="p-3">No</th>
              <th class="p-3">Jenis Surat</th>
              <th class="p-3">Keperluan</th>
              <th class="p-3">Status</th>
              <th class="p-3">ID Surat</th>
              <th class="p-3">Pesan Kelurahan</th>
              <th class="p-3">Diajukan</th>
              <th class="p-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-sm text-gray-800 dark:text-gray-100">
            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
              <tr class="border-b dark:border-gray-600 hover:bg-green-50 dark:hover:bg-gray-700">
                <td class="p-3 text-center"><?= $no++ ?></td>
                <td class="p-3"><?= htmlspecialchars($row['jenis_surat']) ?></td>
                <td class="p-3"><?= htmlspecialchars($row['keperluan']) ?></td>
                <td class="p-3 font-semibold">
                  <?php if ($row['status'] == 'diterima'): ?>
                    <span class="text-green-600">✔ Diterima</span>
                  <?php elseif ($row['status'] == 'ditolak'): ?>
                    <span class="text-red-600">✖ Ditolak</span>
                  <?php else: ?>
                    <span class="text-yellow-600">⏳ Diproses</span>
                  <?php endif; ?>
                </td>
                <td class="p-3 text-center"><?= htmlspecialchars($row['id_surat'] ?? '-') ?></td>
                <td class="p-3"><?= htmlspecialchars($row['pesan_admin'] ?? '-') ?></td>
                <td class="p-3"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                <td class="p-3 text-center">
                  <?php if ($row['status'] === 'diproses'): ?>
                    <div class="flex items-center justify-center gap-2">
                      <a href="edit_permohonan.php?id=<?= $row['id'] ?>" 
                         class="flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow transition">
                        ✏ Edit
                      </a>
                      <a href="hapus_permohonan.php?id=<?= $row['id'] ?>" 
                         onclick="return confirm('Yakin ingin menghapus permohonan ini?')" 
                         class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow transition">
                        🗑 Hapus
                      </a>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400 italic">Tidak tersedia</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-600 dark:text-gray-300 mt-6">Belum ada permohonan surat yang diajukan.</p>
    <?php endif; ?>

    <section class="mt-12">
      <h2 class="text-2xl font-semibold mb-4">Daftar Jenis Surat dan Persyaratanya
         <p class="text-sm text-gray-600 mt-1 italic">* Pilih Surat untuk Melihat Persyaratanya</p>
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-nikah">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Nikah</h3>
              <div id="surat-permohonan-nikah-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Fotokopi KTP calon suami dan istri</li>
                      <li>Fotokopi Kartu Keluarga (KK)</li>
                      <li>Fotokopi Akta Kelahiran</li>
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Pas foto 2x3 atau 3x4</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-domisili-tempat-tinggal">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Domisili Tempat Tinggal</h3>
              <div id="surat-permohonan-domisili-tempat-tinggal-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi Kartu Tanda Penduduk (KTP)</li>
                      <li>Fotokopi Kartu Keluarga (KK)</li>
                      <li>Surat Pernyataan Domisili</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-domisili-perusahaan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Domisili Perusahaan/Usaha</h3>
              <div id="surat-permohonan-domisili-perusahaan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP Penanggung Jawab</li>
                      <li>Fotokopi Akta Pendirian Perusahaan</li>
                      <li>Surat Keterangan Kepemilikan tempat usaha</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-skck">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan SKCK</h3>
              <div id="surat-permohonan-skck-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK</li>
                      <li>Fotokopi Akta Kelahiran/Ijazah</li>
                      <li>Pas Foto 4x6</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-keterangan-usaha">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Keterangan Usaha</h3>
              <div id="surat-keterangan-usaha-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK</li>
                      <li>Surat Pernyataan Usaha</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-keterangan-tidak-mampu">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Keterangan Tidak Mampu</h3>
              <div id="surat-keterangan-tidak-mampu-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK</li>
                      <li>Surat Pernyataan Tidak Mampu</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-keterangan-kependudukan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Keterangan Kependudukan</h3>
              <div id="surat-keterangan-kependudukan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-akta-kelahiran">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Akta Kelahiran</h3>
              <div id="surat-akta-kelahiran-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Keterangan Lahir dari Desa/Kelurahan</li>
                      <li>Fotokopi KTP Orang Tua</li>
                      <li>Fotokopi KK</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-akta-kematian">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Akta Kematian</h3>
              <div id="surat-akta-kematian-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Keterangan Kematian dari Desa/Kelurahan</li>
                      <li>Fotokopi KTP almarhum</li>
                      <li>Fotokopi KK</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-ktp">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat KTP</h3>
              <div id="surat-ktp-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Fotokopi Kartu Keluarga (KK)</li>
                      <li>Fotokopi Ijazah (jika ada)</li>
                      <li>Surat Pengantar dari RT/RW</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-status-pekerjaan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Status Pekerjaan</h3>
              <div id="surat-permohonan-status-pekerjaan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Surat Keterangan dari instansi/perusahaan terkait</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-perubahan-status-perkawinan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Perubahan Status Perkawinan</h3>
              <div id="surat-permohonan-perubahan-status-perkawinan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi Akta Perkawinan/Perceraian</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-status-pendidikan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Status Pendidikan</h3>
              <div id="surat-permohonan-status-pendidikan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Surat Keterangan dari sekolah/universitas</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-keterangan-pensiun">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Keterangan Pensiun</h3>
              <div id="surat-permohonan-keterangan-pensiun-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Surat Keterangan dari instansi terkait</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-cerai">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Cerai</h3>
              <div id="surat-permohonan-cerai-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi Akta Perkawinan</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-keterangan-penghasilan-orang-tua">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Keterangan Penghasilan Orang Tua</h3>
              <div id="surat-keterangan-penghasilan-orang-tua-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP Orang Tua</li>
                      <li>Surat Pernyataan Penghasilan bermeterai</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-izin-perjalanan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Izin Perjalanan</h3>
              <div id="surat-izin-perjalanan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Tujuan dan durasi perjalanan</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-pindah-kependudukan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Pindah Kependudukan</h3>
              <div id="surat-permohonan-pindah-kependudukan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK</li>
                  </ul>
              </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-300" data-surat="surat-permohonan-masuk-kependudukan">
              <h3 class="font-bold text-lg text-green-700 dark:text-green-400">Surat Permohonan Masuk Kependudukan</h3>
              <div id="surat-permohonan-masuk-kependudukan-persyaratan" class="persyaratan hidden mt-4 p-4 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                  <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-200">Persyaratan:</h4>
                  <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                      <li>Surat Pengantar dari RT/RW</li>
                      <li>Fotokopi KTP</li>
                      <li>Fotokopi KK (tempat asal)</li>
                      <li>Surat Keterangan Pindah</li>
                  </ul>
              </div>
          </div>
      </div>
    </section>

  </main>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const daftarSurat = document.querySelector('div.grid');
      daftarSurat.addEventListener('click', function(e) {
        let card = e.target.closest('div[data-surat]');
        if (!card) return;

        let suratId = card.getAttribute('data-surat');
        let persyaratanDiv = document.getElementById(suratId + '-persyaratan');

        // Toggle visibility of the clicked item's requirements
        if (persyaratanDiv) {
          persyaratanDiv.classList.toggle('hidden');
        }

        // Hide all other requirements
        document.querySelectorAll('.persyaratan').forEach(div => {
          if (div.id !== suratId + '-persyaratan') {
            div.classList.add('hidden');
          }
        });
      });
    });
  </script>
</body>
</html>

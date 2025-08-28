<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Hapus permohonan (jika diminta)
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
  $id = intval($_GET['hapus']);

  // Cek status dulu
  $cek = $conn->prepare("SELECT status, dokumen FROM permohonan WHERE id = ? AND user_id = ?");
  $cek->bind_param("ii", $id, $user_id);
  $cek->execute();
  $result = $cek->get_result();

  if ($result && $result->num_rows > 0) {
    $permohonan = $result->fetch_assoc();
    if ($permohonan['status'] === 'diproses') {
      // Hapus dokumen
      $file = 'uploads/' . $permohonan['dokumen'];
      if (file_exists($file)) {
        unlink($file);
      }

      // Hapus dari DB
      $hapus = $conn->prepare("DELETE FROM permohonan WHERE id = ? AND user_id = ?");
      $hapus->bind_param("ii", $id, $user_id);
      $hapus->execute();
      $pesan_sukses = "Permohonan berhasil dihapus.";
    } else {
      $pesan_error = "Permohonan tidak dapat dihapus karena sudah diproses.";
    }
  }
}

// Ambil daftar permohonan user
$stmt = $conn->prepare("SELECT * FROM permohonan WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Riwayat Permohonan</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">

  <div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Riwayat Permohonan Saya</h2>
      <a href="dashboard_user.php" class="text-green-600 hover:underline">← Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
      <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
        <?= htmlspecialchars($pesan_sukses) ?>
      </div>
    <?php elseif (!empty($pesan_error)): ?>
      <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
        <?= htmlspecialchars($pesan_error) ?>
      </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
          <thead>
            <tr class="bg-gray-200 dark:bg-gray-700 text-sm uppercase">
              <th class="p-3">No</th>
              <th class="p-3">Jenis Surat</th>
              <th class="p-3">Keperluan</th>
              <th class="p-3">Status</th>
              <th class="p-3">Dokumen</th>
              <th class="p-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
              <tr class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-3"><?= $no++ ?></td>
                <td class="p-3"><?= htmlspecialchars($row['jenis_surat']) ?></td>
                <td class="p-3"><?= htmlspecialchars($row['keperluan']) ?></td>
                <td class="p-3">
                  <?php if ($row['status'] === 'diterima'): ?>
                    <span class="text-green-600 font-bold">Diterima</span>
                  <?php elseif ($row['status'] === 'ditolak'): ?>
                    <span class="text-red-600 font-bold">Ditolak</span>
                  <?php else: ?>
                    <span class="text-yellow-600 font-bold">Diproses</span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <a href="uploads/<?= urlencode($row['dokumen']) ?>" target="_blank"
                     class="text-blue-600 underline">Lihat</a>
                </td>
                <td class="p-3">
                  <?php if ($row['status'] === 'diproses'): ?>
                    <a href="?hapus=<?= $row['id'] ?>"
                       onclick="return confirm('Yakin ingin menghapus permohonan ini?')"
                       class="text-red-600 hover:underline">Hapus</a>
                  <?php else: ?>
                    <span class="text-gray-400">Tidak tersedia</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-600 dark:text-gray-300">Belum ada permohonan surat.</p>
    <?php endif; ?>
  </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Permohonan tidak ditemukan.";
  exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil data permohonan milik user
$stmt = $conn->prepare("SELECT * FROM permohonan WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows < 1) {
  echo "Permohonan tidak ditemukan atau Anda tidak memiliki akses.";
  exit;
}

$data = $result->fetch_assoc();

// Cegah edit jika status bukan 'diproses'
if ($data['status'] !== 'diproses') {
  echo "Permohonan ini sudah diproses dan tidak dapat diedit.";
  exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $jenis_surat   = trim($_POST['jenis_surat']);
  $nama_lengkap  = trim($_POST['nama_lengkap']);
  $rt_rw         = trim($_POST['rt_rw']);
  $dusun         = trim($_POST['dusun']);
  $keperluan     = trim($_POST['keperluan']);
  $dokumen       = $data['dokumen']; // dokumen lama

  if (empty($jenis_surat) || empty($nama_lengkap) || empty($rt_rw) || empty($dusun) || empty($keperluan)) {
    $errors[] = "Semua field wajib diisi.";
  }

  // Cek jika ada upload dokumen baru
  if (!empty($_FILES['dokumen']['name'])) {
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
      $errors[] = "Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.";
    } else {
      $new_filename = time() . '_' . preg_replace('/\s+/', '_', $_FILES['dokumen']['name']);
      $target_path = "uploads/" . $new_filename;

      if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $target_path)) {
        // Hapus file lama jika ada
        if (!empty($dokumen) && file_exists("uploads/" . $dokumen)) {
          unlink("uploads/" . $dokumen);
        }
        $dokumen = $new_filename;
      } else {
        $errors[] = "Gagal mengunggah file.";
      }
    }
  }

  if (empty($errors)) {
    $update = $conn->prepare("UPDATE permohonan 
      SET jenis_surat = ?, nama_lengkap = ?, rt_rw = ?, dusun = ?, keperluan = ?, dokumen = ? 
      WHERE id = ? AND user_id = ?");
    $update->bind_param("ssssssii", $jenis_surat, $nama_lengkap, $rt_rw, $dusun, $keperluan, $dokumen, $id, $user_id);

    if ($update->execute()) {
      $success = "Permohonan berhasil diperbarui.";
      $data = array_merge($data, [
        'jenis_surat' => $jenis_surat,
        'nama_lengkap' => $nama_lengkap,
        'rt_rw' => $rt_rw,
        'dusun' => $dusun,
        'keperluan' => $keperluan,
        'dokumen' => $dokumen
      ]);
    } else {
      $errors[] = "Gagal memperbarui data.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Permohonan</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Edit Permohonan Surat
      <p class="text-sm text-gray-600 mt-1 italic">* Untuk dokumen yang sudah di unggah tidak dapat di edit.</p>
      <p class="text-sm text-gray-600 mt-1 italic">* Tips anda bisa menambahkan atau membuat permohonan baru</p>
    </h2>
    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul class="list-disc ml-5">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="mb-4">
        <label class="block font-medium mb-1">Jenis Surat</label>
        <input type="text" name="jenis_surat" value="<?= htmlspecialchars($data['jenis_surat']) ?>" required
               class="w-full border px-3 py-2 rounded">
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required
               class="w-full border px-3 py-2 rounded">
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">RT/RW</label>
        <input type="text" name="rt_rw" value="<?= htmlspecialchars($data['rt_rw']) ?>" required
               class="w-full border px-3 py-2 rounded">
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Dusun</label>
        <input type="text" name="dusun" value="<?= htmlspecialchars($data['dusun']) ?>" required
               class="w-full border px-3 py-2 rounded">
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Keperluan</label>
        <textarea name="keperluan" required
                  class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($data['keperluan']) ?></textarea>
      </div>
      <div class="flex justify-between">
        <a href="dashboard_user.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Kembali</a>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Simpan</button>
      </div>
    </form>
  </div>
</body>
</html>

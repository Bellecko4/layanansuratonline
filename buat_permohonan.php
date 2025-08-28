<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $jenis_surat   = trim($_POST['jenis_surat']);
  $nama_lengkap  = trim($_POST['nama_lengkap']);
  $rt_rw         = trim($_POST['rt_rw']);
  $dusun         = trim($_POST['dusun']);
  $keperluan     = trim($_POST['keperluan']);
  $user_id       = $_SESSION['user_id'];

  // Ambil daftar dokumen yang wajib diunggah dari hidden input
  $doc_fields = isset($_POST['required_docs']) ? explode(',', $_POST['required_docs']) : [];

  $uploaded_files = [];

  foreach ($doc_fields as $doc) {
    $doc = trim($doc);
    if (!empty($_FILES[$doc]['name'])) {
      // Ganti spasi dengan underscore dan tambahkan timestamp
      $filename = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES[$doc]['name']));
      $target = 'uploads/' . $filename;
      if (move_uploaded_file($_FILES[$doc]['tmp_name'], $target)) {
        $uploaded_files[$doc] = $filename;
      } else {
        $errors[] = "Gagal mengunggah file " . str_replace('_', ' ', $doc) . ".";
      }
    } else {
      $errors[] = "File " . str_replace('_', ' ', $doc) . " wajib diunggah.";
    }
  }

  if (!$errors) {
    // Kolom dasar
    $columns = ['user_id', 'jenis_surat', 'nama_lengkap', 'rt_rw', 'dusun', 'keperluan', 'status', 'created_at'];
    $placeholders = ['?', '?', '?', '?', '?', '?', "'diproses'", 'NOW()'];
    $values = [$user_id, $jenis_surat, $nama_lengkap, $rt_rw, $dusun, $keperluan];
    $types = 'ssssss'; // 6 kolom pertama adalah string

    // Tambahkan kolom dokumen
    foreach ($uploaded_files as $col => $val) {
      $columns[] = $col; // nama kolom sama dengan field file upload
      $placeholders[] = '?';
      $values[] = $val;
      $types .= 's';
    }

    $sql = "INSERT INTO permohonan (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
      $success = "Permohonan berhasil dikirim.";
    } else {
      $errors[] = "Gagal menyimpan permohonan: " . $conn->error;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Permohonan Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .step { display: none; }
    .step.active { display: block; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-2xl mx-auto p-6 bg-white rounded shadow mt-10">
  <h2 class="text-2xl font-bold mb-6 text-center">Ajukan Permohonan Surat</h2>

  <?php if ($errors): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
      <ul class="list-disc pl-5">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
<div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center">
      <?= htmlspecialchars($success) ?><br>
      <a href="dashboard_user.php" class="underline">Kembali ke Dashboard</a>
    </div>
  <?php if (!$success): ?>
  <form id="permohonanForm" method="POST" enctype="multipart/form-data">
    <!-- Step 1 -->
    <div class="step active">
      <label class="block mb-2 font-medium">Jenis Surat</label>
      <select name="jenis_surat" id="jenis_surat" required class="w-full border px-3 py-2 rounded">
        <option value="">-- Pilih Jenis Surat --</option>
        <option value="Surat Keterangan Usaha" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk,surat_pernyataan_usaha">Surat Keterangan Usaha</option>
        <option value="Surat Permohonan Nikah" data-docs="surat_pengantar_rt_rw,fotokopi_ktp_calon_suami_istri,fotokopi_kk">Surat Permohonan Nikah</option>
        <option value="Surat Permohonan SKCK" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk,fotokopi_akte_kelahiran,pas_foto_4x6">Surat Permohonan SKCK</option>
        <option value="Surat Keterangan Kependudukan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk">Surat Keterangan Kependudukan</option>
        <option value="Surat Permohonan Domisili Tempat Tinggal" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk,surat_pernyataan_domisili">Surat Permohonan Domisili Tempat Tinggal</option>
        <option value="Surat Permohonan Domisili Perusahaan/Usaha" data-docs="surat_pengantar_rt_rw,fotokopi_ktp_penanggung_jawab,fotokopi_akta_pendirian_perusahaan,surat_keterangan_kepemilikan_usaha">Surat Permohonan Domisili Perusahaan/Usaha</option>
        <option value="Surat Akta Kelahiran" data-docs="surat_keterangan_lahir,fotokopi_ktp_orang_tua,fotokopi_kk">Surat Akta Kelahiran</option>
        <option value="Surat Akta Kematian" data-docs="surat_keterangan_kematian,fotokopi_ktp_almarhum,fotokopi_kk">Surat Akta Kematian</option>
        <option value="Surat KTP" data-docs="surat_pengantar_rt_rw,fotokopi_ijazah,fotokopi_kk">Surat KTP</option>
        <option value="Surat Permohonan Status Pekerjaan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,surat_keterangan_instansi">Surat Permohonan Status Pekerjaan</option>
        <option value="Surat Permohonan Perubahan Status Perkawinan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_akta_perkawinan_perceraian">Surat Permohonan Perubahan Status Perkawinan</option>
        <option value="Surat Permohonan Status Pendidikan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,surat_keterangan_sekolah">Surat Permohonan Status Pendidikan</option>
        <option value="Surat Permohonan Keterangan Pensiun" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,surat_keterangan_instansi_pensiun">Surat Permohonan Keterangan Pensiun</option>
        <option value="Surat Permohonan Cerai" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_akta_perkawinan">Surat Permohonan Cerai</option>
        <option value="Surat Keterangan Tidak Mampu" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk,surat_pernyataan_tidak_mampu">Surat Keterangan Tidak Mampu</option>
        <option value="Surat Keterangan Penghasilan Orang Tua" data-docs="surat_pengantar_rt_rw,fotokopi_ktp_orang_tua,surat_pernyataan_penghasilan_bermeterai">Surat Keterangan Penghasilan Orang Tua</option>
        <option value="Surat Ijin Perjalanan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,tujuan_durasi_perjalanan">Surat Ijin Perjalanan</option>
        <option value="Surat Permohonan Pindah Kependudukan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk">Surat Permohonan Pindah Kependudukan</option>
        <option value="Surat Permohonan Masuk Kependudukan" data-docs="surat_pengantar_rt_rw,fotokopi_ktp,fotokopi_kk_asal,surat_keterangan_pindah">Surat Permohonan Masuk Kependudukan</option>
      </select>
    </div>

    <!-- Step 2 -->
    <div class="step">
      <label class="block mb-2 mt-4 font-medium">Nama Lengkap</label>
      <input type="text" name="nama_lengkap" class="w-full border px-3 py-2 rounded" required>
      <label class="block mb-2 mt-4 font-medium">RT/RW</label>
      <input type="text" name="rt_rw" class="w-full border px-3 py-2 rounded" required>
      <label class="block mb-2 mt-4 font-medium">Dusun</label>
      <input type="text" name="dusun" class="w-full border px-3 py-2 rounded" required>
      <label class="block mb-2 mt-4 font-medium">Keperluan</label>
      <textarea name="keperluan" rows="3" class="w-full border px-3 py-2 rounded" required></textarea>
    </div>

    <!-- Step 3 -->
    <div class="step">
      <input type="hidden" name="required_docs" id="required_docs">
      <div id="uploadFields"></div>
    </div>

    <!-- Navigasi -->
    <div class="flex justify-between mt-6">
      <button type="button" id="prevBtn" class="bg-gray-400 text-white px-4 py-2 rounded">Kembali</button>
      <button type="button" id="nextBtn" class="bg-green-600 text-white px-4 py-2 rounded">Lanjut</button>
      <button type="submit" id="submitBtn" class="bg-green-600 text-white px-4 py-2 rounded hidden">Kirim</button>
    </div>
  </form>
  <?php endif; ?>
</div>
<script>
  let currentStep = 0;
  const steps = document.querySelectorAll('.step');
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const submitBtn = document.getElementById('submitBtn');
  const jenisSuratSelect = document.getElementById('jenis_surat');
  const uploadFields = document.getElementById('uploadFields');
  const requiredDocsInput = document.getElementById('required_docs');

  function showStep(n) {
    steps.forEach((step, i) => {
      step.classList.toggle('active', i === n);
    });
    prevBtn.style.display = n > 0 ? 'inline-block' : 'none';
    nextBtn.style.display = n < steps.length - 1 ? 'inline-block' : 'none';
    submitBtn.style.display = n === steps.length - 1 ? 'inline-block' : 'none';
  }

  nextBtn.addEventListener('click', () => {
    if (currentStep === 0) {
      const selected = jenisSuratSelect.options[jenisSuratSelect.selectedIndex];
      const docs = selected.getAttribute('data-docs');
      requiredDocsInput.value = docs;
      uploadFields.innerHTML = '';
      docs.split(',').forEach(doc => {
        const label = doc.replace(/_/g, ' ').toUpperCase();
        uploadFields.innerHTML += `
          <label class="block mb-2 mt-4 font-medium">Upload ${label}</label>
          <input type="file" name="${doc}" required class="w-full border px-3 py-2 rounded">
        `;
      });
    }
    currentStep++;
    showStep(currentStep);
  });

  prevBtn.addEventListener('click', () => {
    if (currentStep > 0) {
      currentStep--;
      showStep(currentStep);
    }
  });

  showStep(currentStep);
</script>


</body>
</html>

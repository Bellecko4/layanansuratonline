<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID permohonan tidak ditemukan.");
}

$permohonan_id = intval($_GET['id']);

// Ambil detail permohonan
$stmt = $conn->prepare("SELECT jenis_surat, nama_lengkap FROM permohonan WHERE id = ?");
$stmt->bind_param("i", $permohonan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {
    die("Permohonan tidak ditemukan.");
}

$permohonan = $result->fetch_assoc();
$jenis_surat = preg_replace('/[^A-Za-z0-9_\-]/', '_', $permohonan['jenis_surat']);
$nama_lengkap = preg_replace('/[^A-Za-z0-9_\-]/', '_', $permohonan['nama_lengkap']);

// Ambil semua dokumen terkait permohonan
$docs_stmt = $conn->prepare("SELECT nama_dokumen, nama_file FROM permohonan_dokumen WHERE permohonan_id = ?");
$docs_stmt->bind_param("i", $permohonan_id);
$docs_stmt->execute();
$docs_result = $docs_stmt->get_result();

if ($docs_result->num_rows < 1) {
    die("Tidak ada dokumen untuk permohonan ini.");
}

// Siapkan nama file ZIP
$zip_filename = "Dokumen_{$permohonan_id}_{$nama_lengkap}_{$jenis_surat}.zip";

// Buat objek ZipArchive
$zip = new ZipArchive();
$temp_file = tempnam(sys_get_temp_dir(), 'zip');

if ($zip->open($temp_file, ZipArchive::CREATE) !== TRUE) {
    die("Gagal membuat file ZIP.");
}

// Tambahkan semua dokumen ke dalam ZIP
while ($doc = $docs_result->fetch_assoc()) {
    $file_path = __DIR__ . "/uploads/" . $doc['nama_file'];
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $doc['nama_dokumen'] . "_" . basename($file_path));
    }
}

$zip->close();

// Header untuk download ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($temp_file));

// Outputkan file ZIP ke browser
readfile($temp_file);

// Hapus file sementara
unlink($temp_file);
exit;
?>

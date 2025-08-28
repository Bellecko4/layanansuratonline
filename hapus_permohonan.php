<?php
session_start();
require_once 'includes/db.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Pastikan ada ID permohonan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Permohonan tidak ditemukan.";
  exit;
}

$id = intval($_GET['id']);

// Jika admin -> ambil permohonan apapun
if ($role === 'admin') {
  $stmt = $conn->prepare("SELECT * FROM permohonan WHERE id = ?");
  $stmt->bind_param("i", $id);
} else {
  // Jika user -> ambil permohonan miliknya dan masih diproses
  $stmt = $conn->prepare("SELECT * FROM permohonan WHERE id = ? AND user_id = ? AND status = 'diproses'");
  $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows < 1) {
  echo "Permohonan tidak ditemukan atau Anda tidak memiliki akses.";
  exit;
}

$data = $result->fetch_assoc();

// Hapus file dokumen jika ada
if (!empty($data['dokumen']) && file_exists("uploads/" . $data['dokumen'])) {
  unlink("uploads/" . $data['dokumen']);
}

// Hapus dari database
$delete = $conn->prepare("DELETE FROM permohonan WHERE id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
  if ($role === 'admin') {
    header("Location: dashboard_admin.php?msg=deleted");
  } else {
    header("Location: dashboard_user.php?msg=deleted");
  }
  exit;
} else {
  echo "Gagal menghapus permohonan.";
}

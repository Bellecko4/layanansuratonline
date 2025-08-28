<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$notif = "";
$notif_type = "success"; // success | error

// ----- HANDLE POST: update status -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id       = intval($_POST['id']);
    $status_u = $_POST['status'] ?? 'diproses';
    $pesan    = trim($_POST['pesan_admin'] ?? '');
    $id_surat = trim($_POST['id_surat'] ?? '');

    $stmt = $conn->prepare("UPDATE permohonan SET status = ?, pesan_admin = ?, id_surat = ? WHERE id = ?");
    $stmt->bind_param("sssi", $status_u, $pesan, $id_surat, $id);
    if ($stmt->execute()) {
        $notif = "Permohonan berhasil diperbarui.";
        $notif_type = "success";
    } else {
        $notif = "Gagal memperbarui permohonan.";
        $notif_type = "error";
    }
}

// ----- HANDLE GET: delete -----
if (isset($_GET['hapus'])) {
    $hapus_id = intval($_GET['hapus']);

    // Hapus file terkait jika ada
    $fstmt = $conn->prepare("SELECT surat_pengantar, fotokopi_ktp, fotokopi_kk FROM permohonan WHERE id = ?");
    $fstmt->bind_param("i", $hapus_id);
    $fstmt->execute();
    $fres = $fstmt->get_result();
    if ($frow = $fres->fetch_assoc()) {
        foreach (['surat_pengantar','fotokopi_ktp','fotokopi_kk'] as $fn) {
            if (!empty($frow[$fn]) && file_exists(__DIR__ . '/uploads/' . $frow[$fn])) {
                @unlink(__DIR__ . '/uploads/' . $frow[$fn]);
            }
        }
    }

    $dstmt = $conn->prepare("DELETE FROM permohonan WHERE id = ?");
    $dstmt->bind_param("i", $hapus_id);
    if ($dstmt->execute()) {
        $notif = "Permohonan berhasil dihapus.";
        $notif_type = "success";
    } else {
        $notif = "Gagal menghapus permohonan.";
        $notif_type = "error";
    }
}

// ----- FILTER (GET) -----
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$status     = $_GET['status'] ?? '';

$where = [];
if (!empty($start_date) && !empty($end_date)) {
    $where[] = "DATE(created_at) BETWEEN '{$conn->real_escape_string($start_date)}' AND '{$conn->real_escape_string($end_date)}'";
} elseif (!empty($start_date)) {
    $where[] = "DATE(created_at) >= '{$conn->real_escape_string($start_date)}'";
} elseif (!empty($end_date)) {
    $where[] = "DATE(created_at) <= '{$conn->real_escape_string($end_date)}'";
}
if (!empty($status)) {
    $where[] = "status = '{$conn->real_escape_string($status)}'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM permohonan $where_sql ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">

<header class="flex items-center gap-3 bg-green-700 text-white p-4 shadow">
    <img src="assets/icons/logo.png" alt="Logo" class="w-10 h-10 rounded-full shadow">
    <h1 class="text-lg md:text-xl font-bold">Kalurahan Banguntapan - Dashboard Admin</h1>
    <div class="ml-auto">
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm">Logout</a>
    </div>
</header>

<main class="max-w-6xl mx-auto py-8 px-4">

    <?php if (!empty($notif)): ?>
        <div id="notification" 
             class="mb-4 p-3 rounded shadow transition-opacity duration-500 ease-in-out opacity-100
             <?= $notif_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($notif) ?>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <form method="GET" class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Awal</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full border rounded px-2 py-1">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full border rounded px-2 py-1">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded px-2 py-1">
                    <option value="">Semua</option>
                    <option value="diproses" <?= $status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="diterima" <?= $status === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                    <option value="ditolak" <?= $status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full">Filter</button>
                <a href="dashboard_admin.php" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded w-full text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-200 text-xs uppercase text-gray-700">
                    <th class="p-3">No</th>
                    <th class="p-3">Nama Lengkap</th>
                    <th class="p-3">Jenis Surat</th>
                    <th class="p-3">Keperluan</th>
                    <th class="p-3">Diajukan</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Proses</th>
                    <th class="p-3">Ubah Status</th>
                    <th class="p-3">Hapus</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-green-50">
                            <td class="p-3 text-center"><?= $no++ ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['jenis_surat']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['keperluan']) ?></td>
                            <td class="p-3"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                            <td class="p-3">
                                <?php
                                $badge_class = match($row['status']) {
                                    'diterima' => 'bg-green-200 text-green-800',
                                    'ditolak'  => 'bg-red-200 text-red-800',
                                    default    => 'bg-yellow-200 text-yellow-800'
                                };
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold <?= $badge_class ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <a href="proses_permohonan.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Proses</a>
                            </td>
                            <td class="p-3">
                                <form method="post" class="space-y-2">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <select name="status" class="w-full border rounded px-2 py-1">
                                        <option value="diproses" <?= $row['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                        <option value="diterima" <?= $row['status'] === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                                        <option value="ditolak" <?= $row['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                    <input type="text" name="id_surat" placeholder="ID Surat (jika diterima)" value="<?= htmlspecialchars($row['id_surat']) ?>" class="w-full border rounded px-2 py-1">
                                    <textarea name="pesan_admin" placeholder="Pesan untuk warga" class="w-full border rounded px-2 py-1"><?= htmlspecialchars($row['pesan_admin']) ?></textarea>
                                    <button type="submit" name="update_status" class="w-full bg-green-600 hover:bg-green-700 text-white py-1 rounded">Simpan</button>
                                </form>
                            </td>
                            <td class="p-3 text-center">
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus permohonan ini?')" class="inline-block bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-600">Tidak ada permohonan yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
<script>
    const notification = document.getElementById('notification');
    if (notification) {
        setTimeout(() => {
            notification.classList.remove('opacity-100');
            notification.classList.add('opacity-0');
        }, 3000);
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3500);
    }
</script>
</body>
</html>

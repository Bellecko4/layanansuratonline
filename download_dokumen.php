<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';
require_once('tcpdf/tcpdf.php'); // pastikan TCPDF sudah ada di folder tcpdf

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID permohonan tidak valid.");
}

$id = intval($_GET['id']);

// Ambil data permohonan
$stmt = $conn->prepare("SELECT * FROM permohonan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows < 1) {
    die("Permohonan tidak ditemukan.");
}

$data = $result->fetch_assoc();

// Daftar kolom dokumen
$dokumen_kolom = [
    'surat_pengantar_rt_rw' => 'Surat Pengantar RT/RW',
    'fotokopi_ktp' => 'Fotokopi KTP',
    'fotokopi_kk' => 'Fotokopi KK',
    'surat_pernyataan_usaha' => 'Surat Pernyataan Usaha',
    'fotokopi_ijazah' => 'Fotokopi Ijazah',
    'fotokopi_akta_kelahiran' => 'Fotokopi Akta Kelahiran',
    'surat_keterangan_lahir' => 'Surat Keterangan Lahir',
    'fotokopi_ktp_orang_tua' => 'Fotokopi KTP Orang Tua',
    'fotokopi_akta_pendirian_perusahaan' => 'Fotokopi Akta Pendirian Perusahaan',
    'surat_keterangan_kepemilikan_usaha' => 'Surat Keterangan Kepemilikan Usaha',
    'surat_pernyataan_domisili' => 'Surat Pernyataan Domisili',
    'fotokopi_akte_perkawinan_perceraian' => 'Fotokopi Akta Perkawinan/Perceraian',
    'surat_keterangan_instansi' => 'Surat Keterangan Instansi',
    'surat_keterangan_sekolah' => 'Surat Keterangan Sekolah',
    'surat_pernyataan_tidak_mampu' => 'Surat Pernyataan Tidak Mampu',
    'surat_pernyataan_penghasilan_bermeterai' => 'Surat Pernyataan Penghasilan Bermeterai',
    'tujuan_durasi_perjalanan' => 'Tujuan & Durasi Perjalanan',
    'fotokopi_kk_asal' => 'Fotokopi KK Asal',
    'surat_keterangan_pindah' => 'Surat Keterangan Pindah',
    'surat_keterangan_kematian' => 'Surat Keterangan Kematian',
    'fotokopi_ktp_almarhum' => 'Fotokopi KTP Almarhum'
];

// --- PDF Generation ---
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Kalurahan');
$pdf->SetTitle('Dokumen Permohonan');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 11);

// Halaman ringkasan
$pdf->AddPage();
$html_ringkasan = '
<h2>Detail Permohonan Surat</h2>
<table border="1" cellpadding="5">
    <tr><td width="30%">Jenis Surat</td><td>' . htmlspecialchars($data['jenis_surat']) . '</td></tr>
    <tr><td>Nama Lengkap</td><td>' . htmlspecialchars($data['nama_lengkap']) . '</td></tr>
    <tr><td>RT/RW</td><td>' . htmlspecialchars($data['rt_rw']) . '</td></tr>
    <tr><td>Dusun</td><td>' . htmlspecialchars($data['dusun']) . '</td></tr>
    <tr><td>Keperluan</td><td>' . htmlspecialchars($data['keperluan']) . '</td></tr>
</table>
';
$pdf->writeHTML($html_ringkasan, true, false, true, false, '');

// Fungsi untuk menambahkan gambar/file ke PDF
function addFileToPdf($pdf, $path, $title) {
    $fullPath = realpath($path);
    if ($fullPath && file_exists($fullPath)) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Write(0, $title, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln(5);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $pdf->Image($fullPath, '', '', 180);
        } else {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, 'File: ' . basename($fullPath), '', 0, 'L', true, 0, false, false, 0);
        }
    }
}

// Tambahkan semua dokumen
foreach ($dokumen_kolom as $kolom => $label) {
    if (!empty($data[$kolom]) && file_exists('uploads/' . $data[$kolom])) {
        addFileToPdf($pdf, 'uploads/' . $data[$kolom], $label);
    }
}

// Output PDF
$pdf_filename = 'Dokumen_Permohonan_' . $id . '.pdf';
$pdf->Output($pdf_filename, 'D');
exit;

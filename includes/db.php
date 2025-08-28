<?php
// Konfigurasi koneksi database
$host     = 'localhost';
$user     = 'root';
$password = ''; // ganti jika password MySQL Anda tidak kosong
$dbname   = 'surat_online'; // ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset agar mendukung karakter UTF-8
$conn->set_charset("utf8");

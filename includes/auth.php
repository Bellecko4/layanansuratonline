<?php
session_start();

// Fungsi ini digunakan di halaman yang hanya boleh diakses user dengan role tertentu.
function require_role($role) {
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
    header("Location: login.php");
    exit;
  }
}

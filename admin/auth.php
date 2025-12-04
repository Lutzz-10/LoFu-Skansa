<?php
session_start();

// Cek apakah user sudah login dan apakah levelnya admin.
// LEVEL_ADMIN diambil dari koneksi.php, jadi kita perlu include file itu.
include_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['level']) || $_SESSION['level'] != LEVEL_ADMIN) {
    // Jika tidak, redirect ke halaman login UTAMA.
    header("Location: ../login.php");
    exit();
}
?>

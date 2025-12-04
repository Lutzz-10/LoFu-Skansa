<?php
// Konfigurasi Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// --- KREDENSIAL DATABASE MySQL ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ganti dengan password Anda
define('DB_NAME', 'lofu_skansa');

// --- FUNGSI KONEKSI ---
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// --- VARIABEL SISTEM ---
define('LEVEL_SISWA', 1);
define('LEVEL_ADMIN', 99); 
?>

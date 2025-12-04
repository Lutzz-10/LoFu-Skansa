<?php
// Jadikan halaman ini sebagai entry point yang aman untuk area admin.
// 1. Panggil auth.php untuk memeriksa apakah user adalah admin.
// 2. Jika ya, auth.php akan membiarkan script lanjut.
// 3. Jika tidak, auth.php akan melempar user ke halaman login utama.
include 'auth.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Jika user berhasil melewati auth.php, berarti dia adalah admin.
// Langsung arahkan ke dashboard.
header("Location: dashboard.php");
exit();
?>

<?php
// File ini hanya untuk dijalankan sekali untuk membuat admin pertama.
// Setelah dijalankan, file ini sebaiknya dihapus dari server.
include '../config/koneksi.php';

echo "<pre>"; // Untuk tampilan yang lebih rapi

// --- DATA ADMIN ---
$nisn_nip_admin = 'admin';
$nama_admin = 'Administrator';
$password_admin = 'password123'; // Password sementara
$level_admin = LEVEL_ADMIN; // 99

// --- PROSES PEMBUATAN ---

// 1. Hash password
$hashed_password = password_hash($password_admin, PASSWORD_DEFAULT);
echo "Password asli: " . $password_admin . "\n";
echo "Password setelah di-hash: " . $hashed_password . "\n\n";

// 2. Cek apakah admin sudah ada
$stmt_check = $koneksi->prepare("SELECT user_id FROM users WHERE nisn_nip = ?");
$stmt_check->bind_param("s", $nisn_nip_admin);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo "HASIL: Akun admin dengan NISN/NIP 'admin' sudah ada. Tidak ada yang diubah.\n";
} else {
    // 3. Jika belum ada, masukkan ke database
    $stmt_insert = $koneksi->prepare("INSERT INTO users (nisn_nip, nama_lengkap, password, level) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("sssi", $nisn_nip_admin, $nama_admin, $hashed_password, $level_admin);

    if ($stmt_insert->execute()) {
        echo "HASIL: Akun admin berhasil dibuat!\n\n";
        echo "Anda sekarang bisa login menggunakan kredensial berikut:\n";
        echo "NISN/NIP: " . $nisn_nip_admin . "\n";
        echo "Password: " . $password_admin . "\n";
    } else {
        echo "ERROR: Gagal membuat akun admin. Pesan error: " . $stmt_insert->error . "\n";
    }
    $stmt_insert->close();
}

$stmt_check->close();
$koneksi->close();

echo "\nCATATAN PENTING: Demi keamanan, segera hapus file 'buat_admin.php' ini dari server Anda setelah selesai.";
echo "</pre>";
?>
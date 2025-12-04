<?php
include 'config/koneksi.php';
include 'includes/header.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn_nip = trim($_POST['nisn_nip']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // --- Validasi Sederhana ---
    if (empty($nisn_nip) || empty($nama_lengkap) || empty($password)) {
        $error_message = "Semua kolom wajib diisi.";
    } elseif ($password !== $konfirmasi_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } else {
        // Cek apakah NISN/NIP sudah ada
        $stmt_check = $koneksi->prepare("SELECT user_id FROM users WHERE nisn_nip = ?");
        $stmt_check->bind_param("s", $nisn_nip);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "NISN/NIP sudah terdaftar. Silakan gunakan yang lain.";
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database menggunakan prepared statement
            $stmt_insert = $koneksi->prepare("INSERT INTO users (nisn_nip, nama_lengkap, password, level) VALUES (?, ?, ?, ?)");
            $level_siswa = LEVEL_SISWA; // Ambil dari koneksi.php
            $stmt_insert->bind_param("sssi", $nisn_nip, $nama_lengkap, $hashed_password, $level_siswa);

            if ($stmt_insert->execute()) {
                $success_message = "Registrasi berhasil! Anda sekarang bisa <a href='login.php'>login</a>.";
            } else {
                $error_message = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!-- Page Header -->
<header class="bg-light py-4">
    <div class="container text-center">
    </div>
</header>

<div class="container mt-5">
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm" data-aos="fade-up">
            <div class="card-header text-center bg-success text-white">
                <h2>Registrasi Akun Baru</h2>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($success_message)) : ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_message)) : ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (empty($success_message)) : // Sembunyikan form jika sukses ?>
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="nisn_nip" class="form-label">NISN / NIP</label>
                        <input type="text" class="form-control" id="nisn_nip" name="nisn_nip" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success btn-lg">Daftar</button>
                    </div>
                </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
session_start();
include 'config/koneksi.php';

$error_message = '';

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn_nip = $_POST['nisn_nip'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($nisn_nip) || empty($password)) {
        $error_message = "NISN/NIP dan Password tidak boleh kosong!";
    } else {
        // Gunakan prepared statement untuk keamanan
        $stmt = $koneksi->prepare("SELECT user_id, nama_lengkap, password, level FROM users WHERE nisn_nip = ?");
        $stmt->bind_param("s", $nisn_nip);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['level'] = $user['level'];

                // Redirect berdasarkan level
                if ($user['level'] == LEVEL_ADMIN) {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Password yang Anda masukkan salah.";
            }
        } else {
            $error_message = "NISN/NIP tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<header class="bg-light py-4">
    <div class="container text-center">
    </div>
</header>

<div class="container mt-5">
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-secondary text-white">
                <h2>Login</h2>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error_message)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <!-- NISN / NIP -->
                    <div class="mb-3">
                        <label for="nisn_nip" class="form-label">NISN / NIP</label>
                        <input type="text" class="form-control" id="nisn_nip" name="nisn_nip" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>

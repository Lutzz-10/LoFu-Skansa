<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$barang_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($barang_id <= 0) {
    header("Location: index.php");
    exit();
}

// Ambil detail barang untuk ditampilkan
$stmt_barang = $koneksi->prepare("SELECT nama_barang FROM barang WHERE barang_id = ? AND jenis_laporan = 'hilang' AND status_barang = 'aktif'");
$stmt_barang->bind_param("i", $barang_id);
$stmt_barang->execute();
$result_barang = $stmt_barang->get_result();
if ($result_barang->num_rows == 0) {
    // Barang tidak valid atau sudah tidak aktif
    header("Location: hilang.php");
    exit();
}
$barang = $result_barang->fetch_assoc();


$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasi_temuan = trim($_POST['lokasi_temuan']);
    $keterangan = trim($_POST['keterangan']);
    $user_id_penemu = $_SESSION['user_id'];

    if (empty($lokasi_temuan)) {
        $message = "Lokasi penemuan harus diisi.";
        $message_type = "danger";
    } else {
        $bukti_foto_path = '';
        // Handle file upload
        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == 0) {
            $target_dir = "uploads/temuan/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = "temuan_" . $barang_id . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $target_file)) {
                    $bukti_foto_path = $new_filename;
                } else {
                    $message = "Gagal mengupload file bukti.";
                    $message_type = "danger";
                }
            } else {
                $message = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
                $message_type = "danger";
            }
        }

        if (empty($message)) {
            // Simpan laporan temuan ke tabel baru (misal: 'temuan')
            // Anda perlu membuat tabel ini di database
            $stmt_temuan = $koneksi->prepare("INSERT INTO temuan (barang_id, user_id_penemu, lokasi_penemuan, keterangan, foto_bukti) VALUES (?, ?, ?, ?, ?)");
            $stmt_temuan->bind_param("iisss", $barang_id, $user_id_penemu, $lokasi_temuan, $keterangan, $bukti_foto_path);

            if ($stmt_temuan->execute()) {
                // Update status barang menjadi 'klaim_tertunda' atau status baru 'menunggu_konfirmasi_penemu'
                $stmt_update = $koneksi->prepare("UPDATE barang SET status_barang = 'klaim_tertunda' WHERE barang_id = ?");
                $stmt_update->bind_param("i", $barang_id);
                $stmt_update->execute();
                $stmt_update->close();

                $success_message = "Laporan penemuan berhasil dikirim! Admin akan segera memverifikasi laporan Anda. Terima kasih!";
            } else {
                $message = "Gagal mengirim laporan. Silakan coba lagi.";
                $message_type = "danger";
            }
            $stmt_temuan->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<header class="bg-light py-4">
    <div class="container text-center">
        <h1 class="display-6 fw-bold">Lapor Penemuan Barang</h1>
        <p class="lead">Anda menemukan barang: <strong><?php echo htmlspecialchars($barang['nama_barang']); ?></strong></p>
    </div>
</header>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success text-center">
                            <h4><?php echo $success_message; ?></h4>
                            <a href="index.php" class="btn btn-primary mt-3">Kembali ke Home</a>
                        </div>
                    <?php else: ?>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="lokasi_temuan" class="form-label">Lokasi Penemuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lokasi_temuan" name="lokasi_temuan" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="4" placeholder="Jelaskan kondisi barang saat ditemukan..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="bukti_foto" class="form-label">Foto Barang Saat Ditemukan</label>
                                <input type="file" class="form-control" id="bukti_foto" name="bukti_foto" accept="image/*">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Kirim Laporan Penemuan</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

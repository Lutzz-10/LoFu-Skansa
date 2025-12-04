<?php
include 'auth.php';

// Get barang_id from URL
$barang_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($barang_id <= 0) {
    header("Location: kelola_barang.php");
    exit();
}

// Query detail barang
$query_barang = "SELECT b.*, u.nama_lengkap as pelapor_nama, u.nisn_nip as pelapor_nip
                 FROM barang b
                 JOIN users u ON b.user_id_pelapor = u.user_id
                 WHERE b.barang_id = ?";
$stmt = $koneksi->prepare($query_barang);
$stmt->bind_param("i", $barang_id);
$stmt->execute();
$result_barang = $stmt->get_result();

if ($result_barang->num_rows == 0) {
    header("Location: kelola_barang.php");
    exit();
}

$barang = $result_barang->fetch_assoc();

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_barang'])) {
    $nama_barang = trim($_POST['nama_barang']);
    $kategori = trim($_POST['kategori']);
    $lokasi = trim($_POST['lokasi']);
    $waktu_kejadian = $_POST['waktu_kejadian'];
    $deskripsi = trim($_POST['deskripsi']);
    $status_barang = $_POST['status_barang'];

    // Validate required fields
    if (empty($nama_barang) || empty($kategori) || empty($lokasi) || empty($waktu_kejadian)) {
        $message = "Semua field wajib diisi kecuali deskripsi.";
        $message_type = "danger";
    } else {
        // Update barang
        $stmt_update = $koneksi->prepare("UPDATE barang SET nama_barang = ?, kategori = ?, lokasi = ?, waktu_kejadian = ?, deskripsi = ?, status_barang = ? WHERE barang_id = ?");
        $stmt_update->bind_param("ssssssi", $nama_barang, $kategori, $lokasi, $waktu_kejadian, $deskripsi, $status_barang, $barang_id);

        if ($stmt_update->execute()) {
            $message = "Barang berhasil diperbarui!";
            $message_type = "success";

            // Refresh data barang
            $stmt->execute();
            $result_barang = $stmt->get_result();
            $barang = $result_barang->fetch_assoc();
        } else {
            $message = "Gagal memperbarui barang. Silakan coba lagi.";
            $message_type = "danger";
        }
        $stmt_update->close();
    }
}

$stmt->close();

// Hitung klaim tertunda untuk badge
$query_klaim = "SELECT COUNT(*) as count FROM klaim WHERE status_klaim = 'tertunda'";
$result_klaim = $koneksi->query($query_klaim);
$klaim_count = $result_klaim->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - LoFu Skansa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-user-shield"></i> Admin Panel</h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="klaim_tertunda.php"><i class="fas fa-hourglass-half"></i> Verifikasi Klaim <span class="badge bg-danger ms-1"><?php echo $klaim_count; ?></span></a></li>
        <li class="nav-item"><a class="nav-link" href="konfirmasi_temuan.php"><i class="fas fa-check-circle"></i> Konfirmasi Temuan <span class="badge bg-warning ms-1"><?php
            $query_temuan = "SELECT COUNT(*) as count FROM temuan WHERE status = 'tertunda'";
            $result_temuan = $koneksi->query($query_temuan);
            echo $result_temuan->fetch_assoc()['count'];
        ?></span></a></li>
        <li class="nav-item"><a class="nav-link active" href="kelola_barang.php"><i class="fas fa-box-open"></i> Kelola Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="kelola_user.php"><i class="fas fa-users"></i> Kelola User</a></li>
        <li class="nav-item mt-auto"><a class="nav-link" href="index.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Edit Barang</h1>
            <p class="text-muted">Edit detail laporan barang.</p>
        </div>
        <a href="kelola_barang.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </header>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-control" id="kategori" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Elektronik" <?php echo $barang['kategori'] == 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                                <option value="Dokumen" <?php echo $barang['kategori'] == 'Dokumen' ? 'selected' : ''; ?>>Dokumen</option>
                                <option value="Pakaian" <?php echo $barang['kategori'] == 'Pakaian' ? 'selected' : ''; ?>>Pakaian</option>
                                <option value="Aksesoris" <?php echo $barang['kategori'] == 'Aksesoris' ? 'selected' : ''; ?>>Aksesoris</option>
                                <option value="Lainnya" <?php echo $barang['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($barang['lokasi']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_kejadian" class="form-label">Waktu Kejadian <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="waktu_kejadian" name="waktu_kejadian" value="<?php echo date('Y-m-d\TH:i', strtotime($barang['waktu_kejadian'])); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="status_barang" class="form-label">Status Barang <span class="text-danger">*</span></label>
                            <select class="form-control" id="status_barang" name="status_barang" required>
                                <option value="aktif" <?php echo $barang['status_barang'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="klaim_tertunda" <?php echo $barang['status_barang'] == 'klaim_tertunda' ? 'selected' : ''; ?>>Klaim Tertunda</option>
                                <option value="selesai" <?php echo $barang['status_barang'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($barang['deskripsi']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" name="update_barang" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

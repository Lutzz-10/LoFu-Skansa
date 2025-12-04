<?php
include 'auth.php';

// Handle aksi approve/reject temuan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $temuan_id = $_POST['temuan_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Update status temuan ke diverifikasi dan status barang ke selesai
        $stmt = $koneksi->prepare("UPDATE temuan SET status = 'diverifikasi' WHERE temuan_id = ?");
        $stmt->bind_param("i", $temuan_id);
        $stmt->execute();

        // Ambil barang_id dari temuan
        $stmt_barang = $koneksi->prepare("SELECT barang_id FROM temuan WHERE temuan_id = ?");
        $stmt_barang->bind_param("i", $temuan_id);
        $stmt_barang->execute();
        $result_barang = $stmt_barang->get_result();
        $temuan = $result_barang->fetch_assoc();
        $barang_id = $temuan['barang_id'];

        // Update status barang ke selesai
        $stmt_update_barang = $koneksi->prepare("UPDATE barang SET status_barang = 'selesai' WHERE barang_id = ?");
        $stmt_update_barang->bind_param("i", $barang_id);
        $stmt_update_barang->execute();

        $stmt_update_barang->close();
        $stmt_barang->close();
    } elseif ($action == 'reject') {
        // Update status temuan ke ditolak dan status barang kembali ke aktif
        $stmt = $koneksi->prepare("UPDATE temuan SET status = 'ditolak' WHERE temuan_id = ?");
        $stmt->bind_param("i", $temuan_id);
        $stmt->execute();

        // Ambil barang_id dari temuan
        $stmt_barang = $koneksi->prepare("SELECT barang_id FROM temuan WHERE temuan_id = ?");
        $stmt_barang->bind_param("i", $temuan_id);
        $stmt_barang->execute();
        $result_barang = $stmt_barang->get_result();
        $temuan = $result_barang->fetch_assoc();
        $barang_id = $temuan['barang_id'];

        // Update status barang kembali ke aktif
        $stmt_update_barang = $koneksi->prepare("UPDATE barang SET status_barang = 'aktif' WHERE barang_id = ?");
        $stmt_update_barang->bind_param("i", $barang_id);
        $stmt_update_barang->execute();

        $stmt_update_barang->close();
        $stmt_barang->close();
    }

    $stmt->close();
    header("Location: konfirmasi_temuan.php");
    exit();
}

// Query temuan tertunda dengan join untuk mendapatkan nama barang dan penemu
$query = "SELECT t.temuan_id, b.nama_barang, u.nama_lengkap, t.tanggal_lapor, b.barang_id, b.deskripsi, b.lokasi, b.waktu_kejadian, t.lokasi_penemuan, t.keterangan, t.foto_bukti, up.nama_lengkap as nama_penemu, up.level
          FROM temuan t
          JOIN barang b ON t.barang_id = b.barang_id
          JOIN users u ON b.user_id_pelapor = u.user_id
          JOIN users up ON t.user_id_penemu = up.user_id
          WHERE t.status = 'tertunda'
          ORDER BY t.tanggal_lapor DESC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Temuan - LoFu Skansa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-user-shield"></i> Admin Panel</h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="klaim_tertunda.php">
                <i class="fas fa-hourglass-half"></i> Verifikasi Klaim
                <span class="badge bg-danger ms-1"><?php
                    $query_klaim = "SELECT COUNT(*) as count FROM klaim WHERE status_klaim = 'tertunda'";
                    $result_klaim = $koneksi->query($query_klaim);
                    echo $result_klaim->fetch_assoc()['count'];
                ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="konfirmasi_temuan.php">
                <i class="fas fa-check-circle"></i> Konfirmasi Temuan
                <span class="badge bg-warning ms-1"><?php echo $result->num_rows; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="kelola_barang.php">
                <i class="fas fa-box-open"></i> Kelola Barang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="kelola_user.php">
                <i class="fas fa-users"></i> Kelola User
            </a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="index.php?logout=1">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <header class="mb-4">
        <h1>Konfirmasi Laporan Temuan</h1>
        <p class="text-muted">Berikut adalah daftar laporan temuan barang hilang yang perlu Anda konfirmasi.</p>
    </header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Barang Ditemukan</th>
                            <th>Pelapor Penemuan</th>
                            <th>Dilaporkan Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['nama_barang']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nama_penemu']); ?> (<?php echo ($row['level'] == 1) ? 'Siswa' : 'Guru'; ?>)</td>
                                    <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_lapor'])); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#konfirmasiModal<?php echo $row['temuan_id']; ?>">
                                            <i class="fas fa-search"></i> Lihat Bukti & Konfirmasi
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada laporan temuan tertunda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<?php if ($result->num_rows > 0): ?>
    <?php $result->data_seek(0); // Reset pointer ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="modal fade" id="konfirmasiModal<?php echo $row['temuan_id']; ?>" tabindex="-1" aria-labelledby="konfirmasiModalLabel<?php echo $row['temuan_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="konfirmasiModalLabel<?php echo $row['temuan_id']; ?>">Detail Konfirmasi Laporan Temuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Informasi Barang -->
                            <div class="col-md-6">
                                <h5>Informasi Barang Hilang (dari Pelapor)</h5>
                                <p><strong>Nama Barang:</strong> <?php echo htmlspecialchars($row['nama_barang']); ?></p>
                                <p><strong>Lokasi Hilang:</strong> <?php echo htmlspecialchars($row['lokasi']); ?></p>
                                <p><strong>Waktu Hilang:</strong> <?php echo date('d M Y, H:i', strtotime($row['waktu_kejadian'])); ?></p>
                                <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <?php
                                // Ambil foto barang dari media
                                $stmt_media = $koneksi->prepare("SELECT file_path FROM media WHERE barang_id = ? LIMIT 1");
                                $stmt_media->bind_param("i", $row['barang_id']);
                                $stmt_media->execute();
                                $result_media = $stmt_media->get_result();
                                $media = $result_media->fetch_assoc();
                                ?>
                                <img src="<?php echo $media ? '../uploads/' . htmlspecialchars($media['file_path']) : 'https://via.placeholder.com/300x200.png?text=Foto+Barang'; ?>" class="img-fluid rounded" alt="Foto Barang">
                                <?php $stmt_media->close(); ?>
                            </div>
                            <!-- Bukti Temuan -->
                            <div class="col-md-6 border-start">
                                <h5>Bukti Laporan Temuan (dari Penemu)</h5>
                                <p><strong>Nama Penemu:</strong> <?php echo htmlspecialchars($row['nama_penemu']); ?></p>
                                <p><strong>Lokasi Ditemukan:</strong> <?php echo htmlspecialchars($row['lokasi_penemuan']); ?></p>
                                <p><strong>Waktu Lapor:</strong> <?php echo date('d M Y, H:i', strtotime($row['tanggal_lapor'])); ?></p>
                                <p><strong>Keterangan:</strong> "<?php echo htmlspecialchars($row['keterangan']); ?>"</p>
                                <h6>Foto Bukti Temuan:</h6>
                                <img src="<?php echo $row['foto_bukti'] ? '../uploads/' . htmlspecialchars($row['foto_bukti']) : 'https://via.placeholder.com/300x200.png?text=Bukti+Foto'; ?>" class="img-fluid rounded" alt="Bukti Foto Temuan">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="temuan_id" value="<?php echo $row['temuan_id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak laporan temuan ini?')"><i class="fas fa-times"></i> Tolak Laporan</button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="temuan_id" value="<?php echo $row['temuan_id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyetujui laporan temuan ini? Barang akan diselesaikan.')"><i class="fas fa-check"></i> Setujui & Selesaikan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

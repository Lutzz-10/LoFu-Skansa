<?php
include 'auth.php';

// Handle delete barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_barang'])) {
    $barang_id = $_POST['barang_id'];
    $stmt = $koneksi->prepare("DELETE FROM barang WHERE barang_id = ?");
    $stmt->bind_param("i", $barang_id);
    if ($stmt->execute()) {
        header("Location: kelola_barang.php?success=deleted");
        exit();
    }
    $stmt->close();
}

// Query semua barang dengan join
$query = "SELECT b.barang_id, b.nama_barang, b.jenis_laporan, b.kategori, u.nama_lengkap, b.status_barang, b.created_at
          FROM barang b
          JOIN users u ON b.user_id_pelapor = u.user_id
          ORDER BY b.created_at DESC";
$result = $koneksi->query($query);

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
    <title>Kelola Barang - LoFu Skansa</title>
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
            <h1>Kelola Barang</h1>
            <p class="text-muted">Manajemen semua laporan barang yang hilang dan ditemukan.</p>
        </div>
        <!-- <button class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Laporan Baru</button> -->
    </header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Status</th>
                            <th>Tgl Laporan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['nama_barang']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $row['jenis_laporan'] == 'ditemukan' ? 'bg-info' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($row['jenis_laporan']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td>
                                        <span class="badge <?php
                                            if ($row['status_barang'] == 'aktif') echo 'bg-success';
                                            elseif ($row['status_barang'] == 'klaim_tertunda') echo 'bg-warning';
                                            elseif ($row['status_barang'] == 'selesai') echo 'bg-secondary';
                                        ?>">
                                            <?php
                                            if ($row['status_barang'] == 'aktif') echo 'Aktif';
                                            elseif ($row['status_barang'] == 'klaim_tertunda') echo 'Klaim Tertunda';
                                            elseif ($row['status_barang'] == 'selesai') echo 'Selesai';
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <a href="../detail_barang.php?id=<?php echo $row['barang_id']; ?>" class="btn btn-success btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_barang.php?id=<?php echo $row['barang_id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                            <input type="hidden" name="barang_id" value="<?php echo $row['barang_id']; ?>">
                                            <button type="submit" name="delete_barang" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada laporan barang.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

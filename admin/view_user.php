<?php
include 'auth.php';

// Get user_id from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header("Location: kelola_user.php");
    exit();
}

// Query detail user
$query_user = "SELECT user_id, nama_lengkap, nisn_nip, level, created_at FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user->num_rows == 0) {
    header("Location: kelola_user.php");
    exit();
}

$user = $result_user->fetch_assoc();

// Query barang yang dilaporkan user
$query_barang = "SELECT barang_id, nama_barang, jenis_laporan, kategori, status_barang, created_at
                 FROM barang WHERE user_id_pelapor = ? ORDER BY created_at DESC";
$stmt_barang = $koneksi->prepare($query_barang);
$stmt_barang->bind_param("i", $user_id);
$stmt_barang->execute();
$result_barang = $stmt_barang->get_result();

$stmt->close();
$stmt_barang->close();

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
    <title>Detail User - LoFu Skansa</title>
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
        <li class="nav-item"><a class="nav-link" href="kelola_barang.php"><i class="fas fa-box-open"></i> Kelola Barang</a></li>
        <li class="nav-item"><a class="nav-link active" href="kelola_user.php"><i class="fas fa-users"></i> Kelola User</a></li>
        <li class="nav-item mt-auto"><a class="nav-link" href="index.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Detail User</h1>
            <p class="text-muted">Informasi lengkap pengguna.</p>
        </div>
        <a href="kelola_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </header>

    <div class="row">
        <!-- Detail User -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Informasi User</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Nama Lengkap:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>NISN / NIP:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user['nisn_nip']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Level:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge <?php echo $user['level'] == LEVEL_ADMIN ? 'bg-primary' : 'bg-secondary'; ?>">
                                <?php echo $user['level'] == LEVEL_ADMIN ? 'Admin' : 'Siswa'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Tanggal Daftar:</strong></div>
                        <div class="col-sm-8"><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laporan Barang -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Laporan Barang (<?php echo $result_barang->num_rows; ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if ($result_barang->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Tgl Laporan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($barang = $result_barang->fetch_assoc()): ?>
                                        <tr>
                                            <td><small><?php echo htmlspecialchars($barang['nama_barang']); ?></small></td>
                                            <td>
                                                <small><span class="badge <?php echo $barang['jenis_laporan'] == 'ditemukan' ? 'bg-info' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($barang['jenis_laporan']); ?>
                                                </span></small>
                                            </td>
                                            <td>
                                                <small><span class="badge <?php
                                                    if ($barang['status_barang'] == 'aktif') echo 'bg-success';
                                                    elseif ($barang['status_barang'] == 'klaim_tertunda') echo 'bg-warning';
                                                    elseif ($barang['status_barang'] == 'selesai') echo 'bg-secondary';
                                                ?>">
                                                    <?php
                                                    if ($barang['status_barang'] == 'aktif') echo 'Aktif';
                                                    elseif ($barang['status_barang'] == 'klaim_tertunda') echo 'Klaim Tertunda';
                                                    elseif ($barang['status_barang'] == 'selesai') echo 'Selesai';
                                                    ?>
                                                </span></small>
                                            </td>
                                            <td><small><?php echo date('d M Y', strtotime($barang['created_at'])); ?></small></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada laporan barang.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

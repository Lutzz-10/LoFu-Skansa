<?php
include 'auth.php';

// Query statistik dinamis
$query_klaim_tertunda = "SELECT COUNT(*) as count FROM klaim WHERE status_klaim = 'tertunda'";
$result_klaim = $koneksi->query($query_klaim_tertunda);
$klaim_tertunda = $result_klaim->fetch_assoc()['count'];

$query_temuan_tertunda = "SELECT COUNT(*) as count FROM temuan WHERE status = 'tertunda'";
$result_temuan = $koneksi->query($query_temuan_tertunda);
$temuan_tertunda = $result_temuan->fetch_assoc()['count'];

$query_barang_aktif = "SELECT COUNT(*) as count FROM barang WHERE status_barang = 'aktif'";
$result_barang = $koneksi->query($query_barang_aktif);
$barang_aktif = $result_barang->fetch_assoc()['count'];

$query_jumlah_user = "SELECT COUNT(*) as count FROM users";
$result_user = $koneksi->query($query_jumlah_user);
$jumlah_user = $result_user->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LoFu Skansa</title>
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
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="klaim_tertunda.php">
                <i class="fas fa-hourglass-half"></i> Verifikasi Klaim
                <span class="badge bg-danger ms-1"><?php echo $klaim_tertunda; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="konfirmasi_temuan.php">
                <i class="fas fa-check-circle"></i> Konfirmasi Temuan
                <span class="badge bg-warning ms-1"><?php echo $temuan_tertunda; ?></span>
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
        <h1>Admin Dashboard</h1>
        <p class="text-muted">Selamat datang, Admin!</p>
    </header>

    <!-- Statistik Section -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card text-white bg-warning mb-3">
                <div class="card-body">
                    <div>
                        <h5 class="card-title">Klaim Tertunda</h5>
                        <p class="card-text fs-1 fw-bold"><?php echo $klaim_tertunda; ?></p>
                    </div>
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-white bg-info mb-3">
                <div class="card-body">
                    <div>
                        <h5 class="card-title">Temuan Tertunda</h5>
                        <p class="card-text fs-1 fw-bold"><?php echo $temuan_tertunda; ?></p>
                    </div>
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-white bg-success mb-3">
                <div class="card-body">
                    <div>
                        <h5 class="card-title">Barang Aktif</h5>
                        <p class="card-text fs-1 fw-bold"><?php echo $barang_aktif; ?></p>
                    </div>
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-white bg-primary mb-3">
                <div class="card-body">
                    <div>
                        <h5 class="card-title">Jumlah User</h5>
                        <p class="card-text fs-1 fw-bold"><?php echo $jumlah_user; ?></p>
                    </div>
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity (Placeholder) -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Aktivitas Terbaru</h5>
        </div>
        <div class="card-body">
            <p>Tidak ada aktivitas terbaru.</p>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include 'auth.php';

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    // Cek apakah user yang akan dihapus adalah admin
    $stmt_check = $koneksi->prepare("SELECT level FROM users WHERE user_id = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $user = $result_check->fetch_assoc();

    if ($user['level'] == LEVEL_ADMIN) {
        header("Location: kelola_user.php?error=cannot_delete_admin");
        exit();
    } else {
        $stmt = $koneksi->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: kelola_user.php?success=deleted");
            exit();
        }
    }
    $stmt_check->close();
    $stmt->close();
}

// Query semua user
$query = "SELECT user_id, nama_lengkap, nisn_nip, level, created_at FROM users ORDER BY created_at DESC";
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
    <title>Kelola User - LoFu Skansa</title>
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
            <h1>Kelola User</h1>
            <p class="text-muted">Manajemen semua pengguna sistem.</p>
        </div>
        <!-- <button class="btn btn-primary"><i class="fas fa-plus"></i> Tambah User Baru</button> -->
    </header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>NISN / NIP</th>
                            <th>Level</th>
                            <th>Tanggal Daftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nisn_nip']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['level'] == LEVEL_ADMIN ? 'bg-primary' : 'bg-secondary'; ?>">
                                            <?php echo $row['level'] == LEVEL_ADMIN ? 'Admin' : 'Siswa'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <a href="view_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-success btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($row['level'] != LEVEL_ADMIN): ?>
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Tidak dapat menghapus admin">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada user terdaftar.</td>
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

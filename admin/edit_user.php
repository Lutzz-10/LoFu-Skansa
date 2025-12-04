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

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nisn_nip = trim($_POST['nisn_nip']);
    $level = (int)$_POST['level'];

    // Validate required fields
    if (empty($nama_lengkap) || empty($nisn_nip)) {
        $message = "Nama lengkap dan NISN/NIP wajib diisi.";
        $message_type = "danger";
    } else {
        // Update user
        $stmt_update = $koneksi->prepare("UPDATE users SET nama_lengkap = ?, nisn_nip = ?, level = ? WHERE user_id = ?");
        $stmt_update->bind_param("ssii", $nama_lengkap, $nisn_nip, $level, $user_id);

        if ($stmt_update->execute()) {
            $message = "User berhasil diperbarui!";
            $message_type = "success";

            // Refresh data user
            $stmt->execute();
            $result_user = $stmt->get_result();
            $user = $result_user->fetch_assoc();
        } else {
            $message = "Gagal memperbarui user. Silakan coba lagi.";
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
    <title>Edit User - LoFu Skansa</title>
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
            <h1>Edit User</h1>
            <p class="text-muted">Edit informasi pengguna.</p>
        </div>
        <a href="kelola_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nisn_nip" class="form-label">NISN / NIP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nisn_nip" name="nisn_nip" value="<?php echo htmlspecialchars($user['nisn_nip']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="1" <?php echo $user['level'] == 1 ? 'selected' : ''; ?>>Siswa</option>
                                <option value="2" <?php echo $user['level'] == 2 ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Daftar</label>
                            <input type="text" class="form-control" value="<?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" name="update_user" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include 'config/koneksi.php';

// Cek apakah user login
session_start();
$user_logged_in = isset($_SESSION['user_id']);
$user_level = isset($_SESSION['level']) ? $_SESSION['level'] : 1;

// Get barang_id from URL
$barang_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($barang_id <= 0) {
    header("Location: index.php");
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
    header("Location: index.php");
    exit();
}

$barang = $result_barang->fetch_assoc();

// Query foto barang
$query_media = "SELECT file_path FROM media WHERE barang_id = ? ORDER BY uploaded_at ASC";
$stmt_media = $koneksi->prepare($query_media);
$stmt_media->bind_param("i", $barang_id);
$stmt_media->execute();
$result_media = $stmt_media->get_result();
$foto_barang = [];
while ($row = $result_media->fetch_assoc()) {
    $foto_barang[] = $row['file_path'];
}

// Handle klaim submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_klaim'])) {
    if (!$user_logged_in) {
        $message = "Anda harus login untuk mengajukan klaim.";
        $message_type = "danger";
    } else {
        $keterangan_klaim = trim($_POST['keterangan_klaim']);
        $bukti_foto_path = '';

        // Handle file upload
        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == 0) {
            $target_dir = "uploads/klaim/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = "klaim_" . $barang_id . "_" . time() . "." . $file_extension;
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
            // Insert klaim
            $stmt_klaim = $koneksi->prepare("INSERT INTO klaim (barang_id, user_id_pengaju, keterangan_klaim, bukti_foto_path) VALUES (?, ?, ?, ?)");
            $stmt_klaim->bind_param("iiss", $barang_id, $_SESSION['user_id'], $keterangan_klaim, $bukti_foto_path);

            if ($stmt_klaim->execute()) {
                // Update status barang ke klaim_tertunda
                $stmt_update = $koneksi->prepare("UPDATE barang SET status_barang = 'klaim_tertunda' WHERE barang_id = ?");
                $stmt_update->bind_param("i", $barang_id);
                $stmt_update->execute();
                $stmt_update->close();

                $message = "Klaim berhasil diajukan! Tunggu verifikasi dari admin.";
                $message_type = "success";

                // Refresh data barang
                $stmt->execute();
                $result_barang = $stmt->get_result();
                $barang = $result_barang->fetch_assoc();
            } else {
                $message = "Gagal mengajukan klaim. Silakan coba lagi.";
                $message_type = "danger";
            }
            $stmt_klaim->close();
        }
    }
}

$stmt->close();
$stmt_media->close();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <!-- Galeri Foto -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($foto_barang)): ?>
                        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($foto_barang as $index => $foto): ?>
                                    <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                        <img src="uploads/<?php echo htmlspecialchars($foto); ?>" class="d-block w-100" alt="Foto Barang" style="max-height: 400px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($foto_barang) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/500x400.png?text=Tidak+ada+foto" class="img-fluid" alt="Tidak ada foto">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detail Barang -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo htmlspecialchars($barang['nama_barang']); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Jenis:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge <?php echo $barang['jenis_laporan'] == 'ditemukan' ? 'bg-info' : 'bg-danger'; ?>">
                                <?php echo ucfirst($barang['jenis_laporan']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Kategori:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($barang['kategori']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Lokasi:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($barang['lokasi']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Waktu:</strong></div>
                        <div class="col-sm-8"><?php echo date('d M Y, H:i', strtotime($barang['waktu_kejadian'])); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Pelapor:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($barang['pelapor_nama']); ?> (<?php echo htmlspecialchars($barang['pelapor_nip']); ?>)</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge <?php
                                if ($barang['status_barang'] == 'aktif') echo 'bg-success';
                                elseif ($barang['status_barang'] == 'klaim_tertunda') echo 'bg-warning';
                                elseif ($barang['status_barang'] == 'selesai') echo 'bg-secondary';
                            ?>">
                                <?php
                                if ($barang['status_barang'] == 'aktif') echo 'Aktif';
                                elseif ($barang['status_barang'] == 'klaim_tertunda') echo 'Klaim Tertunda';
                                elseif ($barang['status_barang'] == 'selesai') echo 'Selesai';
                                ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($barang['jenis_laporan'] == 'hilang' && !empty($barang['kontak_pelapor'])): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Kontak Pelapor:</strong></div>
                            <div class="col-sm-8">
                                <a href="https://wa.me/<?php echo htmlspecialchars($barang['kontak_pelapor']); ?>" target="_blank"><?php echo htmlspecialchars($barang['kontak_pelapor']); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($barang['deskripsi'])): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Deskripsi:</strong></div>
                            <div class="col-sm-8"><?php echo nl2br(htmlspecialchars($barang['deskripsi'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Tombol Lapor Temuan untuk Barang Hilang -->
                    <?php if ($barang['status_barang'] == 'aktif' && $barang['jenis_laporan'] == 'hilang'): ?>
                        <div class="d-grid gap-2 mt-4">
                            <a href="lapor_temuan.php?id=<?php echo $barang['barang_id']; ?>" class="btn btn-lg btn-success">
                                Saya Menemukan Barang Ini
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Form Klaim -->
                    <?php if ($barang['status_barang'] == 'aktif' && $barang['jenis_laporan'] == 'ditemukan'): ?>
                        <?php if (!$user_logged_in): ?>
                            <div class="alert alert-warning">
                                <strong>Login diperlukan!</strong> Anda harus <a href="login.php">login</a> untuk mengajukan klaim.
                            </div>
                        <?php else: ?>
                            <hr>
                            <h5>Ajukan Klaim</h5>
                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="keterangan_klaim" class="form-label">Keterangan Klaim <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="keterangan_klaim" name="keterangan_klaim" rows="3" required
                                              placeholder="Jelaskan mengapa barang ini milik Anda..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="bukti_foto" class="form-label">Bukti Foto (Opsional)</label>
                                    <input type="file" class="form-control" id="bukti_foto" name="bukti_foto" accept="image/*">
                                    <div class="form-text">Upload foto bukti kepemilikan (ID, struk, dll.)</div>
                                </div>
                                <button type="submit" name="submit_klaim" class="btn btn-success">Ajukan Klaim</button>
                            </form>
                        <?php endif; ?>
                    <?php elseif ($barang['status_barang'] == 'klaim_tertunda'): ?>
                        <div class="alert alert-info">
                            <strong>Klaim sedang diproses!</strong> Barang ini sedang menunggu verifikasi dari admin.
                        </div>
                    <?php elseif ($barang['status_barang'] == 'selesai'): ?>
                        <div class="alert alert-success">
                            <strong>Barang sudah dikembalikan!</strong> Klaim untuk barang ini sudah selesai diproses.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lapor Temuan -->
<div class="modal fade" id="laporTemuanModal" tabindex="-1" aria-labelledby="laporTemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="laporTemuanModalLabel">Instruksi Pengembalian Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fs-5 text-center">Terima kasih telah menemukan barang ini!</p>
                <p>Untuk mengembalikan barang kepada pemiliknya, silakan serahkan barang ke <strong>Ruang Tata Usaha (TU)</strong> sekolah.</p>
                <p>Saat menyerahkan, mohon informasikan detail berikut kepada petugas:</p>
                <ul>
                    <li><strong>Nama Barang:</strong> <?php echo htmlspecialchars($barang['nama_barang']); ?></li>
                    <li><strong>ID Laporan:</strong> #<?php echo $barang['barang_id']; ?></li>
                </ul>
                <p>Dengan begitu, kami dapat segera menghubungi pemilik asli barang tersebut. Partisipasi Anda sangat kami hargai.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
include 'config/koneksi.php';

// Cek dan sambungkan ulang jika koneksi terputus
if (!mysqli_ping($koneksi)) {
    mysqli_close($koneksi); // Tutup koneksi lama yang tidak valid
    $koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

// Query barang ditemukan yang aktif
$query = "SELECT b.barang_id, b.nama_barang, b.kategori, b.lokasi, b.waktu_kejadian, b.deskripsi, b.status_barang, u.nama_lengkap,
          (SELECT file_path FROM media WHERE barang_id = b.barang_id LIMIT 1) as foto
          FROM barang b
          JOIN users u ON b.user_id_pelapor = u.user_id
          WHERE b.jenis_laporan = 'ditemukan' AND b.status_barang = 'aktif'
          ORDER BY b.created_at DESC";
$result = $koneksi->query($query);
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<header class="bg-light py-4">
    <div class="container text-center">
    </div>
</header>

<div class="container mt-5">
<div class="row">
    <!-- Sidebar Filter -->
    <aside class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Pencarian</h5>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori" name="kategori">
                            <option value="">Semua Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Dokumen">Dokumen</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi Ditemukan</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Masukkan lokasi">
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="col-md-9">
        <h2 class="mb-4">Daftar Barang Ditemukan</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo $row['foto'] ? 'uploads/' . $row['foto'] : 'https://via.placeholder.com/300x200.png?text=Tidak+ada+foto'; ?>" class="card-img-top" alt="Foto Barang">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['nama_barang']); ?></h5>
                                <p class="card-text text-muted mb-1">Lokasi: <?php echo htmlspecialchars($row['lokasi']); ?></p>
                                <p class="card-text text-muted">Tanggal: <?php echo date('d M Y', strtotime($row['waktu_kejadian'])); ?></p>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="detail_barang.php?id=<?php echo $row['barang_id']; ?>" class="btn btn-primary w-100">Lihat Detail & Klaim</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <h4 class="alert-heading">Belum ada barang ditemukan</h4>
                        <p>Belum ada laporan barang yang ditemukan saat ini.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination (placeholder) -->
        <?php if ($result->num_rows > 0): ?>
        <nav aria-label="Page navigation" data-aos="fade-up">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </main>
</div>
</div>

<?php include 'includes/footer.php'; ?>

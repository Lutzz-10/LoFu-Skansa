<?php 
include 'includes/header.php'; 
include 'config/koneksi.php';

// --- HANDLE SEARCH ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = "%" . $search_term . "%";

// --- FETCH STATISTICS ---
// (Statistik tidak berubah oleh pencarian)
$ditemukan_res = $koneksi->query("SELECT COUNT(*) as total FROM barang WHERE jenis_laporan = 'ditemukan' AND status_barang = 'aktif'");
$ditemukan_count = $ditemukan_res->fetch_assoc()['total'];

$hilang_res = $koneksi->query("SELECT COUNT(*) as total FROM barang WHERE jenis_laporan = 'hilang' AND status_barang = 'aktif'");
$hilang_count = $hilang_res->fetch_assoc()['total'];

$selesai_res = $koneksi->query("SELECT COUNT(*) as total FROM barang WHERE status_barang = 'selesai'");
$selesai_count = $selesai_res->fetch_assoc()['total'];


// --- FETCH RECENTLY FOUND ITEMS (with search) ---
$query_ditemukan = "SELECT b.barang_id, b.nama_barang, b.waktu_kejadian, 
                    (SELECT file_path FROM media WHERE barang_id = b.barang_id LIMIT 1) as foto
                    FROM barang b
                    WHERE b.jenis_laporan = 'ditemukan' AND b.status_barang = 'aktif'
                    AND (b.nama_barang LIKE ? OR b.deskripsi LIKE ?)
                    ORDER BY b.created_at DESC
                    LIMIT 6";
$stmt_ditemukan = $koneksi->prepare($query_ditemukan);
$stmt_ditemukan->bind_param("ss", $search_query, $search_query);
$stmt_ditemukan->execute();
$result_ditemukan = $stmt_ditemukan->get_result();

// --- FETCH RECENTLY LOST ITEMS (with search) ---
$query_hilang = "SELECT b.barang_id, b.nama_barang, b.waktu_kejadian, 
                (SELECT file_path FROM media WHERE barang_id = b.barang_id LIMIT 1) as foto
                FROM barang b
                WHERE b.jenis_laporan = 'hilang' AND b.status_barang = 'aktif'
                AND (b.nama_barang LIKE ? OR b.deskripsi LIKE ?)
                ORDER BY b.created_at DESC
                LIMIT 6";
$stmt_hilang = $koneksi->prepare($query_hilang);
$stmt_hilang->bind_param("ss", $search_query, $search_query);
$stmt_hilang->execute();
$result_hilang = $stmt_hilang->get_result();
?>

<!-- Hero Section -->
<style>
    .hero-section {
        background: url('assets/skansa.jpg') no-repeat center center;
        background-size: cover;
        position: relative;
        color: white;
        height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .hero-content {
        position: relative;
        z-index: 1;
    }
</style>
<header class="hero-section">
    <div class="hero-content" data-aos="fade-up">
        <h1 class="display-4 fw-bold">Ayo Temukan Milikmu, Kembalikan Hak Orang Lain.</h1>
        <p class="lead my-4">Platform Lost and Found untuk seluruh warga SMK Negeri 1 Bawang.</p>
        <form action="index.php" method="GET" class="mx-auto" style="max-width: 600px;">
            <div class="input-group mb-3">
                <input type="text" class="form-control form-control-lg" name="search" placeholder="Cari barang..." aria-label="Cari barang" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-warning" type="submit">Cari</button>
            </div>
        </form>
    </div>
</header>

<div class="container mt-5">
    <?php if (!empty($search_term)): ?>
        <div class="alert alert-info text-center">
            Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search_term); ?>"</strong>. <a href="index.php">Hapus Pencarian</a>
        </div>
    <?php endif; ?>

    <!-- Statistik Section -->
    <section class="text-center mb-5" data-aos="fade-up">
        <h2 class="mb-4">Statistik Laporan Aktif</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title display-4 fw-bold text-primary"><?php echo $ditemukan_count; ?></h3>
                        <p class="card-text fs-5">Barang Ditemukan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title display-4 fw-bold text-danger"><?php echo $hilang_count; ?></h3>
                        <p class="card-text fs-5">Barang Hilang</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title display-4 fw-bold text-success"><?php echo $selesai_count; ?></h3>
                        <p class="card-text fs-5">Barang Telah Kembali</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Barang Baru Ditemukan -->
    <section class="mb-5">
        <h2 class="mb-4 text-center">Baru Ditemukan</h2>
        <div class="row">
            <?php if ($result_ditemukan->num_rows > 0): ?>
                <?php while ($row = $result_ditemukan->fetch_assoc()): ?>
                <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $row['foto'] ? 'uploads/' . $row['foto'] : 'https://via.placeholder.com/300x200.png?text=Tidak+ada+foto'; ?>" class="card-img-top" alt="Foto Barang" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['nama_barang']); ?></h5>
                            <p class="card-text text-muted">Ditemukan pada: <?php echo date('d M Y', strtotime($row['waktu_kejadian'])); ?></p>
                            <a href="detail_barang.php?id=<?php echo $row['barang_id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Belum ada barang yang ditemukan.</p>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="ditemukan.php" class="btn btn-outline-primary">Lihat Semua Barang Ditemukan</a>
        </div>
    </section>

    <!-- Barang Baru Hilang -->
    <section class="mb-5">
        <h2 class="mb-4 text-center">Baru Kehilangan</h2>
        <div class="row">
            <?php if ($result_hilang->num_rows > 0): ?>
                <?php while ($row = $result_hilang->fetch_assoc()): ?>
                <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $row['foto'] ? 'uploads/' . $row['foto'] : 'https://via.placeholder.com/300x200.png?text=Tidak+ada+foto'; ?>" class="card-img-top" alt="Foto Barang" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['nama_barang']); ?></h5>
                            <p class="card-text text-muted">Hilang pada: <?php echo date('d M Y', strtotime($row['waktu_kejadian'])); ?></p>
                            <a href="detail_barang.php?id=<?php echo $row['barang_id']; ?>" class="btn btn-info text-white">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Belum ada laporan barang hilang.</p>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="hilang.php" class="btn btn-outline-info">Lihat Semua Barang Hilang</a>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="text-center bg-light p-5 rounded shadow-sm">
        <h2 class="mb-4">Kehilangan atau Menemukan Sesuatu?</h2>
        <p class="lead mb-4">Jangan ragu untuk melapor. Partisipasi Anda sangat berarti bagi kami.</p>
        <a href="laporkan.php" class="btn btn-success btn-lg me-2">Laporkan Barang Ditemukan</a>
        <a href="laporkan.php" class="btn btn-danger btn-lg">Laporkan Barang Hilang</a>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
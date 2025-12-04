<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $jenis_laporan = $_POST['jenis_laporan'];
    $nama_barang = trim($_POST['nama_barang']);
    $kategori = $_POST['kategori'];
    $lokasi = trim($_POST['lokasi']);
    $waktu_kejadian = $_POST['waktu_kejadian'];
    $deskripsi = trim($_POST['deskripsi']);
    $kontak_pelapor = ($jenis_laporan == 'hilang') ? trim($_POST['kontak_pelapor']) : NULL;
    $user_id = $_SESSION['user_id'];

    // Validasi input
    $errors = [];
    if (empty($nama_barang)) $errors[] = "Nama barang harus diisi";
    if (empty($kategori)) $errors[] = "Kategori harus dipilih";
    if (empty($lokasi)) $errors[] = "Lokasi harus diisi";
    if (empty($waktu_kejadian)) $errors[] = "Waktu kejadian harus diisi";
    if ($jenis_laporan == 'hilang' && empty($kontak_pelapor)) $errors[] = "Nomor telepon harus diisi untuk laporan kehilangan";

    if (empty($errors)) {
        // Insert ke tabel barang
        $stmt = $koneksi->prepare("INSERT INTO barang (user_id_pelapor, jenis_laporan, nama_barang, kategori, lokasi, waktu_kejadian, deskripsi, kontak_pelapor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $jenis_laporan, $nama_barang, $kategori, $lokasi, $waktu_kejadian, $deskripsi, $kontak_pelapor);

        if ($stmt->execute()) {
            $barang_id = $stmt->insert_id;

            // Handle upload foto
            if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $max_files = 3;
                $uploaded_count = 0;

                foreach ($_FILES['foto']['name'] as $key => $filename) {
                    if ($uploaded_count >= $max_files) break;

                    if ($_FILES['foto']['error'][$key] == 0) {
                        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                        if (in_array($file_extension, $allowed_extensions)) {
                            $new_filename = "barang_" . $barang_id . "_" . ($uploaded_count + 1) . "." . $file_extension;
                            $target_file = $upload_dir . $new_filename;

                            if (move_uploaded_file($_FILES['foto']['tmp_name'][$key], $target_file)) {
                                // Insert ke tabel media
                                $stmt_media = $koneksi->prepare("INSERT INTO media (barang_id, file_path) VALUES (?, ?)");
                                $stmt_media->bind_param("is", $barang_id, $new_filename);
                                $stmt_media->execute();
                                $stmt_media->close();
                                $uploaded_count++;
                            }
                        }
                    }
                }
            }

            $message = "Laporan berhasil dikirim! Terima kasih atas partisipasi Anda.";
            $message_type = "success";

            // Reset form
            $_POST = [];
        } else {
            $message = "Gagal mengirim laporan. Silakan coba lagi.";
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Harap lengkapi semua field yang wajib diisi.";
        $message_type = "danger";
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<header class="bg-light py-4">
    <div class="container text-center">
    </div>
</header>

<div class="container mt-5">
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm" data-aos="fade-up">
            <div class="card-header text-center bg-primary text-white">
                <h2>Formulir Laporan</h2>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <!-- Jenis Laporan -->
                    <div class="mb-4 text-center">
                        <p class="form-label">Apa yang ingin Anda laporkan?</p>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_laporan" id="ditemukan" value="ditemukan" <?php echo (!isset($_POST['jenis_laporan']) || $_POST['jenis_laporan'] == 'ditemukan') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ditemukan">Saya Menemukan Barang</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_laporan" id="hilang" value="hilang" <?php echo (isset($_POST['jenis_laporan']) && $_POST['jenis_laporan'] == 'hilang') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="hilang">Saya Kehilangan Barang</label>
                        </div>
                    </div>

                    <!-- Nama Barang -->
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : ''; ?>" required>
                    </div>

                    <div class="row">
                        <!-- Kategori -->
                        <div class="col-md-6 mb-3">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="" disabled <?php echo !isset($_POST['kategori']) ? 'selected' : ''; ?>>Pilih Kategori</option>
                                <option value="Elektronik" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                                <option value="Aksesoris" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Aksesoris') ? 'selected' : ''; ?>>Aksesoris (Jam, Kacamata)</option>
                                <option value="Dokumen" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Dokumen') ? 'selected' : ''; ?>>Dokumen (Kartu, Buku)</option>
                                <option value="Pakaian" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Pakaian') ? 'selected' : ''; ?>>Pakaian (Jaket, Topi)</option>
                                <option value="Lainnya" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                        </div>
                        <!-- Lokasi -->
                        <div class="col-md-6 mb-3">
                            <label for="lokasi" class="form-label">Lokasi Terakhir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Perpustakaan, Kantin" value="<?php echo isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : ''; ?>" required>
                        </div>
                    </div>

                    <!-- Waktu Kejadian -->
                    <div class="mb-3">
                        <label for="waktu_kejadian" class="form-label">Waktu Kejadian <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="waktu_kejadian" name="waktu_kejadian" value="<?php echo isset($_POST['waktu_kejadian']) ? htmlspecialchars($_POST['waktu_kejadian']) : ''; ?>" required>
                    </div>

                    <!-- Nomor Telepon (Hanya untuk kehilangan) -->
                    <div class="mb-3" id="kontak-pelapor-group" style="display: none;">
                        <label for="kontak_pelapor" class="form-label">Nomor Telepon (WhatsApp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kontak_pelapor" name="kontak_pelapor" placeholder="Contoh: 081234567890" value="<?php echo isset($_POST['kontak_pelapor']) ? htmlspecialchars($_POST['kontak_pelapor']) : ''; ?>">
                        <div class="form-text">Nomor ini akan ditampilkan agar penemu bisa menghubungi Anda.</div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi / Ciri-ciri Barang</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan secara detail..."><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                    </div>

                    <!-- Upload Foto -->
                    <div class="mb-4">
                        <label for="foto" class="form-label">Foto Barang (Maks. 3)</label>
                        <div id="drop-area" class="border rounded p-4 text-center" style="border-style: dashed !important;">
                            <p>Seret & Lepaskan file di sini, atau klik untuk memilih file.</p>
                            <input type="file" class="form-control" id="foto" name="foto[]" multiple accept="image/*" class="d-none">
                            <div id="preview" class="mt-3 d-flex justify-content-center flex-wrap"></div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisLaporanRadios = document.querySelectorAll('input[name="jenis_laporan"]');
        const kontakGroup = document.getElementById('kontak-pelapor-group');

        function toggleKontakField() {
            if (document.querySelector('input[name="jenis_laporan"]:checked').value === 'hilang') {
                kontakGroup.style.display = 'block';
            } else {
                kontakGroup.style.display = 'none';
            }
        }

        jenisLaporanRadios.forEach(radio => {
            radio.addEventListener('change', toggleKontakField);
        });

        // Initial check on page load
        toggleKontakField();
    });

    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('foto');
    const preview = document.getElementById('preview');

    dropArea.addEventListener('click', () => fileInput.click());

    dropArea.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropArea.classList.add('bg-light');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('bg-light');
    });

    dropArea.addEventListener('drop', (event) => {
        event.preventDefault();
        dropArea.classList.remove('bg-light');
        const files = event.dataTransfer.files;
        fileInput.files = files;
        handleFiles(files);
    });

    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });

    function handleFiles(files) {
        preview.innerHTML = '';
        for (const file of files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.margin = '5px';
                    img.classList.add('rounded');
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

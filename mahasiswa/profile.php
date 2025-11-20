<?php
session_start();
include "../admin/db.php"; 
include "../templates/sidebar_mahasiswa.php";

// --- 0. LOGIKA PENENTUAN HALAMAN AKTIF ---
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}
$username_login = $_SESSION['npm'];
$message = '';

// --- 1. AMBIL DATA MAHASISWA LENGKAP ---
$sql_fetch = "SELECT 
                m.id, m.nama AS nama_akun, m.foto,
                dm.* /* Ambil semua kolom dari data_mahasiswa */
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              WHERE m.username = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
if ($stmt_fetch === FALSE) { die("Error preparing fetch query: " . $conn->error); }
$stmt_fetch->bind_param("s", $username_login);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
$data_profile = $result_fetch->fetch_assoc();

if (!$data_profile) {
    die("Data profil tidak ditemukan.");
}

$mahasiswa_id = $data_profile['id']; 

// Daftar ENUM options
$jenis_kelamin_options = ['Laki-laki', 'Perempuan'];
$kelas_options = ['Reguler', 'Karyawan', 'Transfer']; // Asumsi options
// Data yang ditampilkan di form:
$npm = $data_profile['npm']; 

// --- 2. LOGIKA UPDATE PROFILE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    
    // Ambil data dari form
    $email = $_POST['email'] ?? null;
    $telepon = $_POST['telepon'] ?? null;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
    $alamat = $_POST['alamat'] ?? null;
    $nik = $_POST['nik'] ?? null;
    $tempat_tgl_lahir = $_POST['tempat_tgl_lahir'] ?? null;
    $nama_ortu = $_POST['nama_ortu_dengan_gelar'] ?? null;
    $kelas = $_POST['kelas'] ?? null;
    $current_ttd = $data_profile['ttd']; // Tanda tangan lama

    // Logika upload TANDA TANGAN (ttd)
    $ttd_filename = $current_ttd;
    
    if (isset($_FILES['ttd']) && $_FILES['ttd']['error'] == UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['ttd']['name'], PATHINFO_EXTENSION);
        $file_name_ttd = "ttd_" . $mahasiswa_id . "_" . time() . "." . $file_extension;
        $target_dir = "../uploads/ttd/";
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $target_file = $target_dir . $file_name_ttd;
        
        if (move_uploaded_file($_FILES['ttd']['tmp_name'], $target_file)) {
            $ttd_filename = $file_name_ttd;
            // Hapus file lama jika ada dan bukan dummy
            if ($current_ttd && file_exists("../uploads/ttd/" . $current_ttd) && $current_ttd !== 'dummy_ttd.png') {
                unlink("../uploads/ttd/" . $current_ttd);
            }
        } else {
            $message = "<div class='alert alert-danger'>Gagal mengunggah file Tanda Tangan.</div>";
        }
    }
    
    // Query Update data_mahasiswa
    $update_sql = "UPDATE data_mahasiswa SET 
                    email = ?, telepon = ?, jenis_kelamin = ?, alamat = ?, 
                    nik = ?, tempat_tgl_lahir = ?, nama_ortu_dengan_gelar = ?, 
                    kelas = ?, ttd = ?
                   WHERE id = ?";
    
    $stmt_update = $conn->prepare($update_sql);
    
    // Tipe binding: ssssssssi
    $stmt_update->bind_param("sssssssssi", 
        $email, $telepon, $jenis_kelamin, $alamat, $nik, 
        $tempat_tgl_lahir, $nama_ortu, $kelas, $ttd_filename, 
        $mahasiswa_id
    );

    if ($stmt_update->execute()) {
        $message = "<div class='alert alert-success'>Profil berhasil diperbarui!</div>";
        // Redirect untuk refresh data
        header("Location: profile.php?msg=" . urlencode("Profil berhasil diperbarui!"));
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Gagal memperbarui profil: " . $conn->error . "</div>";
    }
}

// Ambil pesan setelah redirect
if (isset($_GET['msg'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil Mahasiswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    
    <style>
        /* CSS Layout disederhanakan */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        .sidebar h6 { color: #cfd8dc; opacity: 0.7; padding-left: 25px; margin-bottom: 5px !important; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .foto-profil { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #ddd; }
        .ttd-box { width: 150px; height: 80px; border: 1px solid #ccc; display: flex; justify-content: center; align-items: center; margin-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="../admin/unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
    </div>
    <div class="profile d-flex align-items-center gap-2">
        <div class="text-end d-none d-md-block" style="line-height: 1.2;">
            <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($data_profile['nama_akun']) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px; overflow: hidden;">
            👤
        </div>
    </div>
</div>

<div class="main-content">
    <h2 class="mb-4">Edit Profil & Biodata</h2>
    
    <?= $message ?>
    
    <div class="card p-4 shadow-sm border-0">
        <form method="POST" action="profile.php" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="<?= $data_profile['foto'] ? '../uploads/' . htmlspecialchars($data_profile['foto']) : 'placeholder.png' ?>" 
                         alt="Foto Profil" class="foto-profil mb-3">
                    <h6><?= htmlspecialchars($data_profile['nama_akun']) ?></h6>
                    <p class="text-muted small">NPM: <?= htmlspecialchars($npm) ?></p>
                    <hr class="d-md-none">
                </div>
                
                <div class="col-md-8">
                    <h5 class="mb-4 text-primary border-bottom pb-2">Data Detail</h5>
                    
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data_profile['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telepon" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($data_profile['telepon'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih...</option>
                                <?php foreach ($jenis_kelamin_options as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($data_profile['jenis_kelamin'] == $opt) ? 'selected' : '' ?>>
                                        <?= $opt ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas">
                                <option value="">Pilih...</option>
                                <?php foreach ($kelas_options as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($data_profile['kelas'] == $opt) ? 'selected' : '' ?>>
                                        <?= $opt ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" value="<?= htmlspecialchars($data_profile['nik'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="tempat_tgl_lahir" class="form-label">Tempat, Tanggal Lahir</label>
                            <input type="text" class="form-control" id="tempat_tgl_lahir" name="tempat_tgl_lahir" placeholder="Contoh: Magelang, 11 Oktober 1995" value="<?= htmlspecialchars($data_profile['tempat_tgl_lahir'] ?? '') ?>">
                        </div>
                        
                        <div class="col-12">
                            <label for="nama_ortu_dengan_gelar" class="form-label">Nama Orang Tua (dengan gelar)</label>
                            <input type="text" class="form-control" id="nama_ortu_dengan_gelar" name="nama_ortu_dengan_gelar" value="<?= htmlspecialchars($data_profile['nama_ortu_dengan_gelar'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label for="alamat" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= htmlspecialchars($data_profile['alamat'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">Tanda Tangan (TTD)</label>
                            
                            <div class="mb-2">
                                TTD saat ini: 
                                <div class="ttd-box">
                                    <?php if ($data_profile['ttd'] && file_exists("../uploads/ttd/" . $data_profile['ttd'])): ?>
                                        <img src="../uploads/ttd/<?= htmlspecialchars($data_profile['ttd']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Tanda Tangan">
                                    <?php else: ?>
                                        <span class="text-danger small">Belum ada TTD</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <input class="form-control" type="file" id="ttd" name="ttd" accept=".png,.jpg,.jpeg">
                            <div class="form-text">Unggah file TTD baru untuk mengganti (PNG/JPG).</div>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
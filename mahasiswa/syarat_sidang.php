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

// --- 1. AMBIL DATA MAHASISWA & SKRIPSI ID ---
$sql_mahasiswa = "SELECT 
                    m.id, m.nama, m.foto,
                    dm.npm AS npm_real, dm.prodi
                  FROM mstr_akun m
                  JOIN data_mahasiswa dm ON m.id = dm.id
                  WHERE m.username = ?";
$stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
if ($stmt_mahasiswa === FALSE) { die("Error preparing mahasiswa data: " . $conn->error); }
$stmt_mahasiswa->bind_param("s", $username_login);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$data_mahasiswa = $result_mahasiswa->fetch_assoc();
$mahasiswa_id = $data_mahasiswa['id']; 
$prodi_mahasiswa = $data_mahasiswa['prodi'];

// Cek apakah mahasiswa sudah memiliki Judul Final (Skripsi)
$sql_skripsi = "SELECT id, judul FROM skripsi WHERE id_mahasiswa = ?";
$stmt_skripsi = $conn->prepare($sql_skripsi);
$stmt_skripsi->bind_param("i", $mahasiswa_id);
$stmt_skripsi->execute();
$skripsi_result = $stmt_skripsi->get_result();
$data_skripsi = $skripsi_result->fetch_assoc();

if (!$data_skripsi) {
    $message = "<div class='alert alert-danger'>**Akses Ditolak.** Anda harus memiliki Judul Skripsi Final sebelum mendaftar Pendadaran.</div>";
    $skripsi_id = null;
    $judul_skripsi = 'N/A';
} else {
    $skripsi_id = $data_skripsi['id'];
    $judul_skripsi = $data_skripsi['judul'];
    $message = isset($_GET['msg']) ? "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>" : '';
}

// --- 2. AMBIL JENIS UJIAN PENDADARAN YANG RELEVAN ---
$pendadaran_map = [
    'Teknik Informatika S1' => 6, // Seminar Pendadaran Teknik Informatika 2025
    'Teknologi Informasi D3' => 8, // Seminar Pendadaran Teknologi Informasi D3
    'Teknik Industri S1' => 4,     // Seminar Pendadaran Teknik Industri
    // ...
];
$target_jenis_id = $pendadaran_map[$prodi_mahasiswa] ?? 0;

$sql_jenis = "SELECT id, nama FROM jenis_ujian_skripsi WHERE id = ?";
$stmt_jenis = $conn->prepare($sql_jenis);
$stmt_jenis->bind_param("i", $target_jenis_id);
$stmt_jenis->execute();
$jenis_pendadaran = $stmt_jenis->get_result()->fetch_assoc();

// --- 3. CEK STATUS PENDAFTARAN PENDADARAN SAAT INI ---
$sql_cek_pendadaran = "
SELECT 
    us.id AS ujian_id, us.tanggal_daftar, us.status, us.persetujuan_pembimbing1, us.persetujuan_pembimbing2,
    sp.*
FROM ujian_skripsi us
JOIN syarat_pendadaran sp ON us.id = sp.id_ujian_skripsi
WHERE us.id_skripsi = ? AND us.id_jenis_ujian_skripsi = ?
ORDER BY us.tanggal_daftar DESC LIMIT 1";

$stmt_cek = $conn->prepare($sql_cek_pendadaran);
$stmt_cek->bind_param("ii", $skripsi_id, $target_jenis_id);
$stmt_cek->execute();
$data_pendadaran_aktif = $stmt_cek->get_result()->fetch_assoc();

$is_registered = $data_pendadaran_aktif !== null;
$status_ujian = $data_pendadaran_aktif['status'] ?? 'Belum Mendaftar';

// Daftar kolom yang merupakan syarat file (sesuai tabel `syarat_pendadaran`)
$file_requirements = [
    'naskah' => 'Naskah Pendadaran Final',
    'berita_acara_seminar' => 'Berita Acara Seminar (Sempro)',
    'daftar_nilai_sementara' => 'Daftar Nilai Sementara',
    'krs_terbaru' => 'KRS Terbaru',
    'dokumen_identitas' => 'Dokumen Identitas (KTP)',
    'sertifikat_toefl_niit' => 'Sertifikat TOEFL/NIIT',
    'sertifikat_office_puskom' => 'Sertifikat Office/Puskom',
    'sertifikat_btq_ibadah' => 'Sertifikat BTQ/Ibadah',
    'sertifikat_bahasa' => 'Sertifikat Bahasa',
    'sertifikat_kompetensi_ujian_komprehensif' => 'Sertifikat Kompetensi',
    'sertifikat_semaba_ppk_masta' => 'Sertifikat SEMABA/PPK/MASTA',
    'sertifikat_kkn' => 'Sertifikat KKN',
    'buku_kendali_bimbingan' => 'Buku Kendali Bimbingan',
    'bukti_pembayaran_sidang' => 'Bukti Pembayaran Sidang'
];

// --- 4. LOGIKA SUBMISSION FORM ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $skripsi_id) {
    $ipk_input = $_POST['ipk'] ?? null;
    
    if (!$is_registered) {
        // Proses Pendaftaran Awal (Insert ke ujian_skripsi)
        $insert_ujian_sql = "INSERT INTO ujian_skripsi (id_skripsi, tanggal_daftar, id_jenis_ujian_skripsi) VALUES (?, NOW(), ?)";
        $stmt_insert_ujian = $conn->prepare($insert_ujian_sql);
        $stmt_insert_ujian->bind_param("ii", $skripsi_id, $target_jenis_id);
        $stmt_insert_ujian->execute();
        $ujian_id_baru = $conn->insert_id;
        
        // Proses Insert Awal ke syarat_pendadaran
        $insert_syarat_sql = "INSERT INTO syarat_pendadaran (id_ujian_skripsi, status, ipk) VALUES (?, 0, ?)";
        $stmt_insert_syarat = $conn->prepare($insert_syarat_sql);
        $stmt_insert_syarat->bind_param("id", $ujian_id_baru, $ipk_input); // 'd' for decimal/double
        $stmt_insert_syarat->execute();
        $syarat_pendadaran_id = $conn->insert_id;

        $message_text = "Pendaftaran Pendadaran berhasil, silakan unggah dokumen.";
        
    } else {
        // Jika sudah terdaftar, gunakan ID ujian yang sudah ada dan update IPK
        $ujian_id_baru = $data_pendadaran_aktif['ujian_id'];
        $syarat_pendadaran_id = $data_pendadaran_aktif['id'];
        
        $update_ipk = "UPDATE syarat_pendadaran SET ipk = ? WHERE id = ?";
        $stmt_update_ipk = $conn->prepare($update_ipk);
        $stmt_update_ipk->bind_param("di", $ipk_input, $syarat_pendadaran_id);
        $stmt_update_ipk->execute();
        
        $message_text = "Dokumen berhasil diperbarui.";
    }

    // Proses Upload File & Update syarat_pendadaran
    $file_update_parts = [];
    $file_update_values = [];

    foreach ($file_requirements as $db_col => $label) {
        if (isset($_FILES[$db_col]) && $_FILES[$db_col]['error'] == UPLOAD_ERR_OK) {
            $file_extension = pathinfo($_FILES[$db_col]['name'], PATHINFO_EXTENSION);
            $file_name = "Sidang_{$data_mahasiswa['npm_real']}_{$db_col}_" . time() . ".{$file_extension}";
            $target_dir = "../uploads/pendadaran/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES[$db_col]['tmp_name'], $target_file)) {
                $file_update_parts[] = "{$db_col} = ?";
                $file_update_values[] = $file_name;
            }
        }
    }

    if (!empty($file_update_parts)) {
        $update_sql = "UPDATE syarat_pendadaran SET " . implode(', ', $file_update_parts) . " WHERE id = ?";
        $file_update_values[] = $syarat_pendadaran_id;
        
        $stmt_update = $conn->prepare($update_sql);
        $types = str_repeat('s', count($file_update_values) - 1) . 'i';
        
        $stmt_update->bind_param($types, ...$file_update_values);
        $stmt_update->execute();
        
        header("Location: syarat_sidang.php?msg=" . urlencode("Dokumen Pendadaran berhasil diunggah/diperbarui."));
        exit();
    } elseif(!$is_registered) {
         header("Location: syarat_sidang.php?msg=" . urlencode("Pendaftaran Pendadaran berhasil, silakan unggah dokumen."));
         exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Syarat Pendadaran</title>
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
        .document-status-box { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-top: 20px; }
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
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($data_mahasiswa['nama']) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px; overflow: hidden;">
            👤
        </div>
    </div>
</div>

<div class="main-content">
    <h2 class="mb-4">Data Syarat Pendadaran</h2>
    
    <?= $message ?>
    
    <?php if (!$data_skripsi): ?>
        <div class="alert alert-danger text-center">
            Anda belum memiliki **Judul Skripsi Final**. Pendaftaran Pendadaran tidak bisa dilakukan.
        </div>
    <?php else: ?>
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="mb-3 border-bottom pb-2 text-primary">Status Pendaftaran Sidang Pendadaran</h5>
            
            <p>Judul Skripsi: <strong><?= htmlspecialchars($judul_skripsi); ?></strong></p>
            <p>Jenis Ujian: <strong><?= htmlspecialchars($jenis_pendadaran['nama'] ?? 'N/A'); ?></strong></p>
            
            <?php if ($is_registered): ?>
                <div class="alert alert-info">
                    <i class="bi bi-check-circle-fill me-2"></i>Anda sudah terdaftar Pendadaran. Status: 
                    <span class="badge bg-primary"><?= htmlspecialchars($status_ujian); ?></span>
                    <?php if ($status_ujian == 'Berlangsung'): ?>
                        <br>Menunggu persetujuan Pembimbing (P1: **<?= $data_pendadaran_aktif['persetujuan_pembimbing1'] == 1 ? 'ACC' : 'Menunggu' ?>** | P2: **<?= $data_pendadaran_aktif['persetujuan_pembimbing2'] == 1 ? 'ACC' : 'Menunggu' ?>**)
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Anda belum terdaftar Pendadaran. Pendaftaran akan otomatis dilakukan saat Anda pertama kali mengunggah dokumen.
                </div>
            <?php endif; ?>

            <hr>

            <h5 class="mt-4 mb-3">Unggah Dokumen Persyaratan & Data IPK</h5>
            <form method="POST" action="syarat_sidang.php" enctype="multipart/form-data">
                <input type="hidden" name="skripsi_id" value="<?= $skripsi_id ?>">
                
                <div class="mb-4">
                    <label for="ipk" class="form-label fw-bold">Nilai IPK Terakhir (contoh: 3.50)</label>
                    <input type="number" step="0.01" min="0" max="4" class="form-control" id="ipk" name="ipk" required 
                        value="<?= htmlspecialchars($data_pendadaran_aktif['ipk'] ?? '') ?>">
                </div>

                <div class="row g-3">
                    <?php foreach ($file_requirements as $db_col => $label): 
                        $current_file = $data_pendadaran_aktif[$db_col] ?? null;
                    ?>
                        <div class="col-md-6">
                            <label for="<?= $db_col ?>" class="form-label">**<?= $label ?>** (.pdf / .jpg)</label>
                            <input class="form-control" type="file" id="<?= $db_col ?>" name="<?= $db_col ?>" accept=".pdf,.jpg,.jpeg">
                            <div class="form-text">
                                <?php if ($current_file): ?>
                                    <span class="text-success">File saat ini tersedia.</span> 
                                    <a href="../uploads/pendadaran/<?= htmlspecialchars($current_file) ?>" target="_blank">(Lihat)</a>
                                <?php else: ?>
                                    <span class="text-danger">Wajib diunggah.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>
                        Unggah & Update Syarat Pendadaran
                    </button>
                </div>
            </form>

            <?php if ($is_registered): ?>
            <div class="document-status-box">
                <h6>Status Validasi Dokumen:</h6>
                <p class="small text-muted m-0">
                    Status Ujian Pendadaran Anda akan berubah jika semua syarat sudah disetujui TU/Operator dan Pembimbing.
                </p>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
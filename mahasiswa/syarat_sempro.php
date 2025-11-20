<?php
session_start();
include "../admin/db.php"; 
include "../templates/sidebar_mahasiswa.php";

// --- 0. LOGIKA SESSION & AMBIL DATA DASAR ---
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}
$username_login = $_SESSION['npm'];

// --- Fungsi is_active untuk Sidebar ---
if (!function_exists('is_active')) {
    function is_active($target_page, $current_page) {
        if (is_array($target_page)) {
            return in_array($current_page, $target_page) ? 'active' : '';
        }
        return ($current_page === $target_page) ? 'active' : '';
    }
}

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

if (!$data_mahasiswa) {
    echo "<script>alert('Data biodata belum lengkap. Hubungi Admin.'); window.location='../auth/login.php?action=logout';</script>";
    exit();
}

$mahasiswa_id = $data_mahasiswa['id']; 
$prodi_mahasiswa = $data_mahasiswa['prodi'];
$npm_fix = $data_mahasiswa['npm_real']; // Tambahkan NPM fix untuk upload file

// Cek apakah mahasiswa sudah memiliki Judul Final (Skripsi)
$sql_skripsi = "SELECT id, judul FROM skripsi WHERE id_mahasiswa = ?";
$stmt_skripsi = $conn->prepare($sql_skripsi);
$stmt_skripsi->bind_param("i", $mahasiswa_id);
$stmt_skripsi->execute();
$skripsi_result = $stmt_skripsi->get_result();
$data_skripsi = $skripsi_result->fetch_assoc();

if (!$data_skripsi) {
    $message = "<div class='alert alert-danger'>**Akses Ditolak.** Anda harus mengajukan Judul Skripsi Final terlebih dahulu di menu 'Pengajuan Tugas Akhir'.</div>";
    $skripsi_id = null;
    $judul_skripsi = 'N/A';
} else {
    $skripsi_id = $data_skripsi['id'];
    $judul_skripsi = $data_skripsi['judul'];
    $message = isset($_GET['msg']) ? "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>" : '';
}

// --- 2. AMBIL JENIS UJIAN SEMPRO YANG RELEVAN ---
$sempro_map = [
    'Teknik Informatika S1' => 5,
    'Teknologi Informasi D3' => 7,
    'Teknik Industri S1' => 3,
];
$target_jenis_id = $sempro_map[$prodi_mahasiswa] ?? 0;

$sql_jenis = "SELECT id, nama FROM jenis_ujian_skripsi WHERE id = ?";
$stmt_jenis = $conn->prepare($sql_jenis);
$stmt_jenis->bind_param("i", $target_jenis_id);
$stmt_jenis->execute();
$jenis_sempro = $stmt_jenis->get_result()->fetch_assoc();

// Daftar kolom yang merupakan syarat file (sesuai tabel `syarat_sempro`)
$file_requirements = [
    'naskah' => 'Naskah Sempro',
    'fotokopi_daftar_nilai' => 'Fotokopi Daftar Nilai',
    'fotokopi_krs' => 'Fotokopi KRS',
    'buku_kendali_bimbingan' => 'Buku Kendali Bimbingan',
    'lembar_revisi_ba_dan_tanda_terima_laporan_kp' => 'Lembar Revisi KP',
    'bukti_seminar_teman' => 'Bukti Seminar Teman'
];

// --- 3. CEK STATUS PENDAFTARAN SEMPRO SAAT INI ---
$sql_cek_sempro = "
SELECT 
    us.id AS ujian_id, us.tanggal_daftar, us.status, us.persetujuan_pembimbing1, us.persetujuan_pembimbing2,
    ss.*
FROM ujian_skripsi us
JOIN syarat_sempro ss ON us.id = ss.id_ujian_skripsi
WHERE us.id_skripsi = ? AND us.id_jenis_ujian_skripsi = ?
ORDER BY us.tanggal_daftar DESC LIMIT 1";

$stmt_cek = $conn->prepare($sql_cek_sempro);
$stmt_cek->bind_param("ii", $skripsi_id, $target_jenis_id);
$stmt_cek->execute();
$data_sempro_aktif = $stmt_cek->get_result()->fetch_assoc();

$is_registered = $data_sempro_aktif !== null;
$syarat_sempro_id = $data_sempro_aktif['id'] ?? 0;
$status_ujian = $data_sempro_aktif['status'] ?? 'Belum Mendaftar';

// --- BARU: AMBIL STATUS VALIDASI DARI VALIDASI_SYARAT_SEMPRO ---
$validation_status = [];
if ($syarat_sempro_id > 0) {
    $sql_validasi = "SELECT nama_field_syarat, status, catatan 
                     FROM validasi_syarat_sempro 
                     WHERE id_syarat_sempro = ?";
    $stmt_validasi = $conn->prepare($sql_validasi);
    $stmt_validasi->bind_param("i", $syarat_sempro_id);
    $stmt_validasi->execute();
    $result_validasi = $stmt_validasi->get_result();

    while ($row = $result_validasi->fetch_assoc()) {
        $validation_status[$row['nama_field_syarat']] = [
            'status' => $row['status'],
            'catatan' => $row['catatan']
        ];
    }
}

// --- 4. LOGIKA SUBMISSION FORM ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $skripsi_id) {
    $ujian_id_baru = $data_sempro_aktif['ujian_id'] ?? 0;
    $syarat_sempro_id = $data_sempro_aktif['id'] ?? 0;

    if (!$is_registered) {
        // Pendaftaran Awal (Insert ke ujian_skripsi dan syarat_sempro)
        $insert_ujian_sql = "INSERT INTO ujian_skripsi (id_skripsi, tanggal_daftar, id_jenis_ujian_skripsi) VALUES (?, NOW(), ?)";
        $stmt_insert_ujian = $conn->prepare($insert_ujian_sql);
        $stmt_insert_ujian->bind_param("ii", $skripsi_id, $target_jenis_id);
        $stmt_insert_ujian->execute();
        $ujian_id_baru = $conn->insert_id;
        
        $insert_syarat_sql = "INSERT INTO syarat_sempro (id_ujian_skripsi, status) VALUES (?, 0)";
        $stmt_insert_syarat = $conn->prepare($insert_syarat_sql);
        $stmt_insert_syarat->bind_param("i", $ujian_id_baru);
        $stmt_insert_syarat->execute();
        $syarat_sempro_id = $conn->insert_id;
    } 

    // Proses Upload File & Update syarat_sempro
    $file_update_parts = [];
    $file_update_values = [];

    foreach ($file_requirements as $db_col => $label) {
        if (isset($_FILES[$db_col]) && $_FILES[$db_col]['error'] == UPLOAD_ERR_OK) {
            $file_extension = pathinfo($_FILES[$db_col]['name'], PATHINFO_EXTENSION);
            $file_name = "Sempro_{$npm_fix}_{$db_col}_" . time() . ".{$file_extension}";
            $target_dir = "../uploads/sempro/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES[$db_col]['tmp_name'], $target_file)) {
                $file_update_parts[] = "{$db_col} = ?";
                $file_update_values[] = $file_name;
            }
        }
    }

    if (!empty($file_update_parts)) {
        $update_sql = "UPDATE syarat_sempro SET " . implode(', ', $file_update_parts) . " WHERE id = ?";
        $file_update_values[] = $syarat_sempro_id;
        
        $stmt_update = $conn->prepare($update_sql);
        $types = str_repeat('s', count($file_update_values) - 1) . 'i';
        
        $stmt_update->bind_param($types, ...$file_update_values);
        $stmt_update->execute();
        
        header("Location: syarat_sempro.php?msg=" . urlencode("Dokumen Sempro berhasil diunggah/diperbarui."));
        exit();
    } elseif(!$is_registered) {
         header("Location: syarat_sempro.php?msg=" . urlencode("Pendaftaran Sempro berhasil, silakan unggah dokumen."));
         exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Syarat Proposal</title>
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
    <h2 class="mb-4">Data Syarat Sempro</h2>
    
    <?= $message ?>
    
    <?php if (!$data_skripsi): ?>
        <div class="alert alert-danger text-center">
            Anda belum memiliki **Judul Skripsi Final**. Silakan ajukan judul Anda terlebih dahulu.
        </div>
    <?php else: ?>
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="mb-3 border-bottom pb-2 text-primary">Status Pendaftaran Seminar Proposal</h5>
            
            <p>Judul Skripsi: <strong><?= htmlspecialchars($judul_skripsi); ?></strong></p>
            <p>Jenis Ujian: <strong><?= htmlspecialchars($jenis_sempro['nama'] ?? 'N/A'); ?></strong></p>
            
            <?php if ($is_registered): ?>
                <div class="alert alert-info">
                    <i class="bi bi-check-circle-fill me-2"></i>Anda sudah terdaftar Sempro. Status: 
                    <span class="badge bg-primary"><?= htmlspecialchars($status_ujian); ?></span>
                    <?php if ($status_ujian == 'Berlangsung'): ?>
                        <br>Menunggu persetujuan Pembimbing (P1: **<?= $data_sempro_aktif['persetujuan_pembimbing1'] == 1 ? 'ACC' : 'Menunggu' ?>** | P2: **<?= $data_sempro_aktif['persetujuan_pembimbing2'] == 1 ? 'ACC' : 'Menunggu' ?>**)
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Anda belum terdaftar Sempro. Pendaftaran akan otomatis dilakukan saat Anda pertama kali mengunggah dokumen.
                </div>
            <?php endif; ?>

            <hr>

            <h5 class="mt-4 mb-3">Unggah Dokumen Persyaratan</h5>
            <form method="POST" action="syarat_sempro.php" enctype="multipart/form-data">
                <input type="hidden" name="skripsi_id" value="<?= $skripsi_id ?>">
                
                <div class="row g-3">
                    <?php foreach ($file_requirements as $db_col => $label): 
                        $current_file = $data_sempro_aktif[$db_col] ?? null;
                        $status_data = $validation_status[$db_col] ?? ['status' => 'Belum Dicek', 'catatan' => ''];
                        
                        // Tentukan warna badge dan label
                        $status = $status_data['status'];
                        $catatan = $status_data['catatan'];
                        $badge_class = 'bg-secondary';
                        $status_label = 'Belum Dicek';

                        if ($status == 'Diterima') {
                            $badge_class = 'bg-success';
                            $status_label = 'DITERIMA';
                        } elseif ($status == 'Revisi') {
                            $badge_class = 'bg-danger';
                            $status_label = 'REVISI';
                        } elseif ($status == 'Menunggu') {
                            $badge_class = 'bg-warning text-dark';
                            $status_label = 'MENUNGGU';
                        }
                    ?>
                        <div class="col-md-6">
                            <label for="<?= $db_col ?>" class="form-label">
                                **<?= $label ?>** <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
                            </label>
                            <input class="form-control" type="file" id="<?= $db_col ?>" name="<?= $db_col ?>" accept=".pdf,.jpg,.jpeg">
                            <div class="form-text">
                                <?php if ($current_file): ?>
                                    <span class="text-success">File saat ini tersedia.</span> 
                                    <a href="../uploads/sempro/<?= htmlspecialchars($current_file) ?>" target="_blank">(Lihat File)</a>
                                <?php else: ?>
                                    <span class="text-danger">Wajib diunggah.</span>
                                <?php endif; ?>
                                
                                <?php if ($catatan): ?>
                                    <br><strong class="text-danger">Catatan Revisi:</strong> <?= htmlspecialchars($catatan) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>
                        Unggah & Update Syarat Sempro
                    </button>
                </div>
            </form>

            <?php if ($is_registered): ?>
            <div class="document-status-box">
                <h6>Keterangan Status:</h6>
                <p class="small m-0">
                    Status **DITERIMA** berarti dokumen sudah disetujui oleh Administrator/Tata Usaha dan tidak perlu diunggah ulang kecuali diminta.
                </p>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include "../admin/db.php"; 

// --- 0. LOGIKA PENENTUAN HALAMAN AKTIF ---
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}
$username_login = $_SESSION['npm'];

// --- 1. AMBIL DATA MAHASISWA & ID AKUN ---
$sql_mahasiswa = "SELECT 
                    m.id,                      
                    m.nama, 
                    dm.npm AS npm_real,
                    dm.prodi,
                    m.foto
                  FROM mstr_akun m
                  JOIN data_mahasiswa dm ON m.id = dm.id
                  WHERE m.username = ?";
$stmt_mahasiswa = $conn->prepare($sql_mahasiswa);

if ($stmt_mahasiswa === FALSE) {
    die("Error preparing mahasiswa data: " . $conn->error);
}

$stmt_mahasiswa->bind_param("s", $username_login);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$data_mahasiswa = $result_mahasiswa->fetch_assoc();

if (!$data_mahasiswa) {
    echo "<script>alert('Data biodata belum lengkap. Hubungi Admin.'); window.location='../auth/login.php?action=logout';</script>";
    exit();
}

$npm_fix = $data_mahasiswa['npm_real'];
$mahasiswa_id = $data_mahasiswa['id']; 

// --- 2. AMBIL DATA SKRIPSI (MENGGUNAKAN KOLOM YANG ADA DI FOTO ANDA) ---
$sql_skripsi = "SELECT 
                  id AS id_skripsi, 
                  judul, 
                  tgl_pengajuan_judul AS tgl_diajukan, 
                  naskah AS file_proposal /* Memakai naskah untuk menyimpan file */
                FROM skripsi 
                WHERE id_mahasiswa = ?";
$stmt_skripsi = $conn->prepare($sql_skripsi);

if ($stmt_skripsi === FALSE) { 
    // Jika masih error, database Anda memiliki masalah di luar kolom yang hilang
    die("FATAL ERROR pada query skripsi: " . $conn->error); 
}

$stmt_skripsi->bind_param("i", $mahasiswa_id);
$stmt_skripsi->execute();
$result_skripsi = $stmt_skripsi->get_result();
$skripsi_data = $result_skripsi->fetch_assoc();

// Logika Status Sederhana: Diterima jika ada record, jika tidak, Baru.
$is_title_approved = ($skripsi_data !== null); 
$status = $is_title_approved ? 'Diterima' : 'Baru';
$judul_alternatif1 = "";
$alasan1 = "";
$judul_alternatif2 = "";
$alasan2 = "";


$message = ''; 
if (isset($_GET['msg'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>";
}

// --- 3. LOGIKA FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_judul'])) {
    
    if ($is_title_approved) {
        $message = "<div class='alert alert-warning'>Judul Anda sudah **Diterima**. Tidak bisa mengajukan judul baru.</div>";
    } else {
        $judul_final = $_POST['judul1'] ?? ''; 
        $tema = 'Software Engineering'; // Set default tema
        $skema = 'Reguler';           // Set default skema
        $file_naskah = null;
        
        if (empty($judul_final)) {
             $message = "<div class='alert alert-warning'>**PERHATIAN:** Judul wajib diisi.</div>";
        } else {
            // Logika upload file (simpan ke kolom Naskah)
            if (isset($_FILES['file_proposal']) && $_FILES['file_proposal']['error'] == UPLOAD_ERR_OK) {
                $file_extension = pathinfo($_FILES['file_proposal']['name'], PATHINFO_EXTENSION);
                $file_name = $npm_fix . "_naskah_" . time() . "." . $file_extension;
                $target_dir = "../uploads/naskah/"; // Ubah folder tujuan
                $target_file = $target_dir . $file_name;
                
                if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

                if (move_uploaded_file($_FILES['file_proposal']['tmp_name'], $target_file)) {
                    $file_naskah = $file_name;
                } else {
                    $message = "<div class='alert alert-danger'>Gagal mengunggah file naskah.</div>";
                }
            }
            
            if (empty($message)) {
                // Insert data skripsi baru (langsung dianggap diterima/final)
                $insert_sql = "INSERT INTO skripsi (id_mahasiswa, judul, tema, tgl_pengajuan_judul, naskah, skema) VALUES (?, ?, ?, NOW(), ?, ?)";
                $stmt_insert = $conn->prepare($insert_sql);
                $stmt_insert->bind_param("issss", $mahasiswa_id, $judul_final, $tema, $file_naskah, $skema);

                if ($stmt_insert->execute()) {
                    header("Location: skripsi.php?msg=" . urlencode("Pengajuan judul berhasil. Judul ini dianggap DITERIMA karena keterbatasan tabel."));
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>Gagal menyimpan pengajuan: " . $conn->error . "</div>";
                }
            }
        }
    }
}


// --- 4. LOGIKA TAMPILAN STATUS FLOW (Sederhana) ---
$status_text = $is_title_approved ? 'Diterima' : 'Belum Mengajukan';
$badge_class = $is_title_approved ? 'bg-success' : 'bg-secondary';
$keterangan_status = $is_title_approved ? 'Judul ini sudah masuk ke database final.' : 'Anda belum memiliki pengajuan judul. Isi Judul Final di bawah.';

$step1_class = $is_title_approved ? 'active completed' : 'active';
$step2_class = $is_title_approved ? 'active completed' : '';
$step3_class = $is_title_approved ? 'active completed' : '';
$step3_icon = $is_title_approved ? 'bi-check-circle-fill' : 'bi-hourglass-split';
$final_judul = $skripsi_data['judul'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Tugas Akhir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    
    <style>
        /* CSS re-implementasi dari home_mahasiswa.php */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        .sidebar h6 { color: #cfd8dc; opacity: 0.7; padding-left: 25px; margin-bottom: 5px !important; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        
        /* CSS Khusus Status Flow */
        .status-flow { display: flex; justify-content: space-between; position: relative; padding: 20px 0; }
        .status-flow::before { content: ''; position: absolute; height: 3px; background: #e9ecef; width: 90%; top: 50%; left: 5%; transform: translateY(-50%); z-index: 1; }
        .flow-step { flex: 1; text-align: center; position: relative; z-index: 2; }
        .flow-icon { width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 18px; transition: all 0.3s; border: 3px solid #f4f6f9; }
        .flow-step.active .flow-icon { background: #0d6efd; color: white; }
        .flow-step.completed .flow-icon { background: #198754; color: white; border-color: #198754; }
        .flow-step.rejected .flow-icon { background: #dc3545; color: white; border-color: #dc3545; }
        .flow-label { font-size: 0.9rem; font-weight: 500; color: #6c757d; }
        .flow-step.active .flow-label { color: #0d6efd; font-weight: bold; }
        .flow-step.completed .flow-label { color: #198754; }
        .flow-step.rejected .flow-label { color: #dc3545; }
        .judul-terpilih { background: #f1f8ff; border-left: 5px solid #0d6efd; padding: 15px; border-radius: 6px; }
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
              <?php if (!empty($data_mahasiswa['foto']) && file_exists("../uploads/" . $data_mahasiswa['foto'])): ?>
                 <img src="../uploads/<?= htmlspecialchars($data_mahasiswa['foto']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                 👤
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="<?= ($current_page == 'home_mahasiswa.php') ? 'active' : '' ?>">
        Dashboard
    </a>
    <a href="progres_skripsi.php" class="<?= ($current_page == 'progres_skripsi.php') ? 'active' : '' ?>">
        Upload Progres
    </a>
    
    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Kelola Tugas Akhir</h6>
    <a href="skripsi.php" class="<?= ($current_page == 'skripsi.php') ? 'active' : '' ?>">
        Pengajuan Tugas Akhir
    </a>
    <a href="ujian.php" class="<?= ($current_page == 'ujian.php') ? 'active' : '' ?>">
        Ujian Tugas Akhir
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Persyaratan</h6>
    <a href="syarat_sempro.php" class="<?= ($current_page == 'syarat_sempro.php') ? 'active' : '' ?>">
        Syarat Proposal
    </a>
    <a href="syarat_sidang.php" class="<?= ($current_page == 'syarat_sidang.php') ? 'active' : '' ?>">
        Syarat Pendadaran
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Pengaturan</h6>
    <a href="profile.php" class="<?= ($current_page == 'profile.php') ? 'active' : '' ?>">
        Profile
    </a>

    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">
        Logout
    </a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">&copy; 2025 UNIMMA</div>
</div>
<div class="main-content">
    
    <h2 class="mb-4">Pengajuan Tugas Akhir</h2>
    
    <?= $message ?>

    <div class="card p-4 shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <h4 class="mb-3 border-bottom pb-2 text-primary">Status Pengajuan Judul/Topik</h4>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">Status Saat Ini: <span class="badge <?= $badge_class ?>"><?= $status_text ?></span></h5>
            <?php if ($skripsi_data): ?>
                <small class="text-muted">Tgl. Masuk DB: **<?= date('d M Y, H:i', strtotime($skripsi_data['tgl_diajukan'])) ?>**</small>
            <?php endif; ?>
        </div>

        <div class="status-flow mb-4">
            
            <div class="flow-step <?= $step1_class ?>">
                <div class="flow-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div class="flow-label">Diajukan</div>
            </div>

            <div class="flow-step <?= $step2_class ?>">
                <div class="flow-icon"><i class="bi bi-person-check"></i></div>
                <div class="flow-label">Verifikasi</div>
            </div>

            <div class="flow-step <?= $step3_class ?>">
                <div class="flow-icon"><i class="bi <?= $step3_icon ?>"></i></div>
                <div class="flow-label">Diterima</div>
            </div>
            
        </div>
        <div class="alert alert-info small mt-3">
            <i class="bi bi-info-circle me-2"></i><?= $keterangan_status ?>
        </div>
        
        <?php if ($is_title_approved): ?>
            <div class="judul-terpilih mt-3">
                <p class="mb-1 text-primary fw-bold">Judul Skripsi (Final):</p>
                <p class="m-0 fst-italic fs-5">"<?= htmlspecialchars($final_judul) ?>"</p>
            </div>
        <?php endif; ?>

    </div>
    <hr>
    
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <h4 class="mb-4 border-bottom pb-2 text-danger">Form Pengajuan Judul Final</h4>

        <?php if ($is_title_approved): ?>
            <div class="alert alert-success text-center py-4">
                <i class="bi bi-check-circle-fill fs-3 me-2"></i>
                <h5 class="mt-2">Judul sudah disetujui.</h5>
                <p class="mb-0">Formulir pengajuan dinonaktifkan.</p>
            </div>
        <?php else: ?>
            <form method="POST" action="skripsi.php" enctype="multipart/form-data">
                
                <p class="text-muted small mb-3">
                    Isi Judul Final yang akan dimasukkan ke database skripsi.
                </p>

                <div class="mb-3">
                    <label for="judul1" class="form-label fw-bold">Judul Skripsi (Final)</label>
                    <input type="text" class="form-control" id="judul1" name="judul1" placeholder="Masukkan judul skripsi final Anda" required>
                </div>

                <hr>
                
                <div class="mb-4">
                    <label for="file_proposal" class="form-label fw-bold">Unggah Naskah Awal/Proposal (PDF)</label>
                    <input class="form-control" type="file" id="file_proposal" name="file_proposal" accept=".pdf" required>
                    <div class="form-text">
                        File akan disimpan di kolom **`naskah`** di tabel **`skripsi`**. Hanya file **PDF** yang diperbolehkan.
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="submit_judul" class="btn btn-primary btn-lg">
                        <i class="bi bi-send-fill me-2"></i>
                        Ajukan Judul Final
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
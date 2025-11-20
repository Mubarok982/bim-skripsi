<?php
session_start();
// Pastikan file db.php berisi koneksi ke database Anda ($conn)
include "../admin/db.php"; 

// --- 0. LOGIKA PENENTUAN HALAMAN AKTIF ---
// Digunakan untuk memberikan class 'active' pada menu sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Cek Login
if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username_login = $_SESSION['npm'];
$mahasiswa_id = $_SESSION['id_akun']; // Asumsi ID akun disimpan di sesi

// --- 1. AMBIL DATA MAHASISWA & SKRIPSI ---
$sql = "SELECT 
            m.nama, 
            m.foto,
            dm.npm AS npm_real,
            dm.prodi,
            dm.telepon AS no_hp,
            s.judul AS judul_skripsi,
            d1.nama AS nama_dosen1, d1.username AS nidk1,
            d2.nama AS nama_dosen2, d2.username AS nidk2
        FROM mstr_akun m
        JOIN data_mahasiswa dm ON m.id = dm.id
        LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
        LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
        LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
        WHERE m.username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username_login);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data biodata belum lengkap. Hubungi Admin.'); window.location='../auth/login.php?action=logout';</script>";
    exit();
}

$npm_fix = $data['npm_real']; 

// --- 2. HITUNG PROGRES (LOGIKA PENGHITUNGAN SAMA SEPERTI KODE ASLI ANDA) ---
$total_skor_semua_bab = 0;
$detail_bab = []; 

// Kita Loop Bab 1 sampai 5
for ($i = 1; $i <= 5; $i++) {
    // Ambil status ACC terakhir untuk bab ini
    $q_cek = mysqli_query($conn, "SELECT nilai_dosen1, nilai_dosen2 FROM progres_skripsi WHERE npm='$npm_fix' AND bab='$i' ORDER BY created_at DESC LIMIT 1");
    $d_cek = mysqli_fetch_assoc($q_cek);

    // Logika Poin Per Bab (Max 100)
    $p1 = (isset($d_cek['nilai_dosen1']) && $d_cek['nilai_dosen1'] == 'ACC') ? 50 : 0;
    $p2 = (isset($d_cek['nilai_dosen2']) && $d_cek['nilai_dosen2'] == 'ACC') ? 50 : 0;
    
    $skor_bab = $p1 + $p2; 
    
    $detail_bab[$i] = $skor_bab;

    $total_skor_semua_bab += $skor_bab;
}

// Hitung Persentase Total Skripsi
// (Total Skor Semua Bab / 5 Bab)
$persentase_akhir = round($total_skor_semua_bab / 5);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    <style>
        /* Layout Fixed */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        /* Style untuk Menu Aktif */
        .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        
        /* Style untuk Sub-Heading (Kelola TA, Persyaratan, Pengaturan) */
        .sidebar h6 { color: #cfd8dc; opacity: 0.7; padding-left: 25px; margin-bottom: 5px !important; }

        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .biodata-box { display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
        .foto-profil { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd; }
        .info-table td { padding: 6px 10px; vertical-align: top; }
        .dosen-wrapper { display: flex; gap: 15px; width: 100%; margin-top: 15px; }
        .dosen-box { flex: 1; background: #f8f9fa; padding: 12px; border-radius: 6px; border: 1px solid #e9ecef; }
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
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($data['nama']) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px; overflow: hidden;">
              <?php if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])): ?>
                 <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                 👤
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="<?= ($current_page == 'home_mahasiswa.php' || $current_page == 'index.php') ? 'active' : '' ?>">
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
    
    <div class="card p-4 shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <h4 class="mb-3 border-bottom pb-2 text-primary">Biodata & Judul Skripsi</h4>
        <div class="biodata-box">
            <div class="text-center">
                <?php if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" class="foto-profil">
                <?php else: ?>
                    <div class="foto-profil d-flex align-items-center justify-content-center bg-light text-secondary" style="font-size: 40px;">👤</div>
                <?php endif; ?>
                <div class="mt-2">
                    <a href="profile.php" class="btn btn-sm btn-outline-primary w-100">Edit Profil</a>
                </div>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <table class="info-table w-100">
                    <tr><td width="100" class="fw-bold">Nama</td><td>: <?= htmlspecialchars($data['nama']) ?></td></tr>
                    <tr><td class="fw-bold">NPM</td><td>: <?= htmlspecialchars($data['npm_real']) ?></td></tr>
                    <tr><td class="fw-bold">Prodi</td><td>: <?= htmlspecialchars($data['prodi']) ?></td></tr>
                    <tr><td class="fw-bold">No HP</td><td>: <?= htmlspecialchars($data['no_hp']) ?></td></tr>
                    <tr><td class="fw-bold">Judul</td><td>: <span class="fst-italic text-dark bg-light p-1 rounded"><?= htmlspecialchars($data['judul_skripsi'] ?? 'Belum mengajukan judul') ?></span></td></tr>
                </table>

                <div class="dosen-wrapper">
                    <div class="dosen-box">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Pembimbing 1</small><br>
                        <span class="fw-bold"><?= htmlspecialchars($data['nama_dosen1'] ?? '-') ?></span><br>
                        <span style="font-size: 12px; color: #666;"><?= htmlspecialchars($data['nidk1'] ?? '-') ?></span>
                    </div>
                    <div class="dosen-box">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Pembimbing 2</small><br>
                        <span class="fw-bold"><?= htmlspecialchars($data['nama_dosen2'] ?? '-') ?></span><br>
                        <span style="font-size: 12px; color: #666;"><?= htmlspecialchars($data['nidk2'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0">Statistik Progres Skripsi</h4>
            <span class="badge bg-primary fs-6">Total Selesai: <?= $persentase_akhir ?>%</span>
        </div>
        
        <div class="progress mb-2" style="height: 30px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $persentase_akhir ?>%;">
                <?= $persentase_akhir ?>%
            </div>
        </div>
        <small class="text-muted mb-4 d-block">Persentase dihitung dari kelengkapan ACC.</small>

        <h6 class="text-uppercase text-secondary mb-3" style="font-size: 12px; letter-spacing: 1px;">Rincian Per Bab</h6>
        <div class="row g-3">
            <?php for ($bab = 1; $bab <= 5; $bab++): 
                $p_bab = $detail_bab[$bab] ?? 0;
                
                $bg_color = "bg-info";
                if($p_bab == 100) $bg_color = "bg-success";
                elseif($p_bab == 50) $bg_color = "bg-warning";
            ?>
            <div class="col-md-2 col-4">
                <div class="p-3 border rounded bg-white text-center h-100 shadow-sm">
                    <strong class="d-block mb-2">BAB <?= $bab ?></strong>
                    <div class="progress" style="height: 8px; background-color: #e9ecef;">
                        <div class="progress-bar <?= $bg_color ?>" style="width: <?= $p_bab ?>%"></div>
                    </div>
                    <span class="d-block mt-2 fw-bold text-secondary" style="font-size: 12px;"><?= $p_bab ?>%</span>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
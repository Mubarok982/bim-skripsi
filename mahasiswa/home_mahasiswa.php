<?php
session_start();
// Pastikan path ke db.php benar (naik satu folder)
include "../admin/db.php"; 

// Cek Login & Role Mahasiswa
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../auth/login.php");
    exit();
}

$npm = $_SESSION['npm'];

// --- 1. QUERY UTAMA (Biodata & Skripsi) ---
$sql = "SELECT 
            m.nama, 
            m.foto,
            dm.npm,
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
        WHERE m.username = ?"; // Menggunakan username (NPM) sebagai kunci

$stmt = $conn->prepare($sql);

// Debugging jika query salah ketik
if (!$stmt) {
    die("Query Error: " . mysqli_error($conn));
}

$stmt->bind_param("s", $npm);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Jika data tidak ditemukan (misal akun ada tapi biodata belum diisi)
if (!$data) {
    echo "<div style='padding:20px;'>Data biodata belum lengkap. Hubungi Admin. <br> <a href='../auth/login.php?action=logout'>Logout</a></div>";
    exit();
}

// --- 2. HITUNG PROGRES ---
$total_progress = 0;
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
if (mysqli_num_rows($cek_tabel) > 0) {
    $query_prog = mysqli_query($conn, "SELECT progres_dosen1, progres_dosen2 FROM progres_skripsi WHERE npm = '$npm'");
    while ($row = mysqli_fetch_assoc($query_prog)) {
        $total_progress += (int)$row['progres_dosen1'] + (int)$row['progres_dosen2'];
    }
}
$persentase = min(100, round($total_progress));
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
        /* Layout Fixed (Agar Header & Sidebar Diam) */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        
        /* Header */
        .header {
            position: fixed; top: 0; left: 0; width: 100%; height: 70px;
            background-color: #ffffff; border-bottom: 1px solid #dee2e6;
            z-index: 1050; display: flex; align-items: center; justify-content: space-between;
            padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px);
            background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040;
        }
        /* Style Link Sidebar */
        .sidebar a {
            color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px;
            border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        
        /* Link Aktif Manual */
        .sidebar a.active {
            background-color: #0d6efd; color: #ffffff; font-weight: bold;
            border-left: 4px solid #ffc107; padding-left: 30px;
        }

        /* Content */
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        
        /* Biodata Card */
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
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="active">Dashboard</a>
    
    <a href="progres_skripsi.php">Upload Progres</a>
    
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">
        &copy; ikhbal.khasodiq18@gmail.com
    </div>
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
                    <a href="update_biodata_mahasiswa.php" class="btn btn-sm btn-outline-primary w-100">Edit Profil</a>
                </div>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <table class="info-table w-100">
                    <tr><td width="100" class="fw-bold">Nama</td><td>: <?= htmlspecialchars($data['nama']) ?></td></tr>
                    <tr><td class="fw-bold">NPM</td><td>: <?= htmlspecialchars($data['npm']) ?></td></tr>
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
            <h4 class="m-0">Statistik Progres</h4>
            <span class="badge bg-primary fs-6">Total: <?= $total_progress ?> Poin</span>
        </div>
        
        <div class="progress mb-2" style="height: 30px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $persentase ?>%;">
                <?= $persentase ?>% Selesai
            </div>
        </div>
        <small class="text-muted mb-4 d-block">Bar ini menunjukkan akumulasi poin progres dari Bab 1 hingga Bab 5.</small>

        <h6 class="text-uppercase text-secondary mb-3" style="font-size: 12px; letter-spacing: 1px;">Rincian Per Bab</h6>
        <div class="row g-3">
            <?php for ($bab = 1; $bab <= 5; $bab++): 
                $p_bab = 0;
                // Hitung progres spesifik per bab jika query progres ada
                if (isset($query_prog)) {
                    // Reset pointer query atau query ulang agar akurat
                    $q_bab = mysqli_query($conn, "SELECT MAX(progres_dosen1) as p1, MAX(progres_dosen2) as p2 FROM progres_skripsi WHERE npm='$npm' AND bab='$bab'");
                    $d_bab = mysqli_fetch_assoc($q_bab);
                    $p1 = ($d_bab['p1'] > 0) ? 50 : 0; // Asumsi: jika ada nilai berarti 50%
                    $p2 = ($d_bab['p2'] > 0) ? 50 : 0;
                    $p_bab = $p1 + $p2;
                }
            ?>
            <div class="col-md-2 col-4">
                <div class="p-3 border rounded bg-white text-center h-100 shadow-sm">
                    <strong class="d-block mb-2">BAB <?= $bab ?></strong>
                    <div class="progress" style="height: 8px; background-color: #e9ecef;">
                        <div class="progress-bar bg-info" style="width: <?= $p_bab ?>%"></div>
                    </div>
                    <span class="d-block mt-2 fw-bold text-secondary" style="font-size: 12px;"><?= $p_bab ?>%</span>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

</div>

</body>
</html>
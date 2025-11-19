<?php
session_start();
include "../admin/db.php";

// Cek Login Dosen
if (!isset($_SESSION['nip'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nip_login = $_SESSION['nip'];

// --- 1. AMBIL DATA DOSEN (Foto & Nama) ---
$query_dosen = "SELECT m.nama, m.foto 
                FROM mstr_akun m 
                WHERE m.username = '$nip_login'";
$result_dosen = mysqli_query($conn, $query_dosen);
$dosen = mysqli_fetch_assoc($result_dosen);

// --- 2. AMBIL DATA MAHASISWA (Berdasarkan NPM di URL) ---
$npm_mhs = $_GET['npm'] ?? '';

if (empty($npm_mhs)) {
    echo "<script>alert('NPM Mahasiswa tidak ditemukan!'); window.location='home_dosen.php';</script>";
    exit();
}

// Query untuk mengambil nama, npm, judul, dan pembimbing
$query_mhs = "SELECT 
                m.nama,
                dm.npm,
                s.judul AS judul_skripsi,
                d1.username AS nip_pembimbing1,
                d2.username AS nip_pembimbing2
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
              LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
              LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
              WHERE dm.npm = '$npm_mhs'";

$result_mhs = mysqli_query($conn, $query_mhs);
$mhs = mysqli_fetch_assoc($result_mhs);

if (!$mhs) {
    echo "<script>alert('Data mahasiswa tidak ditemukan di database!'); window.location='home_dosen.php';</script>";
    exit();
}

// --- 3. AMBIL DATA PROGRES SKRIPSI ---
$progres_per_bab = [];
// Cek tabel dulu
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
if (mysqli_num_rows($cek_tabel) > 0) {
    $query_prog = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm = '$npm_mhs' ORDER BY bab, created_at DESC");
    while ($row = mysqli_fetch_assoc($query_prog)) {
        $bab = $row['bab'];
        $progres_per_bab[$bab][] = $row;
    }
}

// Hitung Total Progres
$totalBab = 5;
$totalACC = 0;
for ($bab = 1; $bab <= $totalBab; $bab++) {
    $acc1 = false;
    $acc2 = false;
    if (isset($progres_per_bab[$bab])) {
        foreach ($progres_per_bab[$bab] as $v) {
            if ($v['nilai_dosen1'] === 'ACC') $acc1 = true;
            if ($v['nilai_dosen2'] === 'ACC') $acc2 = true;
        }
    }
    if ($acc1) $totalACC++;
    if ($acc2) $totalACC++;
}
$maxProgress = $totalBab * 2; 
$persentase = ($maxProgress > 0) ? round(($totalACC / $maxProgress) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Progres Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../admin/ccsprogres.css">
  <style>
    /* Layout Fixed */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
    
    .badge-pembimbing { background-color: #ffc107; color: #333; padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 0.85rem; }
    .table-fixed th { vertical-align: middle; text-align: center; }
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
        <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($dosen['nama']) ?></span>
    </div>
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; overflow:hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #ced4da;">
        <?php if (!empty($dosen['foto']) && file_exists("../uploads/" . $dosen['foto'])): ?>
            <img src="../uploads/<?= $dosen['foto'] ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
            <span style="font-size: 20px;">👤</span>
        <?php endif; ?>
    </div>
  </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Dosen</h4>
    <a href="home_dosen.php">Dashboard</a>
    <a href="biodata_dosen.php">Profil Saya</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
</div>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="m-0 fw-bold text-primary">Detail Progres Mahasiswa</h4>
                <p class="text-muted small mb-0">Pantau perkembangan bimbingan skripsi</p>
            </div>
            <a href="home_dosen.php" class="btn btn-secondary btn-sm">← Kembali</a>
        </div>

        <div class="alert alert-light border mb-4">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless m-0">
                        <tr><td width="130"><strong>Nama</strong></td><td>: <?= htmlspecialchars($mhs['nama']) ?></td></tr>
                        <tr><td><strong>NPM</strong></td><td>: <span class="badge bg-secondary"><?= htmlspecialchars($mhs['npm']) ?></span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless m-0">
                        <tr><td width="130"><strong>Judul Skripsi</strong></td><td>: <span class="fst-italic"><?= htmlspecialchars($mhs['judul_skripsi'] ?? '-') ?></span></td></tr>
                        <tr>
                            <td><strong>Status Anda</strong></td>
                            <td>: 
                                <?php if ($mhs['nip_pembimbing1'] == $nip_login): ?>
                                    <span class="badge bg-success">Pembimbing 1</span>
                                <?php elseif ($mhs['nip_pembimbing2'] == $nip_login): ?>
                                    <span class="badge bg-info text-dark">Pembimbing 2</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Bukan Pembimbing</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php for ($bab = 1; $bab <= 5; $bab++): ?>
            <div class="card mb-4 border">
                <div class="card-header bg-light fw-bold text-dark">BAB <?= $bab ?></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">File Dokumen</th>
                                    <th width="20%">Tanggal Upload</th>
                                    <th width="15%">Status Dosen 1</th>
                                    <th width="15%">Status Dosen 2</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($progres_per_bab[$bab])): ?>
                                    <?php $no = 1; foreach ($progres_per_bab[$bab] as $data): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td>
                                            <a href="../mahasiswa/uploads/<?= htmlspecialchars($data['file']) ?>" target="_blank" class="text-decoration-none fw-bold">
                                                📄 <?= htmlspecialchars($data['file']) ?>
                                            </a>
                                        </td>
                                        <td class="small text-muted"><?= date('d M Y H:i', strtotime($data['created_at'])) ?></td>
                                        
                                        <td class="text-center">
                                            <?php 
                                                $s1 = $data['nilai_dosen1'] ?? '-';
                                                $bg1 = ($s1 == 'ACC') ? 'success' : (($s1 == 'Revisi') ? 'danger' : 'secondary');
                                                echo "<span class='badge bg-$bg1'>$s1</span>";
                                            ?>
                                        </td>

                                        <td class="text-center">
                                            <?php 
                                                $s2 = $data['nilai_dosen2'] ?? '-';
                                                $bg2 = ($s2 == 'ACC') ? 'success' : (($s2 == 'Revisi') ? 'danger' : 'secondary');
                                                echo "<span class='badge bg-$bg2'>$s2</span>";
                                            ?>
                                        </td>

                                        <td class="text-center">
                                            <button class='btn btn-sm btn-info text-white' 
                                                onclick='toggleKomentar(<?= $data['id'] ?>, 
                                                        <?= json_encode($data['komentar_dosen1']) ?>, 
                                                        <?= json_encode($data['komentar_dosen2']) ?>)'>
                                                💬 Komentar
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr id="komentar_row_<?= $data['id'] ?>" style="display:none; background-color: #f8f9fa;">
                                        <td colspan="6" class="p-3">
                                            <div class="row">
                                                <div class="col-md-6 border-end">
                                                    <strong class="text-primary d-block mb-1">Komentar Pembimbing 1:</strong>
                                                    <div id="komentar1_<?= $data['id'] ?>" class="text-dark small"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong class="text-info d-block mb-1">Komentar Pembimbing 2:</strong>
                                                    <div id="komentar2_<?= $data['id'] ?>" class="text-dark small"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Belum ada file yang diupload untuk Bab ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

    </div>
</div>

<script>
function toggleKomentar(id, k1, k2) {
    const row = document.getElementById("komentar_row_" + id);
    const div1 = document.getElementById("komentar1_" + id);
    const div2 = document.getElementById("komentar2_" + id);
    
    if (row.style.display === "none") {
        div1.innerHTML = k1 ? k1 : "<em>Tidak ada komentar.</em>";
        div2.innerHTML = k2 ? k2 : "<em>Tidak ada komentar.</em>";
        row.style.display = "table-row";
    } else {
        row.style.display = "none";
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
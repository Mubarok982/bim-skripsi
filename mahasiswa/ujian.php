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

// --- 1. AMBIL ID MAHASISWA & DATA DASAR ---
$sql_mahasiswa = "SELECT 
                    m.id,                      
                    m.nama, 
                    dm.npm AS npm_real,
                    m.foto
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

// --- 2. QUERY UTAMA: MENGAMBIL DATA RIWAYAT UJIAN ---
// Query ini sangat kompleks, melibatkan JOIN untuk mengambil nama dosen penguji/pembimbing
$sql_ujian = "
SELECT 
    us.id, 
    us.tanggal_daftar,
    us.tanggal AS tanggal_ujian,
    s.judul AS judul_skripsi,
    s.naskah,
    jus.nama AS jenis_ujian,
    us.status,
    us.ruang,
    
    -- Pembimbing
    p1.nama AS nama_pembimbing1, 
    p2.nama AS nama_pembimbing2,
    
    -- Penguji
    pg1.nama AS nama_penguji1,
    pg2.nama AS nama_penguji2,
    pg3.nama AS nama_penguji3,
    
    -- Persetujuan Pembimbing (untuk display ACC/Belum)
    us.persetujuan_pembimbing1,
    us.persetujuan_pembimbing2
    
FROM ujian_skripsi us
JOIN skripsi s ON us.id_skripsi = s.id
JOIN jenis_ujian_skripsi jus ON us.id_jenis_ujian_skripsi = jus.id
-- Join Pembimbing 1
LEFT JOIN mstr_akun p1 ON s.pembimbing1 = p1.id
-- Join Pembimbing 2
LEFT JOIN mstr_akun p2 ON s.pembimbing2 = p2.id
-- Join Penguji 1
LEFT JOIN mstr_akun pg1 ON us.penguji1 = pg1.id
-- Join Penguji 2
LEFT JOIN mstr_akun pg2 ON us.penguji2 = pg2.id
-- Join Penguji 3
LEFT JOIN mstr_akun pg3 ON us.penguji3 = pg3.id
WHERE s.id_mahasiswa = ?
ORDER BY us.tanggal_daftar DESC
";

$stmt_ujian = $conn->prepare($sql_ujian);
if ($stmt_ujian === FALSE) {
    die("Error preparing ujian query: " . $conn->error);
}
$stmt_ujian->bind_param("i", $mahasiswa_id);
$stmt_ujian->execute();
$result_ujian = $stmt_ujian->get_result();
$data_ujian = $result_ujian->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ujian Tugas Akhir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    
    <style>
        /* CSS re-implementasi dari home_mahasiswa.php */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        .sidebar h6 { color: #cfd8dc; opacity: 0.7; padding-left: 25px; margin-bottom: 5px !important; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .table th, .table td { vertical-align: middle; font-size: 0.9rem; }
        .table th { background-color: #e9ecef; }
        .btn-sm { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
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
    <h2 class="mb-4">Data Ujian Skripsi</h2>

    <div class="card p-4 shadow-sm border-0">
        <?php if (!empty($data_ujian)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tgl. Daftar</th>
                        <th>Jenis Ujian</th>
                        <th>Judul Skripsi</th>
                        <th>Pembimbing 1</th>
                        <th>Penguji 1, 2, 3</th>
                        <th>Tgl. Ujian</th>
                        <th>Ruang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data_ujian as $ujian): 
                        // Tentukan warna badge status
                        $status_class = match($ujian['status']) {
                            'Diterima' => 'bg-success',
                            'Perbaikan' => 'bg-warning text-dark',
                            'Mengulang' => 'bg-danger',
                            default => 'bg-info'
                        };
                        // Cek Persetujuan Pembimbing
                        $acc1 = $ujian['persetujuan_pembimbing1'] == 1 ? 'ACC' : 'Pending';
                        $acc2 = $ujian['persetujuan_pembimbing2'] == 1 ? 'ACC' : 'Pending';
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d M Y', strtotime($ujian['tanggal_daftar'])); ?></td>
                        <td>
                            <strong><?= htmlspecialchars($ujian['jenis_ujian']); ?></strong>
                            <small class="d-block text-muted">P1: <?= $acc1 ?> | P2: <?= $acc2 ?></small>
                        </td>
                        <td><?= htmlspecialchars($ujian['judul_skripsi']); ?></td>
                        <td>
                            <?= htmlspecialchars($ujian['nama_pembimbing1'] ?? '-'); ?>
                            <small class="d-block text-muted"><?= htmlspecialchars($ujian['nama_pembimbing2'] ?? '-'); ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($ujian['nama_penguji1'] ?? 'N/A'); ?><br>
                            <?= htmlspecialchars($ujian['nama_penguji2'] ?? 'N/A'); ?><br>
                            <?= htmlspecialchars($ujian['nama_penguji3'] ?? 'N/A'); ?>
                        </td>
                        <td><?= $ujian['tanggal_ujian'] ? date('d M Y', strtotime($ujian['tanggal_ujian'])) : 'Belum Ditentukan'; ?></td>
                        <td><?= htmlspecialchars($ujian['ruang'] ?? '-'); ?></td>
                        <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars($ujian['status']); ?></span></td>
                        <td>
                            <a href="detail_ujian.php?id=<?= $ujian['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            <?php if ($ujian['status'] == 'Perbaikan'): ?>
                                <a href="upload_revisi.php?id=<?= $ujian['id']; ?>" class="btn btn-sm btn-warning mt-1">Upload Revisi</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>Anda belum memiliki riwayat pendaftaran ujian (Sempro atau Sidang).
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
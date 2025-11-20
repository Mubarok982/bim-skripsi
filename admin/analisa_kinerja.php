<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) { header("Location: ../auth/login.php"); exit(); }
$nama_admin = $_SESSION['admin_username'] ?? 'Admin';

// Fungsi Hitung Durasi
function hitungDurasi($start, $end) {
    if (empty($end) || $end == '0000-00-00 00:00:00') return "<span class='badge bg-secondary'>Belum Dibalas</span>";
    
    $s = new DateTime($start); $e = new DateTime($end);
    $diff = $s->diff($e);
    
    $total_jam = ($diff->days * 24) + $diff->h;
    $cls = ($total_jam > 72) ? "text-danger fw-bold" : (($total_jam < 24) ? "text-success fw-bold" : "text-dark");
    
    if ($diff->days > 0) return "<span class='$cls'>{$diff->days} hari {$diff->h} jam</span>";
    if ($diff->h > 0) return "<span class='$cls'>{$diff->h} jam {$diff->i} menit</span>";
    return "<span class='$cls'>{$diff->i} menit (Kilat!)</span>";
}

// [QUERY YANG AMAN & SESUAI REQUEST]
// Kita ambil data utama dari progres_skripsi
// Lalu cari Nama Mahasiswa lewat NPM di data_mahasiswa -> mstr_akun
// Untuk Nama Dosen, kita ambil dari tabel skripsi (via id_mahasiswa) -> mstr_akun
$query = "SELECT 
            ps.*,
            m_mhs.nama AS nama_mhs,
            d1.nama AS nama_dosen1,
            d2.nama AS nama_dosen2
          FROM progres_skripsi ps
          LEFT JOIN data_mahasiswa dm ON ps.npm = dm.npm
          LEFT JOIN mstr_akun m_mhs ON dm.id = m_mhs.id
          -- Ambil Pembimbing dari tabel Skripsi (via ID Mahasiswa)
          LEFT JOIN skripsi s ON dm.id = s.id_mahasiswa
          LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
          LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
          ORDER BY ps.created_at DESC";

$result = mysqli_query($conn, $query);

// Cek Error Query
if (!$result) {
    die("<div style='padding:20px; color:red; border:1px solid red;'>
            <h3>❌ SQL Error</h3>
            Pesan: " . mysqli_error($conn) . "<br>
            Query: $query
         </div>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Analisa Kinerja Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ccsprogres.css">
    <style>
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background: #343a40; padding-top: 20px; }
        .sidebar a { color: #cfd8dc; display: block; padding: 12px 25px; text-decoration: none; border-left: 4px solid transparent; }
        .sidebar a:hover { background: #495057; color: #fff; }
        .sidebar a.active { background: #0d6efd; color: #fff; border-left: 4px solid #ffc107; padding-left: 30px; font-weight: bold; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; }
        .table th { background-color: #343a40; color: white; vertical-align: middle; }
    </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 ms-2 text-dark">MONITORING SKRIPSI</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end"><small class="d-block text-muted">Login</small><b><?= htmlspecialchars($nama_admin) ?></b></div>
        <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; justify-content: center; align-items: center;">👤</div>
    </div>
</div>

<div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    <a href="home_admin.php">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
    <a href="akun_dosen.php">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a>
</div>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="text-primary mb-1">Analisis Kinerja Dosen</h4>
                <p class="text-muted mb-0">Pantau kecepatan respon dosen dalam membimbing mahasiswa.</p>
            </div>
            <div>
                <a href="mahasiswa_skripsi.php" class="btn btn-secondary me-2">← Kembali</a>
                <a href="export_csv.php" class="btn btn-success">📥 Download CSV</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Mahasiswa</th>
                        <th rowspan="2">Bab</th>
                        <th rowspan="2">Waktu Upload</th>
                        <th colspan="2">Pembimbing 1</th>
                        <th colspan="2">Pembimbing 2</th>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <th>Respon</th>
                        <th>Nama</th>
                        <th>Respon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php $no=1; while($row = mysqli_fetch_assoc($result)): 
                            $waktu_d1 = $row['waktu_balas_d1'] ?? '';
                            $waktu_d2 = $row['waktu_balas_d2'] ?? '';
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="text-start">
                                <strong><?= htmlspecialchars($row['nama_mhs'] ?? 'Mahasiswa Tidak Ditemukan') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['npm']) ?></small>
                            </td>
                            <td class="fw-bold"><?= $row['bab'] ?></td>
                            <td class="small"><?= date('d/m/y H:i', strtotime($row['created_at'])) ?></td>
                            
                            <td class="small"><?= htmlspecialchars($row['nama_dosen1'] ?? '-') ?></td>
                            <td class="small"><?= hitungDurasi($row['created_at'], $waktu_d1) ?></td>
                            
                            <td class="small"><?= htmlspecialchars($row['nama_dosen2'] ?? '-') ?></td>
                            <td class="small"><?= hitungDurasi($row['created_at'], $waktu_d2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada data bimbingan yang masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
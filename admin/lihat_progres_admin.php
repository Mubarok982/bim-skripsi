<?php
session_start();
include "db.php";

// Cek login
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';
$npm = $_GET['npm'] ?? '';

// Validasi NPM dari URL
if (empty($npm)) {
    echo "<script>alert('NPM tidak ditemukan!'); window.location='home_admin.php';</script>";
    exit();
}

// --- 1. AMBIL DATA MAHASISWA (LOGIKA FIX) ---
// Kita cari mahasiswa di tabel 'data_mahasiswa' yang NPM-nya sesuai.
// Lalu kita JOIN ke 'mstr_akun' untuk ambil Nama.
// Lalu kita LEFT JOIN ke 'skripsi' untuk ambil Judul (via ID).

$query_info = "SELECT 
                m.nama, 
                dm.prodi, 
                s.judul AS judul_skripsi 
              FROM data_mahasiswa dm
              JOIN mstr_akun m ON dm.id = m.id
              LEFT JOIN skripsi s ON dm.id = s.id_mahasiswa
              WHERE dm.npm = '$npm'";

$res_info = mysqli_query($conn, $query_info);

// Error Handling jika Query Gagal
if (!$res_info) {
    die("Query Error: " . mysqli_error($conn));
}

$data_mhs = mysqli_fetch_assoc($res_info);

// Jika data mahasiswa tidak ada di database, tampilkan default agar halaman tidak blank/error
if (!$data_mhs) {
    $nama_display  = "Nama Tidak Ditemukan (Cek Biodata)";
    $prodi_display = "-";
    $judul_display = "-";
} else {
    $nama_display  = $data_mhs['nama'];
    $prodi_display = $data_mhs['prodi'];
    $judul_display = !empty($data_mhs['judul_skripsi']) ? $data_mhs['judul_skripsi'] : '- Belum Mengajukan Judul -';
}

// --- 2. AMBIL DATA PROGRES (DARI TABEL PROGRES_SKRIPSI) ---
// Ini query utama untuk menampilkan file
$progres_result = false;
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");

if (mysqli_num_rows($cek_tabel) > 0) {
    $q_progres = "SELECT * FROM progres_skripsi WHERE npm = ? ORDER BY bab ASC, created_at DESC";
    $stmt = $conn->prepare($q_progres);
    $stmt->bind_param("s", $npm);
    $stmt->execute();
    $progres_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Progres - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
    .badge-status { font-size: 0.85em; padding: 5px 10px; border-radius: 20px; }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <img src="unimma.png" alt="Logo" style="height: 50px;">
    <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
  </div>
  <div class="profile d-flex align-items-center gap-2">
    <div class="text-end d-none d-md-block" style="line-height: 1.2;">
        <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
        <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_admin) ?></span>
    </div>
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">👤</div>
  </div>
</div>

<?php 
    $page = 'lihat_progres_admin'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php"; 
?>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <h4 class="mb-4 text-primary border-bottom pb-2">Detail Progres Skripsi Mahasiswa</h4>
        
        <div class="row mb-4 bg-light p-3 rounded mx-0">
            <div class="col-md-6">
                <table class="table table-borderless m-0">
                    <tr><td width="120"><strong>NPM</strong></td><td>: <span class="fw-bold"><?= htmlspecialchars($npm) ?></span></td></tr>
                    <tr><td><strong>Nama</strong></td><td>: <?= htmlspecialchars($nama_display) ?></td></tr>
                    <tr><td><strong>Prodi</strong></td><td>: <?= htmlspecialchars($prodi_display) ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless m-0">
                    <tr><td><strong>Judul Skripsi</strong></td><td>:</td></tr>
                    <tr><td colspan="2" class="fst-italic text-dark"><?= htmlspecialchars($judul_display) ?></td></tr>
                </table>
            </div>
        </div>
        
        <a href="home_admin.php" class="btn btn-secondary btn-sm mb-3">← Kembali ke Dashboard</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-2 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="10%">BAB</th>
                        <th width="25%">File Dokumen</th>
                        <th width="20%">Tanggal Upload</th>
                        <th class="text-center" width="20%">Status Pembimbing 1</th>
                        <th class="text-center" width="20%">Status Pembimbing 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($progres_result && $progres_result->num_rows > 0): 
                        $no = 1; 
                        while ($row = $progres_result->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold text-primary">BAB <?= htmlspecialchars($row['bab']) ?></td>
                            <td>
                                <a href="../mahasiswa/uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    📄 Lihat File
                                </a>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars(date('d M Y H:i', strtotime($row['created_at']))) ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $s1 = $row['nilai_dosen1'] ?? '-';
                                    $bg1 = ($s1 == 'ACC') ? 'success' : (($s1 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$bg1 badge-status'>$s1</span>";
                                    
                                    if(!empty($row['komentar_dosen1'])) {
                                        echo "<div class='mt-1 small text-muted fst-italic'>\"".substr($row['komentar_dosen1'],0,30)."...\"</div>";
                                    }
                                ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $s2 = $row['nilai_dosen2'] ?? '-';
                                    $bg2 = ($s2 == 'ACC') ? 'success' : (($s2 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$bg2 badge-status'>$s2</span>";

                                    if(!empty($row['komentar_dosen2'])) {
                                        echo "<div class='mt-1 small text-muted fst-italic'>\"".substr($row['komentar_dosen2'],0,30)."...\"</div>";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <em>Belum ada data progres yang diunggah oleh mahasiswa dengan NPM <b><?= htmlspecialchars($npm) ?></b>.</em>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
<?php
session_start();
// Sesuaikan path db.php (Naik satu folder ke root atau admin)
include "../admin/db.php";

// Cek Login Dosen
// Session 'nip' diisi NIDK/NIP saat login
if (!isset($_SESSION['nip'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nip_login = $_SESSION['nip'];

// --- 1. AMBIL DATA DOSEN (Query JOIN) ---
// Kita butuh ID Dosen untuk mencari mahasiswa bimbingan di tabel skripsi
$query_dosen = "SELECT m.id, m.nama, m.foto, d.nidk 
                FROM mstr_akun m
                JOIN data_dosen d ON m.id = d.id
                WHERE m.username = '$nip_login'";

$result_dosen = mysqli_query($conn, $query_dosen);
$dosen = mysqli_fetch_assoc($result_dosen);

if (!$dosen) {
    echo "Data dosen tidak ditemukan. Hubungi Admin.";
    exit();
}

$id_dosen = $dosen['id']; // ID ini dipakai untuk filter bimbingan

// --- 2. AMBIL DAFTAR MAHASISWA BIMBINGAN ---
// Cari di tabel skripsi dimana pembimbing1 atau pembimbing2 adalah ID Dosen ini
$query_mhs = "SELECT 
                m.nama,
                dm.npm,
                s.judul AS judul_skripsi
              FROM skripsi s
              JOIN mstr_akun m ON s.id_mahasiswa = m.id
              JOIN data_mahasiswa dm ON s.id_mahasiswa = dm.id
              WHERE s.pembimbing1 = '$id_dosen' OR s.pembimbing2 = '$id_dosen'
              ORDER BY m.nama ASC";

$result_mhs = mysqli_query($conn, $query_mhs);

// Fungsi Hitung Progres
function hitungProgres($conn, $npm) {
    // Cek tabel dulu
    $cek = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
    if (mysqli_num_rows($cek) == 0) return 0;

    $sql = "SELECT progres_dosen1, progres_dosen2 FROM progres_skripsi WHERE npm = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $npm);
        $stmt->execute();
        $res = $stmt->get_result();
        $total = 0;
        while ($row = $res->fetch_assoc()) {
            $total += (int)$row['progres_dosen1'] + (int)$row['progres_dosen2'];
        }
        return min(100, round($total));
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Dosen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../admin/ccsprogres.css">
  <style>
    /* Layout Fixed */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #fff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; }
    .search-container input { width: 300px; display: inline-block; }
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
    <a href="home_dosen.php" class="active" style="background-color: #0d6efd;">Dashboard</a>
    <a href="biodata_dosen.php">Profil Saya</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">&copy; 2025 UNIMMA</div>
</div>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <h3 class="mb-1">Selamat Datang, <?= htmlspecialchars($dosen['nama']) ?></h3>
        <p class="text-muted mb-4">Dashboard Dosen Pembimbing</p>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0 text-primary fw-bold">Daftar Mahasiswa Bimbingan</h5>
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Cari Mahasiswa...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped" id="mhsTable">
                <thead class="table-dark">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="15%">NPM</th>
                        <th width="25%">Nama Mahasiswa</th>
                        <th width="35%">Judul Skripsi</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php 
                    if (mysqli_num_rows($result_mhs) > 0):
                        $no = 1;
                        while ($mhs = mysqli_fetch_assoc($result_mhs)): 
                            $npm = $mhs['npm'];
                            $progres = hitungProgres($conn, $npm);
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($npm) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td><?= htmlspecialchars($mhs['judul_skripsi']) ?></td>
                        <td class="text-center">
                            <div class="d-grid gap-1">
                                <a href="progres_mahasiswa.php?npm=<?= $npm ?>" class="btn btn-sm btn-primary">📄 Lihat Progres</a>
                                <button class="btn btn-sm btn-info text-white" onclick="toggleProgres('bar_<?= $npm ?>')">📊 Grafik</button>
                            </div>
                        </td>
                    </tr>
                    <tr id="bar_<?= $npm ?>" style="display: none; background-color: #f8f9fa;">
                        <td colspan="5" class="p-3">
                            <label class="fw-bold mb-1 small">Total Progres Skripsi:</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: <?= $progres ?>%;">
                                    <?= $progres ?>%
                                </div>
                            </div>
                            <small class="text-muted">Total poin: <?= $progres ?> / 100</small>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <em>Belum ada mahasiswa yang Anda bimbing saat ini.</em>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById("searchInput");
    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll("#mhsTable tbody tr");
        rows.forEach(row => {
            if (!row.id.startsWith('bar_')) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            }
        });
    });

    function toggleProgres(id) {
        const row = document.getElementById(id);
        row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
    }
</script>

</body>
</html>
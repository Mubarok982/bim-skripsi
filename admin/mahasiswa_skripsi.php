<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- 1. DAFTAR PRODI MANUAL ---
$list_prodi = [
    'Teknik Informatika S1', 'Teknologi Informasi D3', 'Teknik Industri S1', 'Teknik Mesin S1', 'Mesin Otomotif D3'
];

// --- 2. QUERY DATA MAHASISWA ---
$query_sql = "SELECT 
                m.id, m.nama, dm.npm, dm.prodi,
                s.judul AS judul_skripsi,
                d1.nama AS nama_dosen1, d2.nama AS nama_dosen2
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
              LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
              LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
              WHERE m.role = 'mahasiswa'
              ORDER BY dm.prodi ASC, m.nama ASC";

$query = mysqli_query($conn, $query_sql);
if (!$query) { die("Query Error: " . mysqli_error($conn)); }

$perProdi = [];
while ($row = mysqli_fetch_assoc($query)) {
    if (empty($row['judul_skripsi'])) $row['judul_skripsi'] = "- Belum Mengajukan -";
    if (empty($row['npm'])) $row['npm'] = "-";
    $perProdi[$row['prodi']][] = $row;
}

// --- FUNGSI HITUNG PROGRES ---
function hitungProgres($conn, $npm) {
    $cek = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
    if (mysqli_num_rows($cek) == 0) return 0;
    $sql = "SELECT SUM(poin_bab) as total FROM (SELECT (MAX(progres_dosen1) + MAX(progres_dosen2)) as poin_bab FROM progres_skripsi WHERE npm = ? GROUP BY bab) as subquery";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $npm);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return min(100, round(($row['total'] ?? 0) / 5));
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
    .sidebar a:hover { background-color: #495057; color: #fff; }
    .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
    .table-skripsi th, .table-skripsi td { text-align: center; vertical-align: middle; word-wrap: break-word; }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 d-none d-md-block" style="margin-left: 10px; color: #333;">MONITORING SKRIPSI</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end d-none d-md-block" style="line-height: 1.2;">
            <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_admin) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">👤</div>
    </div>
</div>

<div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    <a href="home_admin.php" class="active">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
    <a href="akun_dosen.php">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a> 
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">&copy; 2025 UNIMMA</div>
</div>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
      <h3>Selamat Datang, <?= htmlspecialchars($nama_admin) ?></h3>
      <p class="text-muted">Dashboard Admin Sistem Monitoring Skripsi</p>

      <div class="row align-items-center mb-4 g-2">
          <div class="col-md-4">
            <div class="input-group">
                <label class="input-group-text bg-white">Filter:</label>
                <select id="filterProdi" class="form-select">
                  <option value="all">-- Semua Prodi --</option>
                  <?php foreach ($list_prodi as $prodi): ?>
                    <option value="<?= htmlspecialchars($prodi) ?>"><?= htmlspecialchars($prodi) ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="col-md-4">
              <input type="text" id="search" class="form-control" placeholder="🔍 Cari Mahasiswa...">
          </div>
          <div class="col-md-4 text-end">
              <a href="analisa_kinerja.php" class="btn btn-primary w-100">
                  📊 Analisis Kinerja Dosen
              </a>
          </div>
      </div>

      <?php 
        $ada_data = false;
        foreach ($list_prodi as $prodi): 
            if (isset($perProdi[$prodi])): 
                $ada_data = true;
                $mahasiswas = $perProdi[$prodi];
      ?>
            <div class="prodi-section mb-5" data-prodi="<?= htmlspecialchars($prodi) ?>">
              <div class="d-flex align-items-center mb-3">
                  <div style="width: 5px; height: 25px; background: #0d6efd; margin-right: 10px; border-radius: 2px;"></div>
                  <h5 class="m-0 fw-bold text-primary"><?= htmlspecialchars($prodi) ?></h5>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered table-striped table-skripsi">
                  <thead class="table-dark">
                    <tr>
                      <th width="5%">No</th>
                      <th width="15%">NPM</th>
                      <th width="20%">Nama</th>
                      <th width="15%">Pembimbing 1</th>
                      <th width="15%">Pembimbing 2</th>
                      <th width="20%">Judul Skripsi</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $no = 1; foreach ($mahasiswas as $mhs): ?>
                      <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($mhs['npm']) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td><small><?= !empty($mhs['nama_dosen1']) ? htmlspecialchars($mhs['nama_dosen1']) : '-' ?></small></td>
                        <td><small><?= !empty($mhs['nama_dosen2']) ? htmlspecialchars($mhs['nama_dosen2']) : '-' ?></small></td>
                        <td><?= htmlspecialchars($mhs['judul_skripsi']) ?></td>
                        <?php $progres = hitungProgres($conn, $mhs['npm']); ?>
                        <td>
                          <div class="d-grid gap-1">
                              <a href="lihat_progres_admin.php?npm=<?= $mhs['npm'] ?>" class="btn btn-sm btn-primary">📄 Progres</a>
                              <button class="btn btn-sm btn-info text-white" onclick="toggleProgres('bar_<?= $mhs['id'] ?>')">📊 Grafik</button>
                          </div>
                        </td>
                      </tr>
                      <tr id="bar_<?= $mhs['id'] ?>" style="display: none; background-color: #f8f9fa;">
                        <td colspan="7">
                          <div style="padding: 10px;">
                            <label class="fw-bold small">Total Progres Skripsi:</label>
                            <div style="background: #e0e0e0; border-radius: 8px; overflow: hidden; height: 20px; width: 100%; margin-top: 5px;">
                              <div style="width: <?= $progres ?>%; background: #4caf50; height: 100%; color: white; text-align: center; font-size: 12px; line-height: 20px;">
                                <?= $progres ?>%
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
        <?php endif; endforeach; ?>

        <?php if (!$ada_data): ?>
            <div class="alert alert-info text-center py-5">
                <h4>📭 Belum ada data mahasiswa</h4>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
  document.getElementById("filterProdi").addEventListener("change", function() {
    const selected = this.value;
    document.querySelectorAll(".prodi-section").forEach(section => {
      section.style.display = (selected === "all" || section.dataset.prodi === selected) ? "block" : "none";
    });
  });

  document.getElementById("search").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll(".table-skripsi tbody tr").forEach(row => {
      if (!row.id.startsWith('bar_')) {
          row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
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
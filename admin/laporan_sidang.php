<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- Filter Prodi ---
$prodi_filter = $_GET['prodi'] ?? '';
$whereProdi = $prodi_filter ? "AND m.prodi = '$prodi_filter'" : "";

// --- Ambil daftar prodi unik ---
$prodi_result = mysqli_query($conn, "SELECT DISTINCT prodi FROM biodata_mahasiswa");

// --- SEMPRO ---
$sql_sempro = "
  SELECT m.npm, m.nama, m.prodi, m.judul_skripsi
  FROM biodata_mahasiswa m
  JOIN mahasiswa_skripsi ms ON m.npm = ms.npm
  JOIN (
      SELECT npm, SUM(progres_dosen1) AS total_dosen1, SUM(progres_dosen2) AS total_dosen2
      FROM progres_skripsi
      GROUP BY npm
  ) p ON m.npm = p.npm
  WHERE p.total_dosen1 >= 30 AND p.total_dosen2 >= 30
  $whereProdi
  ORDER BY m.prodi, m.npm
";
$sempro = mysqli_query($conn, $sql_sempro);

// --- PENDADARAN ---
$sql_pendadaran = "
  SELECT m.npm, m.nama, m.prodi, m.judul_skripsi
  FROM biodata_mahasiswa m
  JOIN mahasiswa_skripsi ms ON m.npm = ms.npm
  JOIN (
      SELECT npm, SUM(progres_dosen1) AS total_dosen1, SUM(progres_dosen2) AS total_dosen2
      FROM progres_skripsi
      GROUP BY npm
  ) p ON m.npm = p.npm
  WHERE p.total_dosen1 >= 50 AND p.total_dosen2 >= 50
  $whereProdi
  ORDER BY m.prodi, m.npm
";
$pendadaran = mysqli_query($conn, $sql_pendadaran);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Sidang</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    /* CSS Tambahan agar Layout Pas */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* Wrapper utama untuk menampung Sidebar dan Konten secara horizontal */
    .layout-wrapper {
        display: flex;
        flex: 1;
        width: 100%;
    }

    /* Pastikan konten utama mengisi sisa ruang di sebelah sidebar */
    .main-content {
        flex: 1;
        padding: 20px;
        background-color: #f8f9fa; /* Opsional: warna background konten */
        overflow-x: hidden; /* Mencegah scroll horizontal berlebih */
    }

    /* Styling Header */
    .header {
        z-index: 1000; /* Pastikan header di atas */
        position: relative;
    }

    .table {
        table-layout: fixed;
        width: 100%;
    }

    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word;
    }

    @media print {
      .no-print, .sidebar, .header { display: none !important; }
      .main-content { margin: 0; width: 100%; padding: 0; }
      .page-break { page-break-before: always; }
      .layout-wrapper { display: block; } /* Reset flex saat print */
    }
  </style>
</head>
<body>

<div class="header no-print">
  <div class="logo">
    <img src="unimma.png" alt="Logo">
  </div>
  <div class="title">
    <h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1>
  </div>
  <div class="profile">
    <div style="width: 50px; height: 50px; border-radius: 50%; background: #eee;
                display: flex; align-items: center; justify-content: center;
                font-size: 25px;">👤</div>
  </div>
</div>

<div class="layout-wrapper">
    
    <div class="sidebar-area no-print">
        <?php 
            $page = 'laporan_sidang'; 
            include "../templates/sidebar_admin.php";
        ?>
    </div>

    <div class="main-content">
        <div class="card-box w-100">
          <h3>📑 Laporan Sidang Skripsi</h3>
          <p class="text-muted">Laporan mahasiswa yang layak SEMPRO maupun PENDADARAN</p>

          <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
              🖨 Cetak Laporan
            </button>
          </div>

          <form method="get" class="mb-3 no-print">
            <label for="prodi" class="form-label">Filter Prodi:</label>
            <select name="prodi" id="prodi" class="form-select w-25 d-inline-block" onchange="this.form.submit()">
              <option value="">-- Semua Prodi --</option>
              <?php while ($row = mysqli_fetch_assoc($prodi_result)): ?>
                <option value="<?= htmlspecialchars($row['prodi']) ?>" 
                  <?= $prodi_filter == $row['prodi'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($row['prodi']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </form>

          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              📘 Daftar Mahasiswa Layak SEMPRO
            </div>
            <div class="card-body">
              <div class="table-responsive"> <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">NPM</th>
                        <th style="width: 20%;">Nama</th>
                        <th style="width: 15%;">Prodi</th>
                        <th style="width: 45%;">Judul Skripsi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      // Reset pointer data sempro jika perlu loop ulang, atau jalankan query ulang jika error
                      if(mysqli_num_rows($sempro) > 0) {
                          $no=1; 
                          while ($mhs = mysqli_fetch_assoc($sempro)): ?>
                          <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($mhs['npm']) ?></td>
                            <td><?= htmlspecialchars($mhs['nama']) ?></td>
                            <td><?= htmlspecialchars($mhs['prodi']) ?></td>
                            <td class="text-start"><?= htmlspecialchars($mhs['judul_skripsi']) ?></td>
                          </tr>
                          <?php endwhile; 
                      } else {
                          echo "<tr><td colspan='5'>Belum ada data layak Sempro</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
              </div>
            </div>
          </div>

          <div class="page-break"></div>

          <div class="card">
            <div class="card-header bg-success text-white">
              🎓 Daftar Mahasiswa Layak PENDADARAN
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">NPM</th>
                        <th style="width: 20%;">Nama</th>
                        <th style="width: 15%;">Prodi</th>
                        <th style="width: 45%;">Judul Skripsi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      if(mysqli_num_rows($pendadaran) > 0) {
                          $no=1; 
                          while ($mhs = mysqli_fetch_assoc($pendadaran)): ?>
                          <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($mhs['npm']) ?></td>
                            <td><?= htmlspecialchars($mhs['nama']) ?></td>
                            <td><?= htmlspecialchars($mhs['prodi']) ?></td>
                            <td class="text-start"><?= htmlspecialchars($mhs['judul_skripsi']) ?></td>
                          </tr>
                          <?php endwhile; 
                       } else {
                          echo "<tr><td colspan='5'>Belum ada data layak Pendadaran</td></tr>";
                       }
                      ?>
                    </tbody>
                  </table>
              </div>
            </div>
          </div>

        </div> </div> </div> </body>
</html>
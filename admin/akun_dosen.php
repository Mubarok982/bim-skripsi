<?php
session_start();
// Sesuaikan path db.php. Jika error, coba gunakan include "../admin/db.php" atau include "../db.php"
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php"); // Redirect ke login baru
    exit();
}

// --- PERBAIKAN QUERY DISINI ---
// Mengambil data user dengan role 'dosen' dari tabel mstr_akun
$query_sql = "SELECT * FROM mstr_akun WHERE role = 'dosen' ORDER BY username ASC";
$akun = mysqli_query($conn, $query_sql);

// Cek error query agar tidak muncul fatal error
if (!$akun) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akun Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ccsprogres.css">
    <style>
        /* --- CSS UNTUK LAYOUT SAMPING (FLEXBOX) --- */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Wrapper utama agar Sidebar dan Konten sejajar ke samping */
        .layout-wrapper {
            display: flex; 
            flex: 1;
            width: 100%;
        }

        /* Area Sidebar (lebarnya diatur dari file sidebar.php, ini wadahnya saja) */
        .sidebar-area {
            flex-shrink: 0;
        }

        /* Konten Utama mengisi sisa ruang */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: hidden;
        }

        .header { z-index: 1000; position: relative; }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="unimma.png" alt="Logo" style="height: 40px;">
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
    
    <div class="sidebar-area">
        <?php
            $page = 'akun_dosen'; // Penanda halaman aktif
            include "../templates/sidebar_admin.php"; 
        ?>
    </div>

    <div class="main-content">
      <div class="card-box">
        <h3>Daftar Akun Dosen</h3>
        <a href="tambah_dosen.php" class="btn btn-success mb-3">+ Tambah Dosen Baru</a>

        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>No</th>
                <th>NIP / NIDK (Username)</th>
                <th>Nama</th>
                <th>Password (Hash)</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = mysqli_fetch_assoc($akun)): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                
                <td style="word-break: break-all; font-size: 12px;">
                    <?= substr($row['password'], 0, 20) ?>... (Terenkripsi)
                </td>
                
                <td>
                  <a href="reset_password_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Reset password Dosen ini menjadi 123?')">🔁 Reset Pass</a>
                  <a href="hapus_akun_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus akun ini?')">🗑️ Hapus</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div> </div> </body>
</html>
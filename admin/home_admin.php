<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}
$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- HITUNG STATISTIK ---
// 1. Jumlah Dosen
$q_dosen = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='dosen'");
$total_dosen = mysqli_fetch_assoc($q_dosen)['total'];

// 2. Jumlah Mahasiswa
$q_mhs = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='mahasiswa'");
$total_mhs = mysqli_fetch_assoc($q_mhs)['total'];

// 3. Jumlah Skripsi Aktif
$q_skripsi = mysqli_query($conn, "SELECT COUNT(*) as total FROM skripsi");
$total_skripsi = mysqli_fetch_assoc($q_skripsi)['total'];

// 4. Data untuk Grafik (Jumlah Mhs per Prodi)
$labels = [];
$data_grafik = [];
$q_grafik = mysqli_query($conn, "SELECT prodi, COUNT(*) as jumlah FROM data_mahasiswa GROUP BY prodi");
while($row = mysqli_fetch_assoc($q_grafik)) {
    $labels[] = $row['prodi'];
    $data_grafik[] = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Statistik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f4f6f9; overflow-x: hidden; }
    
    /* Header Fixed */
    .header { 
        position: fixed; top: 0; left: 0; width: 100%; height: 70px; 
        background: #fff; z-index: 1050; padding: 0 25px; 
        display: flex; align-items: center; justify-content: space-between; 
        border-bottom: 1px solid #dee2e6; 
    }
    
    /* Sidebar Fixed */
    .sidebar { 
        position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); 
        background: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; 
    }
    
    /* Sidebar Links */
    .sidebar a { 
        color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; 
        border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; 
        border-left: 4px solid transparent; 
    }
    .sidebar a:hover { background-color: #495057; color: #fff; }
    
    /* Active Menu Style */
    .sidebar a.active { 
        background-color: #0d6efd; color: #ffffff; font-weight: bold; 
        border-left: 4px solid #ffc107; padding-left: 30px; 
    }

    /* Main Content Offset */
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; }
    
    /* Style Kartu Statistik */
    .stat-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 ms-2 text-dark">DASHBOARD UTAMA</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end"><small class="d-block text-muted">Admin</small><b><?= htmlspecialchars($nama_admin) ?></b></div>
        <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; justify-content: center; align-items: center;">👤</div>
    </div>
</div>
<?php 
    $page = 'home_admin'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php"; 
?>

<div class="main-content">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Jumlah Dosen</p>
                        <h2 class="fw-bold m-0"><?= $total_dosen ?></h2>
                    </div>
                    <div class="icon-box bg-primary text-white">
                        👨‍🏫
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Jumlah Mahasiswa</p>
                        <h2 class="fw-bold m-0"><?= $total_mhs ?></h2>
                    </div>
                    <div class="icon-box bg-info text-white">
                        🎓
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Judul Skripsi Aktif</p>
                        <h2 class="fw-bold m-0"><?= $total_skripsi ?></h2>
                    </div>
                    <div class="icon-box bg-success text-white">
                        📚
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card stat-card p-4">
                <h5 class="mb-4">Statistik Mahasiswa per Prodi</h5>
                <canvas id="prodiChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-4">
                <h5 class="mb-3">Status Sistem</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Server <span class="badge bg-success rounded-pill">Online</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Database <span class="badge bg-success rounded-pill">Connected</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        WA Gateway <span class="badge bg-warning text-dark rounded-pill">Check</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
  // Konfigurasi Grafik Chart.js
  const ctx = document.getElementById('prodiChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Jumlah Mahasiswa',
        data: <?= json_encode($data_grafik) ?>,
        backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>

</body>
</html>
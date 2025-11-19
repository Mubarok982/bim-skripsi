<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$npm = $_GET['npm'] ?? '';
if (!$npm) {
    echo "❌ NPM tidak ditemukan!";
    exit();
}

$query = mysqli_query($conn, "SELECT * FROM mahasiswa_skripsi WHERE npm = '$npm'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "❌ Data mahasiswa tidak ditemukan!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Mahasiswa Skripsi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
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

<div class="container-fluid">
    <div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    <a href="home_admin.php">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php" class="active" style="background-color: #0d6efd;">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php" >Akun Mahasiswa</a>
    <a href="akun_dosen.php">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a> 
    
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">
      &copy; 2025 UNIMMA
    </div>
</div>

    
    <div class="main-content">
      <div class="card-box w-100 text-start">
        <h3>Edit Mahasiswa Skripsi</h3>

        <form action="update_mahasiswa_skripsi.php" method="POST">
          <input type="hidden" name="npm_lama" value="<?= htmlspecialchars($data['npm']) ?>">

          <div class="mb-3">
            <label>NPM</label>
            <input type="text" name="npm_baru" class="form-control" required value="<?= htmlspecialchars($data['npm']) ?>">
          </div>

          <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($data['nama']) ?>">
          </div>

          <div class="mb-3">
            <label>Program Studi</label>
            <input type="text" name="prodi" class="form-control" required value="<?= htmlspecialchars($data['prodi']) ?>">
          </div>

           <div class="mb-3">
            <label for="semester">Semester</label>
            <select name="semester" class="form-control" required>
            <option value="7">Semester 7</option>
            <option value="8">Semester 8</option>
            <option value="9">Semester 9</option>
          </select>
          </div>

         <div class="mb-3">
            <label for="periode">Semester</label>
            <select name="periode" class="form-control" required>
            <option value="2025/2026 (Genap)">2025/2026 (Genap)</option>
          </select>
          </div>

          <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
          <a href="mahasiswa_skripsi.php" class="btn btn-secondary">← Kembali</a>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>

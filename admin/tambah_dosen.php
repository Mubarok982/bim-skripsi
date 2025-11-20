<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Dosen</title>
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
  
  <?php 
    $page = 'tambah_dosen'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php";
  ?>

  <div class="main-content">
    <div class="card-box text-start">
      <h3>Tambah Data Dosen</h3>
      <form action="simpan_dosen.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="nip" class="form-label">NIP</label>
          <input type="text" name="nip" id="nip" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="nama" class="form-label">Nama Dosen</label>
          <input type="text" name="nama" id="nama" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="prodi" class="form-label">Program Studi</label>
          <input type="text" name="prodi" id="prodi" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="no_hp" class="form-label">Nomor HP</label>
          <input type="text" name="no_hp" id="no_hp" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="foto" class="form-label">Foto (Opsional)</label>
          <input type="file" name="foto" id="foto" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">💾 Simpan</button>
        <a href="data_dosen.php" class="btn btn-secondary">← Kembali</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>

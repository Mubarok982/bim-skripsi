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
  <title>Tambah Mahasiswa Skripsi</title>
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
</div>

<div class="container-fluid">
<?php 
    $page = 'tambah_mahasiswa_skripsi'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php";
?>  

    <div class="main-content">
      <div class="card-box w-100 text-start">
        <h3>Tambah Mahasiswa Skripsi</h3>

        <form action="simpan_mahasiswa_skripsi.php" method="POST">
          <div class="mb-3">
            <label for="npm">NPM</label>
            <input type="text" name="npm" id="npm" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="nama">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="prodi">Program Studi</label>
            <input type="text" name="prodi" id="prodi" class="form-control" required>
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

          <button type="submit" class="btn btn-primary">+ Tambah</button>
          <a href="mahasiswa_skripsi.php" class="btn btn-secondary">← Kembali</a>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>

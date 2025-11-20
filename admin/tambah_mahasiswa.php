<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npm = $_POST['npm'];
    $nama = $_POST['nama'];
    $prodi = $_POST['prodi'];
    $no_hp = $_POST['no_hp'];
    $judul = $_POST['judul_skripsi'];
    $nip1 = $_POST['nip_pembimbing1'];
    $nip2 = $_POST['nip_pembimbing2'];

    $query = "INSERT INTO biodata_mahasiswa (npm, nama, prodi, no_hp, judul_skripsi, nip_pembimbing1, nip_pembimbing2) 
              VALUES ('$npm', '$nama', '$prodi', '$no_hp', '$judul', '$nip1', '$nip2')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('✅ Mahasiswa berhasil ditambahkan'); location.href='data_mahasiswa.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal menambahkan data');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mahasiswa</title>
    <link rel="stylesheet" href="ccsprogres.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="header">
  <div class="logo"><img src="unimma.png" alt="Logo" style="height: 40px;"></div>
  <div class="title"><h1>WEBSITE MONITORING SKRIPSI UNIMMA</h1></div>
  <div class="profile"><a href="data_mahasiswa.php">🔙</a></div>
</div>
<div class="container-fluid">
<?php 
    $page = 'tambah_mahasiswa'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php";
?>

  <div class="col-md-10 main-content">
    <div class="card-box text-start">
      <h3>Tambah Mahasiswa</h3>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">NPM</label>
          <input type="text" name="npm" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Program Studi</label>
          <input type="text" name="prodi" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">No. HP</label>
          <input type="text" name="no_hp" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Judul Skripsi</label>
          <input type="text" name="judul_skripsi" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Pembimbing 1 (NIP)</label>
          <input type="text" name="nip_pembimbing1" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Pembimbing 2 (NIP)</label>
          <input type="text" name="nip_pembimbing2" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">💾 Simpan</button>
        <a href="data_mahasiswa.php" class="btn btn-secondary">← Batal</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>

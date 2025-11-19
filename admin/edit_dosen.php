<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$nip = $_GET['nip'] ?? '';
if (!$nip) {
    echo "❌ NIP tidak ditemukan!";
    exit();
}

$query = mysqli_query($conn, "SELECT * FROM biodata_dosen WHERE nip='$nip'");
$dosen = mysqli_fetch_assoc($query);

if (!$dosen) {
    echo "❌ Data dosen tidak ditemukan!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Dosen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
</head>
<body>

<!-- HEADER -->
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

<!-- LAYOUT -->
<div class="container-fluid">
  <div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    <a href="home_admin.php">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php">Akun Mahasiswa</a>
    <a href="akun_dosen.php" class="active" style="background-color: #0d6efd;">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a> 
    
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">
      &copy; 2025 UNIMMA
    </div>
</div>

  <div class="main-content">
    <div class="card-box text-start">
      <h3>Edit Data Dosen</h3>
      <form action="update_dosen.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="nip" value="<?= htmlspecialchars($dosen['nip']) ?>">

        <div class="mb-3">
          <label>Nama Dosen</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($dosen['nama']) ?>" required>
        </div>

        <div class="mb-3">
          <label>Program Studi</label>
          <input type="text" name="prodi" class="form-control" value="<?= htmlspecialchars($dosen['prodi']) ?>" required>
        </div>

        <div class="mb-3">
          <label>Nomor HP</label>
          <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($dosen['no_hp']) ?>" required>
        </div>

        <div class="mb-3">
          <label>Foto Saat Ini</label><br>
          <?php if (!empty($dosen['foto']) && file_exists("../dosen/uploads/" . $dosen['foto'])): ?>
              <img src="../dosen/uploads/<?= htmlspecialchars($dosen['foto']) . '?v=' . filemtime('../dosen/uploads/' . $dosen['foto']) ?>" width="100" class="img-thumbnail mb-2">
          <?php else: ?>
              <p class="text-muted">Belum ada foto.</p>
          <?php endif; ?>
        </div>


        <div class="mb-3">
          <label>Ganti Foto (Opsional)</label>
          <input type="file" name="foto" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
        <a href="data_dosen.php" class="btn btn-secondary">← Kembali</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>

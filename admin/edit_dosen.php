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

<?php 
    $page = 'edit_dosen'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php"; 
?>

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

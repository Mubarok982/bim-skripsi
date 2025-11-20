<?php
session_start();
include "db.php";

// Cek login
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 1. Tangkap ID
$id_mhs = $_GET['id'] ?? '';
if (!$id_mhs) {
    echo "<script>alert('ID Mahasiswa tidak ditemukan!'); location.href='data_mahasiswa.php';</script>";
    exit();
}

// 2. QUERY LANGSUNG AMBIL dm.npm
$query = "SELECT 
            m.id, 
            dm.npm,   -- INI DIAMBIL LANGSUNG DARI TABEL DATA_MAHASISWA
            m.nama,
            dm.prodi, 
            dm.telepon,
            s.judul AS judul_skripsi,
            s.pembimbing1, 
            s.pembimbing2
          FROM mstr_akun m
          JOIN data_mahasiswa dm ON m.id = dm.id
          LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
          WHERE m.id = '$id_mhs' AND m.role = 'mahasiswa'";

$result = mysqli_query($conn, $query);
$mahasiswa = mysqli_fetch_assoc($result);

if (!$mahasiswa) {
    echo "<script>alert('Data mahasiswa tidak ditemukan!'); location.href='data_mahasiswa.php';</script>";
    exit();
}

// 3. Query List Dosen
$dosenList = mysqli_query($conn, "SELECT id, nama FROM mstr_akun WHERE role='dosen' ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    /* Layout Fixed */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #fff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <img src="unimma.png" alt="Logo" style="height: 50px;">
    <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
  </div>
  <div class="profile">
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">👤</div>
  </div>
</div>
<?php 
    $page = 'edit_mahasiswa'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php"; 
?>

<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
      <h4 class="mb-4 border-bottom pb-2">Edit Data Mahasiswa</h4>
      
      <form action="update_mahasiswa.php" method="POST">
        <input type="hidden" name="id_mhs" value="<?= $mahasiswa['id'] ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">NPM</label>
                <input type="text" name="npm" class="form-control" value="<?= htmlspecialchars($mahasiswa['npm']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($mahasiswa['nama']) ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Program Studi</label>
                <select name="prodi" class="form-control" required>
                    <option value="<?= htmlspecialchars($mahasiswa['prodi']) ?>"><?= htmlspecialchars($mahasiswa['prodi']) ?> (Saat ini)</option>
                    <option value="Teknik Informatika S1">Teknik Informatika S1</option>
                    <option value="Teknologi Informasi D3">Teknologi Informasi D3</option>
                    <option value="Teknik Industri S1">Teknik Industri S1</option>
                    <option value="Teknik Mesin S1">Teknik Mesin S1</option>
                    <option value="Mesin Otomotif D3">Mesin Otomotif D3</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($mahasiswa['telepon']) ?>">
            </div>
        </div>

        <hr class="my-4">
        <h5 class="text-primary mb-3">Data Skripsi (Opsional)</h5>

        <div class="mb-3">
            <label class="form-label fw-bold">Judul Skripsi</label>
            <textarea name="judul_skripsi" class="form-control" rows="2"><?= htmlspecialchars($mahasiswa['judul_skripsi'] ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Pembimbing 1</label>
                <select name="pembimbing1" class="form-select">
                    <option value="">-- Pilih Pembimbing 1 --</option>
                    <?php 
                    mysqli_data_seek($dosenList, 0);
                    while ($d = mysqli_fetch_assoc($dosenList)) {
                        $selected = ($mahasiswa['pembimbing1'] == $d['id']) ? 'selected' : '';
                        echo "<option value='{$d['id']}' $selected>{$d['nama']}</option>";
                    } 
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Pembimbing 2</label>
                <select name="pembimbing2" class="form-select">
                    <option value="">-- Pilih Pembimbing 2 --</option>
                    <?php 
                    mysqli_data_seek($dosenList, 0);
                    while ($d = mysqli_fetch_assoc($dosenList)) {
                        $selected = ($mahasiswa['pembimbing2'] == $d['id']) ? 'selected' : '';
                        echo "<option value='{$d['id']}' $selected>{$d['nama']}</option>";
                    } 
                    ?>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4">💾 Simpan Perubahan</button>
            <a href="data_mahasiswa.php" class="btn btn-secondary px-4">Batal</a>
        </div>

      </form>
    </div>
</div>

</body>
</html>
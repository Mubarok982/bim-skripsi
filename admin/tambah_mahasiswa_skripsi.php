<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['admin_username'] ?? 'Admin';
$page = 'tugas_akhir'; // Penanda halaman aktif di sidebar

// --- 1. AMBIL DAFTAR DOSEN (untuk dropdown Pembimbing) ---
$query_dosen = "SELECT d.id, m.nama 
                FROM data_dosen d 
                JOIN mstr_akun m ON d.id = m.id 
                WHERE m.role = 'dosen' AND d.is_praktisi = 0
                ORDER BY m.nama ASC";
$result_dosen = mysqli_query($conn, $query_dosen);
if (!$result_dosen) { die("Query Dosen Error: " . mysqli_error($conn)); }

$dosen_list = [];
while ($row = mysqli_fetch_assoc($result_dosen)) {
    $dosen_list[] = $row;
}

// --- 2. AMBIL DAFTAR MAHASISWA (yang belum punya skripsi) ---
$query_mhs = "SELECT m.id, m.nama, dm.npm 
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
              WHERE m.role = 'mahasiswa' AND s.id IS NULL
              ORDER BY m.nama ASC";
$result_mhs = mysqli_query($conn, $query_mhs);
if (!$result_mhs) { die("Query Mahasiswa Error: " . mysqli_error($conn)); }

$mhs_list = [];
while ($row = mysqli_fetch_assoc($result_mhs)) {
    $mhs_list[] = $row;
}

// --- 3. PROSES SIMPAN (Jika form disubmit) ---
if (isset($_POST['simpan'])) {
    $id_mahasiswa = mysqli_real_escape_string($conn, $_POST['id_mahasiswa']);
    $judul        = mysqli_real_escape_string($conn, $_POST['judul']);
    $tema         = mysqli_real_escape_string($conn, $_POST['tema']);
    $pembimbing1  = mysqli_real_escape_string($conn, $_POST['pembimbing1']);
    $pembimbing2  = mysqli_real_escape_string($conn, $_POST['pembimbing2']);
    $tgl_pengajuan = date('Y-m-d'); 

    // Cek apakah Pembimbing 1 dan 2 sama
    if ($pembimbing1 == $pembimbing2) {
        echo "<script>alert('Gagal: Pembimbing 1 dan Pembimbing 2 tidak boleh sama.');</script>";
    } else {
        $query_insert = "INSERT INTO skripsi (id_mahasiswa, tema, judul, pembimbing1, pembimbing2, tgl_pengajuan_judul, skema, nilai_akhir)
                         VALUES ('$id_mahasiswa', '$tema', '$judul', '$pembimbing1', '$pembimbing2', '$tgl_pengajuan', 'Reguler', NULL)";
        
        if (mysqli_query($conn, $query_insert)) {
            mysqli_query($conn, "UPDATE data_mahasiswa SET is_skripsi = 1 WHERE id = '$id_mahasiswa'");
            
            echo "<script>alert('✅ Pendaftaran Skripsi Berhasil!'); window.location='mahasiswa_skripsi.php';</script>";
        } else {
            echo "<script>alert('❌ Gagal Mendaftar Skripsi: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pendaftaran Tugas Akhir</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="ccsprogres.css"> 
  
  <style>
    /* --- LAYOUT FIXED POSITION (KONSISTENSI DASHBOARD) --- */
    body { background-color: #f8f9fe; margin: 0; padding: 0; overflow-x: hidden; font-family: 'Open Sans', sans-serif; }

    .header { /* Header Fixed */
        position: fixed; top: 0; left: 0; width: 100%; height: 70px; 
        background: #fff; z-index: 1050; padding: 0 25px; 
        display: flex; align-items: center; justify-content: space-between; 
        border-bottom: 1px solid #dee2e6; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .sidebar { /* Sidebar Fixed */
        position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); 
        background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; 
        z-index: 1040; 
    }
    
    .main-content { /* Content Offset */
        margin-top: 70px; 
        margin-left: 250px; 
        padding: 30px; 
        width: auto; 
        background-color: #f8f9fe;
    }
    
    /* --- STYLING FORM (FINAL CLEAN UP) --- */
    .card-custom { 
        border: 0; 
        box-shadow: 0 0 1.5rem 0 rgba(136, 152, 170, .1); 
        border-radius: .75rem; 
        background: #fff; 
    }
    .card-header { 
        background-color: #fff; 
        border-bottom: 1px solid #e9ecef; 
        padding: 1.5rem; 
        border-radius: .75rem .75rem 0 0; 
    }
    
    /* Label dan Input yang lebih terpisah dan jelas */
    .form-control, .form-select {
        border-radius: .5rem;
        border: 1px solid #dee2e6;
        padding: .65rem .75rem;
        font-size: .9rem;
    }
    
    .form-label {
        font-weight: 600; 
        color: #525f7f;
        margin-bottom: .25rem;
    }

    /* Tombol */
    .btn-primary { background-color: #5e72e4; border-color: #5e72e4; color: white; transition: all 0.2s; }
    .btn-primary:hover { background-color: #324cdd; border-color: #324cdd; }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 40px;">
        <h5 class="m-0 ms-3 fw-bold text-dark">FAKULTAS TEKNIK</h5>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end lh-sm">
            <small class="text-muted d-block">Admin</small>
            <span class="fw-bold"><?= htmlspecialchars($nama_admin) ?></span>
        </div>
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">👤</div>
    </div>
</div>

<?php 
    $page = 'tugas_akhir'; 
    include "../templates/sidebar_admin.php"; 
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="h2 text-dark d-inline-block mb-0">Pendaftaran Tugas Akhir</h6>
    </div>

    <div class="card card-custom">
        <div class="card-header">
            <h5 class="mb-0">Formulir Pengajuan Judul</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Mahasiswa</label>
                    <select name="id_mahasiswa" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Mahasiswa (yang belum punya judul) --</option>
                        <?php if (empty($mhs_list)): ?>
                            <option disabled>Semua mahasiswa sudah memiliki judul skripsi.</option>
                        <?php endif; ?>
                        <?php foreach ($mhs_list as $mhs): ?>
                            <option value="<?= $mhs['id'] ?>"><?= htmlspecialchars($mhs['nama']) ?> (<?= $mhs['npm'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Skripsi</label>
                    <textarea name="judul" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tema Penelitian</label>
                    <select name="tema" class="form-select" required>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Networking">Networking</option>
                        <option value="Artificial Intelligence">Artificial Intelligence</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pembimbing 1</label>
                        <select name="pembimbing1" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Dosen Pembimbing 1 --</option>
                            <?php foreach ($dosen_list as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>"><?= htmlspecialchars($dosen['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pembimbing 2</label>
                        <select name="pembimbing2" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Dosen Pembimbing 2 --</option>
                            <?php foreach ($dosen_list as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>"><?= htmlspecialchars($dosen['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="mahasiswa_skripsi.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-primary">💾 Daftarkan Judul</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
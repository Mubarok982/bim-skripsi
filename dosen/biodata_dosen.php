<?php
session_start();
include "../admin/db.php"; 

// Cek Login
if (!isset($_SESSION['nip'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nidk_login = $_SESSION['nip'];

// --- QUERY PERBAIKAN (Dengan Error Handling) ---
$query = "SELECT 
            m.id, m.nama, m.foto,
            d.nidk, d.prodi, d.ttd
          FROM mstr_akun m
          LEFT JOIN data_dosen d ON m.id = d.id
          WHERE m.username = ?";

$stmt = $conn->prepare($query);

// Cek apakah query valid
if (!$stmt) {
    die("Query Error: " . mysqli_error($conn));
}

$stmt->bind_param("s", $nidk_login);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Default data jika kosong
if (!$data) {
    $data = [
        'id' => '', 'nama' => '', 'foto' => '', 
        'nidk' => $nidk_login, 'prodi' => '', 'ttd' => ''
    ];
}
// Jika kolom nidk di tabel data_dosen masih kosong, pakai dari session
$nidk_value = !empty($data['nidk']) ? $data['nidk'] : $nidk_login;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Update Biodata Dosen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../admin/ccsprogres.css">
  <style>
    /* Layout Fixed */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
    .sidebar a:hover { background-color: #495057; color: #fff; }
    .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
    .card-form { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="../admin/unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
    </div>
    <div class="profile d-flex align-items-center gap-2">
        <div class="text-end d-none d-md-block" style="line-height: 1.2;">
            <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($data['nama']) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; overflow:hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #ced4da;">
            <?php if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])): ?>
                <img src="../uploads/<?= $data['foto'] ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <span style="font-size: 20px;">👤</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Dosen</h4>
    <a href="home_dosen.php">Dashboard</a>
    <a href="biodata_dosen.php" class="active">Profil Saya</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">&copy; 2025 UNIMMA</div>
</div>

<div class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card card-form">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h4 class="mb-0 text-primary fw-bold text-center">Update Biodata Dosen</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <form method="POST" action="simpan_biodata_dosen.php" enctype="multipart/form-data">
                            <input type="hidden" name="id_dosen" value="<?= $data['id'] ?>">
                            <input type="hidden" name="nidk_lama" value="<?= htmlspecialchars($nidk_value) ?>">

                            <div class="row">
                                <div class="col-md-4 text-center mb-4 border-end pe-md-4">
                                    <div class="mb-3 position-relative">
                                        <?php if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])): ?>
                                            <img src="../uploads/<?= $data['foto'] ?>" class="rounded-circle img-thumbnail shadow-sm" style="width:150px; height:150px; object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width:150px; height:150px; font-size:60px; color:#adb5bd;">👨‍🏫</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="text-start">
                                        <label class="form-label small fw-bold text-secondary">Ganti Foto Profil</label>
                                        <input class="form-control form-control-sm" type="file" name="foto" accept=".jpg, .jpeg, .png">
                                    </div>
                                </div>

                                <div class="col-md-8 ps-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">NIDK / NIP</label>
                                        <input type="text" name="nidk" class="form-control" value="<?= htmlspecialchars($nidk_value) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Lengkap & Gelar</label>
                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Studi</label>
                                        <select name="prodi" class="form-select" required>
                                            <option value="">-- Pilih Prodi --</option>
                                            <?php 
                                                $opsi_prodi = ['Teknik Informatika S1', 'Teknologi Informasi D3', 'Teknik Industri S1', 'Teknik Mesin S1', 'Mesin Otomotif D3'];
                                                foreach ($opsi_prodi as $op) {
                                                    $selected = (strcasecmp($data['prodi'], $op) == 0) ? 'selected' : '';
                                                    echo "<option value='$op' $selected>$op</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Scan Tanda Tangan (Opsional)</label>
                                        <input class="form-control" type="file" name="ttd" accept=".png, .jpg, .jpeg">
                                        <div class="form-text text-muted" style="font-size:11px;">Digunakan untuk validasi dokumen digital.</div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="home_dosen.php" class="btn btn-secondary px-4">Batal</a>
                                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
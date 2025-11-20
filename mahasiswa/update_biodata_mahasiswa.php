<?php
session_start();
// Sesuaikan path db.php (naik satu folder ke admin)
include "../admin/db.php";

// Cek Login
if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Session menyimpan username (yang biasanya NPM)
$session_user = $_SESSION['npm'];

// --- 1. AMBIL DATA LENGKAP (SELECT) ---
// Kita cari ID dulu berdasarkan username login, lalu JOIN ke data_mahasiswa dan skripsi
$query = "SELECT 
            m.id AS id_mhs,
            m.nama,
            m.foto,
            dm.npm,          -- Ambil NPM dari tabel data_mahasiswa
            dm.prodi,
            dm.telepon AS no_hp,
            s.judul AS judul_skripsi,
            s.pembimbing1 AS id_dosen1,
            s.pembimbing2 AS id_dosen2
          FROM mstr_akun m
          JOIN data_mahasiswa dm ON m.id = dm.id
          LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
          WHERE m.username = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_user);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Jika data tidak ditemukan, berarti akun ada tapi biodata belum diisi di data_mahasiswa
if (!$data) {
    echo "<div class='alert alert-danger m-4'>
            Data biodata Anda tidak ditemukan di tabel data_mahasiswa.<br>
            Mohon hubungi Admin untuk sinkronisasi data akun.
          </div>";
    exit();
}

$id_mhs = $data['id_mhs']; // Kunci utama untuk update

// Ambil List Dosen untuk Dropdown
$dosen = [];
$resultDosen = $conn->query("SELECT id, nama FROM mstr_akun WHERE role='dosen' ORDER BY nama ASC");
while ($row = $resultDosen->fetch_assoc()) {
    $dosen[] = $row;
}

// --- 2. PROSES UPDATE JIKA FORM DISUBMIT ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);
    $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $judul    = mysqli_real_escape_string($conn, $_POST['judul_skripsi']);
    
    // Handle Dosen (Set NULL jika tidak dipilih)
    $p1       = !empty($_POST['id_dosen1']) ? "'" . $_POST['id_dosen1'] . "'" : "NULL";
    $p2       = !empty($_POST['id_dosen2']) ? "'" . $_POST['id_dosen2'] . "'" : "NULL";

    // Validasi: Dosen 1 dan 2 tidak boleh sama
    if (!empty($_POST['id_dosen1']) && $_POST['id_dosen1'] === $_POST['id_dosen2']) {
        echo "<script>alert('Dosen Pembimbing 1 dan 2 tidak boleh sama!'); window.history.back();</script>";
        exit();
    }

    // --- HANDLE UPLOAD FOTO ---
    $namafile = $data['foto']; // Default pakai foto lama
    if (!empty($_FILES['foto']['name'])) {
        $fotoBaru = $_FILES['foto']['name'];
        $ext = pathinfo($fotoBaru, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($ext), $allowed)) {
            // Nama file unik: npm_waktu.ext
            $namafile = $data['npm'] . '_' . time() . '.' . $ext;
            $tmp_name = $_FILES['foto']['tmp_name'];
            move_uploaded_file($tmp_name, "../uploads/" . $namafile);
        } else {
            echo "<script>alert('Format foto harus JPG/PNG!'); window.history.back();</script>";
            exit();
        }
    }

    // --- UPDATE KE 3 TABEL BERDASARKAN ID_MAHASISWA ---
    
    // 1. Update mstr_akun (Nama & Foto)
    $q1 = "UPDATE mstr_akun SET nama='$nama', foto='$namafile' WHERE id='$id_mhs'";
    mysqli_query($conn, $q1);

    // 2. Update data_mahasiswa (Prodi & HP)
    // Note: NPM biasanya tidak diedit mahasiswa sendiri, jadi tidak diupdate di sini
    $q2 = "UPDATE data_mahasiswa SET prodi='$prodi', telepon='$no_hp' WHERE id='$id_mhs'";
    mysqli_query($conn, $q2);

    // 3. Update/Insert Skripsi
    $cek_skripsi = mysqli_query($conn, "SELECT id FROM skripsi WHERE id_mahasiswa = '$id_mhs'");
    
    if (mysqli_num_rows($cek_skripsi) > 0) {
        // Update jika sudah ada
        $q3 = "UPDATE skripsi SET judul='$judul', pembimbing1=$p1, pembimbing2=$p2 WHERE id_mahasiswa='$id_mhs'";
        mysqli_query($conn, $q3);
    } else {
        // Insert jika belum ada
        if (!empty($judul)) {
            $q3 = "INSERT INTO skripsi (id_mahasiswa, judul, pembimbing1, pembimbing2, tgl_pengajuan_judul, tema, skema) 
                   VALUES ('$id_mhs', '$judul', $p1, $p2, CURDATE(), 'Software Engineering', 'Reguler')";
            mysqli_query($conn, $q3);
        }
    }

    // Redirect Sukses
    echo "<script>alert('Data Berhasil Diperbarui!'); window.location='home_mahasiswa.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Biodata Mahasiswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/ccsprogres.css"> 
    <style>
        /* Layout Fixed */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        
        /* Header */
        .header {
            position: fixed; top: 0; left: 0; width: 100%; height: 70px;
            background-color: #ffffff; border-bottom: 1px solid #dee2e6;
            z-index: 1050; display: flex; align-items: center; justify-content: space-between;
            padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px);
            background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040;
        }
        .sidebar a {
            color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px;
            border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        
        /* Link Aktif Manual */
        .sidebar a.active {
            background-color: #0d6efd; color: #ffffff; font-weight: bold;
            border-left: 4px solid #ffc107; padding-left: 30px;
        }

        /* Content */
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
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>
    </div>
</div>

<?php
    $page = 'biodata_mahasiswa';
    include "../templates/sidebar_mahasiswa.php";
?>

<div class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-form">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h4 class="mb-0 text-primary fw-bold">Update Biodata Saya</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                
                                <div class="col-md-4 text-center border-end pe-md-4">
                                    <div class="mb-3 position-relative">
                                        <?php if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])): ?>
                                            <img src="../uploads/<?= $data['foto'] ?>" class="rounded-circle img-thumbnail shadow-sm" style="width:160px; height:160px; object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width:160px; height:160px; font-size:60px; color:#adb5bd;">👤</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($data['npm']) ?></h5>
                                    <p class="text-muted small mb-4">Mahasiswa</p>

                                    <div class="text-start">
                                        <label class="form-label small fw-bold text-secondary">Ganti Foto Profil</label>
                                        <input class="form-control form-control-sm" type="file" name="foto" accept=".jpg, .jpeg, .png">
                                        <div class="form-text text-muted" style="font-size: 11px;">Format: JPG/PNG. Max 2MB.</div>
                                    </div>
                                </div>

                                <div class="col-md-8 ps-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Lengkap</label>
                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Program Studi</label>
                                            <select name="prodi" class="form-select" required>
                                                <option value="">-- Pilih Prodi --</option>
                                                <?php 
                                                    $opsi_prodi = ['Teknik Informatika S1', 'Teknologi Informasi D3', 'Teknik Industri S1', 'Teknik Mesin S1', 'Mesin Otomotif D3'];
                                                    foreach ($opsi_prodi as $op) {
                                                        $selected = ($data['prodi'] == $op) ? 'selected' : '';
                                                        echo "<option value='$op' $selected>$op</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Nomor HP (WhatsApp)</label>
                                            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp']) ?>">
                                        </div>
                                    </div>

                                    <hr class="my-4 text-muted opacity-25">
                                    <h6 class="text-primary fw-bold mb-3">Data Skripsi & Pembimbing</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Judul Skripsi</label>
                                        <textarea name="judul_skripsi" class="form-control" rows="3" placeholder="Belum mengajukan judul..."><?= htmlspecialchars($data['judul_skripsi'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Pembimbing 1</label>
                                            <select name="id_dosen1" class="form-select">
                                                <option value="">-- Pilih Dosen --</option>
                                                <?php foreach ($dosen as $d): ?>
                                                    <option value="<?= $d['id'] ?>" <?= $data['id_dosen1'] == $d['id'] ? 'selected' : '' ?>>
                                                        <?= $d['nama'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Pembimbing 2</label>
                                            <select name="id_dosen2" class="form-select">
                                                <option value="">-- Pilih Dosen --</option>
                                                <?php foreach ($dosen as $d): ?>
                                                    <option value="<?= $d['id'] ?>" <?= $data['id_dosen2'] == $d['id'] ? 'selected' : '' ?>>
                                                        <?= $d['nama'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="home_mahasiswa.php" class="btn btn-secondary px-4">Batal</a>
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
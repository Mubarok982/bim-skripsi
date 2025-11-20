<?php
// --- 1. NYALAKAN SEMUA ERROR REPORTING (BIAR KETAHAUN SALAHNYA) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db.php"; // Pastikan file ini benar ada

// Cek koneksi database manual
if (!$conn) {
    die("<h1 style='color:red; text-align:center; margin-top:50px;'>❌ FATAL ERROR: KONEKSI DATABASE GAGAL!</h1>");
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- PROSES SIMPAN DATA ---
if (isset($_POST['simpan'])) {
    
    echo "<div style='background:#333; color:#0f0; padding:20px; font-family:monospace;'>";
    echo "<h3>--- MODE DEBUGGING AKTIF ---</h3>";

    // 1. Tangkap Input
    $npm              = $_POST['npm'];
    $nama             = $_POST['nama'];
    $angkatan         = $_POST['angkatan'];
    $prodi            = $_POST['prodi'];
    $telepon          = $_POST['telepon'];
    $email            = $_POST['email'];
    $jk               = $_POST['jk'];
    $status_mahasiswa = $_POST['status_mahasiswa'];
    $status_beasiswa  = $_POST['status_beasiswa'];

    echo "1. Data Form berhasil ditangkap.<br>";

    // 2. Cek Duplikasi NPM
    $cek_npm = mysqli_query($conn, "SELECT id FROM mstr_akun WHERE username='$npm'");
    if (mysqli_num_rows($cek_npm) > 0) {
        echo "<h2 style='color:red'>❌ STOP: NPM $npm SUDAH ADA DI DATABASE!</h2></div>";
        exit();
    }

    // 3. INSERT KE TABEL UTAMA (mstr_akun)
    $password_hash = password_hash("12345", PASSWORD_DEFAULT);
    $foto_default = "default.png"; 

    $sql_akun = "INSERT INTO mstr_akun (username, password, nama, role, foto) 
                 VALUES ('$npm', '$password_hash', '$nama', 'mahasiswa', '$foto_default')";
    
    echo "2. Mencoba Insert ke tabel mstr_akun...<br>";
    
    if (mysqli_query($conn, $sql_akun)) {
        $new_id = mysqli_insert_id($conn);
        echo "<b style='color:yellow'> >> BERHASIL! ID Akun Baru: $new_id</b><br><br>";

        // 4. INSERT KE TABEL DETAIL (data_mahasiswa)
        // SAYA ISI SEMUA KOLOM NOT NULL DENGAN DATA DUMMY SUPAYA TIDAK DITOLAK MYSQL
        $sql_mhs = "INSERT INTO data_mahasiswa 
        (id, npm, jenis_kelamin, email, telepon, angkatan, prodi, is_skripsi, alamat, 
            status_beasiswa, status_mahasiswa, ttd, nik, tempat_tgl_lahir, nama_ortu_dengan_gelar, 
            kelas, dokumen_identitas, sertifikat_office_puskom, sertifikat_btq_ibadah, 
            sertifikat_bahasa, sertifikat_kompetensi_ujian_komprehensif, 
            sertifikat_semaba_ppk_masta, sertifikat_kkn) 
        VALUES 
        ('$new_id', '$npm', '$jk', '$email', '$telepon', '$angkatan', '$prodi', 0, '-', 
            '$status_beasiswa', '$status_mahasiswa', 'default.png', '-', '-', '-', 
            '-', 'pending', 'pending', 'pending', 
            'pending', 'pending', 
            'pending', 'pending')";

        echo "3. Mencoba Insert ke tabel data_mahasiswa...<br>";

        if (mysqli_query($conn, $sql_mhs)) {
            echo "<h1 style='color:white; background:green; padding:10px;'>✅ SUKSES! SEMUA DATA MASUK.</h1>";
            echo "<a href='data_mahasiswa.php' style='color:white; font-size:20px;'>[ KLIK DISINI UNTUK KEMBALI ]</a>";
        } else {
            // INI BAGIAN PENTINGNYA: TAMPILKAN ERROR SQL JELAS-JELAS
            $error_db = mysqli_error($conn);
            echo "<h1 style='color:white; background:red; padding:10px;'>❌ GAGAL INSERT BIODATA!</h1>";
            echo "<p style='color:red; font-size:18px;'>Pesan Error MySQL: <b>$error_db</b></p>";
            echo "<p>Query yang gagal: <br> $sql_mhs</p>";
            
            // Hapus akun yatim piatu
            mysqli_query($conn, "DELETE FROM mstr_akun WHERE id='$new_id'");
            echo "<small>Rollback: Data akun di mstr_akun sudah dihapus kembali.</small>";
        }

    } else {
        $error_akun = mysqli_error($conn);
        echo "<h1 style='color:red'>❌ GAGAL INSERT AKUN!</h1>";
        echo "<p>Error: $error_akun</p>";
    }
    echo "</div>";
    exit(); // Stop script di sini biar kelihatan errornya
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Mahasiswa (Debug Mode)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css"> 
  <style>
    body { background-color: #f8f9fe; display: flex; flex-direction: column; min-height: 100vh; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; }
    .layout-wrapper { display: flex; flex: 1; margin-top: 70px; }
    .sidebar-area { width: 250px; background: #343a40; color: white; position: fixed; height: 100%; overflow-y: auto; z-index: 1000; }
    .main-content { margin-left: 250px; padding: 30px; width: 100%; }
    .card-custom { background: #fff; border: none; border-radius: 0.375rem; box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15); }
    .card-header { background-color: #fff; border-bottom: 1px solid #e9ecef; padding: 1.5rem; }
    .btn-primary-custom { background-color: #5e72e4; border-color: #5e72e4; color: white; }
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
        <small class="text-muted d-block">Operator</small>
        <span class="fw-bold"><?= htmlspecialchars($nama_admin) ?></span>
    </div>
    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">👤</div>
  </div>
</div>

<div class="layout-wrapper">
    <div class="sidebar-area">
        <?php include "../templates/sidebar_admin.php"; ?>
    </div>

    <div class="main-content">
        <div class="card card-custom">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark fw-bold">⚠️ MODE DEBUGGING: Tambah Mahasiswa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">NPM (Username)</label>
                            <input type="text" name="npm" class="form-control" placeholder="Contoh: 202544..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Program Studi</label>
                            <select name="prodi" class="form-select" required>
                                <option value="Teknik Informatika S1">Teknik Informatika S1</option>
                                <option value="Teknik Industri S1">Teknik Industri S1</option>
                                <option value="Teknik Mesin S1">Teknik Mesin S1</option>
                                <option value="Teknologi Informasi D3">Teknologi Informasi D3</option>
                                <option value="Mesin Otomotif D3">Mesin Otomotif D3</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Angkatan</label>
                            <input type="number" name="angkatan" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nomor Telepon</label>
                            <input type="text" name="telepon" class="form-control" placeholder="08...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Status Mahasiswa</label>
                            <select name="status_mahasiswa" class="form-select">
                                <option value="Murni">Murni</option>
                                <option value="Konversi">Konversi</option>
                                <option value="Transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Status Beasiswa</label>
                            <select name="status_beasiswa" class="form-select">
                                <option value="Tidak Aktif">Tidak Aktif</option>
                                <option value="Aktif">Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jenis Kelamin</label>
                            <select name="jk" class="form-select">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="data_mahasiswa.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" name="simpan" class="btn btn-primary-custom px-4 fw-bold">🚀 TEST SIMPAN DATA</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}
$page = 'tambah_akun_dosen'; // Penanda halaman aktif
include "../templates/sidebar_admin.php";

function generatePassword($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $password = generatePassword();

    $cek = mysqli_query($conn, "SELECT * FROM akun_dosen WHERE nip='$nip'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "❌ NIP sudah memiliki akun!";
    } else {
        $query = mysqli_query($conn, "INSERT INTO akun_dosen (nip, password) VALUES ('$nip', '$password')");
        if ($query) {
            $success = "✅ Akun dosen berhasil dibuat! Password: <strong>$password</strong>";
        } else {
            $error = "❌ Gagal menambahkan akun!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Akun Dosen</title>
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


        <!-- MAIN CONTENT -->
        <div class="col-md-10 main-content">
            <div class="card-box w-100 text-start">
                <h3>Tambah Akun Dosen</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP Dosen</label>
                        <input type="text" name="nip" id="nip" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">➕ Tambahkan Akun</button>
                    <a href="akun_dosen.php" class="btn btn-secondary">← Kembali</a>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>

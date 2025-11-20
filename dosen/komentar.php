<?php
session_start();
include "db.php";
include "../config_fonnte.php";
include "../kirim_fonnte.php";
$page = 'komentar';
include "../templates/sidebar_dosen.php";
// Cek Login Dosen
if (!isset($_SESSION['nip'])) {
    header("Location: login_dosen.php");
    exit();
}

$nip = $_SESSION['nip'];
$dosen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_dosen WHERE nip = '$nip'"));

$foto_path = !empty($dosen['foto']) && file_exists("uploads/" . $dosen['foto']) 
    ? "uploads/" . $dosen['foto']
    : '';
$npm = $_GET['npm'] ?? '';

if (!$npm) {
    echo "❌ NPM tidak dikirim. Silakan akses halaman ini dari menu progres.";
    exit();
}

$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_mahasiswa WHERE npm = '$npm'"));
if (!$mhs) {
    echo "❌ Mahasiswa dengan NPM '$npm' tidak ditemukan.";
    exit();
}

if ($mhs['nip_pembimbing1'] == $nip) {
    $status = 'dosen1';
} elseif ($mhs['nip_pembimbing2'] == $nip) {
    $status = 'dosen2';
} else {
    echo "❌ Anda tidak berhak mengakses komentar mahasiswa ini.";
    exit();
}

$progres_per_bab = [];
for ($bab = 1; $bab <= 5; $bab++) {
    $q = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm='$npm' AND bab='$bab' ORDER BY created_at DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($q)) {
        $progres_per_bab[$bab] = $row;
    }
}
$acc_status = [];
for ($bab = 1; $bab <= 5; $bab++) {
    $q = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm='$npm' AND bab='$bab' ORDER BY created_at DESC");
    while ($row = mysqli_fetch_assoc($q)) {
        if ($status === 'dosen1' && $row['nilai_dosen1'] === 'ACC') {
            $acc_status[$bab] = true;
            break;
        }
        if ($status === 'dosen2' && $row['nilai_dosen2'] === 'ACC') {
            $acc_status[$bab] = true;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bab_komentar = []; 
    $acc_status = [];

for ($bab = 1; $bab <= 5; $bab++) {
    $q = mysqli_query($conn, "SELECT * FROM progres_skripsi WHERE npm='$npm' AND bab='$bab' ORDER BY created_at DESC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $rows[] = $row;
    }

    if (count($rows)) {
        $progres_per_bab[$bab] = $rows[0]; 

        foreach ($rows as $row) {
            if ($status == 'dosen1' && $row['nilai_dosen1'] === 'ACC') {
                $acc_status[$bab] = true;
                break;
            }
            if ($status == 'dosen2' && $row['nilai_dosen2'] === 'ACC') {
                $acc_status[$bab] = true;
                break;
            }
        }
    }
}

    foreach ($_POST['komentar'] as $bab => $komentar) {
        $komentar = mysqli_real_escape_string($conn, $komentar);
        $nilai = mysqli_real_escape_string($conn, $_POST['nilai'][$bab]);

        if (isset($progres_per_bab[$bab])) {
            $id_progres = $progres_per_bab[$bab]['id'];
          if ($status === 'dosen1') {
            $progres = ($nilai === 'ACC') ? 10 : 0;
            mysqli_query($conn, "UPDATE progres_skripsi 
                                 SET komentar_dosen1='$komentar', 
                                     nilai_dosen1='$nilai', 
                                     progres_dosen1=$progres 
                                 WHERE id='$id_progres'");
        } else {
            $progres = ($nilai === 'ACC') ? 10 : 0;
            mysqli_query($conn, "UPDATE progres_skripsi 
                                 SET komentar_dosen2='$komentar', 
                                     nilai_dosen2='$nilai', 
                                     progres_dosen2=$progres 
                                 WHERE id='$id_progres'");
        }

            $bab_komentar[] = "BAB $bab";
        }
    }

    
    $nohp_mhs = $mhs['no_hp'] ?? '';
    $namaMhs = $mhs['nama'] ?? 'Mahasiswa';
    $judul = $mhs['judul_skripsi'] ?? '-';
    $namaDosen = $dosen['nama'] ?? 'Dosen';
    $peran = $status === 'dosen1' ? 'Pembimbing 1' : 'Pembimbing 2';
    $bab_list = implode(', ', $bab_komentar);

    $pesan = "🔔 *Komentar Progres Skripsi*\n"
           . "👨‍🎓 Nama: $namaMhs\n"
           . "📘 Judul: $judul\n"
           . "📄 $bab_list\n"
           . "📝 $namaDosen ($peran) telah memberikan komentar.\n"
           . "Silakan cek sistem untuk melihat detailnya.";

   
    if (!empty($nohp_mhs)) {
        sleep(1); 
        kirimWaFonnte($nohp_mhs, $pesan);
    }

    echo "<script>alert('Komentar dan status berhasil disimpan'); location.href='komentar.php?npm=$npm';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Komentar & Penilaian</title>
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
     <div class="profile">
        <a href="biodata_dosen.php">
  <?php if (!empty($dosen['foto']) && file_exists("../dosen/uploads/" . $dosen['foto'])): ?>
      <img src="uploads/<?= htmlspecialchars($dosen['foto']) ?>?t=<?= time() ?>" width="50" height="50"
     style="object-fit: cover; border-radius: 50%; border: 2px solid white;" />
  <?php else: ?>
      <div style="width: 50px; height: 50px; border-radius: 50%; background: #eee;
                  display: flex; align-items: center; justify-content: center;
                  font-size: 25px;">👤</div>
  <?php endif; ?>
</a>
    </div>
</div>

<div class="container-fluid">


    <div class="col-md-10 main-content">
        <div class="card-box text-start">
            <h3>Komentar & Penilaian</h3>
            <p><strong>Nama:</strong> <?= htmlspecialchars($mhs['nama']) ?></p>
            <p><strong>NPM:</strong> <?= htmlspecialchars($mhs['npm']) ?></p>
            <p><strong>Judul Skripsi:</strong> <?= htmlspecialchars($mhs['judul_skripsi']) ?></p>

            <form method="POST" class="text-start">
                <?php foreach ($progres_per_bab as $bab => $data): ?>
                    <hr>
                    <h5>BAB <?= $bab ?></h5>
                    <p><strong>File Terbaru:</strong>
                        <a href="../mahasiswa/uploads/<?= htmlspecialchars($data['file']) ?>" target="_blank"><?= htmlspecialchars($data['file']) ?></a>
                    </p>
                    <p><strong>Tanggal Upload:</strong> <?= $data['created_at'] ?></p>

                    <?php
                    $komentar = $status === 'dosen1' ? $data['komentar_dosen1'] : $data['komentar_dosen2'];
                    $nilai = $status === 'dosen1' ? $data['nilai_dosen1'] : $data['nilai_dosen2'];
                    ?>

                    <?php if (!empty($acc_status[$bab])): ?>
                    <div class="alert alert-success">✅ Anda sudah ACC untuk BAB ini. Komentar tidak diperlukan lagi.</div>
                <?php elseif ($komentar && $nilai !== null): ?>
                    <p><strong>Komentar:</strong> <?= nl2br(htmlspecialchars($komentar)) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($nilai) ?></p>
                <?php else: ?>
                    <div class="mb-3">
                        <label for="komentar<?= $bab ?>" class="form-label">Komentar</label>
                        <textarea class="form-control" name="komentar[<?= $bab ?>]" id="komentar<?= $bab ?>" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nilai<?= $bab ?>" class="form-label">Status</label>
                        <select class="form-control" name="nilai[<?= $bab ?>]" id="nilai<?= $bab ?>" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Revisi">Revisi</option>
                            <option value="Belum Disetujui">Belum Disetujui</option>
                            <option value="ACC">ACC</option>
                        </select>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary mt-3">💾 Simpan</button>
                <a href="home_dosen.php" class="btn btn-secondary mt-3">← Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>

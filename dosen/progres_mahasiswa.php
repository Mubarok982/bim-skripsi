<?php
session_start();
include "../admin/db.php";

// --- LOAD FONNTE ---
if (file_exists("../config_fonnte.php")) {
    include "../config_fonnte.php";
    include "../kirim_fonnte.php";
} else {
    function kirimWaFonnte($no, $msg) { return false; }
}

// Cek Login
if (!isset($_SESSION['nip'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nip_login = $_SESSION['nip']; 

// --- 1. IDENTIFIKASI DOSEN ---
$q_dosen = mysqli_query($conn, "SELECT id, nama, foto FROM mstr_akun WHERE username='$nip_login'");
$data_dosen = mysqli_fetch_assoc($q_dosen);
$id_dosen_login = $data_dosen['id'];

// --- 2. IDENTIFIKASI MAHASISWA ---
$npm_mhs = $_GET['npm'] ?? '';
if (empty($npm_mhs)) {
    echo "<script>alert('NPM tidak ditemukan'); window.history.back();</script>";
    exit();
}

// Ambil Data Mahasiswa & Skripsi
$q_mhs = "SELECT 
            m.nama, 
            dm.npm, 
            dm.telepon AS no_hp, 
            s.judul AS judul_skripsi, 
            s.pembimbing1, 
            s.pembimbing2 
          FROM mstr_akun m 
          JOIN data_mahasiswa dm ON m.id=dm.id 
          LEFT JOIN skripsi s ON s.id_mahasiswa=dm.id 
          WHERE dm.npm='$npm_mhs'";

$res_mhs = mysqli_query($conn, $q_mhs);
$mhs = mysqli_fetch_assoc($res_mhs);

if (!$mhs) { echo "Mahasiswa tidak ditemukan."; exit(); }

// Tentukan Peran Dosen
$peran = '';
if ($mhs['pembimbing1'] == $id_dosen_login) $peran = 'dosen1';
elseif ($mhs['pembimbing2'] == $id_dosen_login) $peran = 'dosen2';

// --- 3. PROSES SIMPAN KOMENTAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. Handle Chat Bebas
    if (isset($_POST['action']) && $_POST['action'] == 'chat_bebas') {
        $pesan_input = $_POST['pesan_wa'];
        $no_hp = $mhs['no_hp'];

        if (empty($no_hp)) {
            echo "<script>alert('❌ Gagal: Nomor HP Mahasiswa tidak tersedia.');</script>";
        } else {
            $no_hp = preg_replace('/[^0-9]/', '', $no_hp);
            if (substr($no_hp, 0, 1) == '0') $no_hp = '62' . substr($no_hp, 1);

            $nama_dosen_pengirim = $data_dosen['nama'];
            $pesan_final = "💬 *PESAN DARI DOSEN PEMBIMBING*\n"
                         . "----------------------------------\n"
                         . "🧑‍🏫 Dosen: $nama_dosen_pengirim\n"
                         . "✉️ Pesan:\n"
                         . "$pesan_input\n\n"
                         . "> Sent via Sistem Monitoring Skripsi";

            kirimWaFonnte($no_hp, $pesan_final);
            echo "<script>alert('✅ Pesan WA berhasil dikirim!'); window.location='progres_mahasiswa.php?npm=$npm_mhs';</script>";
            exit();
        }
    }
    
    // B. Handle Nilai & Komentar (BAGIAN INI DIPERBAIKI)
    elseif (isset($_POST['action']) && $_POST['action'] == 'nilai') {
        if (empty($peran)) {
            echo "<script>alert('Anda bukan pembimbing mahasiswa ini! (Cek data skripsi)');</script>";
        } else {
            $id_progres = $_POST['id_progres'];
            $komentar   = mysqli_real_escape_string($conn, $_POST['komentar']);
            $status_nilai = $_POST['status']; 
            $bab_ke     = $_POST['bab_ke']; 

            // Tentukan Nama Kolom
            $kolom_komentar = ($peran == 'dosen1') ? 'komentar_dosen1' : 'komentar_dosen2';
            $kolom_nilai    = ($peran == 'dosen1') ? 'nilai_dosen1' : 'nilai_dosen2';
            $kolom_progres  = ($peran == 'dosen1') ? 'progres_dosen1' : 'progres_dosen2';
            $kolom_waktu    = ($peran == 'dosen1') ? 'waktu_balas_d1' : 'waktu_balas_d2'; // <--- INI KOLOM WAKTU
            
            $poin = ($status_nilai == 'ACC') ? 50 : 0; 

            // [FIX QUERY] Tambahkan NOW() untuk mencatat waktu
            $update = "UPDATE progres_skripsi SET 
                        $kolom_komentar = '$komentar',
                        $kolom_nilai = '$status_nilai',
                        $kolom_progres = $poin,
                        $kolom_waktu = NOW() 
                       WHERE id = '$id_progres'";
            
            if (mysqli_query($conn, $update)) {
                
                // Kirim Notifikasi WA
                $no_hp = $mhs['no_hp'];
                if (!empty($no_hp)) {
                    $no_hp = preg_replace('/[^0-9]/', '', $no_hp);
                    if (substr($no_hp, 0, 1) == '0') $no_hp = '62' . substr($no_hp, 1);

                    $nama_dosen_pengirim = $data_dosen['nama'];
                    $peran_text = ($peran == 'dosen1') ? "(Pembimbing 1)" : "(Pembimbing 2)";
                    $judul_skripsi = $mhs['judul_skripsi'] ?? 'Judul Belum Ada';
                    $status_text = ($status_nilai == 'ACC') ? "ACC (Disetujui) ✅" : "Revisi 📝";

                    $pesan = "🔔 *Komentar Progres Skripsi*\n"
                           . "👨‍🎓 Nama: {$mhs['nama']}\n"
                           . "📘 Judul: {$judul_skripsi}\n"
                           . "📄 BAB $bab_ke\n"
                           . "📝 $nama_dosen_pengirim $peran_text telah memberikan komentar.\n"
                           . "💬 *$komentar*\n"
                           . "📊 Status: $status_text\n\n"
                           . "Silakan cek sistem untuk melihat detailnya.\n"
                           . "> Sent via fonnte.com";
                    
                    kirimWaFonnte($no_hp, $pesan);
                }

                echo "<script>alert('✅ Penilaian berhasil disimpan & Notifikasi terkirim!'); window.location='progres_mahasiswa.php?npm=$npm_mhs';</script>";
                exit();
            } else {
                echo "<script>alert('Gagal menyimpan: " . mysqli_error($conn) . "');</script>";
            }
        }
    }
}

// --- 4. AMBIL DATA PROGRES PER BAB ---
$progres_per_bab = [];
$q_prog = "SELECT * FROM progres_skripsi WHERE npm='$npm_mhs' ORDER BY created_at DESC";
$res_prog = mysqli_query($conn, $q_prog);

if ($res_prog) {
    while ($row = mysqli_fetch_assoc($res_prog)) {
        $progres_per_bab[$row['bab']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penilaian Skripsi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    <style>
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .form-komentar { background-color: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; }
        .modal-header { background-color: #0d6efd; color: white; }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="../admin/unimma.png" alt="Logo" style="height: 50px; margin-right: 10px;">
        <h4 class="m-0 text-dark d-none d-md-block">MONITORING SKRIPSI</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end">
            <small class="d-block text-muted">Login Sebagai</small>
            <span class="fw-bold"><?= htmlspecialchars($data_dosen['nama']) ?></span>
        </div>
        <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <?php if(!empty($data_dosen['foto']) && file_exists("../uploads/".$data_dosen['foto'])): ?>
                <img src="../uploads/<?= $data_dosen['foto'] ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Dosen</h6>
    <a href="home_dosen.php">Dashboard</a>
    <a href="biodata_dosen.php">Profil Saya</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
</div>

<div class="main-content">
    <div class="card shadow-sm border-0 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="text-primary fw-bold mb-1">Penilaian Progres Skripsi</h4>
                <p class="text-muted mb-0">Mahasiswa: <strong><?= htmlspecialchars($mhs['nama']) ?></strong> (<?= htmlspecialchars($mhs['npm']) ?>)</p>
                <?php if(empty($mhs['no_hp'])): ?>
                    <span class="badge bg-danger">No HP Kosong (WA tidak akan terkirim)</span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WA: <?= htmlspecialchars($mhs['no_hp']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalChat">
                    <span style="font-size:1.2em; margin-right:5px;">💬</span> Chat WA
                </button>
                <a href="home_dosen.php" class="btn btn-secondary btn-sm d-flex align-items-center">← Kembali</a>
            </div>
        </div>
        
        <div class="alert alert-info mt-3 py-2">
            <strong>Status Anda:</strong> 
            <?php 
                if ($peran == 'dosen1') echo "Pembimbing 1 (Utama)";
                elseif ($peran == 'dosen2') echo "Pembimbing 2 (Pendamping)";
                else echo "Bukan Pembimbing (Hanya Melihat)";
            ?>
        </div>
    </div>

    <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold text-dark py-3">
                BAB <?= $i ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="30%">File</th>
                                <th width="20%">Tanggal Upload</th>
                                <th width="15%" class="text-center">Status Anda</th>
                                <th width="15%" class="text-center">Status Rekan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (isset($progres_per_bab[$i])): ?>
                            <?php $no=1; foreach ($progres_per_bab[$i] as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <a href="../mahasiswa/uploads/<?= $row['file'] ?>" target="_blank" class="text-decoration-none fw-bold">
                                            📄 <?= $row['file'] ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                    
                                    <td class="text-center">
                                        <?php 
                                            $val = ($peran == 'dosen1') ? $row['nilai_dosen1'] : $row['nilai_dosen2'];
                                            $bg = ($val == 'ACC') ? 'success' : (($val == 'Revisi') ? 'danger' : 'secondary');
                                            echo "<span class='badge bg-$bg'>" . ($val ?: '-') . "</span>";
                                        ?>
                                    </td>

                                    <td class="text-center text-muted">
                                        <?php 
                                            $val2 = ($peran == 'dosen1') ? $row['nilai_dosen2'] : $row['nilai_dosen1'];
                                            echo $val2 ?: '-';
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($peran): ?>
                                            <button class="btn btn-sm btn-primary" onclick="toggleForm(<?= $row['id'] ?>)">
                                                📝 Nilai
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <tr id="form_<?= $row['id'] ?>" style="display: none;">
                                    <td colspan="6" class="bg-light p-3">
                                        <form method="POST" class="form-komentar">
                                            <input type="hidden" name="action" value="nilai">
                                            <input type="hidden" name="id_progres" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="bab_ke" value="<?= $i ?>">
                                            
                                            <div class="mb-2">
                                                <label class="fw-bold small">Komentar / Revisi:</label>
                                                <?php 
                                                    $komentar_lama = ($peran == 'dosen1') ? $row['komentar_dosen1'] : $row['komentar_dosen2'];
                                                    $status_lama = ($peran == 'dosen1') ? $row['nilai_dosen1'] : $row['nilai_dosen2'];
                                                ?>
                                                <textarea name="komentar" class="form-control" rows="3" required><?= htmlspecialchars($komentar_lama ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="row align-items-end">
                                                <div class="col-md-4">
                                                    <label class="fw-bold small">Status:</label>
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="Revisi" <?= $status_lama == 'Revisi' ? 'selected' : '' ?>>Revisi</option>
                                                        <option value="ACC" <?= $status_lama == 'ACC' ? 'selected' : '' ?>>ACC (Setujui)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8 text-end">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleForm(<?= $row['id'] ?>)">Batal</button>
                                                    <button type="submit" class="btn btn-success btn-sm">💾 Simpan & Kirim WA</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada file diupload untuk bab ini.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

<div class="modal fade" id="modalChat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">💬 Kirim Pesan WhatsApp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="chat_bebas">
            <div class="mb-3">
                <label class="form-label fw-bold">Penerima:</label>
                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($mhs['nama']) ?> (<?= htmlspecialchars($mhs['no_hp'] ?? 'No HP Kosong') ?>)" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Pesan:</label>
                <textarea name="pesan_wa" class="form-control" rows="4" placeholder="Tulis pesan Anda di sini..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">✈️ Kirim Pesan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleForm(id) {
    var formRow = document.getElementById('form_' + id);
    if (formRow.style.display === 'none') {
        formRow.style.display = 'table-row';
    } else {
        formRow.style.display = 'none';
    }
}
</script>

</body>
</html>
<?php
// FILE: progres_skripsi.php (Professional & Self-Contained)
session_start();
// HANYA INCLUDE KONEKSI DATABASE
include "../admin/db.php"; 

// --- 0. FUNGSI DAN KONSTANTA EMBEDDED (Prioritas 3) ---

/**
 * Fungsi untuk menentukan apakah link navigasi aktif.
 */
if (!function_exists('is_active')) {
    function is_active($target_page, $current_page) {
        if (is_array($target_page)) {
            return in_array($current_page, $target_page) ? 'active' : '';
        }
        return ($current_page === $target_page) ? 'active' : '';
    }
}

/**
 * Fungsi untuk mendapatkan nama lengkap Bab Skripsi.
 */
if (!function_exists('getJudulBab')) {
    function getJudulBab($bab) {
        $judul = [
            1 => "PENDAHULUAN",
            2 => "TINJAUAN PUSTAKA",
            3 => "METODOLOGI PENELITIAN",
            4 => "HASIL DAN PEMBAHASAN",
            5 => "KESIMPULAN DAN SARAN"
        ];
        return $judul[$bab] ?? "BAB $bab";
    }
}

// --- AKHIR FUNGSI EMBEDDED ---

// --- LOGIKA SESI DAN DATA AWAL ---

if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}

$session_user = $_SESSION['npm'];
$current_page = basename($_SERVER['PHP_SELF']);
$error_msg = null;
$success_msg = null;

// --- 1. AMBIL DATA PROFIL & PEMBIMBING (JOIN LENGKAP) ---
$query_user = "SELECT 
                m.nama, 
                m.foto, 
                dm.npm AS npm_real,
                d1.nama AS nama_dosen1, 
                d2.nama AS nama_dosen2
                FROM mstr_akun m
                LEFT JOIN data_mahasiswa dm ON m.id = dm.id
                LEFT JOIN skripsi s ON m.id = s.id_mahasiswa
                LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
                LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
                WHERE m.username = ?";

if (!($stmt_user = $conn->prepare($query_user))) {
    die("Query Error [Profil]: " . $conn->error);
}

$stmt_user->bind_param("s", $session_user);
$stmt_user->execute();
$data_user = $stmt_user->get_result()->fetch_assoc();

if (!$data_user) {
    header("Location: ../auth/login.php?action=logout");
    exit();
}

$nama_mhs = $data_user['nama'];
$foto_mhs = $data_user['foto'];
$nama_dosen1 = $data_user['nama_dosen1'];
$nama_dosen2 = $data_user['nama_dosen2'];
$npm_tampil = !empty($data_user['npm_real']) ? $data_user['npm_real'] : $session_user;

// --- 2. LOGIKA UPLOAD PROGRES (SIMPAN DATA) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_progres_submit'])) {
    $bab = filter_input(INPUT_POST, 'bab', FILTER_VALIDATE_INT);
    
    if ($bab === false || $bab < 1 || $bab > 5) {
        $error_msg = "Nomor Bab tidak valid.";
    } elseif (!isset($_FILES['file_progres']) || $_FILES['file_progres']['error'] != UPLOAD_ERR_OK) {
        $error_msg = "Gagal mengunggah file. Pastikan ukuran file tidak melebihi batas server.";
    } elseif (pathinfo($_FILES['file_progres']['name'], PATHINFO_EXTENSION) !== 'pdf') {
        $error_msg = "Format Salah! File harus berupa PDF.";
    } else {
        // Proses Upload File
        $file_name = "Progres_{$npm_tampil}_BAB{$bab}_" . time() . ".pdf";
        $target_dir = "../uploads/progres_skripsi/";
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['file_progres']['tmp_name'], $target_file)) {
            // INSERT ke tabel progres_skripsi (Menggunakan Prepared Statement)
            $insert_sql = "INSERT INTO progres_skripsi (npm, bab, file, created_at) VALUES (?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($insert_sql);
            
            if ($stmt_insert === FALSE) {
                $error_msg = "Database Error [Insert]: " . $conn->error;
                unlink($target_file);
            } else {
                $stmt_insert->bind_param("sis", $npm_tampil, $bab, $file_name);
                if ($stmt_insert->execute()) {
                    $success_msg = "Progres BAB $bab berhasil diunggah. Menunggu komentar Pembimbing.";
                } else {
                    $error_msg = "Gagal menyimpan data progres: " . $conn->error;
                    unlink($target_file);
                }
            }
        } else {
            $error_msg = "Gagal memindahkan file yang diunggah.";
        }
    }
}

// --- 3. HITUNG PROGRES (Dibutuhkan untuk header) ---
$total_skor_semua_bab = 0;
$detail_bab = []; 

for ($i = 1; $i <= 5; $i++) {
    // Penggunaan mysqli_query di sini adalah karena kode asli Anda menggunakan mysqli_query
    // dan variabel $npm_tampil sudah aman dari injection.
    $q_cek = mysqli_query($conn, "SELECT nilai_dosen1, nilai_dosen2 FROM progres_skripsi WHERE npm='$npm_tampil' AND bab='$i' ORDER BY created_at DESC LIMIT 1");
    $d_cek = mysqli_fetch_assoc($q_cek);

    $p1 = (isset($d_cek['nilai_dosen1']) && $d_cek['nilai_dosen1'] == 'ACC') ? 50 : 0;
    $p2 = (isset($d_cek['nilai_dosen2']) && $d_cek['nilai_dosen2'] == 'ACC') ? 50 : 0;
    
    $skor_bab = $p1 + $p2; 
    
    $detail_bab[$i] = $skor_bab;
    $total_skor_semua_bab += $skor_bab;
}

$persentase_akhir = round($total_skor_semua_bab / 5);
// --- AKHIR LOGIKA PHP ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Progres Skripsi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    <style>
        /* CSS DARI KODE ASLI */
        body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header h4 { font-size: 1.2rem; font-weight: 700; color: #333; margin-left: 10px; }
        .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 12px 25px; border-radius: 0 25px 25px 0; margin-bottom: 5px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar a.active { background-color: #0d6efd; color: #ffffff; font-weight: bold; border-left: 4px solid #ffc107; padding-left: 30px; }
        .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
        .card-bab { background: white; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef; }
        .card-bab h5 { color: #0d6efd; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }
        .table th { background-color: #343a40; color: white; }
        .riwayat-komentar { background: #f8f9fa; padding: 15px; border-left: 4px solid #17a2b8; margin-top: 5px; font-size: 0.9rem; }
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
            <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_mhs) ?></span>
        </div>
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px; overflow: hidden;">
             <?php if (!empty($foto_mhs) && file_exists("../uploads/" . $foto_mhs)): ?>
                 <img src="../uploads/<?= $foto_mhs ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                 👤
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="<?= is_active(['home_mahasiswa.php', 'index.php'], $current_page) ?>">
        Dashboard
    </a>
    <a href="progres_skripsi.php" class="<?= is_active('progres_skripsi.php', $current_page) ?>">
        Upload Progres
    </a>
    
    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Kelola Tugas Akhir</h6>
    <a href="skripsi.php" class="<?= is_active('skripsi.php', $current_page) ?>">
        Pengajuan Tugas Akhir
    </a>
    <a href="ujian.php" class="<?= is_active('ujian.php', $current_page) ?>">
        Ujian Tugas Akhir
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Persyaratan</h6>
    <a href="syarat_sempro.php" class="<?= is_active('syarat_sempro.php', $current_page) ?>">
        Syarat Proposal
    </a>
    <a href="syarat_sidang.php" class="<?= is_active('syarat_sidang.php', $current_page) ?>">
        Syarat Pendadaran
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Pengaturan</h6>
    <a href="profile.php" class="<?= is_active('profile.php', $current_page) ?>">
        Profile
    </a>

    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">
        Logout
    </a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">&copy; 2025 UNIMMA</div>
</div>
<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0 fw-bold text-dark">Progres Skripsi</h3>
        <span class="badge bg-secondary fs-6 px-3 py-2">NPM: <?= htmlspecialchars($npm_tampil) ?></span>
    </div>

    <div class="alert alert-info py-2 mb-4 small">
        <div class="row">
            <div class="col-md-6">
                <strong>Pembimbing 1:</strong> <?= htmlspecialchars($nama_dosen1 ?? 'Belum Ditentukan') ?>
            </div>
            <div class="col-md-6">
                <strong>Pembimbing 2:</strong> <?= htmlspecialchars($nama_dosen2 ?? 'Belum Ditentukan') ?>
            </div>
        </div>
    </div>
    <?php if ($error_msg): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ❌ <strong>Error!</strong> <?= htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <strong>Berhasil!</strong> <?= htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="card-bab">
            <h5>BAB <?= $i ?> - <?= getJudulBab($i) ?></h5>

            <form method="post" action="progres_skripsi.php" enctype="multipart/form-data" class="mb-4">
                <input type="hidden" name="upload_progres_submit" value="1">
                <input type="hidden" name="bab" value="<?= $i ?>">
                <label class="form-label small text-muted fw-bold">Upload File Revisi Terbaru (PDF)</label>
                <div class="input-group">
                    <input type="file" name="file_progres" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-primary" type="submit">
                        <span style="margin-right: 5px;">📤</span> Upload
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">File Dokumen</th>
                            <th width="20%">Tanggal</th>
                            <th width="15%" class="text-center">P1 (<?= htmlspecialchars($nama_dosen1 ?? 'P1'); ?>)</th>
                            <th width="15%" class="text-center">P2 (<?= htmlspecialchars($nama_dosen2 ?? 'P2'); ?>)</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
                        if (mysqli_num_rows($cek_tabel) > 0) {
                            $sql = "SELECT * FROM progres_skripsi WHERE npm = ? AND bab = ? ORDER BY created_at DESC";
                            $stmt = $conn->prepare($sql);
                            
                            if ($stmt === FALSE) {
                                echo "<tr><td colspan='5' class='text-center text-danger'>SQL Error: " . $conn->error . "</td></tr>";
                            } else {
                                $stmt->bind_param("si", $npm_tampil, $i);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <a href="../uploads/progres_skripsi/<?= $row['file'] ?>" target="_blank" class="text-decoration-none fw-bold">
                                    📄 <?= $row['file'] ?>
                                </a>
                            </td>
                            <td class="small text-muted">
                                <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $s1 = $row['nilai_dosen1'];
                                    $bg1 = ($s1 == 'ACC') ? 'success' : (($s1 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$bg1'>" . ($s1 ?: 'Menunggu') . "</span>";
                                ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $s2 = $row['nilai_dosen2'];
                                    $bg2 = ($s2 == 'ACC') ? 'success' : (($s2 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$bg2'>" . ($s2 ?: 'Menunggu') . "</span>";
                                ?>
                            </td>
                            <td class="text-center">
                                <button class='btn btn-sm btn-info text-white'
                                    onclick='toggleKomentar(<?= $row['id'] ?>)'>
                                    💬 Komentar
                                </button>
                            </td>
                        </tr>
                        <tr id="komentar_row_<?= $row['id'] ?>" style="display:none;">
                            <td colspan="5" class="p-0 border-0">
                                <div class="riwayat-komentar">
                                    <div class="row">
                                        <div class="col-md-6 border-end">
                                            <strong class="text-primary">Komentar P. 1 (<?= htmlspecialchars($nama_dosen1 ?? 'P1'); ?>):</strong><br>
                                            <span class="d-block mt-1 text-dark">
                                                <?= !empty($row['komentar_dosen1']) ? nl2br(htmlspecialchars($row['komentar_dosen1'])) : '<em class="text-muted">Tidak ada komentar.</em>' ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong class="text-primary">Komentar P. 2 (<?= htmlspecialchars($nama_dosen2 ?? 'P2'); ?>):</strong><br>
                                            <span class="d-block mt-1 text-dark">
                                                <?= !empty($row['komentar_dosen2']) ? nl2br(htmlspecialchars($row['komentar_dosen2'])) : '<em class="text-muted">Tidak ada komentar.</em>' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                    endwhile;
                                else:
                                    echo "<tr><td colspan='5' class='text-center text-muted py-3'>Belum ada file yang diupload untuk bab ini.</td></tr>";
                                endif;
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-danger'>Tabel Progres Skripsi belum dibuat oleh Admin.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endfor; ?>

</div>

<script>
    // Hanya membersihkan URL jika ada pesan sukses/error dari POST
    if (window.history.replaceState && (window.location.search.includes('upload') || window.location.search.includes('error'))) {
        const url = new URL(window.location);
        url.searchParams.delete('upload');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.pathname);
    }

    // Toggle Komentar
    function toggleKomentar(id) {
        const row = document.getElementById("komentar_row_" + id);
        if (row.style.display === "none") {
            row.style.display = "table-row";
        } else {
            row.style.display = "none";
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
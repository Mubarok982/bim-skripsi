<?php
session_start();
include "../admin/db.php";

// Cek Login
if (!isset($_SESSION['npm'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ini adalah Username Login (Session)
$session_user = $_SESSION['npm'];

// --- 1. AMBIL DATA LENGKAP (TERMASUK NPM DARI DATA_MAHASISWA) ---
// Kita JOIN mstr_akun dengan data_mahasiswa
$query_user = "SELECT 
                m.nama, 
                m.foto, 
                dm.npm AS npm_real  -- Ambil kolom NPM dari tabel data_mahasiswa
               FROM mstr_akun m
               LEFT JOIN data_mahasiswa dm ON m.id = dm.id
               WHERE m.username = '$session_user'";

$result_user = mysqli_query($conn, $query_user);
$data_user   = mysqli_fetch_assoc($result_user);

$nama_mhs = $data_user['nama'];
$foto_mhs = $data_user['foto'];

// Logika Tampilan NPM: 
// Jika di tabel data_mahasiswa ada isinya, pakai itu. 
// Jika kosong (belum sinkron), pakai username login sebagai cadangan.
$npm_tampil = !empty($data_user['npm_real']) ? $data_user['npm_real'] : $session_user;

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Progres Skripsi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/ccsprogres.css">
    <style>
        /* Layout Fixed (Sama dengan Dashboard) */
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
        
        /* Custom Card Bab */
        .card-bab {
            background: white; border-radius: 12px; padding: 20px; margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;
        }
        .card-bab h5 { color: #0d6efd; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }
        
        /* Table Style */
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
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    <a href="home_mahasiswa.php">Dashboard</a>
    <a href="progres_skripsi.php" class="active">Upload Progres</a>
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a>
</div>


<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0 fw-bold text-dark">Progres Skripsi</h3>
        <span class="badge bg-secondary fs-6 px-3 py-2">NPM: <?= htmlspecialchars($npm_tampil) ?></span>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'type'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ❌ <strong>Format Salah!</strong> File harus berupa PDF.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['upload']) && $_GET['upload'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <strong>Berhasil!</strong> File progres telah diupload.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="card-bab">
            <h5>BAB <?= $i ?> - <?= getJudulBab($i) ?></h5>

            <form method="post" action="simpan_progres.php" enctype="multipart/form-data" class="mb-4">
                <input type="hidden" name="bab" value="<?= $i ?>">
                <label class="form-label small text-muted fw-bold">Upload File Revisi Terbaru (PDF)</label>
                <div class="input-group">
                    <input type="file" name="file_bab<?= $i ?>" class="form-control" accept="application/pdf" required>
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
                            <th width="15%" class="text-center">Pembimbing 1</th>
                            <th width="15%" class="text-center">Pembimbing 2</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Cek apakah tabel progres ada
                        $cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'progres_skripsi'");
                        if (mysqli_num_rows($cek_tabel) > 0) {
                            // Gunakan NPM Tampil (NPM Real) untuk mencari progres
                            $sql = "SELECT * FROM progres_skripsi WHERE npm = ? AND bab = ? ORDER BY created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $npm_tampil, $i);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <a href="../mahasiswa/uploads/<?= $row['file'] ?>" target="_blank" class="text-decoration-none fw-bold">
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
                                    echo "<span class='badge bg-$bg1'>" . ($s1 ?: '-') . "</span>";
                                ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $s2 = $row['nilai_dosen2'];
                                    $bg2 = ($s2 == 'ACC') ? 'success' : (($s2 == 'Revisi') ? 'danger' : 'secondary');
                                    echo "<span class='badge bg-$bg2'>" . ($s2 ?: '-') . "</span>";
                                ?>
                            </td>
                            <td class="text-center">
                                <button class='btn btn-sm btn-info text-white'
                                    onclick='toggleKomentar(<?= $row['id'] ?>,
                                            <?= json_encode($row['komentar_dosen1']) ?>,
                                            <?= json_encode($row['komentar_dosen2']) ?>)'>
                                    💬 Komentar
                                </button>
                            </td>
                        </tr>
                        <tr id="komentar_row_<?= $row['id'] ?>" style="display:none;">
                            <td colspan="5" class="p-0 border-0">
                                <div class="riwayat-komentar">
                                    <div class="row">
                                        <div class="col-md-6 border-end">
                                            <strong class="text-primary">Komentar Pembimbing 1:</strong><br>
                                            <span id="komentar1_<?= $row['id'] ?>" class="d-block mt-1 text-dark"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong class="text-primary">Komentar Pembimbing 2:</strong><br>
                                            <span id="komentar2_<?= $row['id'] ?>" class="d-block mt-1 text-dark"></span>
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
    // Bersihkan URL (hapus ?upload=success)
    if (window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.delete('upload');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.pathname);
    }

    // Toggle Komentar
    function toggleKomentar(id, komentar1, komentar2) {
        const row = document.getElementById("komentar_row_" + id);
        const k1 = document.getElementById("komentar1_" + id);
        const k2 = document.getElementById("komentar2_" + id);

        if (row.style.display === "none") {
            k1.innerHTML = komentar1 ? komentar1 : "<em class='text-muted'>Tidak ada komentar.</em>";
            k2.innerHTML = komentar2 ? komentar2 : "<em class='text-muted'>Tidak ada komentar.</em>";
            row.style.display = "table-row";
        } else {
            row.style.display = "none";
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
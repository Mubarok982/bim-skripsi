<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nama_admin = $_SESSION['admin_username'] ?? 'Admin';
$page = 'syarat_proposal'; // Penanda halaman aktif

// --- QUERY DATA PERSYARATAN SEMPRO ---
$query_sql = "SELECT 
                m.nama, dm.npm, dm.prodi, s.judul,
                us.id AS id_ujian,
                ss.id AS id_syarat,
                ss.naskah, ss.fotokopi_daftar_nilai, ss.fotokopi_krs, ss.buku_kendali_bimbingan, ss.status
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              JOIN skripsi s ON m.id = s.id_mahasiswa
              LEFT JOIN ujian_skripsi us ON s.id = us.id_skripsi 
              LEFT JOIN syarat_sempro ss ON us.id = ss.id_ujian_skripsi 
              WHERE m.role = 'mahasiswa' AND us.id_jenis_ujian_skripsi = 1 /* Asumsi ID 1 adalah Sempro */
              ORDER BY m.nama ASC";

$result = mysqli_query($conn, $query_sql);
if (!$result) { die("Query Error: " . mysqli_error($conn)); }
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Syarat Proposal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="ccsprogres.css"> 
  
  <style>
    /* --- LAYOUT FIXED POSITION --- */
    body { background-color: #f8f9fe; margin: 0; padding: 0; overflow-x: hidden; }
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dee2e6; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .sidebar { position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; z-index: 1040; }
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; background-color: #f8f9fe; }
    
    /* --- TABLE & CARD STYLING --- */
    .card-custom { border: 0; box-shadow: 0 0 1.5rem 0 rgba(136, 152, 170, .1); border-radius: .75rem; background: #fff; }
    .table thead th { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; color: #8898aa; background-color: #f6f9fc; padding: 1rem 0.75rem; border-top: none; }
    .table td { padding: 0.75rem 0.75rem; vertical-align: middle; font-size: 0.85rem; color: #525f7f; border-bottom: 1px solid #f0f0f0; }
    .status-badge { font-weight: 600; padding: 5px 10px; border-radius: 50px; }
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
    $page = 'syarat_proposal'; 
    include "../templates/sidebar_admin.php"; 
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="h2 text-dark d-inline-block mb-0">Validasi Persyaratan Seminar Proposal</h6>
    </div>

    <div class="card card-custom">
        <div class="card-header border-0">
            <h5 class="mb-0">Daftar Pengajuan Sempro</h5>
        </div>

        <div class="table-responsive px-4">
            <table class="table align-items-center table-flush table-hover" id="datatable-syarat">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>NPM / Nama</th>
                        <th>Judul Skripsi</th>
                        <th>Naskah</th>
                        <th>Daftar Nilai</th>
                        <th>KRS</th>
                        <th>Buku Kendali</th>
                        <th>Validasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['npm']) ?><br><small><?= htmlspecialchars($row['nama']) ?></small></td>
                            <td><small><?= htmlspecialchars($row['judul']) ?></small></td>
                            
                            <td>
                                <?php if ($row['naskah']): ?>
                                    <a href="../uploads/syarat/sempro/<?= htmlspecialchars($row['naskah']) ?>" target="_blank" class="text-success small"><i class="fas fa-file-pdf"></i> Lihat</a>
                                <?php else: ?>
                                    <span class="text-muted small">Belum Upload</span>
                                <?php endif; ?>
                            </td>

                            <td><span class="text-muted small"><?= $row['fotokopi_daftar_nilai'] ? 'Ada' : 'Tidak Ada' ?></span></td>
                            <td><span class="text-muted small"><?= $row['fotokopi_krs'] ? 'Ada' : 'Tidak Ada' ?></span></td>
                            <td><span class="text-muted small"><?= $row['buku_kendali_bimbingan'] ? 'Ada' : 'Tidak Ada' ?></span></td>
                            
                            <td>
                                <?php 
                                    $status_overall = $row['status'] ?? 'Menunggu';
                                    $badge_class = match ($status_overall) {
                                        'Diterima' => 'bg-success',
                                        'Revisi' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                ?>
                                <span class="badge <?= $badge_class ?> status-badge"><?= $status_overall ?></span>
                            </td>

                            <td class="text-center">
                                <a href="validate_syarat.php?id_syarat=<?= $row['id_syarat'] ?>&type=sempro" class="btn btn-sm btn-primary" title="Validasi Dokumen">
                                    <i class="fas fa-check"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#datatable-syarat').DataTable({
            dom: 'lfrtip', 
            language: { search: "Cari:", paginate: { previous: "Kembali", next: "Lanjut" } },
            columnDefs: [{ orderable: false, targets: [3, 4, 5, 6, 7, 8] }] // Kolom dokumen dan aksi tidak bisa di-sort
        });
    });
</script>

</body>
</html>
<?php
session_start();
// Sesuaikan path ke db.php
include "db.php";

// Cek session login admin/operator
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

// QUERY BARU: Mengambil TTD dan status Kaprodi
$query_dosen = "SELECT 
                    m.id,
                    m.nama AS nama_akun, 
                    d.nidk, 
                    d.prodi,
                    d.ttd,         /* Kolom Tanda Tangan */
                    d.is_kaprodi,  /* Kolom untuk Badge Kaprodi */
                    m.username     /* Digunakan untuk Aksi/Edit jika perlu */
                FROM mstr_akun m
                JOIN data_dosen d ON m.id = d.id
                WHERE m.role = 'dosen'
                ORDER BY m.nama ASC";

$dosen = mysqli_query($conn, $query_dosen);

// Cek error query
if (!$dosen) {
    die("Query Error: " . mysqli_error($conn));
}

// Data untuk Sidebar/Header
$nama_admin = $_SESSION['admin_username'] ?? 'Operator';
$page = 'data_dosen'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Dosen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="ccsprogres.css">
    <style>
        /* --- LAYOUT KONSISTEN DASHBOARD --- */
        body { background-color: #f8f9fe; display: flex; flex-direction: column; min-height: 100vh; font-family: 'Open Sans', sans-serif; }
        .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; }
        .layout-wrapper { display: flex; flex: 1; margin-top: 70px; }
        .sidebar-area { width: 250px; background: #343a40; color: white; position: fixed; height: 100%; overflow-y: auto; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 30px; width: 100%; background-color: #f8f9fe; }

        /* Card Styling CLEAN */
        .card-custom { border: 0; box-shadow: 0 0 1.5rem 0 rgba(136, 152, 170, .1); border-radius: .75rem; background: #fff; }
        .card-header { background-color: #fff; border-bottom: 1px solid #e9ecef; padding: 1.5rem; border-radius: .75rem .75rem 0 0; }

        /* --- TABLE STYLE (VERY CLEAN) --- */
        .table { --bs-table-bg: none; } /* Hilangkan background default Bootstrap */
        
        .table thead th {
            font-size: 0.65rem; /* Font Header Lebih Kecil */
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #8898aa;
            background-color: #f6f9fc; /* Background Head */
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0.75rem;
            border-top: none;
        }
        
        .table td {
            padding: 0.75rem 0.75rem;
            vertical-align: middle;
            font-size: 0.85rem;
            color: #525f7f;
            border-bottom: 1px solid #f0f0f0; /* Garis pemisah tipis */
        }
        
        /* Hilangkan border samping dan bawah pada body tabel */
        .table tbody { border: none; }
        .table tbody tr:last-child td { border-bottom: none; }

        /* TTD Image Style */
        .ttd-img { max-height: 40px; width: auto; object-fit: contain; }
        
        /* Badge Kaprodi */
        .badge-kaprodi { background-color: #5e72e4; color: white; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 0.6rem; }

        /* --- DATATABLES INTEGRATION FIXES --- */
        #datatable-dosen_wrapper { padding: 0 1rem; }
        
        /* Styling Search Input */
        div.dataTables_filter input { border-radius: 0.5rem; padding: 0.5rem 1rem; border: 1px solid #dee2ee; box-shadow: none; width: 250px; }
        
        /* Hilangkan margin/padding bawaan table-responsive */
        div.dataTables_wrapper div.row:nth-child(1) { 
            margin-bottom: 1rem;
            padding: 0 1rem;
        }
        div.dataTables_wrapper div.row:nth-child(3) {
            padding: 0 1rem;
            border-top: 1px solid #e9ecef;
            margin-top: 1rem;
            padding-top: 1rem;
        }
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
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border"
            style="width: 40px; height: 40px;">👤</div>
    </div>
</div>

<div class="layout-wrapper">
    <div class="sidebar-area">
        <?php include "../templates/sidebar_admin.php"; ?>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="h2 text-dark d-inline-block mb-0">Data Dosen</h6>
            <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links mb-0" style="background: none;">
                    <li class="breadcrumb-item"><a href="#">Pages</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dosen</li>
                </ol>
            </nav>
        </div>

        <div class="card card-custom">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col-md-12 d-flex flex-wrap gap-2">
                        <a href="tambah_dosen.php" class="btn btn-primary shadow-sm py-2 px-3">
                            <i class="fas fa-plus me-1"></i> Tambah Dosen
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="px-4"> 
                <table class="table align-items-center table-flush" id="datatable-dosen">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>NIDK/NIP</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>Tanda Tangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($dosen)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                
                                <td class="fw-bold"><?= htmlspecialchars($row['nidk']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['nama_akun']) ?>
                                    <?php if ($row['is_kaprodi']): ?>
                                        <span class="badge-kaprodi ms-1">KAPRODI</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['prodi']) ?></td>
                                
                                <td>
                                    <?php if ($row['ttd']): ?>
                                        <img src="../ttd/<?= htmlspecialchars($row['ttd']) ?>" alt="TTD" class="ttd-img" title="Tanda Tangan Tersedia">
                                    <?php else: ?>
                                        <span class="text-muted small">Belum Ada</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="edit_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="hapus_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" 
                                           onclick="return confirm('Yakin ingin menghapus dosen <?= $row['nama_akun'] ?>? Akun juga akan terhapus.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#datatable-dosen').DataTable({
            // Konfigurasi Dom untuk tampilan minimalis (Hanya length, filter, table, info, paginate)
            dom: 'lfrtip', 
            
            language: {
                search: "Cari:",
                paginate: { previous: "Kembali", next: "Lanjut" },
                lengthMenu: "Tampilkan _MENU_ data"
            },
            columnDefs: [
                { orderable: false, targets: 4 }, // TTD
                { orderable: false, targets: 5 }  // Aksi
            ]
        });
        
        // Custom styling untuk mengintegrasikan DataTables ke Card
        // Memindahkan search dan length di dalam Card
        $('#datatable-dosen_filter').parent().addClass('order-2');
        $('#datatable-dosen_length').parent().addClass('order-1');
    });
</script>

</body>
</html>
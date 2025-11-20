<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page = 'data_mahasiswa';
$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- QUERY DATA MAHASISWA LENGKAP ---
$query_mhs = "SELECT 
                m.id,
                m.nama,
                dm.npm, 
                dm.angkatan,
                dm.prodi,
                dm.telepon,
                dm.email,
                dm.status_mahasiswa,
                dm.status_beasiswa,
                dm.jenis_kelamin
              FROM mstr_akun m
              JOIN data_mahasiswa dm ON m.id = dm.id
              WHERE m.role = 'mahasiswa'
              ORDER BY dm.angkatan DESC, m.nama ASC";

$result = mysqli_query($conn, $query_mhs);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Data Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    body { background-color: #f8f9fe; display: flex; flex-direction: column; min-height: 100vh; }

    /* Header & Sidebar Layout */
    .header { position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: #fff; z-index: 1050; padding: 0 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; }
    .layout-wrapper { display: flex; flex: 1; margin-top: 70px; }
    .sidebar-area { width: 250px; background: #343a40; color: white; position: fixed; height: 100%; overflow-y: auto; z-index: 1000; }
    .main-content { margin-left: 250px; padding: 30px; width: 100%; background-color: #f8f9fe; }

    /* Card Styling */
    .card-custom { background: #fff; border: none; border-radius: 0.375rem; box-shadow: 0 0 1rem 0 rgba(136, 152, 170, .1); } /* Shadow dikurangi */
    .card-header { background-color: #fff; border-bottom: 1px solid #e9ecef; padding: 1.5rem; }

    /* Table Styling Minimalis */
    .table thead th {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
      color: #8898aa;
      background-color: #f6f9fc;
      border-bottom: 1px solid #e9ecef;
      padding: 1rem 0.5rem; /* Padding lebih kecil */
    }

    .table td {
      padding: 0.8rem 0.5rem;
      vertical-align: middle;
      font-size: 0.875rem;
      color: #525f7f;
    }

    /* Dropdown Action Style */
    .btn-plus-action {
        width: 30px; height: 30px; 
        border-radius: 50%; 
        background: #5e72e4; 
        color: white; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 4px rgba(50,50,93,.1);
        transition: all 0.2s ease;
    }
    .btn-plus-action:hover { background-color: #324cdd; }
    .dropdown-menu { border: 0; box-shadow: 0 0 1rem rgba(0,0,0,.15); }
    .dropdown-item { font-size: 0.85rem; }
    .dropdown-toggle::after { content: none; } /* Hilangkan panah default */
    
    /* Tombol Utama */
    .btn-primary-custom { background-color: #5e72e4; border-color: #5e72e4; color: white; }
    .btn-success-custom { background-color: #2dce89; border-color: #2dce89; color: white; }

    /* Sembunyikan Tombol DataTables */
    div.dt-buttons { display: none !important; }
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
        style="width: 40px; height: 40px;">
        👤
      </div>
    </div>
  </div>

  <div class="layout-wrapper">
    <div class="sidebar-area">
      <?php include "../templates/sidebar_admin.php"; ?>
    </div>

    <div class="main-content">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="h2 text-dark d-inline-block mb-0">Data Mahasiswa</h6>
      </div>

      <div class="card card-custom">
        <div class="card-header border-0">
          <div class="row align-items-center">
            <div class="col-md-12 d-flex flex-wrap gap-2">
              <a href="tambah_mahasiswa.php" class="btn btn-primary-custom shadow-sm py-2 px-3">
                <i class="fas fa-plus me-1"></i> Tambah Mahasiswa
              </a>

              <form action="proses_import.php" method="POST" enctype="multipart/form-data" class="d-flex gap-1 align-items-center">
                <input type="file" name="file_excel" class="form-control form-control-sm" style="width: 200px;"
                  required accept=".csv">
                <button type="submit" name="import" class="btn btn-success-custom btn-sm shadow-sm py-2 px-3">
                    <i class="fas fa-file-csv me-1"></i> Import CSV
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="table-responsive py-4 px-4">
          <table class="table align-items-center table-flush table-hover" id="datatable-basic">
            <thead>
              <tr>
                <th class="text-center" width="5%">No</th>
                <th class="text-center" width="5%">Aksi</th>
                <th>NPM</th>
                <th>Nama</th>
                <th>Angkatan</th>
                <th>Prodi</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  
                  <td class="text-center">
                    <div class="dropdown">
                      <button class="btn-plus-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                        <i class="fas fa-plus"></i>
                      </button>
                      
                      <div class="dropdown-menu dropdown-menu-end shadow-lg">
                        <h6 class="dropdown-header text-muted small text-uppercase">Kelola Data</h6>
                        
                        <a class="dropdown-item" href="edit_mahasiswa.php?id=<?= $row['id'] ?>">
                            <i class="fas fa-pen text-warning me-2"></i> Edit Data
                        </a>

                        <div class="dropdown-divider my-1"></div>

                        <a class="dropdown-item text-danger" href="hapus_mahasiswa.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin menghapus data ini?')">
                            <i class="fas fa-trash text-danger me-2"></i> Hapus Data
                        </a>
                      </div>
                    </div>
                  </td>

                  <td class="fw-bold"><?= htmlspecialchars($row['npm']) ?></td>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['angkatan']) ?></td>
                  <td><?= htmlspecialchars($row['prodi']) ?></td>
                  <td><?= htmlspecialchars($row['telepon'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                  <td>
                    <span class="badge bg-secondary"><?= htmlspecialchars($row['status_mahasiswa']) ?></span>
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
      $('#datatable-basic').DataTable({
        // Hapus semua konfigurasi tombol Export (Bfrtip)
        dom: 'frtip', 
        
        // Atur Pagination
        "pageLength": 10,
        
        language: {
          search: "Cari Data:",
          paginate: { previous: "Kembali", next: "Lanjut" }
        },
        columnDefs: [
          { orderable: false, targets: 1 }
        ]
      });
    });
  </script>

</body>
</html>
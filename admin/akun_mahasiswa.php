<?php
session_start();
// Sesuaikan path db.php
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<?php 
    $page = 'akun_mahasiswa'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php"; 


$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- PERBAIKAN QUERY ---
// Mengambil NPM dari tabel data_mahasiswa
$query_sql = "SELECT 
                m.id,
                m.nama,
                m.password,
                m.username,  -- Tetap ambil username sebagai cadangan
                dm.npm       -- INI YANG UTAMA (Dari tabel data_mahasiswa)
              FROM mstr_akun m
              LEFT JOIN data_mahasiswa dm ON m.id = dm.id
              WHERE m.role = 'mahasiswa' 
              ORDER BY m.nama ASC";

$akun = mysqli_query($conn, $query_sql);

if (!$akun) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Akun Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <style>
    /* --- LAYOUT FIXED (Sama dengan Home & Data Mahasiswa) --- */
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
    }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; padding-left: 30px; }
    
    /* Content */
    .main-content { margin-top: 70px; margin-left: 250px; padding: 30px; width: auto; }
    
    .search-container input { width: 300px; display: inline-block; }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <img src="unimma.png" alt="Logo" style="height: 50px;">
    <h4 class="m-0 d-none d-md-block">MONITORING SKRIPSI</h4>
  </div>
  <div class="profile d-flex align-items-center gap-2">
    <div class="text-end d-none d-md-block" style="line-height: 1.2;">
        <small class="text-muted" style="display:block; font-size: 11px;">Login Sebagai</small>
        <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($nama_admin) ?></span>
    </div>
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef;
                display: flex; align-items: center; justify-content: center; border: 1px solid #ced4da; font-size: 20px;">
        👤
    </div>
  </div>
</div>


<div class="main-content">
    <div class="card p-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="m-0">Daftar Akun Mahasiswa</h3>
          <div class="search-container">
             <input type="text" id="searchInput" class="form-control" placeholder="🔍 Cari Nama / NPM...">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-bordered table-striped" id="akunTable">
            <thead class="table-dark">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">NPM</th> <th width="25%">Nama Lengkap</th>
                <th width="25%">Password (Hash)</th>
                <th width="20%" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = mysqli_fetch_assoc($akun)): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                
                <td class="fw-bold">
                    <?php 
                        if(!empty($row['npm'])) {
                            echo htmlspecialchars($row['npm']);
                        } else {
                            // Fallback ke username jika data npm belum sinkron
                            echo htmlspecialchars($row['username']);
                        }
                    ?>
                </td>
                
                <td><?= htmlspecialchars($row['nama']) ?></td>
                
                <td style="font-size: 11px; word-break: break-all; color: #666;">
                    <?= substr($row['password'], 0, 20) ?>...
                </td>
                
                <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="reset_password_mahasiswa.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Reset password Mahasiswa ini menjadi 123?')">🔁 Reset</a>
                      <a href="hapus_akun_mahasiswa.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus akun ini?')">🗑️ Hapus</a>
                    </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById("searchInput");
    input.addEventListener("keyup", function () {
      const filter = input.value.toLowerCase();
      const rows = document.querySelectorAll("#akunTable tbody tr");
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    });
</script>

</body>
</html>
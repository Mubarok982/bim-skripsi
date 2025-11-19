<?php
session_start();
// Sesuaikan path ke db.php
include "db.php";

// Cek session login admin/operator
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

// QUERY BARU: JOIN mstr_akun dengan data_dosen
$query_dosen = "SELECT 
                    m.id,
                    m.nama, 
                    d.nidk, 
                    d.prodi 
                FROM mstr_akun m
                JOIN data_dosen d ON m.id = d.id
                WHERE m.role = 'dosen'
                ORDER BY m.nama ASC";

$dosen = mysqli_query($conn, $query_dosen);

// Cek error query
if (!$dosen) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ccsprogres.css">
    <style>
        .search-box { width: 300px; margin-bottom: 20px; }
    </style>
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
        <div style="width: 50px; height: 50px; border-radius: 50%; background: #eee;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 25px;">👤</div>
    </div>
</div>

<div class="container-fluid">
    <div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    <a href="home_admin.php">Dashboard</a>
    <a href="laporan_sidang.php">Laporan Sidang</a>
    <a href="data_mahasiswa.php">Data Mahasiswa</a>
    <a href="data_dosen.php">Data Dosen</a>
    
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_dosen.php" class="active" style="background-color: #0d6efd;">Akun Dosen</a>
    <a href="mahasiswa_skripsi.php">Data Skripsi</a>
    
    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">Logout</a> 
    
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">
      &copy; 2025 UNIMMA
    </div>
</div>

    <div class="main-content">
        <div class="card-box w-100 text-start">
            <h3>Data Dosen</h3>
            <a href="tambah_dosen.php" class="btn btn-success mb-3">+ Tambah Dosen</a>
            <input type="text" id="search" class="form-control search-box" placeholder="🔍Cari nama / NIDK dosen...">

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dosenTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIDK/NIP</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($dosen)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nidk']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['prodi']) ?></td>
                                <td>
                                    <a href="edit_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                    <a href="hapus_dosen.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById("search");
    searchInput.addEventListener("keyup", function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll("#dosenTable tbody tr");
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>

</body>
</html>
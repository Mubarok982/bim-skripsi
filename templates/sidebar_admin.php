<?php
// Ambil variabel $page, jika tidak ada set kosong biar gak error
$p = isset($page) ? $page : ''; 
?>

<div class="sidebar">
    <h4 class="text-center mb-4">Panel Admin</h4>
    
    <a href="home_admin.php" class="<?= $p == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    
    <a href="laporan_sidang.php" class="<?= $p == 'laporan' ? 'active' : '' ?>">Laporan Sidang</a>
    
    <a href="data_mahasiswa.php" class="<?= $p == 'mahasiswa' ? 'active' : '' ?>">Data Mahasiswa</a>
    
    <a href="data_dosen.php" class="<?= $p == 'dosen' ? 'active' : '' ?>">Data Dosen</a>
    
    <h6 class="text-uppercase text-secondary ms-3 mb-2 mt-3" style="font-size: 11px;">Akun</h6>
    
    <a href="akun_mahasiswa.php" class="<?= $p == 'akun_mhs' ? 'active' : '' ?>">Akun Mahasiswa</a>
    
    <a href="akun_dosen.php" class="<?= $p == 'akun_dosen' ? 'active' : '' ?>">Akun Dosen</a>
    
    <a href="mahasiswa_skripsi.php" class="<?= $p == 'skripsi' ? 'active' : '' ?>">Data Skripsi</a>
    
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">
        &copy; ikhbal.khasodiq18@gmail.com
    </div>
</div>
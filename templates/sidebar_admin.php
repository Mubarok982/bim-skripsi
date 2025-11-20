<?php
// Cek halaman aktif
$p = isset($page) ? $page : ''; 
?>
<div class="sidebar">
    <h6 class="text-uppercase text-secondary ms-3 mb-3" style="font-size: 12px;">Menu Utama</h6>
    
    <a href="home_admin.php" class="<?= $p == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="mahasiswa_skripsi.php" class="<?= $p == 'skripsi' ? 'active' : '' ?>">Monitoring Skripsi</a>
    <a href="laporan_sidang.php" class="<?= $p == 'laporan' ? 'active' : '' ?>">Laporan Sidang</a>
    <a href="data_mahasiswa.php" class="<?= $p == 'mahasiswa' ? 'active' : '' ?>">Data Mahasiswa</a>
    <a href="data_dosen.php" class="<?= $p == 'dosen' ? 'active' : '' ?>">Data Dosen</a>
    
    <h6 class="text-uppercase text-secondary ms-3 mb-3 mt-4" style="font-size: 12px;">Manajemen Akun</h6>
    <a href="akun_mahasiswa.php" class="<?= $p == 'akun_mhs' ? 'active' : '' ?>">Akun Mahasiswa</a>
    <a href="akun_dosen.php" class="<?= $p == 'akun_dosen' ? 'active' : '' ?>">Akun Dosen</a>
    <a href="analisa_kinerja.php" class="<?= $p == 'analisa' ? 'active' : '' ?>">Analisa Kinerja</a>
    
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a> 
    <div class="text-center mt-5 text-muted" style="font-size: 11px;">&copy; 2025 UNIMMA</div>
</div>
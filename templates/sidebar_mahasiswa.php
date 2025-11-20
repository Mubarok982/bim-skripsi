<?php
$p = isset($page) ? $page : ''; 
?>
<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="<?= $p == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="progres_skripsi.php" class="<?= $p == 'progres' ? 'active' : '' ?>">Upload Progres</a>
    
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">
        &copy; ikhbal.khasodiq18@gmail.com
    </div>
</div>
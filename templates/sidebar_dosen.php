<?php
$p = isset($page) ? $page : ''; 
?>
<div class="sidebar">
    <h4 class="text-center mb-4">Panel Dosen</h4>
    
    <a href="home_dosen.php" class="<?= $p == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="biodata_dosen.php" class="<?= $p == 'profil' ? 'active' : '' ?>">Profil Saya</a>
    
    <a href="../auth/logout.php" class="text-danger mt-4 border-top pt-3">Logout</a>
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">&copy; 2025 UNIMMA</div>
</div>
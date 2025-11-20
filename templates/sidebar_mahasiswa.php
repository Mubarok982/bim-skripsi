<?php
// templates/sidebar_mahasiswa.php
// File ini akan di-include dan memerlukan fungsi is_active() dari functions.php

if (!function_exists('is_active')) {
    function is_active($target_page, $current_page) {
        if (is_array($target_page)) {
            return in_array($current_page, $target_page) ? 'active' : '';
        }
        return ($current_page === $target_page) ? 'active' : '';
    }
}
?>
<div class="sidebar">
    <h4 class="text-center mb-4">Panel Mahasiswa</h4>
    
    <a href="home_mahasiswa.php" class="<?= is_active(['home_mahasiswa.php', 'index.php'], $current_page) ?>">
        Dashboard
    </a>
    <a href="progres_skripsi.php" class="<?= is_active('progres_skripsi.php', $current_page) ?>">
        Upload Progres
    </a>
    
    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Kelola Tugas Akhir</h6>
    <a href="skripsi.php" class="<?= is_active('skripsi.php', $current_page) ?>">
        Pengajuan Tugas Akhir
    </a>
    <a href="ujian.php" class="<?= is_active('ujian.php', $current_page) ?>">
        Ujian Tugas Akhir
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Persyaratan</h6>
    <a href="syarat_sempro.php" class="<?= is_active('syarat_sempro.php', $current_page) ?>">
        Syarat Proposal
    </a>
    <a href="syarat_sidang.php" class="<?= is_active('syarat_sidang.php', $current_page) ?>">
        Syarat Pendadaran
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">Pengaturan</h6>
    <a href="profile.php" class="<?= is_active('profile.php', $current_page) ?>">
        Profile
    </a>

    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">
        Logout
    </a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">&copy; 2025 UNIMMA</div>
</div>
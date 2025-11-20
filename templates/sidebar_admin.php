<?php
// Cek halaman aktif
$p = isset($page) ? $page : ''; 
?>
<div class="sidebar">
    
    <div class="ms-3 mb-4 mt-3 text-white fw-bold" style="font-size: 1.1rem;">
        <i class="fas fa-bars me-2"></i> MENU
    </div>

    <h6 class="text-white text-uppercase ms-3 mb-2" style="font-size: 11px; opacity: 0.8;">MENU</h6>
    
    <a href="home_admin.php" class="<?= $p == 'home_admin' ? 'active' : '' ?>">
        <i class="fas fa-th-large fa-fw me-2"></i> Dashboard
    </a>
    
    <a href="mahasiswa_skripsi.php" class="<?= $p == 'jadwal' ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> Jadwal
    </a>
    
    <h6 class="text-white text-uppercase ms-3 mb-2 mt-4" style="font-size: 11px; opacity: 0.8;">KELOLA AKUN</h6>
    
    <a href="data_mahasiswa.php" class="<?= $p == 'data_mahasiswa' ? 'active' : '' ?>">
        <i class="fas fa-user fa-fw me-2"></i> Mahasiswa
    </a>
    
    <a href="data_dosen.php" class="<?= $p == 'data_dosen' ? 'active' : '' ?>">
        <i class="fas fa-user-tie fa-fw me-2"></i> Dosen
    </a>
    
    <a href="akun_operator.php" class="<?= $p == 'tata_usaha' ? 'active' : '' ?>">
        <i class="fas fa-user-cog fa-fw me-2"></i> Tata Usaha
    </a>
    
    <h6 class="text-white text-uppercase ms-3 mb-2 mt-4" style="font-size: 11px; opacity: 0.8;">PENGAJUAN</h6>
    
    <a href="#" class="<?= $p == 'status_semua' ? 'active' : '' ?>">
        <i class="fas fa-clipboard fa-fw me-2"></i> Status TA Semua
    </a>
    
    <a href="#" class="<?= $p == 'status_proposal' ? 'active' : '' ?>">
        <i class="fas fa-clipboard-list fa-fw me-2"></i> Status TA Proposal
    </a>
    
    <a href="#" class="<?= $p == 'status_pendadaran' ? 'active' : '' ?>">
        <i class="fas fa-clipboard-check fa-fw me-2"></i> Status TA Pendadaran
    </a>
    
    <h6 class="text-white text-uppercase ms-3 mb-2 mt-4" style="font-size: 11px; opacity: 0.8;">PERSYARATAN</h6>
    
    <a href="syarat_proposal.php" class="<?= $p == 'syarat_proposal' ? 'active' : '' ?>">
        <i class="fas fa-file-contract fa-fw me-2"></i> Syarat Proposal
    </a>

    <a href="syarat_pendadaran.php" class="<?= $p == 'syarat_pendadaran' ? 'active' : '' ?>">
        <i class="fas fa-file-signature fa-fw me-2"></i> Syarat Pendadaran
    </a>
    
    <h6 class="text-white text-uppercase ms-3 mb-2 mt-4" style="font-size: 11px; opacity: 0.8;">PENILAIAN</h6>
    
    <a href="nilai_proposal.php" class="<?= $p == 'seminar_proposal' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice fa-fw me-2"></i> Seminar Proposal
    </a>

    <a href="nilai_pendadaran.php" class="<?= $p == 'sidang_pendadaran' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice-dollar fa-fw me-2"></i> Sidang Pendadaran
    </a>

    <h6 class="text-white text-uppercase ms-3 mb-2 mt-4" style="font-size: 11px; opacity: 0.8;">PENGATURAN</h6>
    
    <a href="jenis_ujian.php" class="<?= $p == 'jenis_ujian' ? 'active' : '' ?>">
        <i class="fas fa-cogs fa-fw me-2"></i> Jenis Ujian
    </a>

    <a href="komponen_nilai.php" class="<?= $p == 'komponen_nilai' ? 'active' : '' ?>">
        <i class="fas fa-percent fa-fw me-2"></i> Komponen Nilai
    </a>
    
    <a href="profile.php" class="<?= $p == 'profile' ? 'active' : '' ?>">
        <i class="fas fa-user-circle fa-fw me-2"></i> Profile
    </a>
    
    <a href="../auth/logout.php" class="text-danger mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
        <i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout
    </a> 
    
    <div class="text-center mt-5 text-light" style="font-size: 11px; opacity: 0.6;">&copy; 2025 UNIMMA</div>
</div>
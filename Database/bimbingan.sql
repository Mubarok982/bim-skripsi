-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 08:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bimbingan`
--

-- --------------------------------------------------------

--
-- Table structure for table `apresiasi_ujian_skripsi`
--

CREATE TABLE `apresiasi_ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `id_penguji` bigint(20) UNSIGNED NOT NULL,
  `apresiasi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_dosen`
--

CREATE TABLE `data_dosen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nidk` varchar(20) NOT NULL,
  `prodi` enum('Teknik Informatika S1','Teknologi Informasi D3','Teknik Industri S1','Teknik Mesin S1','Mesin Otomotif D3') NOT NULL,
  `ttd` text DEFAULT NULL,
  `is_kaprodi` tinyint(1) NOT NULL DEFAULT 0,
  `is_praktisi` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_dosen`
--

INSERT INTO `data_dosen` (`id`, `nidk`, `prodi`, `ttd`, `is_kaprodi`, `is_praktisi`) VALUES
(3, '057508191', 'Teknologi Informasi D3', '76fd6ae79094c3525832e32681c68162.jpg', 0, 0),
(4, '027108182', 'Teknologi Informasi D3', '22ed2700bb17d49047fda1e850f4b0d2.png', 1, 0),
(5, '107906052', 'Teknologi Informasi D3', '85c81f05c1f3efe09071570d1a01a874.png', 0, 0),
(6, '107806051', 'Teknologi Informasi D3', '306cba0ccd7ffacf4448c71067006036.png', 0, 0),
(7, '107306024', 'Teknologi Informasi D3', '3b22ab010c9b6a6d3c042b121362f75e.png', 0, 0),
(8, '987008138', 'Teknik Informatika S1', '935af1f3659876512c64e8c7022f37a0.png', 0, 0),
(9, '057108188', 'Teknik Informatika S1', '764dad0ebdfc7efd218558c94b30bedf.png', 0, 0),
(10, '067206024', 'Teknik Informatika S1', '97defb35d2be7c7beb30bd6e27146997.png', 0, 0),
(11, '139006116', 'Teknik Informatika S1', '49878337d100e9f05cc62984a2d93c0d.png', 0, 0),
(12, '158808135', 'Teknik Informatika S1', '596e5d30d71ec9ff4f19af56558324d6.png', 0, 0),
(13, '158108139', 'Teknik Informatika S1', '9d545b4608a106b30a425095ba38cabd.png', 0, 0),
(14, '168508156', 'Teknik Informatika S1', '6d70c8fa2be7350042f30365685024a8.png', 0, 0),
(15, '168208163', 'Teknik Informatika S1', 'bc02ddcafb224d7780b201f345550209.png', 1, 0),
(16, '188508188', 'Teknik Informatika S1', '80fe614d55d086ff1a052fb55b57b5ee.png', 0, 0),
(17, '187708189', 'Teknik Informatika S1', '63241824e32d31b2b269cfc35b375369.png', 0, 0),
(18, '199208245', 'Teknik Informatika S1', '2637a992681eb93c8483b384833bf849.png', 0, 0),
(19, '219108337', 'Teknik Informatika S1', '6f267ec3a76eb80d10fec72de91c931d.png', 0, 0),
(9003, '12345678', 'Teknik Informatika S1', 'ttd_9003_1763569882.png', 0, 0),
(9005, 'dosen2', 'Teknik Informatika S1', 'ttd_9005_1763575493.jpg', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `data_mahasiswa`
--

CREATE TABLE `data_mahasiswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `npm` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `angkatan` year(4) NOT NULL,
  `prodi` enum('Teknik Informatika S1','Teknologi Informasi D3','Teknik Industri S1','Teknik Mesin S1','Mesin Otomotif D3') NOT NULL,
  `is_skripsi` tinyint(1) DEFAULT 0,
  `alamat` text DEFAULT NULL,
  `status_beasiswa` enum('Aktif','Tidak Aktif') NOT NULL,
  `status_mahasiswa` enum('Murni','Konversi','Transfer') NOT NULL,
  `ttd` text NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_tgl_lahir` varchar(100) DEFAULT NULL,
  `nama_ortu_dengan_gelar` varchar(255) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `dokumen_identitas` varchar(255) NOT NULL,
  `sertifikat_toefl_niit` varchar(255) DEFAULT NULL,
  `sertifikat_office_puskom` varchar(255) NOT NULL,
  `sertifikat_btq_ibadah` varchar(255) NOT NULL,
  `sertifikat_bahasa` varchar(255) NOT NULL,
  `sertifikat_kompetensi_ujian_komprehensif` varchar(255) NOT NULL,
  `sertifikat_semaba_ppk_masta` varchar(255) NOT NULL,
  `sertifikat_kkn` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_mahasiswa`
--

INSERT INTO `data_mahasiswa` (`id`, `npm`, `jenis_kelamin`, `email`, `telepon`, `angkatan`, `prodi`, `is_skripsi`, `alamat`, `status_beasiswa`, `status_mahasiswa`, `ttd`, `nik`, `tempat_tgl_lahir`, `nama_ortu_dengan_gelar`, `kelas`, `dokumen_identitas`, `sertifikat_toefl_niit`, `sertifikat_office_puskom`, `sertifikat_btq_ibadah`, `sertifikat_bahasa`, `sertifikat_kompetensi_ujian_komprehensif`, `sertifikat_semaba_ppk_masta`, `sertifikat_kkn`) VALUES
(9004, '123', NULL, NULL, '081391005220', '2024', 'Teknik Informatika S1', 0, NULL, 'Tidak Aktif', 'Murni', 'dummy_ttd.png', NULL, NULL, NULL, NULL, 'dummy_doc.pdf', NULL, 'dummy_cert.pdf', 'dummy_cert.pdf', 'dummy_cert.pdf', 'dummy_cert.pdf', 'dummy_cert.pdf', 'dummy_cert.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_ujian_skripsi`
--

CREATE TABLE `jenis_ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `rumus` enum('informatika sempro','informatika sidang','non informatika','informatika 2025') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_ujian_skripsi`
--

INSERT INTO `jenis_ujian_skripsi` (`id`, `nama`, `rumus`) VALUES
(1, 'Seminar Proposal Teknik Lama', 'informatika sempro'),
(2, 'Seminar Pendadaran Teknik Lama', 'informatika sidang'),
(3, 'Seminar Proposal Teknik Industri', 'non informatika'),
(4, 'Seminar Pendadaran Teknik Industri', 'non informatika'),
(5, 'Seminar Proposal Teknik Informatika 2025', 'informatika 2025'),
(6, 'Seminar Pendadaran Teknik Informatika 2025', 'informatika 2025'),
(7, 'Seminar Proposal Teknologi Informasi D3', 'informatika 2025'),
(8, 'Seminar Pendadaran Teknologi Informasi D3', 'informatika 2025');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa_skripsi`
--

CREATE TABLE `mahasiswa_skripsi` (
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `prodi` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `periode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa_skripsi`
--

INSERT INTO `mahasiswa_skripsi` (`npm`, `nama`, `prodi`, `semester`, `periode`) VALUES
('123', 'Mahasiswa Dummy', 'Teknik Informatika', 7, '2025/2026 (Genap)');

-- --------------------------------------------------------

--
-- Table structure for table `mstr_akun`
--

CREATE TABLE `mstr_akun` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `nama` varchar(100) NOT NULL,
  `foto` text DEFAULT NULL,
  `role` enum('dosen','mahasiswa','operator','tata_usaha') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mstr_akun`
--

INSERT INTO `mstr_akun` (`id`, `username`, `password`, `nama`, `foto`, `role`) VALUES
(1, 'admin', '$2y$10$ulZz10dOrybrWYEG6henjesE70uWs3l.BDTpoB3jimbZEmQcMXSoO', 'Operator FT', '9d1a523eeda16bd038923cab38fc0c70.png', 'operator'),
(2, 'tatausahaft', '$2y$10$ulZz10dOrybrWYEG6henjesE70uWs3l.BDTpoB3jimbZEmQcMXSoO', 'Tata Usaha FT', NULL, 'tata_usaha'),
(3, '0602047502', '$2y$10$/zQ7QgWuDxEuJP9yXNPz7eH58otjA3BW3I2Shd5TL9H0bgdKhicPu', 'Mukhtar Hanafi, ST., MCs.', '0ac2ec8183d84c2880f3cc0896ad1abc.JPG', 'dosen'),
(4, '0616127102', '$2y$10$6V6jrZa26PuYXS2jWa19EuradXrZc5DnalE6evJCA.bX0BxfBnNUq', 'R. Arri Widyanto, S.Kom.,MT', 'bd10699e06a0baf6d58bb681be27248d.JPG', 'dosen'),
(5, '0623087901', '$2y$10$6udEasoJ4Cmv0qbBwi5skueggdGS.rrSIvx4ALslFWMw41pcqrMbO', 'Andi Widiyanto, M.Kom', 'f7c9a5c3a7678e1ac07a8e94405d7606.JPG', 'dosen'),
(6, '0623107802', '$2y$10$Jz9Bt9XeFz0eR54eC2OC7uhNanUF9epzRUcEZJAOGkfEpr3ih/3dq', 'Bambang Pujiarto, M.Kom', 'e83601a799be5dbb5b55af9605e8d551.JPG', 'dosen'),
(7, '0624077302', '$2y$10$nka/8mzzBv45hDq14bhq1uZl3WLG3gYAOaB9FtTT.itJpY4pOggE.', 'Nugroho Agung Prabowo, M.Kom., Ph.D', '4db64be85fa33ff0511a7389d595daf8.JPG', 'dosen'),
(8, '0605037002', '$2y$10$C3W08YomI/9pgHBvfEKizuz2az.xtPgtItVbozL9LuytXFOZabFq2', 'Nuryanto, ST., M. Kom', 'f308d09c239581637fe3b861c2c91d9d.JPG', 'dosen'),
(9, '0624077101', '$2y$10$9q47XRi/Fw6CppwKLhOq6OWFlWRZl8sLDzv32WvqhnbAwQevjw3UG', 'Purwono Hendradi, M.Kom., Ph.D', '7345bdef042d66404e8cc96c4f957581.JPG', 'dosen'),
(10, '0605107201', '$2y$10$LwEQfOD6XqOyY9ru5xPWZeTxzXDYeG8NklMII9JiGsPA14HJhheU.', 'Dr. Uky Yudatama, S.Si.,M.Kom., M.M.', '52a163938d7eab4d1e7100f9da5f324a.JPG', 'dosen'),
(11, '0601129001', '$2y$10$54UghxtXFYdNGBRUXmz/Qu9LJ.fpf.kgNEVeqGuSoZPEjhSHDLbOi', 'Endah Ratna Arumi, M. Cs.', '03cc0d6a365688b541d3c3a48d916fc1.JPG', 'dosen'),
(12, '0617088801', '$2y$10$dotVs8atHlu/0tYrpNvav.mALStuF9M3Ss5lz38wk8YbPE9EPf032', 'Agus Setiawan, M.Eng.', '952f061697f17ae5ad90ccd13c378c7d.JPG', 'dosen'),
(13, '0512128101', '$2y$10$e26kRA97YMg69/SJYrdgOeqE550UjVv7mJYp21bznhdnmrg/WCK7q', 'Emilya Ully Artha, M.Kom.', '7b5e103e1e60fd28a1d5a352e96b393e.JPG', 'dosen'),
(14, '0619048501', '$2y$10$paQuGQRtnv1h2wjTA.UEm.7OfIpHbEXlzJk22VZEWFPdGA9V1z6wK', 'Ardhin Primadewi, S.Si, M.TI.', '02b2b610202babfe1a7c1deb3f483ad0.JPG', 'dosen'),
(15, '0631088203', '$2y$10$9jIcrIboHUhnm.eP/H490e2puHKqLwW3cqy0zfAnBdZX7z3CSWN9C', 'Setiya Nugroho, ST., M.Eng.', '637a5e5aafcfd08bd5c8a68819da332c.JPG', 'dosen'),
(16, '0602058502', '$2y$10$/EHCjpyqmT7QeY/1bvaFXe5B69je2tHihyqn4Xp3w7BmbCMCl07Fm', 'Dimas Sasongko, S. Kom., M. Eng', 'e54a12ea5349fbc20c176f1515b44e4f.JPG', 'dosen'),
(17, '0612117702', '$2y$10$XsS5v74UlxjHyby7toOWqOjEK/Xzc1EdGS.2ZBulayP7lyR8dVRRa', 'Maimunah, S. Si., M. Kom', '43972ba885d631f3bd6c9bcd31565846.JPG', 'dosen'),
(18, '0618129201', '$2y$10$6MiM9I4uYacfsmGLAj1VNeONmAG4cVjvJ3/jbFZE3oGDzGydZZU.G', 'Pristi Sukmasetya, S.Komp., M.Kom', 'a01222aca189c5939dbdc5ddb406e2be.JPG', 'dosen'),
(19, '0631079101', '$2y$10$tguDAdnaxppdo.JN7UajROAy9qNuz/G.MK1gWZCnnisIMCxaYqL1q', 'Rofi Abul Hasani, S.Kom., M.Eng', 'cbd4483a91f02f901d9a8051ea01f1bf.JPG', 'dosen'),
(9001, 'admin_test', '$2y$10$Z9VCUi1tSsZtMAL0.P0Iju1m89gQasvoVR3byKIZ63cWTIn98tA72', 'Operator Dummy', 'default.png', 'operator'),
(9002, 'tu_test', '$2y$10$Z9VCUi1tSsZtMAL0.P0Iju1m89gQasvoVR3byKIZ63cWTIn98tA72', 'Staff TU Dummy', 'default.png', 'tata_usaha'),
(9003, 'dosen_test', '$2y$10$Z9VCUi1tSsZtMAL0.P0Iju1m89gQasvoVR3byKIZ63cWTIn98tA72', 'Dosen Dummy', 'dosen_9003_1763569882.jpeg', 'dosen'),
(9004, 'rizqy', '$2y$10$F49w9/SOtciUu3X/6bs6dOAx.lyFOwsUnAguXemTvxQ.2DF4sttHW', 'Mahasiswa Dummy', '123_1763570025.jpeg', 'mahasiswa'),
(9005, 'dosen2', '$2y$10$ACGWvWH5gHzJKvR3VPzPc.i4.k7LhFwNFL2EG329TpDJz4nDHQt6q', 'Dosen Baru, M.Kom', 'dosen_9005_1763575493.png', 'dosen');

-- --------------------------------------------------------

--
-- Table structure for table `mstr_komponen_nilai_ujian_skripsi`
--

CREATE TABLE `mstr_komponen_nilai_ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `keterangan` text NOT NULL,
  `keterangan_berita_acara` text DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `bobot` float NOT NULL,
  `bobot_berita_acara` float DEFAULT NULL,
  `jenis_nilai` enum('naskah','presentasi') NOT NULL,
  `status` enum('aktif','tidak aktif') NOT NULL,
  `id_jenis_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `jenis_indikator` enum('1-5','10-100') NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mstr_komponen_nilai_ujian_skripsi`
--

INSERT INTO `mstr_komponen_nilai_ujian_skripsi` (`id`, `keterangan`, `keterangan_berita_acara`, `gambar`, `bobot`, `bobot_berita_acara`, `jenis_nilai`, `status`, `id_jenis_ujian_skripsi`, `jenis_indikator`, `urutan`) VALUES
(1, 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', '1558595cd9426d9306a5a1d9b60d5936.png', 1, NULL, 'naskah', 'aktif', 5, '10-100', 1),
(2, 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', '68b6727c22d6dde4829432a699731b1f.png', 1, NULL, 'naskah', 'aktif', 5, '10-100', 2),
(3, 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab', 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab', '00a88dc864b36ccbc411ae2856ed1fd3.png', 1, NULL, 'naskah', 'aktif', 5, '10-100', 3),
(4, 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', '194099c489861b6d7475bcbedddbb23f.png', 1, NULL, 'naskah', 'aktif', 5, '10-100', 4),
(5, 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', '8e20ca953f23af12d5e87169806f3d55.png', 1, NULL, 'naskah', 'aktif', 5, '10-100', 5),
(6, 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', '30f16d13a3d35cf38e943c8cc92f2595.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 1),
(7, 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'd7dc329a37980af2ed1207c3302645f0.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 2),
(8, 'Kualitas proses induktif: (1) Keakuratan metode, (2) Penyajian hasil, (3) Pembahasan, dan (4) Kualitas penyimpulan.', 'Kualitas proses induktif: (1) Keakuratan metode, (2) Penyajian hasil, (3) Pembahasan, dan (4) Kualitas penyimpulan.', '1270d00813e21738235d40b5c921e783.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 3),
(9, 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', '793c47e831d84547c0590191ea35f685.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 4),
(10, 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', '153243b967e196ba9df04714014a5c5b.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 5),
(11, 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', '03f5ab0bba9fdb658b5092f2126e99c5.png', 1, NULL, 'naskah', 'aktif', 6, '10-100', 6),
(12, 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', '10af34cd2db76fcc12ae3c810e481cbd.png', 1, NULL, 'naskah', 'aktif', 7, '10-100', 1),
(13, 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', ' Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'ca3737a1bbdb161a7a1405a302642225.png', 1, NULL, 'naskah', 'aktif', 7, '10-100', 2),
(14, 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', 'a2e2dc8243801a290cb7e28cc2b598a6.png', 1, NULL, 'naskah', 'aktif', 7, '10-100', 3),
(15, 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', '87b328f22df8b4ea14f291b55b5bf7a3.png', 1, NULL, 'naskah', 'aktif', 7, '10-100', 4),
(16, 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', '6cc15996a8550126bd391c3fa288bab6.png', 1, NULL, 'naskah', 'aktif', 7, '10-100', 5),
(17, 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', 'Relevansi topik TA dengan (1) body of knowledge program studi, (2) profil lulusan dan CPL, serta (3) roadmap penelitian Fakultas/Prodi.', 'e41979ed2d729f40e5af59ce36044d87.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 1),
(18, 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'Kualitas proses deduktif: (1) Inovasi dan kebaruan ide/gagasan, (2) Perumusan masalah, (3) Tinjauan literatur, (4) Kerangka konsep, dan (5) Hipotesis.', 'bb6506bb63611cde3f52eeabf90d621b.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 2),
(19, 'Kualitas proses induktif: (1) Keakuratan metode, (2) Penyajian hasil, (3) Pembahasan, dan (4) Kualitas penyimpulan.*', 'Kualitas proses induktif: (1) Keakuratan metode, (2) Penyajian hasil, (3) Pembahasan, dan (4) Kualitas penyimpulan.*', '4dd439a3cfc2c3a8858517b627adc3e4.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 3),
(20, 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', 'Presentasi laporan: (1) Typografi, (2) Kerapian dan kejelasan instrumen pendukung berupa gambar dan tabel, dan (3) Struktur dan keterkaitan antar bab.', '0be98a43290f2d82271609f2d0f5d4ed.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 4),
(21, 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', 'Penguasaan materi: (1) Kemampuan mengkomunikasikan ide, dan (2) Kemampuan merespons pertanyaan.', '9ed6417321c69d4b1565c885969fc495.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 5),
(22, 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', 'Kemampuan bekerja sama dalam tim (termasuk dengan pembimbing), berbagi tanggung jawab, dan memberikan kontribusi yang konstruktif.', 'eb38cf4091b5023e10cb47f0ad219154.png', 1, NULL, 'naskah', 'aktif', 8, '10-100', 6);

-- --------------------------------------------------------

--
-- Table structure for table `progres_skripsi`
--

CREATE TABLE `progres_skripsi` (
  `id` int(11) NOT NULL,
  `npm` varchar(20) DEFAULT NULL,
  `bab` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `komentar_dosen1` text DEFAULT NULL,
  `komentar_dosen2` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `nilai_dosen1` varchar(20) DEFAULT NULL,
  `nilai_dosen2` varchar(20) DEFAULT NULL,
  `progres_dosen1` int(11) DEFAULT 0,
  `progres_dosen2` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progres_skripsi`
--

INSERT INTO `progres_skripsi` (`id`, `npm`, `bab`, `file`, `komentar_dosen1`, `komentar_dosen2`, `created_at`, `nilai_dosen1`, `nilai_dosen2`, `progres_dosen1`, `progres_dosen2`) VALUES
(11, '123', 1, 'Progres_Mahasiswa_Dummy_123_BAB1_1763573225.pdf', 'haloo', 'jghjgh', '2025-11-20 00:27:05', 'ACC', 'ACC', 50, 50),
(14, '123', 2, 'Progres_Mahasiswa_Dummy_123_BAB2_1763576334.pdf', 'ghnfgf', 'saasdx', '2025-11-20 01:18:54', 'ACC', 'ACC', 50, 50),
(15, '123', 3, 'Progres_Mahasiswa_Dummy_123_BAB3_1763576859.pdf', 'vxxxxxddsc fdffff', NULL, '2025-11-20 01:27:39', 'Revisi', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `saran_ujian_skripsi`
--

CREATE TABLE `saran_ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `id_penguji` bigint(20) UNSIGNED NOT NULL,
  `saran` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skripsi`
--

CREATE TABLE `skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_mahasiswa` bigint(20) UNSIGNED NOT NULL,
  `tema` enum('Software Engineering','Networking','Artificial Intelligence') NOT NULL,
  `judul` text NOT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `tgl_pengajuan_judul` date NOT NULL,
  `skema` enum('Reguler','Penyetaraan') NOT NULL,
  `naskah` text DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skripsi`
--

INSERT INTO `skripsi` (`id`, `id_mahasiswa`, `tema`, `judul`, `pembimbing1`, `pembimbing2`, `tgl_pengajuan_judul`, `skema`, `naskah`, `nilai_akhir`) VALUES
(1, 9004, 'Software Engineering', 'NGGAK TAHU NJIR, KOK TANYA SAYAjjj', 9003, 9005, '2025-11-19', 'Reguler', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `syarat_pendadaran`
--

CREATE TABLE `syarat_pendadaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `naskah` varchar(255) NOT NULL,
  `id_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `berita_acara_seminar` varchar(255) DEFAULT NULL,
  `daftar_nilai_sementara` varchar(255) NOT NULL,
  `krs_terbaru` varchar(255) NOT NULL,
  `dokumen_identitas` varchar(255) NOT NULL,
  `sertifikat_toefl_niit` varchar(255) DEFAULT NULL,
  `sertifikat_office_puskom` varchar(255) NOT NULL,
  `sertifikat_btq_ibadah` varchar(255) NOT NULL,
  `sertifikat_bahasa` varchar(255) NOT NULL,
  `sertifikat_kompetensi_ujian_komprehensif` varchar(255) NOT NULL,
  `sertifikat_semaba_ppk_masta` varchar(255) NOT NULL,
  `sertifikat_kkn` varchar(255) NOT NULL,
  `buku_kendali_bimbingan` varchar(255) NOT NULL,
  `bukti_pembayaran_sidang` varchar(255) NOT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syarat_sempro`
--

CREATE TABLE `syarat_sempro` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `naskah` varchar(255) NOT NULL,
  `id_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `fotokopi_daftar_nilai` varchar(255) DEFAULT NULL,
  `fotokopi_krs` varchar(255) DEFAULT NULL,
  `buku_kendali_bimbingan` varchar(255) DEFAULT NULL,
  `lembar_revisi_ba_dan_tanda_terima_laporan_kp` varchar(255) DEFAULT NULL,
  `bukti_seminar_teman` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nilai_ujian_skripsi`
--

CREATE TABLE `tbl_nilai_ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_komponen_nilai` bigint(20) UNSIGNED NOT NULL,
  `id_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `id_penguji` bigint(20) UNSIGNED NOT NULL,
  `nilai` float NOT NULL CHECK (`nilai` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ujian_skripsi`
--

CREATE TABLE `ujian_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_skripsi` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date DEFAULT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `ruang` varchar(50) DEFAULT NULL,
  `penguji1` bigint(20) UNSIGNED DEFAULT NULL,
  `penguji2` bigint(20) UNSIGNED DEFAULT NULL,
  `penguji3` bigint(20) UNSIGNED DEFAULT NULL,
  `id_jenis_ujian_skripsi` bigint(20) UNSIGNED NOT NULL,
  `persetujuan_pembimbing1` tinyint(1) DEFAULT 0,
  `persetujuan_pembimbing2` tinyint(1) DEFAULT 0,
  `status` enum('Berlangsung','Diterima','Perbaikan','Mengulang') DEFAULT 'Berlangsung'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `validasi_syarat_pendadaran`
--

CREATE TABLE `validasi_syarat_pendadaran` (
  `id` int(11) NOT NULL,
  `id_syarat_pendadaran` bigint(20) UNSIGNED NOT NULL,
  `nama_field_syarat` varchar(100) NOT NULL,
  `status` enum('Diterima','Revisi','Menunggu') NOT NULL DEFAULT 'Menunggu',
  `catatan` text DEFAULT NULL,
  `id_validator` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `validasi_syarat_sempro`
--

CREATE TABLE `validasi_syarat_sempro` (
  `id` int(11) NOT NULL,
  `id_syarat_sempro` bigint(20) UNSIGNED NOT NULL,
  `nama_field_syarat` varchar(100) NOT NULL,
  `status` enum('Diterima','Revisi','Menunggu') NOT NULL DEFAULT 'Menunggu',
  `catatan` text DEFAULT NULL,
  `id_validator` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apresiasi_ujian_skripsi`
--
ALTER TABLE `apresiasi_ujian_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_apresisasi_penguji` (`id_penguji`),
  ADD KEY `fk_apresisasi_ujian_skripsi` (`id_ujian_skripsi`);

--
-- Indexes for table `data_dosen`
--
ALTER TABLE `data_dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nidk` (`nidk`);

--
-- Indexes for table `data_mahasiswa`
--
ALTER TABLE `data_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telepon` (`telepon`),
  ADD KEY `npm` (`npm`);

--
-- Indexes for table `jenis_ujian_skripsi`
--
ALTER TABLE `jenis_ujian_skripsi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mahasiswa_skripsi`
--
ALTER TABLE `mahasiswa_skripsi`
  ADD PRIMARY KEY (`npm`);

--
-- Indexes for table `mstr_akun`
--
ALTER TABLE `mstr_akun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `mstr_komponen_nilai_ujian_skripsi`
--
ALTER TABLE `mstr_komponen_nilai_ujian_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mst_komponen_jenis_skripsi` (`id_jenis_ujian_skripsi`);

--
-- Indexes for table `progres_skripsi`
--
ALTER TABLE `progres_skripsi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saran_ujian_skripsi`
--
ALTER TABLE `saran_ujian_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_saran_penguji` (`id_penguji`),
  ADD KEY `fk_saran_ujian_skripsi` (`id_ujian_skripsi`);

--
-- Indexes for table `skripsi`
--
ALTER TABLE `skripsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `pembimbing1` (`pembimbing1`),
  ADD KEY `pembimbing2` (`pembimbing2`);

--
-- Indexes for table `syarat_pendadaran`
--
ALTER TABLE `syarat_pendadaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ujian_skripsi` (`id_ujian_skripsi`);

--
-- Indexes for table `syarat_sempro`
--
ALTER TABLE `syarat_sempro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ujian_skripsi` (`id_ujian_skripsi`);

--
-- Indexes for table `tbl_nilai_ujian_skripsi`
--
ALTER TABLE `tbl_nilai_ujian_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_sempro` (`id_ujian_skripsi`,`id_komponen_nilai`,`id_penguji`),
  ADD KEY `tbl_nilai_sempro_ibfk_3` (`id_penguji`),
  ADD KEY `tbl_nilai_sempro_ibfk_4` (`id_komponen_nilai`);

--
-- Indexes for table `ujian_skripsi`
--
ALTER TABLE `ujian_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_skripsi` (`id_skripsi`),
  ADD KEY `penguji1` (`penguji1`),
  ADD KEY `penguji2` (`penguji2`),
  ADD KEY `fk_ujian_jenis` (`id_jenis_ujian_skripsi`),
  ADD KEY `penguji3` (`penguji3`);

--
-- Indexes for table `validasi_syarat_pendadaran`
--
ALTER TABLE `validasi_syarat_pendadaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_syarat_field` (`id_syarat_pendadaran`,`nama_field_syarat`);

--
-- Indexes for table `validasi_syarat_sempro`
--
ALTER TABLE `validasi_syarat_sempro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_syarat_field` (`id_syarat_sempro`,`nama_field_syarat`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apresiasi_ujian_skripsi`
--
ALTER TABLE `apresiasi_ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_ujian_skripsi`
--
ALTER TABLE `jenis_ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mstr_akun`
--
ALTER TABLE `mstr_akun`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9006;

--
-- AUTO_INCREMENT for table `mstr_komponen_nilai_ujian_skripsi`
--
ALTER TABLE `mstr_komponen_nilai_ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `progres_skripsi`
--
ALTER TABLE `progres_skripsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `saran_ujian_skripsi`
--
ALTER TABLE `saran_ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skripsi`
--
ALTER TABLE `skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `syarat_pendadaran`
--
ALTER TABLE `syarat_pendadaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syarat_sempro`
--
ALTER TABLE `syarat_sempro`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_nilai_ujian_skripsi`
--
ALTER TABLE `tbl_nilai_ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian_skripsi`
--
ALTER TABLE `ujian_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `validasi_syarat_pendadaran`
--
ALTER TABLE `validasi_syarat_pendadaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `validasi_syarat_sempro`
--
ALTER TABLE `validasi_syarat_sempro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apresiasi_ujian_skripsi`
--
ALTER TABLE `apresiasi_ujian_skripsi`
  ADD CONSTRAINT `fk_apresisasi_penguji` FOREIGN KEY (`id_penguji`) REFERENCES `data_dosen` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_apresisasi_ujian_skripsi` FOREIGN KEY (`id_ujian_skripsi`) REFERENCES `ujian_skripsi` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `data_dosen`
--
ALTER TABLE `data_dosen`
  ADD CONSTRAINT `data_dosen_ibfk_1` FOREIGN KEY (`id`) REFERENCES `mstr_akun` (`id`);

--
-- Constraints for table `data_mahasiswa`
--
ALTER TABLE `data_mahasiswa`
  ADD CONSTRAINT `data_mahasiswa_ibfk_1` FOREIGN KEY (`id`) REFERENCES `mstr_akun` (`id`);

--
-- Constraints for table `mstr_komponen_nilai_ujian_skripsi`
--
ALTER TABLE `mstr_komponen_nilai_ujian_skripsi`
  ADD CONSTRAINT `fk_mst_komponen_jenis_skripsi` FOREIGN KEY (`id_jenis_ujian_skripsi`) REFERENCES `jenis_ujian_skripsi` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `saran_ujian_skripsi`
--
ALTER TABLE `saran_ujian_skripsi`
  ADD CONSTRAINT `fk_saran_penguji` FOREIGN KEY (`id_penguji`) REFERENCES `data_dosen` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_saran_ujian_skripsi` FOREIGN KEY (`id_ujian_skripsi`) REFERENCES `ujian_skripsi` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `skripsi`
--
ALTER TABLE `skripsi`
  ADD CONSTRAINT `skripsi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `data_mahasiswa` (`id`),
  ADD CONSTRAINT `skripsi_ibfk_2` FOREIGN KEY (`pembimbing1`) REFERENCES `data_dosen` (`id`),
  ADD CONSTRAINT `skripsi_ibfk_3` FOREIGN KEY (`pembimbing2`) REFERENCES `data_dosen` (`id`);

--
-- Constraints for table `syarat_pendadaran`
--
ALTER TABLE `syarat_pendadaran`
  ADD CONSTRAINT `syarat_pendadaran_ibfk_1` FOREIGN KEY (`id_ujian_skripsi`) REFERENCES `ujian_skripsi` (`id`);

--
-- Constraints for table `syarat_sempro`
--
ALTER TABLE `syarat_sempro`
  ADD CONSTRAINT `syarat_sempro_ibfk_1` FOREIGN KEY (`id_ujian_skripsi`) REFERENCES `ujian_skripsi` (`id`);

--
-- Constraints for table `tbl_nilai_ujian_skripsi`
--
ALTER TABLE `tbl_nilai_ujian_skripsi`
  ADD CONSTRAINT `tbl_nilai_ujian_skripsi_ibfk_2` FOREIGN KEY (`id_ujian_skripsi`) REFERENCES `ujian_skripsi` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_nilai_ujian_skripsi_ibfk_3` FOREIGN KEY (`id_penguji`) REFERENCES `data_dosen` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_nilai_ujian_skripsi_ibfk_4` FOREIGN KEY (`id_komponen_nilai`) REFERENCES `mstr_komponen_nilai_ujian_skripsi` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ujian_skripsi`
--
ALTER TABLE `ujian_skripsi`
  ADD CONSTRAINT `fk_ujian_jenis` FOREIGN KEY (`id_jenis_ujian_skripsi`) REFERENCES `jenis_ujian_skripsi` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ujian_skripsi_penguji3` FOREIGN KEY (`penguji3`) REFERENCES `data_dosen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ujian_skripsi_ibfk_1` FOREIGN KEY (`id_skripsi`) REFERENCES `skripsi` (`id`),
  ADD CONSTRAINT `ujian_skripsi_ibfk_2` FOREIGN KEY (`penguji1`) REFERENCES `data_dosen` (`id`),
  ADD CONSTRAINT `ujian_skripsi_ibfk_3` FOREIGN KEY (`penguji2`) REFERENCES `data_dosen` (`id`);

--
-- Constraints for table `validasi_syarat_pendadaran`
--
ALTER TABLE `validasi_syarat_pendadaran`
  ADD CONSTRAINT `validasi_syarat_pendadaran_ibfk_1` FOREIGN KEY (`id_syarat_pendadaran`) REFERENCES `syarat_pendadaran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `validasi_syarat_sempro`
--
ALTER TABLE `validasi_syarat_sempro`
  ADD CONSTRAINT `validasi_syarat_sempro_ibfk_1` FOREIGN KEY (`id_syarat_sempro`) REFERENCES `syarat_sempro` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

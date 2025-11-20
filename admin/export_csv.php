<?php
session_start();
include "db.php";

// Cek login
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// --- 1. SET HEADER UNTUK DOWNLOAD CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Kinerja_Dosen_' . date('Y-m-d_H-i') . '.csv');

// Buka output stream
$output = fopen('php://output', 'w');

// Tambahkan BOM agar Excel membaca karakter dengan benar (UTF-8)
fputs($output, "\xEF\xBB\xBF");

// --- 2. TULIS JUDUL KOLOM (HEADER) ---
fputcsv($output, array(
    'No', 
    'NPM', 
    'Nama Mahasiswa', 
    'Judul Skripsi',
    'Bab', 
    'Waktu Upload Mhs', 
    'Pembimbing 1', 
    'Waktu Balas P1', 
    'Durasi Respon P1', 
    'Pembimbing 2', 
    'Waktu Balas P2', 
    'Durasi Respon P2'
));

// --- 3. FUNGSI HITUNG DURASI (TEXT) ---
function hitungDurasiText($start, $end) {
    // Jika belum dibalas atau format tanggal kosong
    if (empty($end) || $end == '0000-00-00 00:00:00') return "Belum Dibalas";
    
    $s = new DateTime($start);
    $e = new DateTime($end);
    
    // Jika waktu balas lebih dulu dari upload (kasus aneh), anggap baru saja
    if ($e < $s) return "Invalid Date";

    $diff = $s->diff($e);
    
    if ($diff->days > 0) {
        return $diff->days . " hari " . $diff->h . " jam";
    } elseif ($diff->h > 0) {
        return $diff->h . " jam " . $diff->i . " menit";
    } else {
        return $diff->i . " menit (Cepat)";
    }
}

// --- 4. QUERY DATA UTAMA ---
// Menggunakan JOIN yang sudah diperbaiki (skripsi join via ID)
$query = "SELECT 
            ps.*,
            m.nama AS nama_mhs,
            dm.npm,
            s.judul AS judul_skripsi,
            d1.nama AS nama_dosen1,
            d2.nama AS nama_dosen2
          FROM progres_skripsi ps
          LEFT JOIN data_mahasiswa dm ON ps.npm = dm.npm
          LEFT JOIN mstr_akun m ON dm.id = m.id
          LEFT JOIN skripsi s ON dm.id = s.id_mahasiswa
          LEFT JOIN mstr_akun d1 ON s.pembimbing1 = d1.id
          LEFT JOIN mstr_akun d2 ON s.pembimbing2 = d2.id
          ORDER BY ps.created_at DESC";

$result = mysqli_query($conn, $query);

// --- 5. LOOPING DATA KE CSV ---
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    
    // Siapkan data per baris
    $csv_row = array(
        $no++,
        $row['npm'],
        $row['nama_mhs'],
        $row['judul_skripsi'] ?? '-',
        'BAB ' . $row['bab'],
        $row['created_at'],
        
        // Data Dosen 1
        $row['nama_dosen1'] ?? '-',
        $row['waktu_balas_d1'] ?? '-',
        hitungDurasiText($row['created_at'], $row['waktu_balas_d1']),
        
        // Data Dosen 2
        $row['nama_dosen2'] ?? '-',
        $row['waktu_balas_d2'] ?? '-',
        hitungDurasiText($row['created_at'], $row['waktu_balas_d2'])
    );

    // Tulis ke file CSV
    fputcsv($output, $csv_row);
}

// Tutup dan selesai
fclose($output);
exit();
?>
<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['import'])) {
    // Cek apakah ada file yang diupload
    if ($_FILES['file_excel']['name'] != "") {
        
        $fileName = $_FILES['file_excel']['tmp_name'];
        
        // Buka file CSV
        if ($_FILES['file_excel']['size'] > 0) {
            $file = fopen($fileName, "r");
            
            // Lewati baris pertama (Header Judul Kolom)
            fgetcsv($file); 
            
            $sukses = 0;
            $gagal = 0;

            // Looping baris data
            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                
                // Sesuikan urutan kolom dengan Template CSV Anda
                // Misal urutan di CSV: [0]NPM, [1]Nama, [2]Angkatan, [3]Prodi, [4]NoHP, [5]Email, [6]JK
                
                $npm        = mysqli_real_escape_string($conn, $column[0]);
                $nama       = mysqli_real_escape_string($conn, $column[1]);
                $angkatan   = mysqli_real_escape_string($conn, $column[2]);
                $prodi      = mysqli_real_escape_string($conn, $column[3]);
                $telepon    = mysqli_real_escape_string($conn, $column[4]);
                $email      = mysqli_real_escape_string($conn, $column[5]);
                $jk         = mysqli_real_escape_string($conn, $column[6]); // Laki-laki / Perempuan

                // Default Password: 12345
                $password_default = password_hash("12345", PASSWORD_DEFAULT);
                $foto_default = "default.png";

                // 1. Cek dulu apakah NPM sudah ada di mstr_akun (Username = NPM)
                $cek = mysqli_query($conn, "SELECT id FROM mstr_akun WHERE username='$npm'");
                
                if (mysqli_num_rows($cek) == 0) {
                    // 2. Insert ke mstr_akun dulu (Login info)
                    $sql_akun = "INSERT INTO mstr_akun (username, password, nama, foto, role) 
                                 VALUES ('$npm', '$password_default', '$nama', '$foto_default', 'mahasiswa')";
                    
                    if (mysqli_query($conn, $sql_akun)) {
                        // Ambil ID yang baru saja dibuat
                        $new_id = mysqli_insert_id($conn);

                        // 3. Insert ke data_mahasiswa (Biodata)
                        // Pastikan kolom sesuai dengan database Anda
                        $sql_mhs = "INSERT INTO data_mahasiswa 
                                    (id, npm, jenis_kelamin, email, telepon, angkatan, prodi, status_beasiswa, status_mahasiswa, ttd, dokumen_identitas, sertifikat_office_puskom, sertifikat_btq_ibadah, sertifikat_bahasa, sertifikat_kompetensi_ujian_komprehensif, sertifikat_semaba_ppk_masta, sertifikat_kkn) 
                                    VALUES 
                                    ('$new_id', '$npm', '$jk', '$email', '$telepon', '$angkatan', '$prodi', 'Tidak Aktif', 'Murni', 'default_ttd.png', 'pending', 'pending', 'pending', 'pending', 'pending', 'pending', 'pending')";
                        
                        if (mysqli_query($conn, $sql_mhs)) {
                            $sukses++;
                        } else {
                            // Jika gagal insert biodata, hapus akun biar tidak nyampah
                            mysqli_query($conn, "DELETE FROM mstr_akun WHERE id='$new_id'");
                            $gagal++;
                        }
                    } else {
                        $gagal++;
                    }
                } else {
                    $gagal++; // NPM sudah ada
                }
            }
            
            fclose($file);
            echo "<script>alert('Import Selesai! Sukses: $sukses, Gagal/Duplikat: $gagal'); window.location='data_mahasiswa.php';</script>";
        }
    }
} else {
    // Jika file diakses langsung tanpa post
    header("Location: data_mahasiswa.php");
}
?>
<?php
session_start();
include "../admin/db.php"; // Pastikan path ini benar

// 1. Cek Validitas Data
if (!isset($_POST['id_dosen'])) {
    echo "<script>alert('❌ Data tidak valid. Akses ditolak.'); window.history.back();</script>";
    exit();
}

$id_dosen = $_POST['id_dosen'];
$nama     = mysqli_real_escape_string($conn, $_POST['nama']);
$prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);

// Catatan: Di database baru, tabel data_dosen TIDAK ADA kolom no_hp. 
// Jadi kita tidak mengupdate no_hp.

// --- 2. HANDLE FOTO ---
$upload_folder = '../uploads/'; // Naik satu folder karena ini ada di folder dosen/

// Ambil nama foto lama dari database
$query_lama = mysqli_query($conn, "SELECT foto FROM mstr_akun WHERE id = '$id_dosen'");
$data_lama  = mysqli_fetch_assoc($query_lama);
$nama_foto  = $data_lama['foto']; // Default pakai foto lama

// Jika ada upload foto baru
if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo "<script>alert('❌ Format foto salah! Hanya JPG, JPEG, PNG.'); window.history.back();</script>";
        exit();
    }

    // Buat nama file unik
    $nama_foto_baru = 'dosen_' . $id_dosen . '_' . time() . '.' . $ext;
    $tujuan = $upload_folder . $nama_foto_baru;

    // Upload file
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        // Hapus foto lama jika ada dan bukan default
        if (!empty($nama_foto) && file_exists($upload_folder . $nama_foto)) {
            unlink($upload_folder . $nama_foto);
        }
        $nama_foto = $nama_foto_baru;
    } else {
        echo "<script>alert('❌ Gagal mengupload foto.'); window.history.back();</script>";
        exit();
    }
}

// --- 3. HANDLE TANDA TANGAN (Opsional) ---
// Kita ambil dulu data lama dari tabel data_dosen
$query_ttd = mysqli_query($conn, "SELECT ttd FROM data_dosen WHERE id = '$id_dosen'");
$data_ttd  = mysqli_fetch_assoc($query_ttd);
$nama_ttd  = $data_ttd['ttd'] ?? ''; // Default pakai TTD lama

if (!empty($_FILES['ttd']['name'])) {
    $ext = strtolower(pathinfo($_FILES['ttd']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($ext, $allowed)) {
        $nama_ttd_baru = 'ttd_' . $id_dosen . '_' . time() . '.' . $ext;
        $tujuan_ttd = $upload_folder . $nama_ttd_baru;

        if (move_uploaded_file($_FILES['ttd']['tmp_name'], $tujuan_ttd)) {
            // Hapus ttd lama
            if (!empty($nama_ttd) && file_exists($upload_folder . $nama_ttd)) {
                unlink($upload_folder . $nama_ttd);
            }
            $nama_ttd = $nama_ttd_baru;
        }
    }
}

// --- 4. UPDATE DATABASE (Transaksional) ---
// Kita harus update 2 tabel: mstr_akun dan data_dosen

// A. Update mstr_akun (Nama & Foto)
$update_akun = mysqli_query($conn, "UPDATE mstr_akun SET nama='$nama', foto='$nama_foto' WHERE id='$id_dosen'");

// B. Update data_dosen (Prodi & TTD)
// Cek dulu apakah data dosen sudah ada di tabel data_dosen?
$cek_dosen = mysqli_query($conn, "SELECT id FROM data_dosen WHERE id='$id_dosen'");

if (mysqli_num_rows($cek_dosen) > 0) {
    // Jika ada, lakukan UPDATE
    $update_detail = mysqli_query($conn, "UPDATE data_dosen SET prodi='$prodi', ttd='$nama_ttd' WHERE id='$id_dosen'");
} else {
    // Jika belum ada (kasus jarang), lakukan INSERT
    // Kita butuh NIDK dari session atau mstr_akun
    $nidk = $_SESSION['nip']; 
    $update_detail = mysqli_query($conn, "INSERT INTO data_dosen (id, nidk, prodi, ttd) VALUES ('$id_dosen', '$nidk', '$prodi', '$nama_ttd')");
}

// --- 5. CEK KEBERHASILAN ---
if ($update_akun && $update_detail) {
    echo "<script>alert('✅ Biodata berhasil diperbarui!'); window.location.href='biodata_dosen.php';</script>";
} else {
    echo "<script>alert('❌ Gagal menyimpan ke database: " . mysqli_error($conn) . "'); window.history.back();</script>";
}
?>
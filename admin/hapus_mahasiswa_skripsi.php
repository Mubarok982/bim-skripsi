<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. Hapus data di tabel detail (data_mahasiswa)
    $del_mhs = mysqli_query($conn, "DELETE FROM data_mahasiswa WHERE id='$id'");

    // 2. Hapus data di tabel master (mstr_akun)
    $del_akun = mysqli_query($conn, "DELETE FROM mstr_akun WHERE id='$id'");

    if ($del_akun) {
        echo "<script>alert('Data Berhasil Dihapus!'); window.location='data_mahasiswa.php';</script>";
    } else {
        echo "<script>alert('Gagal Menghapus Data: " . mysqli_error($conn) . "'); window.location='data_mahasiswa.php';</script>";
    }
}
?>
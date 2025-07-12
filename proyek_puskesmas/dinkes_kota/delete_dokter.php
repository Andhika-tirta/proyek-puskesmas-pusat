<?php
// delete_dokter.php

// --- PENTING: AKTIFKAN LAPORAN KESALAHAN PHP (UNTUK DEBUGGING) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR LAPORAN KESALAHAN PHP ---

include 'config/database.php'; // Sertakan file konfigurasi database

// Pastikan koneksi ke database Dinkes Kota lokal berhasil
if ($conn_dinkes_kota->connect_error) {
    die("Koneksi ke database Dinkes Kota lokal gagal: " . $conn_dinkes_kota->connect_error);
}

// Ambil ID dokter dari parameter GET
$id_dokter = $_GET['id'] ?? null;

if (!$id_dokter) {
    // Jika ID tidak ada, redirect kembali ke dashboard dengan pesan error
    header('Location: index.php?status=error&message=ID Dokter tidak ditemukan untuk dihapus.');
    exit();
}

// Persiapkan query DELETE
$stmt = $conn_dinkes_kota->prepare("DELETE FROM dokter WHERE id_dokter = ?");
$stmt->bind_param("i", $id_dokter);

// Eksekusi query
if ($stmt->execute()) {
    // Redirect kembali ke dashboard dengan pesan sukses
    header('Location: index.php?status=success&message=Data dokter berhasil dihapus!');
    exit();
} else {
    // Redirect kembali ke dashboard dengan pesan error
    header('Location: index.php?status=error&message=Gagal menghapus data dokter: ' . $stmt->error);
    exit();
}



// Menutup koneksi database (meskipun sudah di-redirect, tetap jaga konsistensi)
if (isset($conn_dinkes_kota) && $conn_dinkes_kota instanceof mysqli) {
    $conn_dinkes_kota->close();
}
?>

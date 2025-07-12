<?php
// config/database.php (Di laptop Dinkes Kota)

// --- KONEKSI KE DATABASE DINKES KOTA (LOKAL) ---
$dinkes_servername = "localhost"; // Database dinkes_kota ada di laptop Anda sendiri
$dinkes_username = "root";        // Ganti dengan username database dinkes_kota Anda
$dinkes_password = "";            // Ganti dengan password database dinkes_kota Anda
$dinkes_dbname = "db_dinkes_kota"; // Pastikan nama database ini benar

// Buat koneksi ke db_dinkes_kota
$conn_dinkes_kota = new mysqli($dinkes_servername, $dinkes_username, $dinkes_password, $dinkes_dbname);

// Cek koneksi db_dinkes_kota
if ($conn_dinkes_kota->connect_error) {
    die("Koneksi ke database Dinkes Kota gagal: " . $conn_dinkes_kota->connect_error);
}
$conn_dinkes_kota->set_charset("utf8mb4");

// --- KONEKSI KE DATABASE PUSKESMAS SUKAMAJU ---
$sukamaju_servername = "192.168.219.124"; // IP Laptop Puskesmas Sukamaju
$sukamaju_username = "user1";             // User database di laptop Sukamaju (harus ada dan punya hak SELECT)
$sukamaju_password = "";                  // Password user di laptop Sukamaju
$sukamaju_dbname = "db_puskesmas_sukamaju"; // Nama database di laptop Sukamaju

// Buat koneksi ke db_puskesmas_sukamaju
$conn_sukamaju = new mysqli($sukamaju_servername, $sukamaju_username, $sukamaju_password, $sukamaju_dbname);

// Cek koneksi db_puskesmas_sukamaju
if ($conn_sukamaju->connect_error) {
    error_log("Koneksi ke Puskesmas Sukamaju gagal: " . $conn_sukamaju->connect_error);
    // Menggunakan $status_message untuk menampilkan pesan di UI
    // Pastikan $status_message sudah didefinisikan sebelum ini jika file ini di-include
    // Awalnya, Anda sudah punya: if (!isset($status_message)) { $status_message = ''; }
    // Jadi ini aman:
    $status_message .= '<div class="alert error">Koneksi ke Puskesmas Sukamaju gagal: Pastikan laptop online dan MySQL bisa diakses.</div>';
    $conn_sukamaju = null; // Set null agar tidak ada upaya query jika koneksi gagal
} else {
    $conn_sukamaju->set_charset("utf8mb4");
}

// --- KONEKSI KE DATABASE PUSKESMAS MEKARSARI ---
$mekarsari_servername = "192.168.219.150"; // IP Laptop Puskesmas Mekarsari
$mekarsari_username = "user4";   // User database di laptop Mekarsari
$mekarsari_password = "";   // Password user di laptop Mekarsari
$mekarsari_dbname = "db_puskesmas_mekarsari"; // Nama database di laptop Mekarsari

// Buat koneksi ke db_puskesmas_mekarsari
$conn_mekarsari = new mysqli($mekarsari_servername, $mekarsari_username, $mekarsari_password, $mekarsari_dbname);

// Cek koneksi db_puskesmas_mekarsari
if ($conn_mekarsari->connect_error) {
    error_log("Koneksi ke Puskesmas Mekarsari gagal: " . $conn_mekarsari->connect_error);
    // Menggunakan $status_message untuk menampilkan pesan di UI
    // Pastikan $status_message sudah didefinisikan sebelum ini jika file ini di-include
    // Awalnya, Anda sudah punya: if (!isset($status_message)) { $status_message = ''; }
    // Jadi ini aman:
    $status_message .= '<div class="alert error">Koneksi ke Puskesmas Mekarsari gagal: Pastikan laptop online dan MySQL bisa diakses.</div>';
    $conn_mekarsari = null; // Set null
} else {
    $conn_mekarsari->set_charset("utf8mb4");
}

// Global variable untuk pesan status (pastikan ini diinisialisasi sekali saja)
// Penempatan inisialisasi ini di akhir file 'config/database.php' adalah sedikit tidak konvensional
// namun jika ini adalah bagian dari "kode asli" yang tidak boleh diubah, maka tetap di sini.
// Idealnya, inisialisasi ini dilakukan di awal file yang meng-include `database.php`.
if (!isset($status_message)) {
    $status_message = '';
}

?>
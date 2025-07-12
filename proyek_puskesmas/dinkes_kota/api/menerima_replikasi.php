<?php
// dinkes_kota/api/menerima_replikasi.php

// --- PENTING: AKTIFKAN LAPORAN KESALAHAN PHP (UNTUK DEBUGGING) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR LAPORAN KESALAHAN PHP ---

// Sertakan file konfigurasi database pusat Dinkes Kota.
// Ini akan menginisialisasi $conn_dinkes_kota, $conn_sukamaju, $conn_mekarsari
// dan $status_message.
include '../config/database.php';

// Pastikan respons selalu dalam format JSON.
header('Content-Type: application/json');

// Pastikan koneksi ke database Dinkes Kota lokal berhasil.
// Jika koneksi ke database pusat gagal, tidak ada gunanya memproses replikasi.
if ($conn_dinkes_kota->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi ke database Dinkes Kota lokal gagal: ' . $conn_dinkes_kota->connect_error]);
    // Log error ini agar bisa dipantau di server
    error_log("Koneksi API ke db_dinkes_kota gagal: " . $conn_dinkes_kota->connect_error);
    exit;
}

// Hanya izinkan metode HTTP POST.
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Hanya metode POST yang diizinkan untuk API ini.']);
    exit;
}

// Ambil input JSON dari request body.
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Periksa apakah input JSON valid.
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Input JSON tidak valid. Error: ' . json_last_error_msg()]);
    error_log("Invalid JSON input for replikasi.php: " . $input);
    exit;
}

// Ambil dan sanitasi data yang diterima.
// Menggunakan operator null coalescing (??) untuk default value jika tidak ada.
$tanggal_rekap = $data['tanggal_rekap'] ?? null;
$asal_puskesmas = $data['asal_puskesmas'] ?? null;
$jumlah_pasien_baru = $data['jumlah_pasien_baru'] ?? 0; // Default 0 jika tidak ada
$jumlah_kunjungan = $data['jumlah_kunjungan'] ?? 0;     // Default 0 jika tidak ada
$diagnosa_terbanyak = $data['diagnosa_terbanyak'] ?? null;

// Validasi data yang diperlukan.
if (empty($tanggal_rekap) || empty($asal_puskesmas)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap. Field `tanggal_rekap` dan `asal_puskesmas` wajib diisi.']);
    error_log("Missing required fields in replikasi data: " . json_encode($data));
    exit;
}

// --- Logika UPSERT (UPDATE atau INSERT) ---

// 1. Cek apakah data untuk tanggal dan puskesmas ini sudah ada di tabel rekap_kunjungan_harian
// BUG FIX: Mengubah $conn menjadi $conn_dinkes_kota
$stmt_check = $conn_dinkes_kota->prepare("SELECT id_rekap FROM rekap_kunjungan_harian WHERE tanggal_rekap = ? AND asal_puskesmas = ?");
if (!$stmt_check) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan statement cek data: ' . $conn_dinkes_kota->error]);
    error_log("Failed to prepare check statement in replikasi.php: " . $conn_dinkes_kota->error);
    exit;
}
$stmt_check->bind_param("ss", $tanggal_rekap, $asal_puskesmas);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Data sudah ada, lakukan UPDATE
    // BUG FIX: Mengubah $conn menjadi $conn_dinkes_kota
    $stmt_update = $conn_dinkes_kota->prepare("UPDATE rekap_kunjungan_harian SET jumlah_pasien_baru = ?, jumlah_kunjungan = ?, diagnosa_terbanyak = ? WHERE tanggal_rekap = ? AND asal_puskesmas = ?");
    if (!$stmt_update) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan statement update data: ' . $conn_dinkes_kota->error]);
        error_log("Failed to prepare update statement in replikasi.php: " . $conn_dinkes_kota->error);
        $stmt_check->close();
        exit;
    }
    $stmt_update->bind_param("iisss", $jumlah_pasien_baru, $jumlah_kunjungan, $diagnosa_terbanyak, $tanggal_rekap, $asal_puskesmas);
    if ($stmt_update->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Data rekap harian berhasil diperbarui.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data: ' . $stmt_update->error]);
        error_log("Failed to execute update statement in replikasi.php: " . $stmt_update->error);
    }
    $stmt_update->close();
} else {
    // Data belum ada, lakukan INSERT
    // BUG FIX: Mengubah $conn menjadi $conn_dinkes_kota
    $stmt_insert = $conn_dinkes_kota->prepare("INSERT INTO rekap_kunjungan_harian (tanggal_rekap, asal_puskesmas, jumlah_pasien_baru, jumlah_kunjungan, diagnosa_terbanyak) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt_insert) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan statement insert data: ' . $conn_dinkes_kota->error]);
        error_log("Failed to prepare insert statement in replikasi.php: " . $conn_dinkes_kota->error);
        $stmt_check->close();
        exit;
    }
    $stmt_insert->bind_param("ssiis", $tanggal_rekap, $asal_puskesmas, $jumlah_pasien_baru, $jumlah_kunjungan, $diagnosa_terbanyak);
    if ($stmt_insert->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Data rekap harian berhasil ditambahkan.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan data: ' . $stmt_insert->error]);
        error_log("Failed to execute insert statement in replikasi.php: " . $stmt_insert->error);
    }
    $stmt_insert->close();
}

// Tutup statement check
$stmt_check->close();

// Menutup koneksi database Dinkes Kota lokal
if (isset($conn_dinkes_kota) && $conn_dinkes_kota instanceof mysqli) {
    $conn_dinkes_kota->close();
}
?>

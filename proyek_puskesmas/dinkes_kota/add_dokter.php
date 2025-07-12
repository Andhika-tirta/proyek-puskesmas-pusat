<?php
// add_dokter.php

// --- PENTING: AKTIFKAN LAPORAN KESALAHAN PHP (UNTUK DEBUGGING) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR LAPORAN KESALAHAN PHP ---

include 'config/database.php'; // Sertakan file konfigurasi database

$message = ''; // Variabel untuk menyimpan pesan status

// Pastikan koneksi ke database Dinkes Kota lokal berhasil
if ($conn_dinkes_kota->connect_error) {
    die('<div class="alert error"><strong>Fatal Error:</strong> Koneksi ke database Dinkes Kota lokal gagal: ' . $conn_dinkes_kota->connect_error . '</div></body></html>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_dokter = $_POST['nama_dokter'] ?? '';
    $spesialisasi = $_POST['spesialisasi'] ?? '';
    $puskesmas_bertugas = $_POST['puskesmas_bertugas'] ?? '';

    // Validasi input sederhana
    if (empty($nama_dokter) || empty($spesialisasi) || empty($puskesmas_bertugas)) {
        $message = '<div class="alert error">Nama Dokter, Spesialisasi, dan Puskesmas Bertugas tidak boleh kosong.</div>';
    } else {
        // Persiapkan query INSERT
        $stmt = $conn_dinkes_kota->prepare("INSERT INTO dokter (nama_dokter, spesialisasi, puskesmas_bertugas) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama_dokter, $spesialisasi, $puskesmas_bertugas);

        // Eksekusi query
        if ($stmt->execute()) {
            $message = '<div class="alert success">Data dokter berhasil ditambahkan!</div>';
            // Opsional: Redirect ke dashboard setelah berhasil
            // header('Location: index.php');
            // exit();
        } else {
            $message = '<div class="alert error">Gagal menambahkan data dokter: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Menutup koneksi database
if (isset($conn_dinkes_kota) && $conn_dinkes_kota instanceof mysqli) {
    $conn_dinkes_kota->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Dokter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .container { background-color: #ffffff; padding: 2.5rem; border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); max-width: 42rem; margin: 2rem auto; }
        .section-heading { font-size: 1.875rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
        .form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; margin-bottom: 1.5rem; }
        .btn-primary { background-color: #2563eb; color: #ffffff; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease-in-out; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-secondary { background-color: #6b7280; color: #ffffff; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease-in-out; margin-left: 0.5rem; }
        .btn-secondary:hover { background-color: #4b5563; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; font-weight: 500; }
        .alert.error { background-color: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; }
        .alert.success { background-color: #d1fae5; color: #10b981; border: 1px solid #6ee7b7; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="container">
        <h1 class="section-heading">Tambah Data Dokter Baru</h1>

        <?php echo $message; // Tampilkan pesan status ?>

        <form action="add_dokter.php" method="POST">
            <div class="mb-4">
                <label for="nama_dokter" class="form-label">Nama Dokter</label>
                <input type="text" id="nama_dokter" name="nama_dokter" class="form-input" required>
            </div>
            <div class="mb-4">
                <label for="spesialisasi" class="form-label">Spesialisasi</label>
                <input type="text" id="spesialisasi" name="spesialisasi" class="form-input" required>
            </div>
            <div class="mb-6">
                <label for="puskesmas_bertugas" class="form-label">Puskesmas Bertugas</label>
                <input type="text" id="puskesmas_bertugas" name="puskesmas_bertugas" class="form-input" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Tambah Dokter</button>
                <a href="index.php" class="btn-secondary">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
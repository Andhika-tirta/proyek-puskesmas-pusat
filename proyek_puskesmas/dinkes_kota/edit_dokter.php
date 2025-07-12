<?php
// edit_dokter.php

// --- PENTING: AKTIFKAN LAPORAN KESALAHAN PHP (UNTUK DEBUGGING) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR LAPORAN KESALAHAN PHP ---

include 'config/database.php'; // Sertakan file konfigurasi database

$message = ''; // Variabel untuk menyimpan pesan status
$dokter_data = null; // Untuk menyimpan data dokter yang akan diedit

// Pastikan koneksi ke database Dinkes Kota lokal berhasil
if ($conn_dinkes_kota->connect_error) {
    die('<div class="alert error"><strong>Fatal Error:</strong> Koneksi ke database Dinkes Kota lokal gagal: ' . $conn_dinkes_kota->connect_error . '</div></body></html>');
}

// Cek apakah ada ID dokter yang dikirimkan via GET atau POST
$id_dokter = $_GET['id'] ?? $_POST['id_dokter'] ?? null;

if (!$id_dokter) {
    $message = '<div class="alert error">ID Dokter tidak ditemukan.</div>';
} else {
    // Jika data dikirimkan via POST (form disubmit)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_dokter = $_POST['nama_dokter'] ?? '';
        $spesialisasi = $_POST['spesialisasi'] ?? '';
        $puskesmas_bertugas = $_POST['puskesmas_bertugas'] ?? '';

        // Validasi input sederhana
        if (empty($nama_dokter) || empty($spesialisasi) || empty($puskesmas_bertugas)) {
            $message = '<div class="alert error">Nama Dokter, Spesialisasi, dan Puskesmas Bertugas tidak boleh kosong.</div>';
        } else {
            // Persiapkan query UPDATE
            $stmt = $conn_dinkes_kota->prepare("UPDATE dokter SET nama_dokter = ?, spesialisasi = ?, puskesmas_bertugas = ? WHERE id_dokter = ?");
            $stmt->bind_param("sssi", $nama_dokter, $spesialisasi, $puskesmas_bertugas, $id_dokter);

            if ($stmt->execute()) {
                $message = '<div class="alert success">Data dokter berhasil diperbarui!</div>';
            } else {
                $message = '<div class="alert error">Gagal memperbarui data dokter: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }

    // Ambil data dokter untuk ditampilkan di form (baik saat pertama kali loading atau setelah update)
    $stmt_select = $conn_dinkes_kota->prepare("SELECT id_dokter, nama_dokter, spesialisasi, puskesmas_bertugas FROM dokter WHERE id_dokter = ?");
    $stmt_select->bind_param("i", $id_dokter);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();

    if ($result_select->num_rows > 0) {
        $dokter_data = $result_select->fetch_assoc();
    } else {
        $message = '<div class="alert error">Data dokter tidak ditemukan.</div>';
    }
    $stmt_select->close();
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
    <title>Edit Data Dokter</title>
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
        <h1 class="section-heading">Edit Data Dokter</h1>

        <?php echo $message; // Tampilkan pesan status ?>

        <?php if ($dokter_data): ?>
        <form action="edit_dokter.php" method="POST">
            <input type="hidden" name="id_dokter" value="<?php echo htmlspecialchars($dokter_data['id_dokter']); ?>">

            <div class="mb-4">
                <label for="nama_dokter" class="form-label">Nama Dokter</label>
                <input type="text" id="nama_dokter" name="nama_dokter" class="form-input" value="<?php echo htmlspecialchars($dokter_data['nama_dokter']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="spesialisasi" class="form-label">Spesialisasi</label>
                <input type="text" id="spesialisasi" name="spesialisasi" class="form-input" value="<?php echo htmlspecialchars($dokter_data['spesialisasi']); ?>" required>
            </div>
            <div class="mb-6">
                <label for="puskesmas_bertugas" class="form-label">Puskesmas Bertugas</label>
                <input type="text" id="puskesmas_bertugas" name="puskesmas_bertugas" class="form-input" value="<?php echo htmlspecialchars($dokter_data['puskesmas_bertugas']); ?>" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Perbarui Data</button>
                <a href="index.php" class="btn-secondary">Batal</a>
            </div>
        </form>
        <?php else: ?>
            <p class="text-gray-600">Data dokter tidak dapat dimuat atau tidak ditemukan.</p>
            <div class="flex justify-end">
                <a href="index.php" class="btn-secondary">Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

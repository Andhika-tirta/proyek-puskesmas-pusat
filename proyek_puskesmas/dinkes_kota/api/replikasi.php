<?php
// dinkes_kota/api/menerima_replikasi.php
include '../config/database.php'; // Koneksi ke database pusat

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
        exit;
    }

    $tanggal_rekap = $data['tanggal_rekap'] ?? null;
    $asal_puskesmas = $data['asal_puskesmas'] ?? null;
    $jumlah_pasien_baru = $data['jumlah_pasien_baru'] ?? 0;
    $jumlah_kunjungan = $data['jumlah_kunjungan'] ?? 0;
    $diagnosa_terbanyak = $data['diagnosa_terbanyak'] ?? null;

    if (!$tanggal_rekap || !$asal_puskesmas) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields (tanggal_rekap, asal_puskesmas).']);
        exit;
    }

    // Cek apakah data untuk tanggal dan puskesmas ini sudah ada
    $stmt_check = $conn->prepare("SELECT id_rekap FROM rekap_kunjungan_harian WHERE tanggal_rekap = ? AND asal_puskesmas = ?");
    $stmt_check->bind_param("ss", $tanggal_rekap, $asal_puskesmas);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Data sudah ada, lakukan UPDATE
        $stmt_update = $conn->prepare("UPDATE rekap_kunjungan_harian SET jumlah_pasien_baru = ?, jumlah_kunjungan = ?, diagnosa_terbanyak = ? WHERE tanggal_rekap = ? AND asal_puskesmas = ?");
        $stmt_update->bind_param("iisss", $jumlah_pasien_baru, $jumlah_kunjungan, $diagnosa_terbanyak, $tanggal_rekap, $asal_puskesmas);
        if ($stmt_update->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Data rekap harian berhasil diperbarui.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data: ' . $stmt_update->error]);
        }
        $stmt_update->close();
    } else {
        // Data belum ada, lakukan INSERT
        $stmt_insert = $conn->prepare("INSERT INTO rekap_kunjungan_harian (tanggal_rekap, asal_puskesmas, jumlah_pasien_baru, jumlah_kunjungan, diagnosa_terbanyak) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("ssiis", $tanggal_rekap, $asal_puskesmas, $jumlah_pasien_baru, $jumlah_kunjungan, $diagnosa_terbanyak);
        if ($stmt_insert->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Data rekap harian berhasil ditambahkan.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan data: ' . $stmt_insert->error]);
        }
        $stmt_insert->close();
    }
    $stmt_check->close();

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Only POST method is allowed.']);
}

$conn->close();
?>
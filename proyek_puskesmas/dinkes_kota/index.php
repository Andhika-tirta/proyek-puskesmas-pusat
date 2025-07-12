<?php
// Aktifkan laporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inisialisasi variabel status
$status_message = '';

// Hubungkan ke database
include 'config/database.php';

// Tampilkan pesan status dari URL
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $status_message .= '<div class="alert success">Data dokter berhasil ditambahkan!</div>'; break;
        case 'edited': $status_message .= '<div class="alert success">Data dokter berhasil diperbarui!</div>'; break;
        case 'deleted': $status_message .= '<div class="alert success">Data dokter berhasil dihapus!</div>'; break;
        case 'no_id': case 'dokter_not_found': case 'invalid_id': case 'no_id_to_delete':
            $status_message .= '<div class="alert error">Operasi gagal: Data tidak ditemukan atau ID tidak valid.</div>'; break;
        case 'add_error': case 'edit_error': case 'delete_error':
            $status_message .= '<div class="alert error">Terjadi kesalahan dalam operasi. Silakan coba lagi atau hubungi administrator.</div>'; break;
    }
}

// Ambil data dokter dari Dinkes
$query_dokter = "SELECT id_dokter, nama_dokter, spesialisasi FROM dokter ORDER BY nama_dokter ASC";
$result_dokter = $conn_dinkes_kota->query($query_dokter);

// Ambil rekap kunjungan dari cabang
$rekap_data_all = [];

function getTopDiagnosa($conn, $tanggal) {
    if (!$conn || empty($tanggal)) return '-';

    $stmt = $conn->prepare("SELECT diagnosa, COUNT(*) as total FROM kunjungan WHERE DATE(tanggal_kunjungan) = ? GROUP BY diagnosa ORDER BY total DESC LIMIT 1");
    if ($stmt === false) return '-';

    $stmt->bind_param("s", $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['diagnosa'] : '-';
}

function ambilRekap($conn, $nama_puskesmas, &$rekap_data_all) {
    if (!$conn) return;

    $query = "SELECT DATE(tanggal_kunjungan) AS tanggal_rekap,
                     COUNT(id_kunjungan) AS jumlah_kunjungan,
                     COUNT(DISTINCT id_pasien) AS jumlah_pasien_baru
              FROM kunjungan
              GROUP BY DATE(tanggal_kunjungan)
              ORDER BY tanggal_rekap DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (empty($row['tanggal_rekap'])) continue;
            $row['diagnosa_terbanyak'] = getTopDiagnosa($conn, $row['tanggal_rekap']);
            $row['asal_puskesmas'] = $nama_puskesmas;
            $rekap_data_all[] = $row;
        }
    }
}

ambilRekap($conn_sukamaju, 'Sukamaju', $rekap_data_all);
ambilRekap($conn_mekarsari, 'Mekarsari', $rekap_data_all);

// Urutkan berdasarkan tanggal terbaru
usort($rekap_data_all, function($a, $b) {
    return strtotime($b['tanggal_rekap']) - strtotime($a['tanggal_rekap']);
});

// Data Chart
$chart_labels = [];
$chart_sukamaju = [];
$chart_mekarsari = [];

$latest_data = array_slice($rekap_data_all, 0, 14);
$latest_data = array_reverse($latest_data);

foreach ($latest_data as $data) {
    $tanggal = date('Y-m-d', strtotime($data['tanggal_rekap']));
    if (!in_array($tanggal, $chart_labels)) {
        $chart_labels[] = $tanggal;
        $chart_sukamaju[] = 0;
        $chart_mekarsari[] = 0;
    }

    $index = array_search($tanggal, $chart_labels);
    if ($data['asal_puskesmas'] === 'Sukamaju') {
        $chart_sukamaju[$index] = (int)$data['jumlah_kunjungan'];
    } elseif ($data['asal_puskesmas'] === 'Mekarsari') {
        $chart_mekarsari[$index] = (int)$data['jumlah_kunjungan'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dinas Kesehatan Kota</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #eef;
        }

        .add-button {
            display: inline-block;
            padding: 8px 12px;
            margin: 10px 0;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-button:hover {
            background-color: #218838;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .chart-container {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Dinas Kesehatan Kota</h1>
        <?= $status_message ?>

        <div class="chart-container">
            <h2>Tren Kunjungan Harian</h2>
            <canvas id="kunjunganChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('kunjunganChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [
                        {
                            label: 'Sukamaju',
                            data: <?= json_encode($chart_sukamaju) ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Mekarsari',
                            data: <?= json_encode($chart_mekarsari) ?>,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>

        <h2>Data Rekap Kunjungan</h2>
        <?php if ($rekap_data_all): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Puskesmas</th>
                        <th>Pasien Baru</th>
                        <th>Kunjungan</th>
                        <th>Diagnosa Terbanyak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rekap_data_all as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['tanggal_rekap']) ?></td>
                            <td><?= htmlspecialchars($r['asal_puskesmas']) ?></td>
                            <td><?= htmlspecialchars($r['jumlah_pasien_baru']) ?></td>
                            <td><?= htmlspecialchars($r['jumlah_kunjungan']) ?></td>
                            <td><?= htmlspecialchars($r['diagnosa_terbanyak']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada data kunjungan.</p>
        <?php endif; ?>

        <h2>Data Dokter</h2>
        <a href="add_dokter.php" class="add-button">+ Tambah Dokter</a>
        <?php if ($result_dokter && $result_dokter->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Dokter</th>
                        <th>Spesialisasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = $result_dokter->fetch_assoc()): ?>
                        <tr>
                            <td><?= $d['id_dokter'] ?></td>
                            <td><?= htmlspecialchars($d['nama_dokter']) ?></td>
                            <td><?= htmlspecialchars($d['spesialisasi']) ?></td>
                            <td>
                                <a href="edit_dokter.php?id=<?= $d['id_dokter'] ?>" style="color: #007bff;">Edit</a> |
                                <a href="delete_dokter.php?id=<?= $d['id_dokter'] ?>" onclick="return confirm('Hapus dokter ini?')" style="color: red;">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada data dokter.</p>
        <?php endif; ?>
    </div>
</body>
</html>

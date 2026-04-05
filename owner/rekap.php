<?php
require '../koneksi.php';
checkRole(['owner']);

$rekap = null;
$detail = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];

    // Query rekap transaksi
    $sql_rekap = "
        SELECT 
            COUNT(*) as total_transaksi,
            SUM(biaya_total) as total_pendapatan,
            SUM(durasi_jam) as total_durasi,
            AVG(biaya_total) as rata_rata
        FROM tb_transaksi 
        WHERE status = 'keluar' 
        AND DATE(waktu_keluar) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ";
    $rekap = mysqli_query($conn, $sql_rekap);
    $data_rekap = mysqli_fetch_assoc($rekap);

    // Query detail transaksi
    $sql_detail = "
        SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.pemilik, a.nama_area, u.nama_lengkap as petugas
        FROM tb_transaksi t 
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
        LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
        LEFT JOIN tb_user u ON t.id_user = u.id_user 
        WHERE t.status = 'keluar' 
        AND DATE(t.waktu_keluar) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
        ORDER BY t.waktu_keluar DESC
    ";
    $detail = mysqli_query($conn, $sql_detail);

    logAktivitas($conn, $_SESSION['user_id'], 'Melihat rekap transaksi periode ' . $tanggal_awal . ' sampai ' . $tanggal_akhir);
}

// Data untuk dropdown filter
$periode_options = [
    'hari_ini' => 'Hari Ini',
    'kemarin' => 'Kemarin',
    'minggu_ini' => 'Minggu Ini',
    'bulan_ini' => 'Bulan Ini',
    'custom' => 'Kustom'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Rekap Transaksi - Owner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: cadetblue;
        }

        .header {
            background: #2E3440;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: antiquewhite;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .btn-tampil {
            padding: 14px 20px;
            font-size: 16px;
            border-radius: 8px;
            background-color: #27ae60;
            color: white;
        }

        .btn-tambah:hover {
            background-color: #219150;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: transparent;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: 2px solid #2c3e50;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #2c3e50;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-success {
            background: #2ecc71;
        }

        .btn-rekap {
            display: inline-block;
            padding: 10px 20px;
            background: #2E3440;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: 2px solid #2c3e50;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-rekap:hover {
            background: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, teal 0%, cadetblue 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .no-print {
                display: none;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>
    <div class="header no-print">
        <h1>Rekap Transaksi</h1>
        <div>
            <a href="../dashboard.php" class="btn">Kembali</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card no-print">
            <h2>Pilih Periode</h2>
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" required>
                    </div>
                </div>
                <button type="submit" class="btn-tampil">Tampilkan Rekap</button>
            </form>
        </div>

        <?php if ($rekap && $detail): ?>
            <div class="card">
                <h2 style="display: flex; justify-content: space-between; align-items: center;">
                    Hasil Rekap
                    <button class="btn-rekap" onclick="window.print()">Print Rekap</button>
                </h2>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $data_rekap['total_transaksi'] ?? 0; ?></h3>
                        <p>Total Transaksi</p>
                    </div>
                    <div class="stat-card">
                        <h3>Rp <?php echo number_format($data_rekap['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $data_rekap['total_durasi'] ?? 0; ?> jam</h3>
                        <p>Total Durasi</p>
                    </div>
                    <div class="stat-card">
                        <h3>Rp <?php echo number_format($data_rekap['rata_rata'] ?? 0, 0, ',', '.'); ?></h3>
                        <p>Rata-rata per Transaksi</p>
                    </div>
                </div>

                <h3>Detail Transaksi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Pemilik</th>
                            <th>Area</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($detail)):
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['waktu_keluar'])); ?></td>
                                <td><?php echo $row['plat_nomor']; ?></td>
                                <td><?php echo $row['jenis_kendaraan']; ?></td>
                                <td><?php echo $row['pemilik']; ?></td>
                                <td><?php echo $row['nama_area']; ?></td>
                                <td><?php echo $row['durasi_jam']; ?> jam</td>
                                <td>Rp <?php echo number_format($row['biaya_total'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['petugas']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
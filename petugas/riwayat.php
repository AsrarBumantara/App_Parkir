<?php
require '../koneksi.php';
checkRole(['petugas']);

// Fungsi untuk format durasi dalam jam dan menit
function formatDurasi($sec){
    if ($sec < 0)
        $sec = 0;
    if ($sec < 60)
        return $sec . " detik";
    $mins = intdiv($sec, 60);
    if ($sec < 3600)
        return $mins . " menit";
    $hours = intdiv($sec, 3600);
    $mins2 = intdiv($sec % 3600, 60);
    if ($mins2 > 0)
        return $hours . " jam " . $mins2 . " menit";
    return $hours . " jam";
}

// Ambil riwayat transaksi yang sudah selesai (keluar)
$riwayat = mysqli_query($conn, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.pemilik, tar.tarif_per_jam, a.nama_area, u.nama_lengkap as petugas
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    LEFT JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif 
    LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
    LEFT JOIN tb_user u ON t.id_user = u.id_user 
    WHERE t.status = 'keluar'
    ORDER BY t.waktu_keluar DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Riwayat Transaksi - Petugas</title>
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

        .btn {
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

        .btn:hover {
            background: #2c3e50;
        }

        .btn-danger {
            background: #e74c3c;
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

        .struk {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border: 2px solid #333;
            width: 350px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .struk h3 {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
        }

        .struk-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .struk-total {
            border-top: 2px dashed #333;
            padding-top: 10px;
            font-weight: bold;
            font-size: 18px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media print {
            .no-print {
                display: none;
            }

            .struk {
                display: block !important;
                position: static;
                transform: none;
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="header no-print">
        <h1>Riwayat Transaksi</h1>
        <div>
            <a href="transaksi.php" class="btn">Input Transaksi</a>
            <a href="../dashboard.php" class="btn">Kembali</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container no-print">
        <div class="card">
            <h2>Daftar Transaksi Selesai (50 Terakhir)</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plat Nomor</th>
                        <th>Pemilik</th>
                        <th>Area</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Durasi</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?php echo $row['id_parkir']; ?></td>
                            <td><?php echo $row['plat_nomor']; ?></td>
                            <td><?php echo $row['pemilik']; ?></td>
                            <td><?php echo $row['nama_area']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_keluar'])); ?></td>
                            <td><?php echo $row['durasi_jam']; ?> jam</td>
                            <td>Rp <?php echo number_format($row['biaya_total'], 0, ',', '.'); ?></td>
                            <td><?php echo $row['petugas']; ?>  
                                <button class="btn" onclick="cetakStruk(
                                '<?php echo $row['id_parkir']; ?>',
                                '<?php echo $row['plat_nomor']; ?>',
                                '<?php echo $row['jenis_kendaraan']; ?>',
                                '<?php echo $row['pemilik']; ?>',
                                '<?php echo $row['waktu_masuk']; ?>',
                                '<?php echo $row['waktu_keluar']; ?>',
                                '<?php echo $row['durasi_jam']; ?>',
                                '<?php echo $row['biaya_total']; ?>',
                                '<?php echo $row['tarif_per_jam']; ?>',
                                '<?php echo $row['petugas']; ?>',
                                '<?php echo $row['nama_area']; ?>'
                            )">Cetak Struk</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="tutupStruk()"></div>

    <!-- Struk -->
    <div id="struk" class="struk">
        <h3>STRUK PARKIR</h3>
        <div class="struk-row"><span>ID Parkir:</span><span id="s_id"></span></div>
        <div class="struk-row"><span>Area:</span><span id="s_area"></span></div>
        <div style="border-bottom: 1px dashed #ccc; margin: 10px 0;"></div>
        <div class="struk-row"><span>Plat Nomor:</span><span id="s_plat"></span></div>
        <div class="struk-row"><span>Jenis:</span><span id="s_jenis"></span></div>
        <div class="struk-row"><span>Pemilik:</span><span id="s_pemilik"></span></div>
        <div style="border-bottom: 1px dashed #ccc; margin: 10px 0;"></div>
        <div class="struk-row"><span>Waktu Masuk:</span><span id="s_masuk"></span></div>
        <div class="struk-row"><span>Waktu Keluar:</span><span id="s_keluar"></span></div>
        <div class="struk-row"><span>Durasi:</span><span id="s_durasi"></span></div>
        <div class="struk-row"><span>Tarif/Jam:</span><span id="s_tarif"></span></div>
        <div class="struk-row struk-total"><span>TOTAL:</span><span id="s_total"></span></div>
        <div
            style="border-top: 2px dashed #333; margin-top: 10px; padding-top: 10px; font-size: 12px; text-align: center;">
            Petugas: <span id="s_petugas"></span><br>
            Terima kasih atas kunjungan Anda
        </div>
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button class="btn" onclick="window.print()">Print</button>
            <button class="btn btn-danger" onclick="tutupStruk()">Tutup</button>
        </div>
    </div>

    <script>
        function cetakStruk(id, plat, jenis, pemilik, masuk, keluar, durasi, total, tarif, petugas, area) {
            document.getElementById('s_id').textContent = id;
            document.getElementById('s_area').textContent = area;
            document.getElementById('s_plat').textContent = plat;
            document.getElementById('s_jenis').textContent = jenis;
            document.getElementById('s_pemilik').textContent = pemilik;
            document.getElementById('s_masuk').textContent = new Date(masuk).toLocaleString('id-ID');
            document.getElementById('s_keluar').textContent = new Date(keluar).toLocaleString('id-ID');
            document.getElementById('s_durasi').textContent = durasi + ' jam';
            document.getElementById('s_tarif').textContent = 'Rp ' + parseInt(tarif).toLocaleString('id-ID');
            document.getElementById('s_total').textContent = 'Rp ' + parseInt(total).toLocaleString('id-ID');
            document.getElementById('s_petugas').textContent = petugas;

            document.getElementById('struk').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function tutupStruk() {
            document.getElementById('struk').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</body>

</html>
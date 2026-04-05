<?php
require '../koneksi.php';
checkRole(['petugas']);

$message = '';

// Menformat durasi dalam jam dan menit
function durasiFormat($seconds)
{
    if ($seconds < 0)
        $seconds = 0;
    if ($seconds < 60)
        return $seconds . ' detik';
    $mins = intdiv($seconds, 60);
    if ($seconds < 3600)
        return $mins . ' menit';
    $hours = intdiv($seconds, 3600);
    $mins2 = intdiv($seconds % 3600, 60);
    if ($mins2 > 0)
        return $hours . ' jam ' . $mins2 . ' menit';
    return $hours . ' jam';
}

// Proses kendaraan masuk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['masuk'])) {
    $plat_nomor = mysqli_real_escape_string($conn, $_POST['plat_nomor']);
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $warna = mysqli_real_escape_string($conn, $_POST['warna']);
    $pemilik = mysqli_real_escape_string($conn, $_POST['pemilik']);
    $id_area = $_POST['id_area'];

    // Cek atau buat data kendaraan
    $cek_kendaraan = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor'");
    if (mysqli_num_rows($cek_kendaraan) > 0) {
        $kendaraan = mysqli_fetch_assoc($cek_kendaraan);
        $id_kendaraan = $kendaraan['id_kendaraan'];
    } else {
        $sql = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $plat_nomor, $jenis_kendaraan, $warna, $pemilik);
        mysqli_stmt_execute($stmt);
        $id_kendaraan = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
    }

    // Catat transaksi masuk
    $sql = "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, status, id_user, id_area) VALUES (?, NOW(), 'masuk', ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $id_kendaraan, $_SESSION['user_id'], $id_area);

    if (mysqli_stmt_execute($stmt)) {
        // Update terisi area
        mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = $id_area");
        logAktivitas($conn, $_SESSION['user_id'], 'Kendaraan masuk: ' . $plat_nomor);
        $message = 'Kendaraan berhasil masuk parkir!';
    } else {
        $message = 'Gagal mencatat kendaraan masuk!';
    }
    mysqli_stmt_close($stmt);
}

// Proses kendaraan keluar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['keluar'])) {
    $id_parkir = (int) $_POST['id_parkir'];

    // Ambil data transaksi terkait dengan tarif
    $sqlData = "
        SELECT t.*, k.plat_nomor, k.jenis_kendaraan,
               tar.id_tarif AS tar_id, tar.tarif_per_jam
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        LEFT JOIN tb_tarif tar ON k.jenis_kendaraan = tar.jenis_kendaraan
        WHERE t.id_parkir = ?
    ";
    $stmt = mysqli_prepare($conn, $sqlData);
    mysqli_stmt_bind_param($stmt, "i", $id_parkir);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);

    // Hitung durasi menggunakan DateTime
    $tz = new DateTimeZone('Asia/Jakarta');
    $masuk = new DateTime($data['waktu_masuk'], $tz);
    $keluar = new DateTime('now', $tz);
    $diffSec = $keluar->getTimestamp() - $masuk->getTimestamp();

    $tarifPerJam = (float) $data['tarif_per_jam'];
    $durasiJam = (int) ceil($diffSec / 3600);
    if ($diffSec < 0)
        $diffSec = 0; // guard negatif
    $durasiJam = (int) ceil($diffSec / 3600);
    if ($durasiJam < 1)
        $durasiJam = 1;
    $biayaTotal = $durasiJam * $tarifPerJam;
    $waktuKeluarStr = $keluar->format('Y-m-d H:i:s');
    $tarifId = isset($data['tar_id']) ? (int) $data['tar_id'] : 0;

    // Update transaksi
    $sqlUpdate = "
        UPDATE tb_transaksi
        SET waktu_keluar = ?, id_tarif = ?, durasi_jam = ?, biaya_total = ?, status = 'keluar'
        WHERE id_parkir = ?
    ";
    $stmt2 = mysqli_prepare($conn, $sqlUpdate);
    mysqli_stmt_bind_param($stmt2, "siidd", $waktuKeluarStr, $tarifId, $durasiJam, $biayaTotal, $id_parkir);
    mysqli_stmt_execute($stmt2);

    // Update area parkir (terisi berkurang)
    if (isset($data['id_area'])) {
        $areaId = (int) $data['id_area'];
        mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area = $areaId");
    }

    logAktivitas($conn, $_SESSION['user_id'], 'Kendaraan keluar: ' . $data['plat_nomor'] . ' - Rp ' . number_format($biayaTotal, 0, ',', '.'));
    $message = 'Kendaraan keluar. Total biaya: Rp ' . number_format($biayaTotal, 0, ',', '.');
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt2);
}

// Data untuk form
$areas = mysqli_query($conn, "SELECT * FROM tb_area_parkir ORDER BY nama_area");
$tarifs = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY jenis_kendaraan");

// Kendaraan yang masih di dalam parkir
$parkir_aktif = mysqli_query($conn, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, a.nama_area 
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN tb_area_parkir a ON t.id_area = a.id_area 
    WHERE t.status = 'masuk' 
    ORDER BY t.waktu_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Transaksi Parkir - Petugas</title>
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

        .btn-catat {
            padding: 14px 20px;
            font-size: 16px;
            border-radius: 8px;
            background-color: #27ae60;
            color: white;
        }

        .btn-catat:hover {
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

        .message {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Transaksi Parkir</h1>
        <div>
            <a href="../dashboard.php" class="btn">Kembali</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <div class="card">
                <h2>Kendaraan Masuk</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Plat Nomor</label>
                        <input type="text" name="plat_nomor" required placeholder="Contoh: B 1234 ABC">
                    </div>
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" required>
                            <option value="motor">Motor</option>
                            <option value="mobil">Mobil</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Warna</label>
                        <input type="text" name="warna" placeholder="Contoh: Hitam">
                    </div>
                    <div class="form-group">
                        <label>Pemilik</label>
                        <input type="text" name="pemilik" placeholder="Nama pemilik kendaraan">
                    </div>
                    <div class="form-group">
                        <label>Area Parkir</label>
                        <select name="id_area" required>
                            <?php while ($area = mysqli_fetch_assoc($areas)): ?>
                                <option value="<?php echo $area['id_area']; ?>">
                                    <?php echo $area['nama_area']; ?> (Sisa:
                                    <?php echo $area['kapasitas'] - $area['terisi']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="masuk" class="btn-catat">Catat Masuk</button>
                </form>
            </div>

            <div class="card">
                <h2>Kendaraan dalam Parkir</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Jenis</th>
                            <th>Area</th>
                            <th>Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($parkir_aktif)): ?>
                            <tr>
                                <td><?php echo $row['plat_nomor']; ?></td>
                                <td><?php echo $row['jenis_kendaraan']; ?></td>
                                <td><?php echo $row['nama_area']; ?></td>
                                <td><?php echo date('H:i', strtotime($row['waktu_masuk'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_parkir" value="<?php echo $row['id_parkir']; ?>">
                                        <select name="id_tarif" style="width: auto; display: inline;">
                                            <?php
                                            mysqli_data_seek($tarifs, 0);
                                            while ($tarif = mysqli_fetch_assoc($tarifs)):
                                                if ($tarif['jenis_kendaraan'] == $row['jenis_kendaraan']):
                                                    ?>
                                                    <option value="<?php echo $tarif['id_tarif']; ?>">
                                                        <?php echo $tarif['jenis_kendaraan']; ?> - Rp
                                                        <?php echo number_format($tarif['tarif_per_jam'], 0, ',', '.'); ?>/jam
                                                    </option>
                                                    <?php
                                                endif;
                                            endwhile;
                                            ?>
                                        </select>
                                        <button type="submit" name="keluar" class="btn btn-success"
                                            style="margin-left: 5px;">Keluar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
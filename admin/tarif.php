<?php
require '../koneksi.php';
checkRole(['admin']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
        $tarif_per_jam = $_POST['tarif_per_jam'];

        $sql = "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sd", $jenis_kendaraan, $tarif_per_jam);

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Menambah tarif: ' . $jenis_kendaraan);
            $message = 'Tarif berhasil ditambahkan!';
        } else {
            $message = 'Gagal menambahkan tarif!';
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['edit'])) {
        $id_tarif = $_POST['id_tarif'];
        $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
        $tarif_per_jam = $_POST['tarif_per_jam'];

        $sql = "UPDATE tb_tarif SET jenis_kendaraan=?, tarif_per_jam=? WHERE id_tarif=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdi", $jenis_kendaraan, $tarif_per_jam, $id_tarif);

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Mengupdate tarif ID: ' . $id_tarif);
            $message = 'Tarif berhasil diupdate!';
        } else {
            $message = 'Gagal mengupdate tarif!';
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['hapus'])) {
        $id_tarif = $_POST['id_tarif'];
        $sql = "DELETE FROM tb_tarif WHERE id_tarif=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_tarif);

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Menghapus tarif ID: ' . $id_tarif);
            $message = 'Tarif berhasil dihapus!';
        } else {
            $message = 'Gagal menghapus tarif!';
        }
        mysqli_stmt_close($stmt);
    }
}

$result = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY id_tarif ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Manajemen Tarif - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, teal 0%, cadetblue 100%);
            min-height: 100vh;
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
            background: #e8f4f8;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .btn-tambah {
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
            margin: 5px;
        }

        .btn:hover {
            background: #2c3e50;
        }

        .btn-success {
            background: #2ecc71;
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
            padding: 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        /* === Perbaikan Tampilan === */

        /* Tombol Tambah */
        .btn-tambah {
            background: linear-gradient(135deg, #27ae60, #2ecc71) !important;
            border: none;
            box-shadow: 0 3px 8px rgba(39, 174, 96, 0.3);
            transition: all 0.2s;
        }

        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
            background: linear-gradient(135deg, #219150, #27ae60) !important;
        }

        /* Tombol Edit & Hapus */
        .btn {
            transition: all 0.2s;
        }

        .btn[style*="f39c12"],
        button[style*="f39c12"] {
            background: #f39c12 !important;
            border: none;
            box-shadow: 0 2px 6px rgba(243, 156, 18, 0.3);
        }

        .btn-danger {
            box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
        }

        .btn-success {
            box-shadow: 0 2px 6px rgba(46, 204, 113, 0.3);
        }

        /* Hover baris tabel */
        tbody tr:hover {
            background: #f0f4f8 !important;
            transition: background 0.15s;
        }

        /* Header tabel */
        th {
            letter-spacing: 0.5px;
        }

        /* Card shadow lebih dalam */
        .card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12) !important;
            border-radius: 12px !important;
        }

        /* Badge jenis/role */
        td .badge-motor {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #d5f5e3;
            color: #1e8449;
        }

        td .badge-mobil {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #d6eaf8;
            color: #1a5276;
        }

        td .badge-lainnya {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #fdebd0;
            color: #784212;
        }

        td .badge-admin {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #fadbd8;
            color: #922b21;
        }

        td .badge-petugas {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #d1f2eb;
            color: #0e6655;
        }

        td .badge-owner {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #d6eaf8;
            color: #1a5276;
        }

        td .badge-aktif {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #d5f5e3;
            color: #1e8449;
        }

        td .badge-nonaktif {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #fadbd8;
            color: #922b21;
        }

        /* Garis aksen atas card */
        .card::before {
            content: '';
            display: block;
            height: 4px;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(90deg, #2c3e50, #3498db);
            margin: -30px -30px 20px -30px;
        }

        /* Input focus */
        input:focus,
        select:focus {
            border-color: #3498db !important;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15) !important;
            outline: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Manajemen Tarif Parkir</h1>
        <div>
            <span style="color:white; font-size:14px;">
                <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>
                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; text-transform:uppercase; margin-left:8px;
                    background:<?php echo $_SESSION['role'] == 'admin' ? '#ff6b6b' : ($_SESSION['role'] == 'petugas' ? '#4ecdc4' : '#45b7d1'); ?>;
                    color:white;">
                    <?php echo $_SESSION['role']; ?>
                </span>
            </span>

            <a href="../dashboard.php" class="btn">Kembali</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <button class="btn-tambah" onclick="document.getElementById('modalTambah').style.display='block'">+
                Tambah Tarif</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jenis Kendaraan</th>
                        <th>Tarif per Jam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id_tarif']; ?></td>
                            <td><span
                                    class="badge-<?php echo $row['jenis_kendaraan']; ?>"><?php echo $row['jenis_kendaraan']; ?></span>
                            </td>
                            <td>Rp <?php echo number_format($row['tarif_per_jam'], 0, ',', '.'); ?></td>
                            <td>
                                <button class="btn"
                                    onclick="editTarif(<?php echo $row['id_tarif']; ?>, '<?php echo $row['jenis_kendaraan']; ?>', <?php echo $row['tarif_per_jam']; ?>)"
                                    style="background: #f39c12;">Edit</button>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus?');">
                                    <input type="hidden" name="id_tarif" value="<?php echo $row['id_tarif']; ?>">
                                    <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalTambah').style.display='none'">&times;</span>
            <h2>Tambah Tarif Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" required>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tarif per Jam (Rp)</label>
                    <input type="number" name="tarif_per_jam" required min="0">
                </div>
                <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalEdit').style.display='none'">&times;</span>
            <h2>Edit Tarif</h2>
            <form method="POST">
                <input type="hidden" name="id_tarif" id="edit_id">
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" id="edit_jenis" required>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tarif per Jam (Rp)</label>
                    <input type="number" name="tarif_per_jam" id="edit_tarif" required min="0">
                </div>
                <button type="submit" name="edit" class="btn btn-success">Update</button>
            </form>
        </div>
    </div>

    <script>
        function editTarif(id, jenis, tarif) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_jenis').value = jenis;
            document.getElementById('edit_tarif').value = tarif;
            document.getElementById('modalEdit').style.display = 'block';
        }
    </script>
    <script>
        document.addEventListener('keydown', function (e) {
            if (e.altKey && (e.key === 'd' || e.key === 'D')) { e.preventDefault(); window.location.href = '../dashboard.php'; }
        });
    </script>
</body>

</html>
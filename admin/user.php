<?php
require '../koneksi.php';
checkRole(['admin']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        $sql = "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES (?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $nama_lengkap, $username, $password, $role);

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Menambah user baru: ' . $username);
            $message = 'User berhasil ditambahkan!';
        } else {
            $message = 'Gagal menambahkan user!';
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['edit'])) {
        $id_user = $_POST['id_user'];
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $status_aktif = $_POST['status_aktif'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE tb_user SET nama_lengkap=?, username=?, password=?, role=?, status_aktif=? WHERE id_user=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssii", $nama_lengkap, $username, $password, $role, $status_aktif, $id_user);
        } else {
            $sql = "UPDATE tb_user SET nama_lengkap=?, username=?, role=?, status_aktif=? WHERE id_user=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssii", $nama_lengkap, $username, $role, $status_aktif, $id_user);
        }

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Mengupdate user: ' . $username);
            $message = 'User berhasil diupdate!';
        } else {
            $message = 'Gagal mengupdate user!';
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['hapus'])) {
        $id_user = $_POST['id_user'];
        $sql = "DELETE FROM tb_user WHERE id_user=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_user);

        if (mysqli_stmt_execute($stmt)) {
            logAktivitas($conn, $_SESSION['user_id'], 'Menghapus user ID: ' . $id_user);
            $message = 'User berhasil dihapus!';
        } else {
            $message = 'Gagal menghapus user!';
        }
        mysqli_stmt_close($stmt);
    }
}

$result = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY id_user ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Manajemen User - Admin</title>
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

        .btn-success:hover {
            background: #27ae60;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
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

        tr:hover {
            background: #f5f5f5;
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
            font-size: 14px;
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

        .close:hover {
            color: #000;
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
        <h1>Manajemen User</h1>
        <div>
            <span style="color:white; font-size:14px;">
                <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>
                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; text-transform:uppercase; margin-left:8px;
                    background:<?php echo $_SESSION['role'] == 'admin' ? '#ff6b6b' : ($_SESSION['role'] == 'petugas' ? '#4ecdc4' : '#45b7d1'); ?>;
                    color:white;">
                    <?php echo $_SESSION['role']; ?>
                </span>
            </span>

            <a href="../dashboard.php" class="btn">Kembali ke Dashboard</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <button class="btn-tambah" onclick="document.getElementById('modalTambah').style.display='block'">+
                Tambah User</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id_user']; ?></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><span class="badge-<?php echo $row['role']; ?>"><?php echo $row['role']; ?></span></td>
                            <td><span
                                    class="<?php echo $row['status_aktif'] ? 'badge-aktif' : 'badge-nonaktif'; ?>"><?php echo $row['status_aktif'] ? 'Aktif' : 'Nonaktif'; ?></span>
                            </td>
                            <td>
                                <button class="btn"
                                    onclick="editUser(<?php echo $row['id_user']; ?>, '<?php echo $row['nama_lengkap']; ?>', '<?php echo $row['username']; ?>', '<?php echo $row['role']; ?>', <?php echo $row['status_aktif']; ?>)"
                                    style="background: #f39c12;">Edit</button>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus?');">
                                    <input type="hidden" name="id_user" value="<?php echo $row['id_user']; ?>">
                                    <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalTambah').style.display='none'">&times;</span>
            <h2>Tambah User Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalEdit').style.display='none'">&times;</span>
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="id_user" id="edit_id">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>Password (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role" required>
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_aktif" id="edit_status" required>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <button type="submit" name="edit" class="btn btn-success">Update</button>
            </form>
        </div>
    </div>

    <script>
        function editUser(id, nama, username, role, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_status').value = status;
            document.getElementById('modalEdit').style.display = 'block';
        }

        window.onclick = function (event) {
            if (event.target.className == 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
    <script>
        document.addEventListener('keydown', function (e) {
            if (e.altKey && (e.key === 'd' || e.key === 'D')) { e.preventDefault(); window.location.href = '../dashboard.php'; }
        });
    </script>
</body>

</html>
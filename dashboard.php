<?php
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama_lengkap'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Dashboard - Aplikasi Parkir</title>
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

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: #ff4757;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #ff3344;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            background: antiquewhite;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #666;
            font-size: 16px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: antiquewhite;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .menu-card h3 {
            color: black;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .menu-card p {
            color: #666;
            font-size: 14px;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .role-admin {
            background: #ff6b6b;
            color: white;
        }

        .role-petugas {
            background: #4ecdc4;
            color: white;
        }

        .role-owner {
            background: #45b7d1;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Aplikasi Parkir</h1>
        <div class="user-info">
            <span>Selamat datang, <strong><?php echo $nama; ?></strong></span>
            <span class="role-badge role-<?php echo $role; ?>"><?php echo $role; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Dashboard</h2>
            <p>Silakan pilih menu yang tersedia sesuai dengan hak akses Anda.</p>
        </div>

        <div class="menu-grid">
            <?php if ($role == 'admin'): ?>
                <a href="admin/user.php" class="menu-card">
                    <h3>Manajemen User</h3>
                    <p>Kelola data pengguna sistem (CRUD User)</p>
                </a>
                <a href="admin/tarif.php" class="menu-card">
                    <h3>Manajemen Tarif</h3>
                    <p>Kelola tarif parkir per jenis kendaraan</p>
                </a>
                <a href="admin/area.php" class="menu-card">
                    <h3>Manajemen Area</h3>
                    <p>Kelola area parkir dan kapasitasnya</p>
                </a>
                <a href="admin/kendaraan.php" class="menu-card">
                    <h3>Manajemen Kendaraan</h3>
                    <p>Kelola data kendaraan terdaftar</p>
                </a>
                <a href="admin/log.php" class="menu-card">
                    <h3>Log Aktivitas</h3>
                    <p>Lihat riwayat aktivitas pengguna</p>
                </a>
            <?php elseif ($role == 'petugas'): ?>
                <a href="petugas/transaksi.php" class="menu-card">
                    <h3>Transaksi Parkir</h3>
                    <p>Proses masuk dan keluar kendaraan</p>
                </a>
                <a href="petugas/riwayat.php" class="menu-card">
                    <h3>Riwayat Transaksi</h3>
                    <p>Lihat dan cetak struk transaksi</p>
                </a>
            <?php elseif ($role == 'owner'): ?>
                <a href="owner/rekap.php" class="menu-card">
                    <h3>Rekap Transaksi</h3>
                    <p>Lihat rekap transaksi sesuai periode</p>
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
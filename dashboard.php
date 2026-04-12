<?php
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama_lengkap'];

// Statistik keseluruhan
$stat_parkir_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'"));
$stat_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(biaya_total),0) as pendapatan FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_keluar)=CURDATE()"));
$stat_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(biaya_total),0) as pendapatan FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar)=MONTH(CURDATE()) AND YEAR(waktu_keluar)=YEAR(CURDATE())"));
$stat_area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kapasitas) as kapasitas, SUM(terisi) as terisi FROM tb_area_parkir"));
$stat_kendaraan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_kendaraan"));
$stat_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_user WHERE status_aktif=1"));
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
            background: #e8f4f8;
            background-image: linear-gradient(135deg, teal 0%, cadetblue 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(90deg, #2E3440, #3b4a5a);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            font-size: 24px;
            letter-spacing: 0.5px;
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
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(255, 71, 87, 0.3);
        }

        .logout-btn:hover {
            background: #ff3344;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.4);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            padding: 28px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            border-left: 5px solid #2E3440;
        }

        .welcome h2 {
            color: #2E3440;
            margin-bottom: 6px;
            font-size: 22px;
        }

        .welcome p {
            color: #888;
            font-size: 14px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: #333;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
            border-top: 4px solid #2E3440;
            position: relative;
            overflow: hidden;
        }

        .menu-card::after {
            content: '→';
            position: absolute;
            bottom: 20px;
            right: 22px;
            font-size: 18px;
            color: #ccc;
            transition: color 0.25s, transform 0.25s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.13);
            border-top-color: #3498db;
            color: #333;
        }

        .menu-card:hover::after {
            color: #3498db;
            transform: translateX(4px);
        }

        .menu-card h3 {
            color: #2E3440;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .menu-card p {
            color: #888;
            font-size: 13px;
            line-height: 1.5;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .role-admin {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .role-petugas {
            background: linear-gradient(135deg, #4ecdc4, #26de81);
            color: white;
        }

        .role-owner {
            background: linear-gradient(135deg, #45b7d1, #2980b9);
            color: white;
        }

        .section-label {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
            margin-bottom: 12px;
            padding-left: 4px;
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

        <!-- Statistik Keseluruhan -->
        <p class="section-label">📊 Statistik Keseluruhan</p>
        <div
            style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:15px; margin-bottom:28px;">
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #2ecc71;">
                <div style="font-size:32px; font-weight:bold; color:#2ecc71;"><?php echo $stat_parkir_aktif['total']; ?>
                </div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Kendaraan Parkir Saat Ini</div>
            </div>
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #3498db;">
                <div style="font-size:32px; font-weight:bold; color:#3498db;"><?php echo $stat_hari_ini['total']; ?>
                </div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Transaksi Hari Ini</div>
            </div>
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #f39c12;">
                <div style="font-size:16px; font-weight:bold; color:#f39c12; margin-top:6px;">Rp
                    <?php echo number_format($stat_hari_ini['pendapatan'], 0, ',', '.'); ?></div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Pendapatan Hari Ini</div>
            </div>
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #9b59b6;">
                <div style="font-size:16px; font-weight:bold; color:#9b59b6; margin-top:6px;">Rp
                    <?php echo number_format($stat_bulan_ini['pendapatan'], 0, ',', '.'); ?></div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Pendapatan Bulan Ini</div>
            </div>
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #e74c3c;">
                <div style="font-size:32px; font-weight:bold; color:#e74c3c;">
                    <?php echo ($stat_area['kapasitas'] - $stat_area['terisi']); ?>/<?php echo $stat_area['kapasitas']; ?>
                </div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Slot Tersedia / Total</div>
            </div>
            <div
                style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #1abc9c;">
                <div style="font-size:32px; font-weight:bold; color:#1abc9c;"><?php echo $stat_kendaraan['total']; ?>
                </div>
                <div
                    style="font-size:12px; color:#999; margin-top:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Total Kendaraan Terdaftar</div>
            </div>
        </div>

        <p class="section-label">⚡ Menu</p>
        <div class="menu-grid">
            <?php if ($role == 'admin'): ?>
                <a href="admin/user.php" class="menu-card">
                    <h3>Manajemen User <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+U</small>
                    </h3>
                    <p>Kelola data pengguna sistem (CRUD User)</p>
                </a>
                <a href="admin/tarif.php" class="menu-card">
                    <h3>Manajemen Tarif <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+T</small>
                    </h3>
                    <p>Kelola tarif parkir per jenis kendaraan</p>
                </a>
                <a href="admin/area.php" class="menu-card">
                    <h3>Manajemen Area <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+A</small>
                    </h3>
                    <p>Kelola area parkir dan kapasitasnya</p>
                </a>
                <a href="admin/kendaraan.php" class="menu-card">
                    <h3>Manajemen Kendaraan <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+K</small>
                    </h3>
                    <p>Kelola data kendaraan terdaftar</p>
                </a>
                <a href="admin/log.php" class="menu-card">
                    <h3>Log Aktivitas <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+L</small>
                    </h3>
                    <p>Lihat riwayat aktivitas pengguna</p>
                </a>
            <?php elseif ($role == 'petugas'): ?>
                <a href="petugas/transaksi.php" class="menu-card">
                    <h3>Transaksi Parkir <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+T</small>
                    </h3>
                    <p>Proses masuk dan keluar kendaraan</p>
                </a>
                <a href="petugas/riwayat.php" class="menu-card">
                    <h3>Riwayat Transaksi <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+R</small>
                    </h3>
                    <p>Lihat dan cetak struk transaksi</p>
                </a>
            <?php elseif ($role == 'owner'): ?>
                <a href="owner/rekap.php" class="menu-card">
                    <h3>Rekap Transaksi <small
                            style="font-size:11px;background:#ddd;padding:2px 7px;border-radius:4px;color:#555">Alt+R</small>
                    </h3>
                    <p>Lihat rekap transaksi sesuai periode</p>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Keyboard shortcut
        document.addEventListener('keydown', function (e) {
            if (!e.altKey) return;
            if (e.key === 'd' || e.key === 'D') { e.preventDefault(); window.location.href = 'dashboard.php'; }
            <?php if ($role == 'admin'): ?>
                if (e.key === 'u' || e.key === 'U') { e.preventDefault(); window.location.href = 'admin/user.php'; }
                if (e.key === 't' || e.key === 'T') { e.preventDefault(); window.location.href = 'admin/tarif.php'; }
                if (e.key === 'a' || e.key === 'A') { e.preventDefault(); window.location.href = 'admin/area.php'; }
                if (e.key === 'k' || e.key === 'K') { e.preventDefault(); window.location.href = 'admin/kendaraan.php'; }
                if (e.key === 'l' || e.key === 'L') { e.preventDefault(); window.location.href = 'admin/log.php'; }
            <?php elseif ($role == 'petugas'): ?>
                if (e.key === 't' || e.key === 'T') { e.preventDefault(); window.location.href = 'petugas/transaksi.php'; }
                if (e.key === 'r' || e.key === 'R') { e.preventDefault(); window.location.href = 'petugas/riwayat.php'; }
            <?php elseif ($role == 'owner'): ?>
                if (e.key === 'r' || e.key === 'R') { e.preventDefault(); window.location.href = 'owner/rekap.php'; }
            <?php endif; ?>
        });
    </script>

</html>
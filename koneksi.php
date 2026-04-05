<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'parkir';

$conn = mysqli_connect($host, $user, $pass, $dbname);

date_default_timezone_set('Asia/Jakarta');
mysqli_query($conn, "SET time_zone = '+07:00'");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

session_start();

function logAktivitas($conn, $id_user, $aktivitas) {
    $sql = "INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $id_user, $aktivitas);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function checkRole($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: index.php");
        exit();
    }
}
?>

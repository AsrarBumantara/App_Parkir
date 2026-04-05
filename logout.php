<?php
require_once 'koneksi.php';
checkRole(['admin', 'petugas', 'owner']);

logAktivitas($conn, $_SESSION['user_id'], 'Logout dari sistem');

session_destroy();
header("Location: index.php");
exit();
?>

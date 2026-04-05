<?php
require_once 'koneksi.php';

$users = [
    'admin'  => 'admin123',
    'petugas'=> 'petugas123',
    'owner'  => 'owner123'
];

foreach ($users as $username => $plain) {
    // cari user berdasarkan username
    $sql = "SELECT id_user FROM tb_user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        // hashing password baru
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        // update password
        $sql2 = "UPDATE tb_user SET password = ? WHERE id_user = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('si', $hash, $row['id_user']);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();
}   
?>
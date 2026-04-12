<?php
require 'koneksi.php';
require 'hash.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM tb_user WHERE username = ? AND status_aktif = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            logAktivitas($conn, $user['id_user'], 'Login ke sistem');
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan atau akun tidak aktif!";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Login - Aplikasi Parkir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1a2a3a 0%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: #e8f4f8;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 400px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2E3440, #3b4a5a);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(46, 52, 64, 0.3);
        }

        h2 {
            text-align: center;
            color: #2E3440;
            margin-bottom: 4px;
            font-size: 22px;
        }

        .login-subtitle {
            text-align: center;
            color: #aaa;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: bold;
            font-size: 13px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background: #fafafa;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #2E3440;
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 52, 64, 0.1);
        }

        button {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2E3440, #3b4a5a);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(46, 52, 64, 0.3);
            margin-top: 6px;
        }

        button:hover {
            background: linear-gradient(135deg, #3b4a5a, #4a5a6a);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(46, 52, 64, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .info {
            margin-top: 20px;
            padding: 15px;
            background: #3EB489;
            border-radius: 5px;
            font-size: 12px;
            color: #555;
        }

        .info strong {
            color: #333;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="icon">🅿️</div>
        </div>
        <h2>Aplikasi Parkir</h2>
        <p class="login-subtitle">Masuk untuk melanjutkan</p>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

    </div>
    <?php if (!empty($error)): ?>
        <script>
            alert("<?= $error ?>");
        </script>
    <?php endif; ?>
</body>

</html>
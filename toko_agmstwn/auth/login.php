<?php
include '../config/koneksi.php';
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($user) > 0) {
        $u = mysqli_fetch_assoc($user);
        $_SESSION['user'] = $u;

        if ($u['role'] == 'admin') {
            $_SESSION['admin'] = $u;
            header("Location: ../admin/index.php");
        } else {
            $_SESSION['user'] = $u;
            header("Location: ../users/index.php");
        }
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
</head>
<body>

<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 10px;">
            <a href="../auth/login.php">
                <img src="../assets/logo_login.png" alt="eCommerce Agmstwn" style="width: 250px; height: auto;">
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-3 link-register">
            <small>Belum punya akun? <a href="register.php">Daftar Sekarang</a></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
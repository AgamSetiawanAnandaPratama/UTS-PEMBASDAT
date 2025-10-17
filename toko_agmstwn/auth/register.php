<?php
include '../config/koneksi.php';
session_start();

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $nama = strtoupper(trim($_POST['nama']));
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username atau Email sudah terdaftar!";
    } else {
        mysqli_query($conn, "INSERT INTO users(username,nama,email,password,role) VALUES('$username','$nama','$email','$password','user')");
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 10px;">
                <img src="../assets/logo_login.png" alt="eCommerce Agmstwn" style="width: 250px; height: auto;">
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100">Daftar</button>
        </form>

        <div class="text-center mt-3">
            <small>Sudah punya akun? <a href="login.php" class="text-primary fw-semibold">Login di sini</a></small>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
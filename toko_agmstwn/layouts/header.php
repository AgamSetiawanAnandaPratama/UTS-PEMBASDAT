<?php
session_start();

// Deteksi role untuk menyesuaikan navbar
$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'guest';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' | eCommerce Agmstwn' : 'eCommerce Agmstwn' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Modern CSS -->
    <link rel="stylesheet" href="../assets/style-modern.css">

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/logo.png">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="../index.php">eCommerce Agmstwn</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">

        <?php if ($role === 'admin'): ?>
        <!-- ===== NAVBAR ADMIN ===== -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
          <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-white" href="../admin/index.php">eCommerce Agmstwn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
              <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
              <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link text-white" href="../admin/index.php"> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../admin/produk.php"> Produk</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../admin/transaksi.php"> Transaksi</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../admin/laporan.php"> Laporan</a></li>
                <li class="nav-item ms-2">
                  <a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a>
                </li>
              </ul>
            </div>
          </div>
        </nav>

        <!-- Tambahkan jarak bawah navbar agar konten tidak tertutup -->
        <div style="margin-top: 80px;"></div>

        <?php elseif ($role === 'user'): ?>
          <!-- ===== NAVBAR UNTUK USER ===== -->
          <li class="nav-item"><a class="nav-link" href="../users/index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="../users/keranjang.php">Keranjang</a></li>
          <li class="nav-item"><a class="nav-link" href="../users/riwayat.php">Riwayat</a></li>
          <li class="nav-item"><a class="btn btn-primary ms-3" href="../auth/logout.php">Keluar</a></li>

        <?php else: ?>
          <!-- ===== NAVBAR UNTUK TAMU ===== -->
          <li class="nav-item"><a class="nav-link" href="../auth/login.php">Masuk</a></li>
          <li class="nav-item"><a class="btn btn-success ms-2" href="../auth/register.php">Daftar</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<!-- ====== MAIN CONTAINER ====== -->
<main class="container my-4">

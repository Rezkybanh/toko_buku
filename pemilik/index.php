<?php
session_start();

include '../config/middleware.php';

authorize('pemilik');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Check if user role is admin
if ($_SESSION['user']['peran'] != 'pemilik') {
    echo "Anda tidak memiliki akses ke halaman ini!";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Full-width Navbar with Typing Effect</title>
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-left">
      <a href="index.php" class="icon-link">
        <img src="https://cdn-icons-png.freepik.com/256/15623/15623916.png?ga=GA1.1.1383598034.1728886405&semt=ais_hybrid" 
             alt="Logo Buku" class="navbar-icon">
      </a>
    </div>
    <div class="navbar-right">
      <ul id="menu-list" class="hidden">
        <li><a href="stokBuku.php">Laporan Stok Buku</a></li>
        <li><a href="transaksi.php">Laporan Transaksi</a></li>
        <li><a href="cetakLaporan.php">Cetak Laporan</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
      <div class="hamburger" id="hamburger-menu">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
      </div>
    </div>
  </nav>

  <div class="main">
    <p id="typing-text"></p>
  </div>

  <script src="../js/deskripsi.js"></script>
  <script src="../js/responsive.js"></script>
</body>
</html>

<?php
session_start();
include '../config/middleware.php';

authorize('admin');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit();
}

// Cek apakah peran user adalah admin
if ($_SESSION['user']['peran'] != 'admin') {
  echo "Anda tidak memiliki akses ke halaman ini!";
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Transaksi</title>

  <!-- CSS -->
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/responsive.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <style>
    /* Container untuk form pencarian dan tabel */
    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 20px;
    }

    .form-container {
      margin-bottom: 10px;
    }

    /* Membuat scroll khusus untuk tabel */
    .table-container {
      overflow-y: auto;
      max-height: 525px;
      scrollbar-width: thin;
      /* Untuk Firefox */
      scrollbar-color: #FBCEB5 #f1f1f1;
      /* Warna scrollbar di Firefox */
    }

    /* Scrollbar untuk Webkit (Chrome, Edge, Safari) */
    .table-container::-webkit-scrollbar {
      width: 10px;
    }

    .table-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      /* Warna track (jalur) scrollbar */
    }

    .table-container::-webkit-scrollbar-thumb {
      background-color: #FBCEB5;
      /* Warna thumb (bagian bergerak) */
      border-radius: 10px;
      /* Membuat thumb lebih bulat */
      border: 2px solid #ffffff;
      /* Jarak antara thumb dan track */
    }

    .table-container::-webkit-scrollbar-thumb:hover {
      background-color: #FBCEB5;
      /* Warna thumb saat di-hover */
    }

    /* Tabel responsive */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    /* Style alert */
    .alert {
      background-color: #f0f0f0;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
    }
  </style>
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
        <li><a href="dataUser.php">Data User</a></li>
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

    <!-- Tabel Data -->
    <div class="table-container">
    <table id="transaksiTable" class="display">
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Nama Pembeli</th>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal Transaksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Ambil data transaksi dari database
        include '../config/koneksi.php';

        // Query untuk mendapatkan data transaksi, nama pembeli, dan judul buku
        $query = "
        SELECT 
            t.id_transaksi,
            u.nama_pengguna,
            b.judul_buku,
            t.jumlah,
            t.total_harga,
            t.tanggal_transaksi
        FROM 
            transaksi t
        JOIN 
            user u ON t.id_pengguna = u.id_pengguna
        JOIN 
            buku b ON t.id_buku = b.id_buku;
        ";

        // Eksekusi query
        $result = $conn->query($query);

        // Loop melalui hasil query dan tampilkan data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id_transaksi']}</td>
                    <td>{$row['nama_pengguna']}</td>
                    <td>{$row['judul_buku']}</td>
                    <td>{$row['jumlah']}</td>
                    <td>{$row['total_harga']}</td>
                    <td>{$row['tanggal_transaksi']}</td>
                  </tr>";
        }
        ?>
    </tbody>
</table>

        </div>
    </div>

    <script src=" https://code.jquery.com/jquery-3.6.0.min.js">
        </script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="../js/responsive.js"></script>

        <script>
          $(document).ready(function() {
            // Inisialisasi DataTables
            var table = $('#transaksiTable').DataTable();

            // Fungsi pencarian
            $('#search').on('keyup', function() {
              table.search(this.value).draw();
            });
          });
        </script>
</body>

</html>
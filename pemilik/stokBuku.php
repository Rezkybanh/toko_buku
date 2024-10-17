<?php
session_start();
include '../config/middleware.php';
include '../config/koneksi.php'; // Pastikan ini termasuk untuk koneksi database

authorize('pemilik');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah peran user adalah pemilik
if ($_SESSION['user']['peran'] != 'pemilik') {
    echo "Anda tidak memiliki akses ke halaman ini!";
    exit();
}

// Inisialisasi variabel pencarian
$search = '';

// Cek apakah form pencarian telah disubmit
if (isset($_GET['judul_buku'])) {
    $search = $_GET['judul_buku'];
}

// Siapkan pernyataan SQL
if (!empty($search)) {
    // Cari berdasarkan judul_buku, penulis, atau penerbit
    $query = "SELECT * FROM buku WHERE judul_buku LIKE ? OR penulis LIKE ? OR penerbit LIKE ?";
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
} else {
    // Jika tidak ada pencarian, tampilkan semua buku
    $query = "SELECT * FROM buku";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .table-container {
            overflow-y: auto;
            max-height: 525px;
            scrollbar-width: thin;
            scrollbar-color: #FBCEB5 #f1f1f1;
        }

        .table-container::-webkit-scrollbar {
            width: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #FBCEB5;
            border-radius: 10px;
            border: 2px solid #ffffff;
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
            <table id="bukuTable" class="display">
                <thead>
                    <tr>
                        <th>ID Buku</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Harga Buku</th>
                        <th>Stok Buku</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop melalui hasil query dan tampilkan data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id_buku']}</td>
                                <td>{$row['judul_buku']}</td>
                                <td>{$row['penulis']}</td>
                                <td>{$row['penerbit']}</td>
                                <td>{$row['harga_buku']}</td>
                                <td>{$row['stok_buku']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/responsive.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#bukuTable').DataTable();
        });
    </script>
</body>

</html>

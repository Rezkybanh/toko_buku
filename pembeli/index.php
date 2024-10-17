<?php
session_start();
include '../config/middleware.php';
include '../config/koneksi.php'; // Koneksi database

authorize('pembeli'); // Pastikan hanya pembeli yang bisa mengakses

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah peran user adalah pembeli
if ($_SESSION['user']['peran'] != 'pembeli') {
    echo "Anda tidak memiliki akses ke halaman ini!";
    exit();
}

// Inisialisasi variabel pencarian
$search = '';

// Cek apakah form pencarian disubmit
if (isset($_GET['judul_buku'])) {
    $search = $_GET['judul_buku'];
}

// Query untuk menampilkan buku dengan stok > 0 dan filter pencarian
$query = "SELECT * FROM buku WHERE stok_buku > 0";
if (!empty($search)) {
    $query .= " AND judul_buku LIKE ?";
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $searchTerm);
} else {
    $stmt = $conn->prepare($query);
}

// Eksekusi query untuk menampilkan buku
$stmt->execute();
$result = $stmt->get_result();

// Proses pembelian
if (isset($_POST['beli'])) {
    $id_buku = $_POST['id_buku'];
    $jumlah_beli = (int)$_POST['jumlah_beli'];

    // Ambil stok buku
    $stokQuery = "SELECT stok_buku FROM buku WHERE id_buku = ?";
    $stokStmt = $conn->prepare($stokQuery);
    $stokStmt->bind_param('i', $id_buku);
    $stokStmt->execute();
    $stokStmt->bind_result($stok);
    $stokStmt->fetch();
    $stokStmt->close();

    if ($stok >= $jumlah_beli) {
        // Kurangi stok buku
        $stok -= $jumlah_beli;
        if ($stok == 0) {
            // Hapus buku jika stok habis
            $deleteQuery = "DELETE FROM buku WHERE id_buku = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param('i', $id_buku);
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // Update stok buku
            $updateQuery = "UPDATE buku SET stok_buku = ? WHERE id_buku = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ii', $stok, $id_buku);
            $updateStmt->execute();
            $updateStmt->close();
        }
        header("Location: index.php"); // Redirect setelah pembelian
        exit();
    } else {
        echo "Jumlah stok tidak mencukupi!";
    }
}

$stmt->close(); // Tutup statement
$conn->close(); // Tutup koneksi
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .btn {
            background: linear-gradient(to right, #e58562, #bb724b);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: linear-gradient(to right, #bb724b, #e58562);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="index.php" class="icon-link">
                <img src="https://cdn-icons-png.freepik.com/256/15623/15623916.png" 
                     alt="Logo Buku" class="navbar-icon">
            </a>
        </div>
        <div class="navbar-right">
            <ul id="menu-list">
                <li><a href="index.php">Beli Buku</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main">
        <div class="table-container">
            <table id="bookTable" class="display">
                <thead>
                    <tr>
                        <th>ID Buku</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop melalui hasil query dan tampilkan data buku
                    while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                            <form method="POST" action="index.php">
                                <td><?php echo $row['id_buku']; ?></td>
                                <td><?php echo $row['judul_buku']; ?></td>
                                <td><?php echo $row['penulis']; ?></td>
                                <td><?php echo $row['penerbit']; ?></td>
                                <td><?php echo $row['harga_buku']; ?></td>
                                <td><?php echo $row['stok_buku']; ?></td>
                                <td>
                                    <input type="hidden" name="id_buku" value="<?php echo $row['id_buku']; ?>">
                                    <input type="number" name="jumlah_beli" placeholder="Jumlah" required min="1" max="<?php echo $row['stok_buku']; ?>">
                                    <button type="submit" name="beli" class="btn">Beli</button>
                                </td>
                            </form>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#bookTable').DataTable();
        });
    </script>
</body>

</html>

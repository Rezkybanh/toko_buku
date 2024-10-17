<?php
session_start();
include '../config/middleware.php';
include '../config/koneksi.php'; // Pastikan koneksi database disertakan

authorize('admin'); // Pastikan hanya admin yang bisa mengakses

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

// Inisialisasi variabel pencarian
$search = '';

// Cek apakah form pencarian telah disubmit
if (isset($_GET['judul_buku'])) {
    $search = $_GET['judul_buku'];
}

// Siapkan pernyataan SQL untuk pencarian atau menampilkan semua data
if (!empty($search)) {
    $query = "SELECT * FROM buku WHERE judul_buku LIKE ?";
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $searchTerm);
} else {
    $query = "SELECT * FROM buku";
    $stmt = $conn->prepare($query);
}

// Hapus buku jika tombol delete ditekan
if (isset($_GET['delete'])) {
    $id_buku = $_GET['delete'];

    // Query untuk menghapus data
    $deleteQuery = "DELETE FROM buku WHERE id_buku = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id_buku); // ID bertipe integer

    if ($deleteStmt->execute()) {
        $_SESSION['delete_success'] = true; // Simpan status penghapusan
        header("Location: stokBuku.php"); // Redirect kembali ke halaman
        exit();
    } else {
        echo "Gagal menghapus buku: " . $deleteStmt->error;
    }
    $deleteStmt->close(); // Tutup statement
}

// Tambah buku baru jika form submit ditekan
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $id_buku = $_POST['id_buku'];
    $judul_buku = $_POST['judul_buku'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $harga_buku = $_POST['harga_buku'];
    $stok_buku = $_POST['stok_buku'];

    // Query untuk insert data
    $insertQuery = "INSERT INTO buku (id_buku, judul_buku, penulis, penerbit, harga_buku, stok_buku) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("isssii", $id_buku, $judul_buku, $penulis, $penerbit, $harga_buku, $stok_buku);

    if ($insertStmt->execute()) {
        header("Location: daBuku.php"); // Redirect setelah berhasil
        exit();
    } else {
        echo "Error: " . $insertStmt->error;
    }
    $insertStmt->close(); // Tutup statement
}

// Update buku jika form edit submit ditekan
if (isset($_POST['submit_edit'])) {
    // Ambil data dari form edit
    $id_buku = $_POST['id_buku_edit'];
    $judul_buku = $_POST['judul_buku_edit'];
    $penulis = $_POST['penulis_edit'];
    $penerbit = $_POST['penerbit_edit'];
    $harga_buku = $_POST['harga_buku_edit'];
    $stok_buku = $_POST['stok_buku_edit'];

    // Query untuk update data
    $updateQuery = "UPDATE buku SET judul_buku = ?, penulis = ?, penerbit = ?, harga_buku = ?, stok_buku = ? 
                    WHERE id_buku = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssiii", $judul_buku, $penulis, $penerbit, $harga_buku, $stok_buku, $id_buku);

    if ($updateStmt->execute()) {
        header("Location: stokBuku.php"); // Redirect setelah berhasil
        exit();
    } else {
        echo "Error: " . $updateStmt->error;
    }
    $updateStmt->close(); // Tutup statement
}

// Eksekusi query untuk menampilkan data
$stmt->execute();
$result = $stmt->get_result();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css">

    <style>
        /* Tambahkan CSS sesuai kebutuhan */
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


        .btn {
            background: linear-gradient(to right, #e58562, #bb724b);
            text-decoration: none;
            border: none;
            font-size: 15px;
            color: white;
            padding: 5px;
            border-radius: 35px / 30px;
        }

        .btn:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
            text-decoration: none;
            background: linear-gradient(to right, #bb724b, #e58562);
        }

        .btna {
            background: linear-gradient(to right, #e58562, #bb724b);
            color: white;
            border: none;
            text-decoration: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            width: 100px;
            border-radius: 35px / 30px;
            /* Rounded button */
            margin-left: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Shadow for button */
            transition: all 0.3s ease;
        }

        .btna:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            /* Increased shadow on hover */
            transform: translateY(-3px);
            /* Makes the button appear to rise */
            background: linear-gradient(to right, #bb724b, #e58562);
        }

        .btnplus {
            background: linear-gradient(to right, #e58562, #bb724b);
            color: white;
            border: none;
            text-decoration: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            width: 50px;
            border-radius: 15px / 15px;
            /* Rounded button */
            margin-left: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Shadow for button */
            transition: all 0.3s ease;
        }

        .btnplus:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            /* Increased shadow on hover */
            transform: translateY(-3px);
            /* Makes the button appear to rise */
            background: linear-gradient(to right, #bb724b, #e58562);
        }

        /* Modal CSS */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        form {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .inpt[type="text"],
        .inpt[type="password"],
        .slct {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .inpt[type="text"]:focus,
        .inpt[type="password"]:focus,
        .slct:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
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
            <li><a href="#" id="openModalButton">Tambah Buku</a></li>
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
    <!-- Modal untuk Tambah Buku -->
    <div id="addBookModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Tambah Buku</h2>
            <form method="POST" action="stokBuku.php">
                <input class="inpt" type="hidden" name="id_buku" value="<?= uniqid('buku_'); ?>">

                <div>
                    <label for="judul_buku">Judul Buku:</label>
                    <input class="inpt" type="text" id="judul_buku" name="judul_buku" required>
                </div>

                <div>
                    <label for="penulis">Penulis:</label>
                    <input class="inpt" type="text" id="penulis" name="penulis" required>
                </div>

                <div>
                    <label for="penerbit">Penerbit:</label>
                    <input class="inpt" type="text" id="penerbit" name="penerbit" required>
                </div>

                <div>
                    <label for="harga_buku">Harga Buku:</label>
                    <input class="inpt" type="number" id="harga_buku" name="harga_buku" required>
                </div>

                <div>
                    <label for="stok_buku">Stok Buku:</label>
                    <input class="inpt" type="number" id="stok_buku" name="stok_buku" required>
                </div>

                <div>
                    <button type="submit" class="btn" name="submit">Tambah Buku</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Edit Buku -->
    <div id="editBookModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Buku</h2>
            <form method="POST" action="stokBuku.php">
                <input class="inpt" type="hidden" name="id_buku_edit" id="id_buku_edit">

                <div>
                    <label for="judul_buku_edit">Judul Buku:</label>
                    <input class="inpt" type="text" id="judul_buku_edit" name="judul_buku_edit" required>
                </div>

                <div>
                    <label for="penulis_edit">Penulis:</label>
                    <input class="inpt" type="text" id="penulis_edit" name="penulis_edit" required>
                </div>

                <div>
                    <label for="penerbit_edit">Penerbit:</label>
                    <input class="inpt" type="text" id="penerbit_edit" name="penerbit_edit" required>
                </div>

                <div>
                    <label for="harga_buku_edit">Harga Buku:</label>
                    <input class="inpt" type="number" id="harga_buku_edit" name="harga_buku_edit" required>
                </div>

                <div>
                    <label for="stok_buku_edit">Stok Buku:</label>
                    <input class="inpt" type="number" id="stok_buku_edit" name="stok_buku_edit" required>
                </div>

                <div>
                    <button type="submit" class="btn" name="submit_edit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Buku -->
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
                        <td><?php echo $row['id_buku']; ?></td>
                        <td><?php echo $row['judul_buku']; ?></td>
                        <td><?php echo $row['penulis']; ?></td>
                        <td><?php echo $row['penerbit']; ?></td>
                        <td><?php echo $row['harga_buku']; ?></td>
                        <td><?php echo $row['stok_buku']; ?></td>
                        <td>
                            <a href="#" class="btn edit-button" 
                               data-id="<?php echo $row['id_buku']; ?>">Edit</a>
                            <a href="stokBuku.php?delete=<?php echo $row['id_buku']; ?>" 
                               class="btn delete-button">Hapus</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/responsive.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Inisialisasi DataTables untuk tabel buku
        $('#bookTable').DataTable();
    });

    $(document).ready(function() {
        // SweetAlert untuk konfirmasi penghapusan
        $('.delete-button').on('click', function(e) {
            e.preventDefault();
            var bookId = $(this).data('id'); // Ambil ID Buku

            Swal.fire({
                title: 'Anda yakin?',
                text: "Data buku akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke URL untuk menghapus data buku
                    window.location.href = "stokBuku.php?delete=" + bookId;
                }
            });
        });

        // SweetAlert setelah penghapusan berhasil
        <?php if (isset($_SESSION['delete_success'])): ?>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Buku telah dihapus.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        <?php unset($_SESSION['delete_success']); endif; ?>
    });

    // Script untuk modal tambah buku
    document.getElementById('openModalButton').addEventListener('click', function() {
        document.getElementById('addBookModal').style.display = 'block';
    });

    document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('addBookModal').style.display = 'none';
    });

    // Tutup modal jika klik di luar konten modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('addBookModal')) {
            document.getElementById('addBookModal').style.display = 'none';
        }
    };

    $(document).ready(function() {
        // Tampilkan modal tambah buku
        $('#openModalButton').on('click', function() {
            $('#addBookModal').show();
        });

        // Tampilkan modal edit buku
        $('.edit-button').on('click', function() {
            // Dapatkan data buku dari baris tabel
            const row = $(this).closest('tr');
            const idBuku = row.find('td:nth-child(1)').text();
            const judulBuku = row.find('td:nth-child(2)').text();
            const penulis = row.find('td:nth-child(3)').text();
            const penerbit = row.find('td:nth-child(4)').text();
            const hargaBuku = row.find('td:nth-child(5)').text();
            const stokBuku = row.find('td:nth-child(6)').text();

            // Isi data di modal edit buku
            $('#id_buku_edit').val(idBuku);
            $('#judul_buku_edit').val(judulBuku);
            $('#penulis_edit').val(penulis);
            $('#penerbit_edit').val(penerbit);
            $('#harga_buku_edit').val(hargaBuku);
            $('#stok_buku_edit').val(stokBuku);

            // Tampilkan modal edit
            $('#editBookModal').show();
        });

        // Tutup modal tambah buku
        $('#closeModal').on('click', function() {
            $('#addBookModal').hide();
        });

        // Tutup modal edit buku
        $('#closeEditModal').on('click', function() {
            $('#editBookModal').hide();
        });
    });
</script>

</body>

</html>

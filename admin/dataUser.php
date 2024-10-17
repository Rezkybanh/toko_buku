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
if (isset($_GET['nama_pengguna'])) {
    $search = $_GET['nama_pengguna'];
}

// Siapkan pernyataan SQL untuk pencarian atau menampilkan semua data
if (!empty($search)) {
    $query = "SELECT * FROM user WHERE nama_pengguna LIKE ?";
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $searchTerm);
} else {
    $query = "SELECT * FROM user";
    $stmt = $conn->prepare($query);
}

// Hapus pengguna jika tombol delete ditekan
if (isset($_GET['delete'])) {
    $id_pengguna = $_GET['delete'];

    // Query untuk menghapus data
    $deleteQuery = "DELETE FROM user WHERE id_pengguna = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $id_pengguna); // ID bertipe string

    if ($deleteStmt->execute()) {
        $_SESSION['delete_success'] = true; // Simpan status penghapusan
        header("Location: dataUser.php"); // Redirect kembali ke halaman
        exit();
    } else {
        echo "Gagal menghapus pengguna: " . $deleteStmt->error;
    }
    $deleteStmt->close(); // Tutup statement
}

// Tambah pengguna baru jika form submit ditekan
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $id_pengguna = $_POST['id_pengguna'];
    $nama_pengguna = $_POST['nama_pengguna'];
    $kata_sandi = $_POST['kata_sandi']; // Enkripsi kata sandi
    $peran = $_POST['peran'];

    // Query untuk insert data
    $insertQuery = "INSERT INTO user (id_pengguna, nama_pengguna, kata_sandi, peran) 
                    VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ssss", $id_pengguna, $nama_pengguna, $kata_sandi, $peran);

    if ($insertStmt->execute()) {
        // Berhasil disimpan, redirect atau beri pesan
    } else {
        echo "Error: " . $insertStmt->error;
    }
    $insertStmt->close(); // Tutup statement
}

// Proses edit pengguna
if (isset($_POST['submit_edit'])) {
    // Ambil data dari form edit
    $id_pengguna = $_POST['id_pengguna_edit'];
    $nama_pengguna = $_POST['nama_pengguna_edit'];
    $kata_sandi = $_POST['kata_sandi_edit'];
    $peran = $_POST['peran_edit'];

    // Query untuk update data
    $updateQuery = "UPDATE user SET nama_pengguna = ?, kata_sandi = ?, peran = ? WHERE id_pengguna = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssss", $nama_pengguna, $kata_sandi, $peran, $id_pengguna);

    if ($updateStmt->execute()) {
        // Berhasil diupdate, redirect
        header("Location: dataUser.php");
        exit();
    } else {
        echo "Error: " . $updateStmt->error;
    }
    $updateStmt->close(); // Tutup statement
}

// Eksekusi query dan ambil hasil
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
    <title>Data User</title>
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
    cursor: pointer; /* Tambahkan cursor pointer */
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
    margin-left: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.btna:hover {
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(-3px);
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
    margin-left: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.btnplus:hover {
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(-3px);
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
                <li><a href="#" id="openModalButton">Tambah User</a></li>
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
        <!-- Modal untuk Tambah Pengguna -->
        <div id="addUserModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2>Tambah Pengguna</h2>
                <form method="POST" action="dataUser.php">
                    <input class="inpt" type="hidden" name="id_pengguna" value="<?= uniqid('user_'); ?>">

                    <div>
                        <label for="nama_pengguna">Nama Pengguna:</label>
                        <input class="inpt" type="text" id="nama_pengguna" name="nama_pengguna" required>
                    </div>

                    <div>
                        <label for="kata_sandi">Kata Sandi:</label>
                        <input class="inpt" type="password" id="kata_sandi" name="kata_sandi" required>
                    </div>

                    <div>
                        <label for="peran">Peran:</label>
                        <select class="slct" id="peran" name="peran" required>
                            <option value="pembeli">Pembeli</option>
                            <option value="pemilik">Pemilik</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn" name="submit">Tambah Pengguna</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal untuk Edit Pengguna -->
        <div id="editUserModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" id="closeEditModal">&times;</span>
                <h2>Edit Pengguna</h2>
                <form method="POST" action="dataUser.php">
                    <input class="inpt" type="hidden" name="id_pengguna_edit" id="id_pengguna_edit">

                    <div>
                        <label for="nama_pengguna_edit">Nama Pengguna:</label>
                        <input class="inpt" type="text" id="nama_pengguna_edit" name="nama_pengguna_edit" required>
                    </div>

                    <div>
                        <label for="kata_sandi_edit">Kata Sandi:</label>
                        <input class="inpt" type="password" id="kata_sandi_edit" name="kata_sandi_edit" required>
                    </div>

                    <div>
                        <label for="peran_edit">Peran:</label>
                        <select class="slct" id="peran_edit" name="peran_edit" required>
                            <option value="pembeli">Pembeli</option>
                            <option value="pemilik">Pemilik</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn" name="submit_edit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="table-container">
            <table id="userTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Peran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                            <td><?php echo $row['id_pengguna']; ?></td>
                            <td><?php echo $row['nama_pengguna']; ?></td>
                            <td><?php echo $row['kata_sandi']; ?></td>
                            <td><?php echo $row['peran']; ?></td>
                            <td>
                                <button class="btn edit-button">Edit</button>
                                <button data-id="<?php echo $row['id_pengguna']; ?>" class="btn delete-button">Hapus</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#userTable').DataTable();

            // SweetAlert untuk konfirmasi penghapusan
            $('.delete-button').on('click', function(e) {
                e.preventDefault();
                var userId = $(this).data('id');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Data pengguna akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect ke URL untuk menghapus data
                        window.location.href = "dataUser.php?delete=" + userId;
                    }
                });
            });

            // SweetAlert setelah penghapusan berhasil
            <?php if (isset($_SESSION['delete_success'])): ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Pengguna telah dihapus.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php unset($_SESSION['delete_success']);
            endif; ?>

            // Script untuk modal
            document.getElementById('openModalButton').addEventListener('click', function() {
                document.getElementById('addUserModal').style.display = 'block';
            });

            document.getElementById('closeModal').addEventListener('click', function() {
                document.getElementById('addUserModal').style.display = 'none';
            });

            // Tutup modal jika klik di luar konten modal
            window.onclick = function(event) {
                if (event.target == document.getElementById('addUserModal')) {
                    document.getElementById('addUserModal').style.display = 'none';
                }
            }

            $(document).ready(function() {
                // Tampilkan modal tambah pengguna
                $('#openModalButton').on('click', function() {
                    $('#addUserModal').show();
                });

                // Tampilkan modal edit pengguna
                $('.edit-button').on('click', function() {
                    // Dapatkan data pengguna dari baris tabel
                    const row = $(this).closest('tr');
                    const idPengguna = row.find('td:nth-child(1)').text();
                    const namaPengguna = row.find('td:nth-child(2)').text();
                    const kataSandi = row.find('td:nth-child(3)').text();
                    const peran = row.find('td:nth-child(4)').text();

                    // Isi data di modal edit
                    $('#id_pengguna_edit').val(idPengguna);
                    $('#nama_pengguna_edit').val(namaPengguna);
                    $('#kata_sandi_edit').val(kataSandi);
                    $('#peran_edit').val(peran);

                    // Tampilkan modal edit
                    $('#editUserModal').show();
                });

                // Tutup modal tambah pengguna
                $('#closeModal').on('click', function() {
                    $('#addUserModal').hide();
                });

                // Tutup modal edit pengguna
                $('#closeEditModal').on('click', function() {
                    $('#editUserModal').hide();
                });
            });
        });
    </script>

</body>

</html>
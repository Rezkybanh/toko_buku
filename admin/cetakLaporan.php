<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
require '../config/koneksi.php'; 
require '../vendor/autoload.php'; 
use Dompdf\Dompdf;

// Ambil data dari tabel user, buku, dan transaksi
$queryUser = "SELECT id_pengguna, nama_pengguna, kata_sandi, peran FROM user";
$queryBuku = "SELECT id_buku, judul_buku, penulis, penerbit, harga_buku, stok_buku FROM buku";
$queryTransaksi = "
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

$resultUser = mysqli_query($conn, $queryUser);
$resultBuku = mysqli_query($conn, $queryBuku);
$resultTransaksi = mysqli_query($conn, $queryTransaksi);

// Mulai buffering output HTML
ob_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data User, Buku, dan Transaksi</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Data User</h2>
    <table>
        <thead>
            <tr>
                <th>ID Pengguna</th>
                <th>Nama Pengguna</th>
                <th>Kata Sandi</th>
                <th>Peran</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultUser)) { ?>
            <tr>
                <td><?= $row['id_pengguna'] ?></td>
                <td><?= $row['nama_pengguna'] ?></td>
                <td><?= $row['kata_sandi'] ?></td>
                <td><?= $row['peran'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Data Buku</h2>
    <table>
        <thead>
            <tr>
                <th>ID Buku</th>
                <th>Judul Buku</th>
                <th>Penulis</th>
                <th>Penerbit</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultBuku)) { ?>
            <tr>
                <td><?= $row['id_buku'] ?></td>
                <td><?= $row['judul_buku'] ?></td>
                <td><?= $row['penulis'] ?></td>
                <td><?= $row['penerbit'] ?></td>
                <td><?= $row['harga_buku'] ?></td>
                <td><?= $row['stok_buku'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Data Transaksi</h2>
    <table>
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
            <?php while ($row = mysqli_fetch_assoc($resultTransaksi)) { ?>
            <tr>
                <td><?= $row['id_transaksi'] ?></td>
                <td><?= $row['nama_pengguna'] ?></td>
                <td><?= $row['judul_buku'] ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td><?= $row['total_harga'] ?></td>
                <td><?= $row['tanggal_transaksi'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

<?php
$html = ob_get_clean();

// Inisialisasi Dompdf dan cetak PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporanAdmin.pdf", ["Attachment" => true]);
?>

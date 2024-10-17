<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
require '../config/koneksi.php'; 
require '../vendor/autoload.php'; 
use Dompdf\Dompdf;

// Ambil data buku dan transaksi
$queryBuku = "SELECT * FROM buku";
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
$resultBuku = mysqli_query($conn, $queryBuku);
$resultTransaksi = mysqli_query($conn, $queryTransaksi);

// Mulai buffering output HTML
ob_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi dan Stok Buku</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Laporan Stok Buku</h2>
    <table>
        <tr>
            <th>ID Buku</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Stok</th>
            <th>Harga</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($resultBuku)) { ?>
        <tr>
            <td><?= $row['id_buku'] ?></td>
            <td><?= $row['judul_buku'] ?></td>
            <td><?= $row['penulis'] ?></td>
            <td><?= $row['penerbit'] ?></td>
            <td><?= $row['stok_buku'] ?></td>
            <td><?= $row['harga_buku'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <h2>Laporan Transaksi</h2>
    <table>
        <tr>
            <th>ID Transaksi</th>
            <th>Nama Pembeli</th>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal Transaksi</th>
        </tr>
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
$dompdf->stream("laporanOwner.pdf", ["Attachment" => true]);
?>

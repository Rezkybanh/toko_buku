<?php
// Koneksi ke database
$host = 'localhost';  // Sesuaikan dengan host database Anda
$dbname = 'toko_buku';  // Nama database Anda
$username = 'root';  // Sesuaikan dengan username Anda
$password = '';  // Sesuaikan dengan password Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil parameter pencarian dari URL
    if (isset($_GET['judul_buku'])) {
        $judul_buku = $_GET['judul_buku'];

        // Query untuk mencari buku berdasarkan judul_buku
        $sql = "SELECT * FROM buku WHERE judul_buku LIKE :judul_buku";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['judul_buku' => '%' . $judul_buku . '%']);
        $hasil = $stmt->fetchAll();

        if ($hasil) {
            echo "<h2>Hasil Pencarian:</h2>";
            echo "<ul>";
            foreach ($hasil as $buku) {
                echo "<li>Judul: " . $buku['judul_buku'] . " | Penulis: " . $buku['penulis'] . " | Penerbit: " . $buku['penerbit'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Tidak ada buku yang ditemukan.</p>";
        }
    } else {
        echo "<p>Masukkan judul buku untuk mencari.</p>";
    }
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?>

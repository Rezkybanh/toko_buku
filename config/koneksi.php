<?php
$host = '127.0.0.1'; 
$user = 'root'; 
$password = ''; 
$dbname = 'toko_buku';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

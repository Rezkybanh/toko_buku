<?php
session_start(); // Memulai session

// Menyimpan pesan sukses logout ke dalam session
$_SESSION['logout_success'] = 'Berhasil logout';

// Redirect to index.php
header('Location: ../index.php');
exit;
?>

<?php
session_start();
include 'config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($username) || empty($password) || empty($role)) {
        $_SESSION['register_error'] = 'Semua field harus diisi!';
        header('Location: register.php');
        exit();
    }

    // Cek apakah username sudah ada
    $checkQuery = "SELECT * FROM user WHERE nama_pengguna = ?";
    $stmt = $conn->prepare($checkQuery);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Username sudah terdaftar!';
        header('Location: register.php');
        exit();
    }

    // Hashing password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data pengguna baru
    $query = "INSERT INTO user (nama_pengguna, kata_sandi, peran) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($stmt->execute()) {
        // Registrasi sukses
        $_SESSION['register_success'] = 'Registrasi berhasil! Silakan login.';
        header('Location: index.php');
    } else {
        // Kesalahan saat insert
        $_SESSION['register_error'] = 'Terjadi kesalahan saat registrasi. Coba lagi.';
        header('Location: register.php');
    }
    exit();
}
?>


<!doctype html>
<html lang="en">
<head>
    <title>Registrasi Toko Buku</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="img js-fullheight" style="background-image: url(https://img.freepik.com/premium-photo/row-books-shelf-with-books-top_1083198-3577.jpg?w=740); overflow: hidden;">

    <section class="ftco-section">
        <div class="container">
            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="alert alert-danger alert-full-width" role="alert" id="alert-danger">
                    <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Registrasi Toko Buku</h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="login-wrap p-0">
                        <h3 class="mb-4 text-center">Daftar Sekarang!</h3>
                        <form action="register.php" method="POST" class="signin-form">
                            <div class="form-group">
                                <input type="text" class="form-control" name="username" placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                            </div>
                            <div class="form-group">
                                <select name="role" class="form-control" required>
                                    <option value="" style="color: black;">Role</option>
                                    <option value="pembeli" style="color: black;">Pembeli</option>
                                    <option value="pemilik" style="color: black;">Pemilik</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="form-control btn btn-primary submit px-3">Daftar</button>
                            </div>
                        </form>
                        <p>Sudah punya akun? <a href="index.php">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        setTimeout(function() {
            var dangerAlert = document.getElementById('alert-danger');
            if (dangerAlert) {
                dangerAlert.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>

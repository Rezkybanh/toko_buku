<?php
session_start();
include 'config/koneksi.php';

$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$logout_success = isset($_SESSION['logout_success']) ? $_SESSION['logout_success'] : '';
$register_success = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : '';

// Hapus pesan login error dari session setelah ditampilkan
if ($login_error) {
    unset($_SESSION['login_error']);
}

// Hapus pesan logout success dari session setelah ditampilkan
if ($logout_success) {
    unset($_SESSION['logout_success']);
}

if ($register_success) {
    unset($_SESSION['register_success']);
}

// Check if the database connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    // Mengambil input dari form login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi dasar untuk mengecek apakah username dan password diisi
    if (empty($username) || empty($password)) {
        echo "<div class='alert alert-danger' role='alert'>Username dan password harus diisi!</div>";
        exit();
    }

    // Query untuk memeriksa apakah username dan password cocok dengan database
    $query = "SELECT * FROM user WHERE nama_pengguna = ? AND kata_sandi = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password); // Gunakan bind_param untuk mencegah SQL Injection
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Jika user ditemukan
    if ($user) {
        // Menyimpan data user dalam session
        $_SESSION['user'] = $user;

        // Redirect sesuai role
        if ($user['peran'] == 'admin') {
            header("Location: admin/index.php");
        } elseif ($user['peran'] == 'pembeli') {
            header("Location: pembeli/index.php");
        } elseif ($user['peran'] == 'pemilik') {
            header("Location: pemilik/index.php");
        } else {
            echo "<div class='alert alert-danger' role='alert'>Role tidak valid!</div>";
        }
    } else {
        // Login gagal
        $_SESSION['login_error'] = 'Username dan Password salah, Silahkan Login kembali';
        header('Location: index.php'); // Redirect kembali ke halaman login
        exit();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Toko Buku</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .alert-full-width {
            position: fixed; 
            top: 20px; 
            left: 0; 
            right: 0; 
            z-index: 1050; 
            margin: 0; 
        }
    </style>
</head>

<body class="img js-fullheight" style="background-image: url(https://img.freepik.com/premium-photo/row-books-shelf-with-books-top_1083198-3577.jpg?w=740);overflow: hidden;">
    <section class="ftco-section">
        <div class="container">
            <!-- Alert untuk sukses logout -->
            <?php if ($logout_success): ?>
                <div class="alert alert-success alert-full-width" role="alert" id="alert-success">
                    <?php echo $logout_success; ?>
                </div>
            <?php endif; ?>

            <?php if ($register_success): ?>
                <div class="alert alert-success alert-full-width" role="alert" id="alert-success">
                    <?php echo $register_success; ?>
                </div>
            <?php endif; ?>

            <!-- Alert untuk kesalahan login -->
            <?php if ($login_error): ?>
                <div class="alert alert-danger alert-full-width" role="alert" id="alert-danger">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Toko Buku</h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="login-wrap p-0">
                        <h3 class="mb-4 text-center">Login Terlebih Dahulu!</h3>
                        <form action="index.php" method="POST" class="signin-form">
                            <div class="form-group">
                                <input type="text" class="form-control" name="username" placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <input id="password-field" type="password" class="form-control" name="password" placeholder="Password" required>
                                <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="form-control btn btn-primary submit px-3">LOGIN</button>
                            </div>
                        </form>
                        <p>Belum punya akun? <a href="register.php">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        setTimeout(function() {
            var successAlert = document.getElementById('alert-success');
            var dangerAlert = document.getElementById('alert-danger');
            if (successAlert) {
                successAlert.style.display = 'none'; // Sembunyikan alert sukses
            }
            if (dangerAlert) {
                dangerAlert.style.display = 'none'; // Sembunyikan alert kesalahan
            }
        }, 1000); // 1000 ms = 1 detik
    </script>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>

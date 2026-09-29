<?php
session_start();

$koneksi = mysqli_connect("localhost", "root", "", "tokobaju_batik");

$swal_script = '';
$username_input = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
    $password = mysqli_real_escape_string($koneksi, $_POST['password'] ?? '');
    $username_input = $_POST['username'] ?? '';

    $login = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($login) > 0) {
        $data = mysqli_fetch_array($login);

        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        $redirect_url = 'index.php';
        if ($data['role'] == 'admin') {
            $_SESSION['admin'] = $data['username'];
            $redirect_url = 'dashboard.php';
        } elseif ($data['role'] == 'pelanggan') {
            $_SESSION['pelanggan'] = $data['username'];
            $redirect_url = 'index.php';
        }

        $swal_script = "
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, " . htmlspecialchars($data['username']) . "!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = '$redirect_url';
            });
        ";
    } else {
        
        $swal_script = "
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Username atau password salah. Periksa lagi!',
                confirmButtonColor: '#1b2a5e'
            });
        ";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Ajil Batik</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --indigo-900: #0e1839;
            --indigo-700: #1b2a5e;
            --indigo-500: #3a4f9e;
            --indigo-50: #eef1fa;
            --gold: #c9973f;
            --gold-dark: #a87a28;
            --gold-soft: #f6ecd3;
            --ink: #1c2233;
            --batik: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cg fill='none' stroke='%23c9973f' stroke-opacity='.28' stroke-width='1'%3E%3Cellipse cx='30' cy='15' rx='9' ry='15'/%3E%3Cellipse cx='30' cy='45' rx='9' ry='15'/%3E%3Cellipse cx='15' cy='30' rx='15' ry='9'/%3E%3Cellipse cx='45' cy='30' rx='15' ry='9'/%3E%3C/g%3E%3Ccircle cx='30' cy='30' r='2' fill='%23c9973f' fill-opacity='.4'/%3E%3C/svg%3E");
        }
        html, body { height: 100%; }
        body {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, sans-serif;
            background:
                linear-gradient(180deg, rgba(14,24,57,.55), rgba(14,24,57,.94)),
                var(--batik),
                var(--indigo-700);
            background-size: auto, 60px 60px, auto;
        }
        .form-signin {
            width: 100%;
            max-width: 400px;
            margin: auto;
            padding: 2.5rem 2.25rem 1.75rem;
            background: #fff;
            border-radius: 18px;
            border-top: 5px solid var(--gold);
            box-shadow: 0 24px 50px rgba(6, 12, 34, 0.45);
            text-align: center;
        }
        .form-signin img {
            display: block;
            margin: 0 auto 1rem;
            width: 92px;
            height: 92px;
            object-fit: cover;
            border-radius: 50%;
            padding: 4px;
            background: #fff;
            box-shadow: 0 0 0 3px var(--gold), 0 6px 16px rgba(14, 24, 57, 0.25);
        }
        .form-signin h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            color: var(--indigo-900);
            letter-spacing: -0.01em;
            margin-bottom: 0.25rem;
        }
        .form-signin .subtitle {
            color: #6b7590;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }
        .form-signin .form-floating { margin-bottom: 1rem; text-align: left; }
        .form-signin .form-control {
            border-radius: 10px;
            border: 1.5px solid #d9dff0;
            background-color: #f7f8fd;
            padding-top: 1.6rem;
        }
        .form-signin .form-control:focus {
            border-color: var(--indigo-500);
            background-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(58, 79, 158, 0.18);
        }
        .form-signin .form-floating > label { color: #7686a8; }
        .form-signin .form-check-input:checked {
            background-color: var(--indigo-700);
            border-color: var(--indigo-700);
        }
        .form-signin .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(201, 151, 63, 0.35);
            border-color: var(--gold);
        }
        .form-signin .form-check-label { color: #4a5670; font-size: 0.9rem; }
        .btn-masuk {
            background: var(--indigo-700);
            border: 1px solid var(--indigo-700);
            color: #fff;
            border-radius: 999px;
            font-weight: 600;
            padding: 0.7rem;
        }
        .btn-masuk:hover, .btn-masuk:focus {
            background: var(--indigo-900);
            border-color: var(--indigo-900);
            color: #fff;
        }
        .form-signin a.link {
            color: var(--indigo-700);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
        }
        .form-signin a.link:hover { color: var(--gold-dark); }
        .form-signin a:focus-visible,
        .btn-masuk:focus-visible {
            outline: 3px solid var(--gold);
            outline-offset: 2px;
        }
        .divider-ornament {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: var(--gold); margin: 1.25rem 0 0.75rem; font-size: 0.8rem;
        }
        .divider-ornament::before, .divider-ornament::after {
            content: ""; height: 1px; width: 44px; background: var(--gold); opacity: .6;
        }
        .footer-note { color: #98a1b8; font-size: 0.8rem; margin: 1rem 0 0; }
        @media (max-width: 400px) {
            .form-signin { padding: 2rem 1.5rem 1.5rem; }
        }
    </style>
</head>
<body>
    <main class="form-signin">
        <form method="post">
            <img src="img/logo_tokobatik.png" alt="Logo Ajil Batik" width="92" height="92">
            <h1 class="h3">Ajil Batik</h1>
            <p class="subtitle">Selamat datang kembali</p>

            <div class="form-floating">
                <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" value="<?= htmlspecialchars($username_input) ?>" required autofocus>
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>

            <div class="form-check text-start my-3">
                <input class="form-check-input" type="checkbox" value="remember-me" id="checkDefault">
                <label class="form-check-label" for="checkDefault">Ingat saya</label>
            </div>

            <button class="btn btn-masuk w-100" type="submit" name="login">Masuk</button>

            <div class="divider-ornament">◆</div>
            <a class="link" href="register.php">Belum punya akun? Daftar</a>
            <div class="mt-3">
                <a class="link" href="index.php" style="border-bottom-color: transparent; font-weight: 500;">&larr; Kembali ke beranda</a>
            </div>
            <p class="footer-note">&copy; <?= date('Y') ?> Ajil Batik. Elegan dan Berbudaya.</p>
        </form>
    </main>
    
    <?php if ($swal_script !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?= $swal_script ?>
        });
    </script>
    <?php endif; ?>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
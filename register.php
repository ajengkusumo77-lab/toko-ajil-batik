<?php
include 'koneksi.php';

function kolom_ada($k, $tabel,$kolom) {
    $r = mysqli_query($k, "SHOW COLUMNS FROM `$tabel` LIKE '" . mysqli_real_escape_string($k,$kolom) . "'");
    return $r && mysqli_num_rows($r) > 0;
}

$swal_script = '';
$username_input = '';$form = ['nama_lengkap' => '', 'email' => '', 'no_hp' => '', 'alamat' => ''];

if (isset($_POST['register'])) {
    $username_input = trim($_POST['username'] ?? '');
    foreach ($form as $k =>$v) { $form[$k] = trim($_POST[$k] ?? ''); }

    $username = mysqli_real_escape_string($koneksi, $username_input);$password = mysqli_real_escape_string($koneksi,$_POST['password'] ?? '');

    // Cek username sudah dipakai atau belum
    $cek = mysqli_query($koneksi, "SELECT 1 FROM tb_user WHERE username='$username' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $error_msg = 'Username sudah dipakai, silakan pilih username lain.';$swal_script = "
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal',
                text: '$error_msg',
                confirmButtonColor: '#1b2a5e'
            });
        ";
    } else {
        $kolom = ['username', 'password', 'role'];$isi   = ["'$username'", "'$password'", "'pelanggan'"];

        // Kolom data pelanggan hanya ikut disimpan jika kolomnya ada di tb_user
        foreach ($form as $k =>$v) {
            if (kolom_ada($koneksi, 'tb_user', $k)) {$kolom[] = "`$k`";
                $isi[]   = "'" . mysqli_real_escape_string($koneksi,$v) . "'";
            }
        }

        $register = mysqli_query($koneksi, "INSERT INTO tb_user (" . implode(', ', $kolom) . ") VALUES (" . implode(', ', $isi) . ")");
        if ($register) {$swal_script = "
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Akun Anda berhasil dibuat. Silakan login.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location='login.php';
                });
            ";
        } else {
            $error_msg = 'Registrasi gagal, coba lagi.';$swal_script = "
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '$error_msg',
                    confirmButtonColor: '#1b2a5e'
                });
            ";
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Ajil Batik</title>
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
        body {
            min-height: 100vh;
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
            max-width: 440px;
            margin: auto;
            padding: 2.25rem 2.25rem 1.75rem;
            background: #fff;
            border-radius: 18px;
            border-top: 5px solid var(--gold);
            box-shadow: 0 24px 50px rgba(6, 12, 34, 0.45);
            text-align: center;
        }
        .form-signin img {
            display: block;
            margin: 0 auto 0.75rem;
            width: 80px;
            height: 80px;
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
        .form-signin .subtitle { color: #6b7590; font-size: 0.95rem; margin-bottom: 1.25rem; }
        .form-signin .form-floating { margin-bottom: 0.85rem; text-align: left; }
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
        .form-label-group-title {
            text-align: left; font-size: .75rem; letter-spacing: .08em;
            text-transform: uppercase; color: var(--gold-dark); font-weight: 700;
            margin: 1.1rem 0 .6rem;
        }
        .btn-masuk {
            background: var(--indigo-700);
            border: 1px solid var(--indigo-700);
            color: #fff;
            border-radius: 999px;
            font-weight: 600;
            padding: 0.7rem;
            margin-top: 0.5rem;
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
        .btn-masuk:focus-visible { outline: 3px solid var(--gold); outline-offset: 2px; }
        .divider-ornament {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: var(--gold); margin: 1.25rem 0 0.75rem; font-size: 0.8rem;
        }
        .divider-ornament::before, .divider-ornament::after {
            content: ""; height: 1px; width: 44px; background: var(--gold); opacity: .6;
        }
        .footer-note { color: #98a1b8; font-size: 0.8rem; margin: 1rem 0 0; }
        @media (max-width: 480px) {
            .form-signin { padding: 1.75rem 1.25rem 1.25rem; margin: 0 .75rem; width: auto; }
        }
    </style>
</head>
<body>
    <main class="form-signin">
        <form method="post">
            <img src="img/logo_tokobatik.png" alt="Logo Ajil Batik" width="80" height="80">
            <h1 class="h3">Buat Akun Baru</h1>
            <p class="subtitle">Bergabung dengan Ajil Batik dan mulai belanja</p>

            <div class="form-label-group-title" style="margin-top:0;">Akun</div>
            <div class="form-floating">
                <input type="text" name="username" class="form-control" id="username" placeholder="Username" value="<?= htmlspecialchars($username_input) ?>" required autofocus>
                <label for="username">Username</label>
            </div>
            <div class="form-floating">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <div class="form-label-group-title">Data Diri</div>
            <div class="form-floating">
                <input type="text" name="nama_lengkap" class="form-control" id="nama_lengkap" placeholder="Nama lengkap" value="<?= htmlspecialchars($form['nama_lengkap']) ?>" required>
                <label for="nama_lengkap">Nama Lengkap</label>
            </div>
            <div class="form-floating">
                <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?= htmlspecialchars($form['email']) ?>">
                <label for="email">Email (opsional)</label>
            </div>
            <div class="form-floating">
                <input type="tel" name="no_hp" class="form-control" id="no_hp" placeholder="No. HP" inputmode="numeric" pattern="[0-9+ ]{8,16}" title="Isi nomor HP dengan angka, 8 sampai 16 digit" value="<?= htmlspecialchars($form['no_hp']) ?>" required>
                <label for="no_hp">No. HP</label>
            </div>
            <div class="form-floating">
                <textarea name="alamat" class="form-control" id="alamat" placeholder="Alamat" style="height: 96px;" required><?= htmlspecialchars($form['alamat']) ?></textarea>
                <label for="alamat">Alamat Lengkap</label>
            </div>

            <button class="btn btn-masuk w-100" type="submit" name="register">Daftar Sekarang</button>

            <div class="divider-ornament">◆</div>
            <a class="link" href="login.php">Sudah punya akun? Masuk</a>
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
<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$id = $_GET['id'] ?? '';
$id_aman = mysqli_real_escape_string($koneksi, $id);
$hasil = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tb_produk p
    LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori WHERE p.id = '$id_aman'");
$data = $hasil ? mysqli_fetch_assoc($hasil) : null;

if (!$data) {
    header("Location: index.php");
    exit;
}

$nama      = $data['nama'];
$harga     = $data['harga'];
$deskripsi = $data['deskripsi'];
$foto      = $data['foto'] ?? $data['poto'] ?? 'default.jpg';
$stok      = (int)$data['stok'];
$kategori  = $data['nama_kategori'] ?? '-';

// Produk terkait dari kategori yang sama
$terkait = mysqli_query($koneksi, "SELECT * FROM tb_produk
    WHERE id_kategori = '" . mysqli_real_escape_string($koneksi, $data['id_kategori']) . "' AND id != '$id_aman'
    ORDER BY id DESC LIMIT 3");

$jml_keranjang = isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($nama) ?> - Ajil Batik</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo-900: #0e1839;
            --indigo-700: #1b2a5e;
            --indigo-500: #3a4f9e;
            --indigo-50: #eef1fa;
            --gold: #c9973f;
            --gold-dark: #a87a28;
            --gold-soft: #f6ecd3;
            --paper: #f5f6fa;
            --ink: #1c2233;
            --batik: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cg fill='none' stroke='%23c9973f' stroke-opacity='.28' stroke-width='1'%3E%3Cellipse cx='30' cy='15' rx='9' ry='15'/%3E%3Cellipse cx='30' cy='45' rx='9' ry='15'/%3E%3Cellipse cx='15' cy='30' rx='15' ry='9'/%3E%3Cellipse cx='45' cy='30' rx='15' ry='9'/%3E%3C/g%3E%3Ccircle cx='30' cy='30' r='2' fill='%23c9973f' fill-opacity='.4'/%3E%3C/svg%3E");
        }
        body {
            background-color: var(--paper);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, sans-serif;
            line-height: 1.65;
        }
        h1, h2, h3, h4, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        a:focus-visible, button:focus-visible, .form-control:focus-visible {
            outline: 3px solid var(--gold); outline-offset: 2px;
        }

        /* NAVBAR (sama seperti index.php) */
        .navbar-custom { background: var(--indigo-900) !important; border-bottom: 3px solid var(--gold); }
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.78); font-weight: 500; font-size: 0.93rem;
            padding: 8px 14px; border-bottom: 2px solid transparent;
        }
        .navbar-custom .nav-link:hover { color: #fff; }
        .navbar-custom .nav-link.active { color: #fff; border-bottom-color: var(--gold); }
        .search-form .form-control { border-radius: 999px 0 0 999px; border: none; font-size: 0.9rem; min-width: 200px; padding-left: 18px; }
        .search-form .btn { border-radius: 0 999px 999px 0; background: var(--gold); border-color: var(--gold); color: var(--indigo-900) !important; }
        .search-form .btn:hover { background: var(--gold-dark); border-color: var(--gold-dark); }
        .profile-link { display: flex; align-items: center; gap: 8px; color: white; text-decoration: none; font-size: 0.9rem; }
        .profile-avatar {
            width: 32px; height: 32px; border-radius: 50%; background: var(--gold); color: var(--indigo-900);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;
        }

        /* BREADCRUMB */
        .breadcrumb-wrap { background: #fff; border-bottom: 1px solid #e3e7f3; }
        .breadcrumb { margin: 0; font-size: .85rem; }
        .breadcrumb a { color: var(--indigo-700); text-decoration: none; font-weight: 500; }
        .breadcrumb a:hover { color: var(--gold-dark); }
        .breadcrumb-item.active { color: #8a93ad; }

        /* DETAIL PRODUK */
        .detail-section { background: #fff; }
        .foto-wrap {
            background:
                linear-gradient(180deg, rgba(14,24,57,.06), rgba(14,24,57,.02)),
                var(--batik);
            background-size: auto, 46px 46px;
            border-radius: 20px;
            border: 1px solid #e3e7f3;
            padding: 28px;
            display: flex; align-items: center; justify-content: center;
            min-height: 380px;
        }
        .foto-wrap img {
            max-width: 100%; max-height: 420px; object-fit: contain;
            filter: drop-shadow(0 18px 30px rgba(14,24,57,.18));
        }
        .badge-kat { background: var(--gold-soft); color: var(--gold-dark); font-weight: 600; padding: 5px 12px; border-radius: 999px; font-size: .8rem; }
        .badge-stok-ok { background:#e6f4ea; color:#1e7a3c; font-weight:600; padding:5px 12px; border-radius:999px; font-size:.8rem; }
        .badge-stok-sedikit { background:#fdecea; color:#b3261e; font-weight:600; padding:5px 12px; border-radius:999px; font-size:.8rem; }
        .badge-stok-habis { background:#e9ecf3; color:#5b6480; font-weight:600; padding:5px 12px; border-radius:999px; font-size:.8rem; }
        .produk-judul { font-weight: 700; color: var(--indigo-900); }
        .produk-harga { color: var(--indigo-700); font-weight: 700; font-size: 2rem; }
        .produk-deskripsi { color: #4a5468; }
        .ornament-kecil { color: var(--gold); letter-spacing: .2em; font-size: .8rem; margin: 4px 0 14px; }

        .qty-box { display: inline-flex; align-items: center; border: 1.5px solid #d9dff0; border-radius: 999px; overflow: hidden; }
        .qty-box button {
            width: 42px; height: 42px; border: none; background: var(--indigo-50); color: var(--indigo-900);
            font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
        }
        .qty-box button:hover { background: var(--gold-soft); }
        .qty-box input {
            width: 56px; text-align: center; border: none; font-weight: 700; color: var(--indigo-900);
            -moz-appearance: textfield;
        }
        .qty-box input::-webkit-outer-spin-button, .qty-box input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        .btn-gold { background: var(--gold); border-color: var(--gold); color: var(--indigo-900); font-weight: 700; }
        .btn-gold:hover { background: var(--gold-dark); border-color: var(--gold-dark); color: #fff; }
        .btn-gold:disabled { background: #d8dce8; border-color: #d8dce8; color: #8a93ad; }
        .btn-outline-primary { color: var(--indigo-700); border-color: var(--indigo-700); }
        .btn-outline-primary:hover { background: var(--indigo-700); border-color: var(--indigo-700); color: #fff; }

        .info-strip { border-top: 1px dashed #d9dff0; margin-top: 1.75rem; padding-top: 1.25rem; }
        .info-strip .item { display: flex; align-items: flex-start; gap: 10px; font-size: .88rem; color: #4a5468; }
        .info-strip .ico { font-size: 1.2rem; }

        /* PRODUK TERKAIT */
        .terkait-section { background: var(--indigo-50); }
        .card-produk {
            border: 1px solid #e3e7f3; border-radius: 14px; overflow: hidden; background: #fff;
            transition: transform 0.25s ease, box-shadow 0.25s ease; height: 100%;
        }
        .card-produk:hover { transform: translateY(-5px); box-shadow: 0 16px 34px rgba(14, 24, 57, 0.14); }
        .card-produk img { width: 100%; height: 200px; object-fit: contain; background: var(--indigo-50); padding: 10px; border-bottom: 3px solid var(--gold-soft); }

        .footer-batik { background: var(--indigo-900); color: rgba(255,255,255,.75); border-top: 3px solid var(--gold); }
        .footer-batik a { color: #f0c46c; }

        @media (max-width: 767.98px) {
            .foto-wrap { min-height: 280px; padding: 18px; }
            .produk-harga { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm py-2">
        <div class="container">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                <span style="font-size: 1.5rem;">👘</span>
                <strong class="fs-4">Ajil Batik</strong>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto me-2 d-lg-none">
                <a href="index.php?view=keranjang" class="btn btn-light position-relative btn-sm rounded-pill px-3 fw-semibold text-primary">
                    🛒
                    <?php if ($jml_keranjang > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;"><?= $jml_keranjang ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-lg-3 mb-2 mb-lg-0 gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#katalog">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php#produk">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#tentang">Tentang Kami</a></li>
                </ul>

                <form class="search-form d-flex mx-lg-auto mb-2 mb-lg-0" action="index.php" method="get" role="search">
                    <input class="form-control" type="search" name="q" placeholder="Cari batik..." aria-label="Cari produk">
                    <button class="btn btn-light text-primary fw-semibold px-3" type="submit">🔍</button>
                </form>

                <div class="d-flex align-items-center gap-3 ms-lg-3">
                    <a href="index.php?view=keranjang" class="btn btn-light position-relative btn-sm rounded-pill px-3 fw-semibold text-primary d-none d-lg-flex align-items-center gap-1">
                        🛒 <span>Keranjang</span>
                        <?php if ($jml_keranjang > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;"><?= $jml_keranjang ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['username'])): ?>
                        <div class="dropdown">
                            <a href="#" class="profile-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php elseif (isset($_SESSION['user'])): ?>
                        <span class="text-white ms-2 small d-none d-md-inline">Halo, <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pelanggan') ?></span>
                        <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Masuk</a>
                        <a href="register.php" class="btn btn-light text-primary btn-sm rounded-pill px-3 fw-semibold">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- BREADCRUMB -->
<div class="breadcrumb-wrap py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?kategori=<?= urlencode($data['id_kategori']) ?>#produk"><?= htmlspecialchars($kategori) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($nama) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- DETAIL PRODUK -->
<main class="detail-section py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-6">
                <div class="foto-wrap">
                    <img src="img/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($nama) ?>">
                </div>
            </div>

            <div class="col-lg-6">
                <span class="badge-kat"><?= htmlspecialchars($kategori) ?></span>
                <h1 class="produk-judul mt-3 mb-1"><?= htmlspecialchars($nama) ?></h1>
                <div class="ornament-kecil">◆ ◆ ◆</div>

                <div class="produk-harga mb-3">Rp <?= number_format($harga, 0, ',', '.') ?></div>

                <div class="mb-4">
                    <?php if ($stok <= 0): ?>
                        <span class="badge-stok-habis">Stok Habis</span>
                    <?php elseif ($stok <= 5): ?>
                        <span class="badge-stok-sedikit">Stok tinggal <?= $stok ?> pcs</span>
                    <?php else: ?>
                        <span class="badge-stok-ok">Stok tersedia: <?= $stok ?> pcs</span>
                    <?php endif; ?>
                </div>

                <p class="produk-deskripsi"><?= nl2br(htmlspecialchars($deskripsi)) ?></p>

                <?php if ($stok > 0): ?>
                    <form method="get" action="index.php" class="d-flex flex-wrap align-items-center gap-3 mt-4">
                        <input type="hidden" name="aksi" value="tambah">
                        <input type="hidden" name="id" value="<?= $data['id'] ?>">

                        <div class="qty-box">
                            <button type="button" onclick="ubahJumlah(-1)" aria-label="Kurangi jumlah">&minus;</button>
                            <input type="number" name="jumlah" id="jumlahInput" value="1" min="1" max="<?= $stok ?>" aria-label="Jumlah">
                            <button type="button" onclick="ubahJumlah(1)" aria-label="Tambah jumlah">+</button>
                        </div>

                        <button type="submit" class="btn btn-gold rounded-pill px-4 py-2">🛒 Tambah ke Keranjang</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-gold rounded-pill px-4 py-2 mt-4" disabled>Stok Habis</button>
                <?php endif; ?>

                <div class="info-strip d-flex flex-column gap-2">
                    <div class="item"><span class="ico">🧵</span> Bahan batik pilihan, adem dan nyaman dipakai.</div>
                    <div class="item"><span class="ico">📦</span> Dikemas rapi sebelum dikirim ke alamatmu.</div>
                    <div class="item"><span class="ico">↩️</span> Ada kendala pesanan? Hubungi admin Ajil Batik.</div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- PRODUK TERKAIT -->
<?php if ($terkait && mysqli_num_rows($terkait) > 0): ?>
<section class="terkait-section py-5">
    <div class="container">
        <h3 class="fw-bold text-dark mb-4">Produk Sejenis Lainnya</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php while ($t = mysqli_fetch_assoc($terkait)): ?>
                <div class="col">
                    <div class="card card-produk shadow-sm">
                        <img src="img/<?= htmlspecialchars($t['foto'] ?? $t['poto'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($t['nama']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($t['nama']) ?></h6>
                            <p class="text-primary fw-bold mb-3">Rp <?= number_format($t['harga'], 0, ',', '.') ?></p>
                            <a href="detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill mt-auto">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<footer class="footer-batik py-5">
    <div class="container text-center">
        <p class="mb-1 fw-semibold text-white">&copy; <?= date('Y') ?> Ajil Batik. Elegan dan Berbudaya.</p>
        <p class="mb-0"><a href="index.php" class="text-decoration-none">&larr; Kembali ke Beranda</a></p>
    </div>
</footer>

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function ubahJumlah(langkah) {
        const input = document.getElementById('jumlahInput');
        const maks = parseInt(input.max || '999', 10);
        let nilai = parseInt(input.value || '1', 10) + langkah;
        if (nilai < 1) nilai = 1;
        if (nilai > maks) nilai = maks;
        input.value = nilai;
    }
</script>
</body>
</html>
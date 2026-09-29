<?php
session_start();
include 'koneksi.php';


if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


if (isset($_GET['aksi']) && $_GET['aksi'] === 'tambah' && isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    $jumlah_tambah = isset($_GET['jumlah']) ? max(1, (int)$_GET['jumlah']) : 1;
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] += $jumlah_tambah;
    } else {
        $_SESSION['keranjang'][$id_produk] = $jumlah_tambah;
    }
    $_SESSION['swal_berhasil_tambah'] = true;
    header("Location: index.php#produk");
    exit;
}


if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    unset($_SESSION['keranjang'][$id_produk]);
    header("Location: index.php?view=keranjang");
    exit;
}

$view = $_GET['view'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$q = trim($_GET['q'] ?? '');


$sql = "
    SELECT tb_produk.*, tb_kategori.nama_kategori
    FROM tb_produk
    JOIN tb_kategori
    ON tb_produk.id_kategori = tb_kategori.id_kategori
";

$where = [];
if ($kategori !== '') {
    $kat = mysqli_real_escape_string($koneksi, $kategori);
    $where[] = "tb_kategori.id_kategori = '$kat'";
}
if ($q !== '') {
    $cari = mysqli_real_escape_string($koneksi, $q);
    $where[] = "(tb_produk.nama LIKE '%$cari%' OR tb_produk.deskripsi LIKE '%$cari%' OR tb_kategori.nama_kategori LIKE '%$cari%')";
}
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY tb_produk.id DESC";
$produk = mysqli_query($koneksi, $sql);
$jumlah_hasil = mysqli_num_rows($produk);


$cats = mysqli_query(
    $koneksi,
    "SELECT id_kategori, nama_kategori 
     FROM tb_kategori 
     ORDER BY nama_kategori"
);


$featured = mysqli_query(
    $koneksi,
    "SELECT tb_produk.*, tb_kategori.nama_kategori
     FROM tb_produk
     JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori
     ORDER BY tb_produk.id DESC
     LIMIT 5"
);
$featuredList = mysqli_fetch_all($featured, MYSQLI_ASSOC);

$sedang_filter = ($q !== '' || $kategori !== '');
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajil Batik - Pusat Busana Batik Modern & Elegan</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    
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
            --paper: #f5f6fa;
            --ink: #1c2233;
            --batik: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cg fill='none' stroke='%23c9973f' stroke-opacity='.28' stroke-width='1'%3E%3Cellipse cx='30' cy='15' rx='9' ry='15'/%3E%3Cellipse cx='30' cy='45' rx='9' ry='15'/%3E%3Cellipse cx='15' cy='30' rx='15' ry='9'/%3E%3Cellipse cx='45' cy='30' rx='15' ry='9'/%3E%3C/g%3E%3Ccircle cx='30' cy='30' r='2' fill='%23c9973f' fill-opacity='.4'/%3E%3C/svg%3E");
        }
        html { scroll-behavior: smooth; }
        section[id], div[id="produk"], div[id="katalog"] { scroll-margin-top: 80px; }
        body {
            background-color: var(--paper);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, sans-serif;
            line-height: 1.65;
        }
        h1, h2, h3, h4, .navbar-brand {
            font-family: 'Playfair Display', Georgia, serif;
            letter-spacing: -0.01em;
        }
        h5, h6 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .text-primary { color: var(--indigo-700) !important; }
        .text-info { color: #f0c46c !important; }
        .text-dark { color: var(--ink) !important; }

        /* NAVBAR */
        .navbar-custom {
            background: var(--indigo-900) !important;
            border-bottom: 3px solid var(--gold);
        }
        .navbar-brand strong { font-weight: 700; color: #fff; }
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.78);
            font-weight: 500;
            font-size: 0.93rem;
            padding: 8px 14px;
            border-radius: 0;
            border-bottom: 2px solid transparent;
        }
        .navbar-custom .nav-link:hover { color: #fff; }
        .navbar-custom .nav-link.active {
            color: #fff;
            background: transparent;
            border-bottom-color: var(--gold);
        }
        .search-form .form-control {
            border-radius: 999px 0 0 999px;
            border: none;
            font-size: 0.9rem;
            min-width: 200px;
            padding-left: 18px;
        }
        .search-form .btn {
            border-radius: 0 999px 999px 0;
            background: var(--gold);
            border-color: var(--gold);
            color: var(--indigo-900) !important;
        }
        .search-form .btn:hover { background: var(--gold-dark); border-color: var(--gold-dark); }
        .profile-link {
            display: flex; align-items: center; gap: 8px;
            color: white; text-decoration: none; font-size: 0.9rem;
        }
        .profile-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--gold); color: var(--indigo-900);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
        }

        
        .hero {
            background:
                linear-gradient(180deg, rgba(14,24,57,.55), rgba(14,24,57,.92)),
                var(--batik),
                var(--indigo-700);
            background-size: auto, 60px 60px, auto;
            color: #fff;
            padding-bottom: 6rem !important;
        }
        .hero h1 {
            font-weight: 700;
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            line-height: 1.15;
            color: #fff;
        }
        .hero .lead { color: rgba(255,255,255,.82); font-size: 1.08rem; }
        .btn-gold {
            background: var(--gold); border-color: var(--gold);
            color: var(--indigo-900); font-weight: 600;
        }
        .btn-gold:hover { background: var(--gold-dark); border-color: var(--gold-dark); color: #fff; }
        .btn-ghost {
            color: #fff; border: 1.5px solid rgba(255,255,255,.6); background: transparent;
        }
        .btn-ghost:hover { background: #fff; color: var(--indigo-900); }

        
        .carousel-wrap { margin-top: -4rem; position: relative; z-index: 2; }
        #produkCarousel {
            border-radius: 20px;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 18px 40px rgba(14, 24, 57, 0.28);
        }
        #produkCarousel .carousel-item { height: 400px; background-color: var(--indigo-50); }
        #produkCarousel .carousel-item img {
            width: 100%; height: 100%; object-fit: contain; background-color: var(--indigo-50);
        }
        #produkCarousel .carousel-caption {
            background: rgba(14, 24, 57, 0.82);
            border-left: 4px solid var(--gold);
            border-radius: 8px;
            bottom: 24px;
            left: 24px; right: auto;
            padding: 12px 22px;
            backdrop-filter: blur(4px);
        }

        
        .section-title { font-weight: 700; color: var(--indigo-900); }
        .ornament {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: var(--gold); margin: 10px 0 14px;
        }
        .ornament::before, .ornament::after {
            content: ""; height: 1px; width: 56px; background: var(--gold); opacity: .6;
        }

        
        .album { background: linear-gradient(180deg, var(--indigo-50), var(--paper)) !important; }
        .card-produk {
            border: 1px solid #e3e7f3;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .card-produk:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(14, 24, 57, 0.14) !important;
        }
        .card-produk img {
            width: 100%; height: 235px; object-fit: contain;
            background: var(--indigo-50); padding: 10px;
            border-bottom: 3px solid var(--gold-soft);
        }
        .badge-kat {
            background: var(--gold-soft); color: var(--gold-dark);
            font-weight: 600; padding: 4px 10px; border-radius: 999px;
        }
        .badge-stok-ok {
            background: #e6f4ea; color: #1e7a3c;
            font-weight: 600; padding: 5px 10px; border-radius: 999px; font-size: .78rem;
        }
        .badge-stok-sedikit {
            background: #fdecea; color: #b3261e;
            font-weight: 600; padding: 5px 10px; border-radius: 999px; font-size: .78rem;
        }

        /* TENTANG KAMI / STORE INFO */
        .about-section { background: #fff; }
        .about-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--gold-soft);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 12px;
        }
        .about-box {
            background:
                linear-gradient(135deg, rgba(14,24,57,.94), rgba(27,42,94,.94)),
                var(--batik);
            background-size: auto, 60px 60px;
            border-radius: 20px; border-top: 4px solid var(--gold);
            color: #fff; padding: 34px;
            box-shadow: 0 14px 32px rgba(14, 24, 57, 0.25);
        }
        .about-box h4 { color: #f0c46c; }

        
        .footer-batik {
            background: var(--indigo-900);
            color: rgba(255,255,255,.75);
            border-top: 3px solid var(--gold);
        }
        .footer-batik a { color: #f0c46c; text-decoration: none; }
        .footer-batik a:hover { text-decoration: underline; }
    </style>
</head>
<body>


<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm py-2">
        <div class="container">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                <span style="font-size: 1.5rem;">👘</span>
                <strong class="fs-4">Ajil Batik</strong>
            </a>

            <?php 
            $jml_keranjang = isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
            ?>
            <div class="d-flex align-items-center gap-2 ms-auto me-2 d-lg-none">
                <a href="index.php?view=keranjang" class="btn btn-light position-relative btn-sm rounded-pill px-3 fw-semibold text-primary">
                    🛒
                    <?php if ($jml_keranjang > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?= $jml_keranjang ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-lg-3 mb-2 mb-lg-0 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link <?= ($view !== 'keranjang' && !$sedang_filter) ? 'active' : '' ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#katalog">Katalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($view !== 'keranjang' && $sedang_filter) ? 'active' : '' ?>" href="index.php#produk">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#tentang">Tentang Kami</a>
                    </li>
                </ul>

                
                <form class="search-form d-flex mx-lg-auto mb-2 mb-lg-0" action="index.php#produk" method="get" role="search">
                    <?php if ($kategori !== ''): ?>
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                    <?php endif; ?>
                    <input class="form-control" type="search" name="q" placeholder="Cari batik..." aria-label="Cari produk" value="<?= htmlspecialchars($q) ?>">
                    <button class="btn btn-light text-primary fw-semibold px-3" type="submit">🔍</button>
                </form>

                <div class="d-flex align-items-center gap-3 ms-lg-3">
                    <a href="index.php?view=keranjang" class="btn btn-light position-relative btn-sm rounded-pill px-3 fw-semibold text-primary d-none d-lg-flex align-items-center gap-1">
                        🛒 <span>Keranjang</span>
                        <?php if ($jml_keranjang > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $jml_keranjang ?>
                            </span>
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

<main>
    <?php if ($view === 'keranjang'): ?>
        
        <div class="container py-5" style="min-height: 70vh;">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Keranjang Belanja Ajil Batik</h2>
                <p class="text-muted">Periksa kembali pilihan busana batik eleganmu sebelum melanjutkan ke pembayaran.</p>
            </div>

            <?php if (empty($_SESSION['keranjang'])): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center py-5 card custom p-5 bg-white rounded-4 shadow-sm border">
                        <div class="display-1 mb-3">🛒</div>
                        <h4 class="fw-bold text-dark mb-2">Keranjangmu Masih Kosong Nih!</h4>
                        <p class="text-muted mb-4">Belum ada busana batik yang masuk ke daftar pesanan.</p>
                        <a href="index.php#produk" class="btn btn-primary rounded-pill px-4 shadow-sm">Pilih Koleksi Batik</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card p-4 rounded-4 border shadow-sm bg-white">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Produk Batik</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $total_belanja = 0;
                                foreach ($_SESSION['keranjang'] as $id_produk => $jumlah):
                                    $id_aman = mysqli_real_escape_string($koneksi, (string)$id_produk);
                                    $res_p = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id = '$id_aman'");
                                    $row_p = mysqli_fetch_assoc($res_p);
                                    if (!$row_p) continue;
                                    $subtotal = $row_p['harga'] * $jumlah;
                                    $total_belanja += $subtotal;
                                    $foto_produk = $row_p['foto'] ?? $row_p['poto'] ?? 'default.jpg';
                                ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="img/<?= htmlspecialchars($foto_produk) ?>" width="55" height="55" class="rounded object-fit-contain bg-light border p-1 shadow-sm" alt="Batik">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($row_p['nama']) ?></h6>
                                                <small class="text-muted">Koleksi Eksklusif Ajil Batik</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-secondary">Rp <?= number_format($row_p['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2"><?= $jumlah ?> Pcs</span>
                                    </td>
                                    <td class="fw-bold text-primary">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <button onclick="hapusKeranjang('index.php?view=keranjang&aksi=hapus&id=<?= urlencode($id_produk) ?>')" 
                                           class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1">
                                            🗑️ Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="4" class="text-end fw-bold fs-5 py-4">Total Pembayaran:</td>
                                    <td colspan="2" class="fw-bold text-primary fs-4 py-4">Rp <?= number_format($total_belanja, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="index.php#produk" class="btn btn-outline-secondary rounded-pill px-4">
                            &larr; Pilih Koleksi Lain
                        </a>
                        <a href="checkout.php" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm">
                            Checkout Pesanan ✨
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
       
        <section id="home" class="hero pt-5 text-center">
            <div class="container">
                <div class="row py-lg-3">
                    <div class="col-lg-6 col-md-8 mx-auto">
                        <h1 class="mb-2">Koleksi Busana Ajil Batik</h1>
                        <p class="lead">Mahakarya batik elegan dan modern bernuansa biru eksklusif untuk menyempurnakan gaya Anda.</p>
                        <a href="#produk" class="btn btn-gold rounded-pill px-4 mt-2">Belanja Sekarang</a>
                        <a href="#tentang" class="btn btn-ghost rounded-pill px-4 mt-2">Tentang Kami</a>
                    </div>
                </div>
            </div>
        </section>

        
        <?php if (count($featuredList) > 0) { ?>
            <div class="container carousel-wrap mb-4">
                <div id="produkCarousel" class="carousel slide shadow" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($featuredList as $i => $item) { ?>
                            <button type="button" data-bs-target="#produkCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                        <?php } ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($featuredList as $i => $item) { ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                <img src="img/<?= htmlspecialchars($item['foto'] ?? $item['poto'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                                <div class="carousel-caption text-start">
                                    <h5 class="text-white fw-bold"><?= htmlspecialchars($item['nama']) ?></h5>
                                    <p class="mb-0 text-info fw-semibold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        
        <section id="katalog" class="py-4 text-center container">
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <h2 class="section-title">Katalog Pilihan Batik</h2>
                    <div class="ornament">◆</div>
                    <p class="text-muted">Temukan motif dan desain batik terbaik asli Indonesia</p>
                </div>
            </div>
        </section>

        <div class="container mb-5 text-center">
            <a href="index.php#produk" class="btn <?= $kategori === '' ? 'btn-primary shadow-sm' : 'btn-outline-primary' ?> m-1 rounded-pill px-4 fw-semibold">
                ✨ Semua Koleksi
            </a>
            <?php while ($cat = mysqli_fetch_array($cats)) { ?>
                <a href="index.php?kategori=<?= urlencode($cat['id_kategori']) ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>#produk" 
                   class="btn <?= $kategori == $cat['id_kategori'] ? 'btn-primary shadow-sm' : 'btn-outline-primary' ?> m-1 rounded-pill px-4 fw-semibold">
                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                </a>
            <?php } ?>
        </div>

        
        <div id="produk" class="album py-4 bg-light">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <h3 class="section-title mb-2 mb-md-0">Semua Produk</h3>
                    <?php if ($q !== ''): ?>
                        <div class="text-muted">
                            Hasil pencarian "<strong><?= htmlspecialchars($q) ?></strong>":
                            <?= $jumlah_hasil ?> produk
                            <a href="index.php#produk" class="btn btn-sm btn-outline-secondary rounded-pill ms-2">Reset</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php while ($data = mysqli_fetch_array($produk)) { ?>
                        <div class="col">
                            <div class="card card-produk shadow-sm h-100 bg-white">
                                <img src="img/<?= htmlspecialchars($data['foto'] ?? $data['poto'] ?? 'default.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($data['nama']) ?>">

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark mb-2">
                                        <?= htmlspecialchars($data['nama']) ?>
                                    </h5>
                                    <p class="card-text text-primary fs-5 fw-bold mb-1">
                                        Rp <?= number_format($data['harga'], 0, ',', '.') ?>
                                    </p>
                                    <p class="card-text text-muted small mb-2">
                                        Kategori: <span class="badge-kat"><?= htmlspecialchars($data['nama_kategori']) ?></span>
                                    </p>
                                    <p class="card-text text-secondary small flex-grow-1">
                                        <?= htmlspecialchars(substr($data['deskripsi'] ?? '', 0, 90)) ?>...
                                    </p>
                                    <span class="<?= (int)$data['stok'] <= 5 ? 'badge-stok-sedikit' : 'badge-stok-ok' ?> align-self-start mb-3">
                                        <?= (int)$data['stok'] <= 5 ? 'Stok tinggal' : 'Stok' ?>: <?= $data['stok'] ?> pcs
                                    </span>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div class="btn-group w-100 gap-2">
                                            <a href="detail.php?id=<?= $data['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill w-50">
                                                🔍 Detail
                                            </a>
                                            <a href="index.php?aksi=tambah&id=<?= $data['id'] ?>" class="btn btn-sm btn-primary rounded-pill w-50">
                                                + Keranjang
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <section id="tentang" class="about-section py-5">
            <div class="container py-4">
                <div class="row align-items-center g-5 mb-5">
                    <div class="col-lg-6">
                        <div class="about-box">
                            <h4 class="fw-bold mb-3">Mengenal Lebih Dekat Ajil Batik</h4>
                            <p class="mb-3">Ajil Batik hadir sejak tahun 2024 sebagai wujud dedikasi tinggi dalam melestarikan warisan budaya luhur bangsa Indonesia. Kami memadukan seni motif tradisional khas nusantara dengan tren fesyen modern yang elegan dan berkelas.</p>
                            <p class="mb-0">Setiap busana dirancang bukan sekadar pakaian, melainkan sebuah karya seni bernilai budaya tinggi yang siap menyempurnakan penampilan formal maupun kasual Anda.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-4 bg-light rounded-4 border shadow-sm">
                            <h4 class="fw-bold text-dark mb-3">🌟 Visi & Komitmen Kami</h4>
                            <p class="text-muted small mb-3">Menjadi pusat perbelanjaan busana batik terpercaya yang mengutamakan keaslian motif, kenyamanan bahan, serta kepuasan pelanggan di seluruh Indonesia.</p>
                            <ul class="list-unstyled text-muted small mb-0">
                                <li class="mb-2">✅ <strong>100% Produk Berkualitas:</strong> Melewati proses kurasi dan kontrol kualitas yang ketat.</li>
                                <li class="mb-2">✅ <strong>Pelayanan Prima:</strong> Siap membantu Anda menemukan ukuran dan model terbaik.</li>
                                <li>✅ <strong>Harga Kompetitif:</strong> Menghadirkan kemewahan batik dengan penawaran harga terbaik.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row g-4 text-center">
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-4 h-100 border shadow-sm bg-white rounded-4">
                            <div class="about-icon">🧵</div>
                            <h6 class="fw-bold text-dark">Bahan Premium</h6>
                            <p class="text-muted small mb-0">Menggunakan kain katun dan sutra pilihan yang adem, lembut, serta menyerap keringat dengan sempurna.</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-4 h-100 border shadow-sm bg-white rounded-4">
                            <div class="about-icon">✨</div>
                            <h6 class="fw-bold text-dark">Desain Eksklusif</h6>
                            <p class="text-muted small mb-0">Pola dan corak dirancang khusus dengan sentuhan estetika modern yang anggun dan berwibawa.</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-4 h-100 border shadow-sm bg-white rounded-4">
                            <div class="about-icon">🎨</div>
                            <h6 class="fw-bold text-dark">Motif Autentik</h6>
                            <p class="text-muted small mb-0">Menampilkan ragam hias dan corak batik klasik nusantara yang kaya akan filosofi dan makna sejarah.</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-4 h-100 border shadow-sm bg-white rounded-4">
                            <div class="about-icon">🔒</div>
                            <h6 class="fw-bold text-dark">Transaksi Aman</h6>
                            <p class="text-muted small mb-0">Kemudahan sistem pemesanan online, keranjang belanja praktis, serta pilihan pembayaran fleksibel.</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="footer-batik py-4 mt-5">
    <div class="container text-center py-3">
        <div class="mb-2 fs-4">👘 <strong>Ajil Batik</strong></div>
        <p class="small mb-3 text-white-50">Pusat Busana Batik Modern & Elegan Berkualitas Tinggi</p>
        <p class="small text-white-50 mb-0">&copy; <?= date('Y') ?> Ajil Batik. Hak Cipta Dilindungi.</p>
    </div>
</footer>

<script>
function hapusKeranjang(url) {
    Swal.fire({
        title: 'Hapus Produk?',
        text: "Produk ini akan dikeluarkan dari keranjang belanja.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1b2a5e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

<?php if (isset($_SESSION['swal_berhasil_tambah'])): unset($_SESSION['swal_berhasil_tambah']); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Berhasil Ditambahkan!',
            text: 'Produk batik telah dimasukkan ke keranjang belanja.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true
        });
    });
</script>
<?php endif; ?>

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
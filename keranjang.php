<?php
session_start();
include "koneksi.php";

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }


if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


if (isset($_GET['aksi']) AND $_GET['aksi'] === 'tambah' AND isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] += 1;
    } else {
        $_SESSION['keranjang'][$id_produk] = 1;
    }
    header("Location: index.php?view=keranjang");
    exit;
}


if (isset($_GET['aksi']) AND $_GET['aksi'] === 'hapus' AND isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    unset($_SESSION['keranjang'][$id_produk]);
    header("Location: index.php?view=keranjang");
    exit;
}

$view = $_GET['view'] ?? 'home';


$jumlah_keranjang = 0;
foreach ($_SESSION['keranjang'] as $qty) {
    $jumlah_keranjang += $qty;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajil Batik - Toko Batik Elegan & Berkualitas</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo-900: #0e1839; --indigo-700: #1b2a5e; --indigo-500: #3a4f9e; --indigo-50: #eef1fa;
            --gold: #c9973f; --paper: #f5f6fa; --ink: #1c2233;
        }
        body { background: var(--paper); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        .topbar { background: var(--indigo-900); border-bottom: 3px solid var(--gold); }
        .card-custom { background: #fff; border: 1px solid #e3e7f3; border-radius: 16px; box-shadow: 0 8px 24px rgba(14,24,57,.06); transition: transform .2s ease; }
        .card-custom:hover { transform: translateY(-4px); }
        .product-img { height: 220px; object-fit: cover; border-top-left-radius: 16px; border-top-right-radius: 16px; background: var(--indigo-50); }
        .btn-primary { background: var(--indigo-700); border-color: var(--indigo-700); }
        .btn-primary:hover { background: var(--indigo-900); border-color: var(--indigo-900); }
        .badge-cart { font-size: .75rem; }
    </style>
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-dark topbar py-3 sticky-top">
    <div class="container">
        <a href="index.php" class="navbar-brand text-decoration-none text-white fw-bold fs-4">👘 Ajil Batik</a>
        
        <div class="d-flex align-items-center gap-3">
            
            <a href="index.php?view=keranjang" class="btn btn-outline-light btn-sm rounded-pill px-3 position-relative">
                🛒 Keranjang
                <?php if ($jumlah_keranjang > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-cart">
                        <?= $jumlah_keranjang ?>
                    </span>
                <?php endif; ?>
            </a>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle fw-semibold" type="button" id="dropdownProfile" data-bs-toggle="dropdown" aria-expanded="false">
                        👤 <?= e($_SESSION['username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 rounded-3" aria-labelledby="dropdownProfile">
                        <li><a class="dropdown-item py-2 small" href="profil.php">⚙️ Pengaturan Profil</a></li>
                        <li><a class="dropdown-item py-2 small" href="riwayat.php">📦 Riwayat Transaksi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 small text-danger fw-semibold" href="logout.php">🚪 Keluar</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-5">
    <?php if ($view === 'home'): ?>
        
        <div class="p-5 mb-5 rounded-4 text-white shadow-sm" style="background: linear-gradient(135deg, var(--indigo-900), var(--indigo-700));">
            <h1 class="display-5 fw-bold mb-2">Koleksi Batik Eksklusif</h1>
            <p class="lead text-light opacity-75 mb-4">Temukan keindahan motif batik pilihan terbaik untuk gaya formal maupun kasual Anda.</p>
            <a href="#katalog" class="btn btn-light text-dark rounded-pill px-4 fw-semibold">Jelajahi Produk</a>
        </div>

        <div id="katalog" class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0" style="color: var(--indigo-900);">Daftar Produk</h3>
        </div>

        <div class="row g-4">
            <?php
            $q_produk = mysqli_query($koneksi, "SELECT * FROM tb_produk ORDER BY id DESC");
            if ($q_produk AND mysqli_num_rows($q_produk) > 0):
                while ($p = mysqli_fetch_assoc($q_produk)):
                    $foto = $p['foto'] ?? $p['poto'] ?? 'default.jpg';
            ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card card-custom h-100 border-0">
                        <img src="img/<?= e($foto) ?>" class="product-img w-100" alt="<?= e($p['nama']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold card-title text-truncate" style="color: var(--indigo-900);" title="<?= e($p['nama']) ?>"><?= e($p['nama']) ?></h5>
                            <p class="text-primary fw-bold fs-5 mb-2"><?= rupiah($p['harga']) ?></p>
                            <p class="text-muted small mb-4">Stok: <b><?= (int)$p['stok'] ?></b></p>
                            
                            <div class="mt-auto">
                                <?php if ((int)$p['stok'] > 0): ?>
                                    <a href="index.php?aksi=tambah&id=<?= $p['id'] ?>" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                        + Tambah ke Keranjang
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 rounded-pill py-2" disabled>Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-12 text-center py-5">
                    <div class="card card-custom p-5">
                        <p class="text-muted mb-0">Belum ada produk tersedia di database.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($view === 'keranjang'): ?>
        
        <h2 class="fw-bold mb-1" style="color: var(--indigo-900);">Keranjang Belanja</h2>
        <p class="text-muted mb-4">Periksa kembali daftar produk yang ingin Anda beli.</p>

        <?php if (empty($_SESSION['keranjang'])): ?>
            <div class="card card-custom p-5 text-center">
                <p class="text-muted mb-3">Keranjang belanja Anda masih kosong.</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-4 mx-auto">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-custom p-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_semua = 0;
                                    foreach ($_SESSION['keranjang'] as $id_p => $jml):
                                        $id_aman = mysqli_real_escape_string($koneksi, (string)$id_p);
                                        $rp = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id='$id_aman'");
                                        $prod = $rp ? mysqli_fetch_assoc($rp) : null;
                                        if (!$prod) continue;

                                        $subtotal = $prod['harga'] * $jml;
                                        $total_semua += $subtotal;
                                        $ft = $prod['foto'] ?? $prod['poto'] ?? 'default.jpg';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="img/<?= e($ft) ?>" class="rounded" style="width: 48px; height: 48px; object-fit: cover;" alt="">
                                                    <span class="fw-semibold small"><?= e($prod['nama']) ?></span>
                                                </div>
                                            </td>
                                            <td class="small"><?= rupiah($prod['harga']) ?></td>
                                            <td class="small fw-bold"><?= $jml ?>x</td>
                                            <td class="fw-semibold text-primary"><?= rupiah($subtotal) ?></td>
                                            <td class="text-center">
                                                <a href="index.php?aksi=hapus&id=<?= $id_p ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3" style="color: var(--indigo-900);">Ringkasan Belanja</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total Harga</span>
                            <span class="fw-bold fs-5" style="color: var(--indigo-700);"><?= rupiah($total_semua) ?></span>
                        </div>
                        <a href="Checkout.php" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold mb-2">Lanjut ke Checkout</a>
                        <a href="index.php" class="btn btn-outline-secondary w-100 rounded-pill py-2">&larr; Belanja Lagi</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
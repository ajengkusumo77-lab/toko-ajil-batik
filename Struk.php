<?php
session_start();
include "koneksi.php";

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$id_transaksi = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
$q_trx = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_transaksi = '$id_transaksi' LIMIT 1");
$trx = $q_trx ? mysqli_fetch_assoc($q_trx) : null;

if (!$trx) {
    echo "Transaksi tidak ditemukan!";
    exit;
}

$q_detail = mysqli_query($koneksi, "SELECT tb_detail.*, tb_produk.nama, tb_produk.harga, tb_produk.foto FROM tb_detail JOIN tb_produk ON tb_detail.id_produk = tb_produk.id WHERE tb_detail.id_transaksi = '$id_transaksi'");
$items = [];
while ($d = mysqli_fetch_assoc($q_detail)) { $items[] = $d; }

$metode = $trx['metode_pembayaran'] ?? 'COD';
$total = $trx['total_harga'] ?? $trx['total'] ?? 0;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #<?= e($trx['id_transaksi']) ?> - Ajil Batik</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo-900: #0e1839;
            --indigo-700: #1b2a5e;
            --indigo-50: #eef1fa;
            --gold: #c9973f;
            --paper: #f5f6fa;
            --ink: #1c2233;
        }
        body { background: var(--paper); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, h5, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        .topbar { background: var(--indigo-900); border-bottom: 3px solid var(--gold); }
        .card-custom {
            background: #fff;
            border: 1px solid #e3e7f3;
            border-radius: 18px;
            border-top: 5px solid var(--gold);
            box-shadow: 0 12px 30px rgba(14,24,57,.08);
        }
        .thumb { width: 52px; height: 52px; object-fit: cover; background: var(--indigo-50); border: 1px solid #d9dff0; border-radius: 10px; padding: 2px; }
        .info-box { background: #f8f9fc; border: 1px solid #e3e7f3; border-radius: 12px; }
        .badge-trx { background-color: var(--indigo-50); color: var(--indigo-700); border: 1px solid #d9dff0; font-weight: 600; }
        
        @media print {
            body { background: #fff; }
            .topbar, .no-print { display: none !important; }
            .card-custom { border: none; box-shadow: none; padding: 0 !important; }
            main { padding: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

<header class="topbar py-3 no-print">
    <div class="container">
        <a href="index.php" class="navbar-brand text-decoration-none text-white fw-bold">👘 Ajil Batik Official</a>
    </div>
</header>

<main class="container py-5" style="max-width: 750px;">
    <div class="card-custom p-4 p-md-5">
        
        <div class="text-center mb-4 pb-3 border-bottom">
            <div class="display-6 mb-2">👘</div>
            <h2 class="fw-bold" style="color: var(--indigo-900);">Ajil Batik</h2>
            <p class="text-muted small mb-2">Pusat Belanja Batik Elegan & Berkualitas</p>
            <h4 class="text-success fw-bold mt-3">Pesanan Berhasil Dibuat!</h4>
            <span class="badge badge-trx fs-6 px-3 py-2 mt-1">ID Transaksi: #<?= e($trx['id_transaksi']) ?></span>
        </div>

        <div class="info-box p-3 p-md-4 mb-4">
            <h6 class="fw-bold mb-3" style="color: var(--indigo-900);">📦 Informasi Pengiriman & Penerima</h6>
            <div class="row g-2 small">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nama Penerima</span>
                    <strong class="text-dark fs-6"><?= e($trx['nama_penerima']) ?></strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nomor Telepon / HP</span>
                    <strong class="text-dark fs-6"><?= e($trx['no_hp']) ?></strong>
                </div>
                <div class="col-12 mt-2">
                    <span class="text-muted d-block">Alamat Lengkap</span>
                    <span class="text-dark"><?= e($trx['alamat_pengiriman']) ?></span>
                </div>
            </div>
        </div>

        
        <div class="mb-4 p-4 border rounded-3 shadow-sm bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0" style="color: var(--indigo-900);">Metode Pembayaran</h6>
                <span class="badge bg-primary px-3 py-2"><?= e($metode) ?></span>
            </div>

            <?php if ($metode === 'QRIS'): ?>
                <div class="text-center bg-light p-3 rounded-3 border">
                    <p class="text-muted small mb-2">Scan QR Code di bawah ini menggunakan m-Banking atau E-Wallet:</p>
                    <img src="img/scan1.jpg" alt="QRIS QR Code" class="img-fluid border p-2 bg-white rounded mb-2 shadow-sm" style="max-width: 200px;">
                    <p class="text-danger small fw-bold mb-0">Total Tagihan: <b><?= rupiah($total) ?></b></p>
                </div>
            <?php elseif ($metode === 'Transfer Bank'): ?>
                <div class="p-3 bg-light border rounded-3">
                    <p class="text-muted small mb-2">Silakan lakukan transfer sejumlah <b class="text-dark"><?= rupiah($total) ?></b> ke rekening resmi berikut:</p>
                    <div class="p-3 bg-white border rounded-2 shadow-sm">
                        <div class="fw-bold text-dark">Bank BNI</div>
                        <div class="fs-4 fw-bold text-primary font-monospace my-1">0575128415</div>
                        <div class="small text-muted">a.n. <b>Ajil Batik Official</b></div>
                    </div>
                </div>
            <?php elseif ($metode === 'COD'): ?>
                <div class="alert alert-warning mb-0 border-0 shadow-sm text-center py-3">
                    <h6 class="fw-bold text-dark mb-1">Cash on Delivery (COD)</h6>
                    <p class="small text-danger fw-semibold mb-0">Mohon siapkan uang tunai senilai <b><?= rupiah($total) ?></b> saat kurir mengantar pesanan Anda.</p>
                </div>
            <?php endif; ?>
        </div>

        
        <h6 class="fw-bold mb-3" style="color: var(--indigo-900);">Rincian Produk</h6>
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Produk</th>
                        <th class="py-3 text-center">Jumlah</th>
                        <th class="py-3 text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="img/<?= e($it['foto'] ?? 'default.jpg') ?>" class="thumb" alt="">
                                    <div>
                                        <span class="fw-semibold small d-block text-dark"><?= e($it['nama']) ?></span>
                                        <span class="text-muted" style="font-size: 0.8rem;"><?= rupiah($it['harga']) ?> / item</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center small fw-bold py-3"><?= $it['jumlah'] ?>x</td>
                            <td class="text-end fw-semibold small text-primary py-3"><?= rupiah($it['harga'] * $it['jumlah']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-top">
                    <tr>
                        <td colspan="2" class="fw-bold text-dark pt-3 fs-6">Total Pembayaran</td>
                        <td class="text-end fw-bold fs-5 text-primary pt-3"><?= rupiah($total) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="d-flex gap-2 no-print pt-3 border-top">
            <a href="index.php" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-semibold">&larr; Kembali ke Beranda</a>
            <button onclick="window.print()" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold" style="background: var(--indigo-700); border-color: var(--indigo-700);">🖨️ Cetak Struk</button>
        </div>

    </div>
</main>
</body>
</html>
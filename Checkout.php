<?php
session_start();
include "koneksi.php";

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function esc($k,$v) { return mysqli_real_escape_string($k, (string)$v); }

function kolom_ada($k, $tabel,$kolom) {
    $r = mysqli_query($k, "SHOW COLUMNS FROM `$tabel` LIKE '" . esc($k,$kolom) . "'");
    if ($r AND mysqli_num_rows($r) > 0) {
        return true;
    }
    return false;
}

function pastikan_kolom($k,$tabel, $kolom,$definisi) {
    if (!kolom_ada($k, $tabel,$kolom)) {
        mysqli_query($k, "ALTER TABLE `$tabel` ADD COLUMN `$kolom` $definisi");
    }
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$role_user =$_SESSION['role'] ?? '';
if ($role_user !== 'pelanggan') {
    header("Location: login.php");
    exit;
}
$username =$_SESSION['username'];

if (empty($_SESSION['keranjang'])) {
    header("Location: index.php?view=keranjang");
    exit;
}

// Pastikan kolom & struktur tb_transaksi termasuk metode_pembayaran, kurir, dan ongkir
pastikan_kolom($koneksi, 'tb_transaksi', 'username', "VARCHAR(50) NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'nama_penerima', "VARCHAR(100) NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'no_hp', "VARCHAR(20) NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'alamat_pengiriman', "TEXT NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'catatan', "VARCHAR(255) NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'kurir', "VARCHAR(100) NULL");
pastikan_kolom($koneksi, 'tb_transaksi', 'ongkir', "INT NOT NULL DEFAULT 0");
pastikan_kolom($koneksi, 'tb_transaksi', 'total', "INT NOT NULL DEFAULT 0");
pastikan_kolom($koneksi, 'tb_transaksi', 'total_harga', "INT NOT NULL DEFAULT 0");
pastikan_kolom($koneksi, 'tb_transaksi', 'status', "VARCHAR(20) NOT NULL DEFAULT 'Menunggu'");
pastikan_kolom($koneksi, 'tb_transaksi', 'tanggal', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
pastikan_kolom($koneksi, 'tb_transaksi', 'metode_pembayaran', "VARCHAR(50) NOT NULL DEFAULT 'COD'");

$r_id_cek = mysqli_query($koneksi, "SHOW COLUMNS FROM `tb_transaksi` LIKE 'id_transaksi'");
$tambah_pk = (!$r_id_cek OR mysqli_num_rows($r_id_cek) == 0);

if ($tambah_pk) {
    @mysqli_query($koneksi, "ALTER TABLE `tb_transaksi` ADD COLUMN `id_transaksi` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
} else {
    $col_info = mysqli_fetch_assoc($r_id_cek);
    $extra_col =$col_info['Extra'] ?? '';
    if (strpos(strtolower($extra_col), 'auto_increment') === false) {
        @mysqli_query($koneksi, "ALTER TABLE `tb_transaksi` MODIFY COLUMN `id_transaksi` INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }
}

// Pastikan struktur tb_detail & id_detail AUTO_INCREMENT
pastikan_kolom($koneksi, 'tb_detail', 'id_transaksi', "INT NOT NULL");
pastikan_kolom($koneksi, 'tb_detail', 'id_produk', "INT NOT NULL");
pastikan_kolom($koneksi, 'tb_detail', 'jumlah', "INT NOT NULL DEFAULT 1");

$r_idd_cek = mysqli_query($koneksi, "SHOW COLUMNS FROM `tb_detail` LIKE 'id_detail'");
$tambah_pk_detail = (!$r_idd_cek OR mysqli_num_rows($r_idd_cek) == 0);

if ($tambah_pk_detail) {
    @mysqli_query($koneksi, "ALTER TABLE `tb_detail` ADD COLUMN `id_detail` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
} else {
    $col_info_det = mysqli_fetch_assoc($r_idd_cek);
    $extra_col_det =$col_info_det['Extra'] ?? '';
    if (strpos(strtolower($extra_col_det), 'auto_increment') === false) {
        @mysqli_query($koneksi, "ALTER TABLE `tb_detail` MODIFY COLUMN `id_detail` INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }
}

$nama_default =$username;
$hp_default = '';
$alamat_default = '';$id_pelanggan = 0;

$qu = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='" . esc($koneksi,$username) . "' LIMIT 1");
if ($qu AND $ru = mysqli_fetch_assoc($qu)) {
    $id_pelanggan =$ru['id_pelanggan'] ?? $ru['id_user'] ?? $ru['id'] ?? 0;
    if (!empty($ru['nama_lengkap'])) { $nama_default =$ru['nama_lengkap']; }
    if (!empty($ru['no_hp'])) { $hp_default =$ru['no_hp']; }
    if (!empty($ru['alamat'])) { $alamat_default =$ru['alamat']; }
}

function ambil_item_keranjang($koneksi) {$items = [];
    foreach ($_SESSION['keranjang'] as$id_produk => $jumlah) {$id_aman = mysqli_real_escape_string($koneksi, (string)$id_produk);
        $r = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id='$id_aman'");
        $p = $r ? mysqli_fetch_assoc($r) : null;
        if (!$p) { continue; }$items[] = [
            'id'     => $p['id'],
            'nama'   => $p['nama'],
            'harga'  => (int)$p['harga'],
            'stok'   => (int)$p['stok'],
            'foto'   => $p['foto'] ?? $p['poto'] ?? 'default.jpg',
            'jumlah' => (int)$jumlah,
        ];
    }
    return $items;
}

$error = '';

if (isset($_POST['konfirmasi'])) {
    $nama_penerima     = trim($_POST['nama_penerima'] ?? '');
    $no_hp             = trim($_POST['no_hp'] ?? '');
    $alamat            = trim($_POST['alamat'] ?? '');
    $catatan           = trim($_POST['catatan'] ?? '');
    $kurir_input       = trim($_POST['kurir'] ?? '');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');

    if ($nama_penerima === '' OR$no_hp === '' OR $alamat === '') {$error = 'Nama penerima, No. HP, dan Alamat wajib diisi.';
    } elseif ($kurir_input === '') {$error = 'Silakan pilih metode pengiriman terlebih dahulu.';
    } elseif ($metode_pembayaran === '') {$error = 'Silakan pilih metode pembayaran terlebih dahulu.';
    } else {
        $items = ambil_item_keranjang($koneksi);
        if (empty($items)) {
            header("Location: index.php?view=keranjang");
            exit;
        }

        $stok_kurang = [];
        foreach ($items as$it) {
            if ($it['jumlah'] >$it['stok']) { 
                $stok_kurang[] =$it['nama']; 
            }
        }

        if (!empty($stok_kurang)) {$error = 'Maaf, stok tidak cukup untuk: ' . implode(', ', $stok_kurang) . '.';
        } else {
            $total_produk = 0;
            foreach ($items as $it) {$total_produk += $it['harga'] *$it['jumlah']; 
            }

            // Pecah data kurir dan ongkir
            $pecah      = explode('|', $kurir_input);$nama_kurir = $pecah[0] ?? '';$ongkir     = (int)($pecah[1] ?? 0);$total_harga = $total_produk +$ongkir;

            mysqli_query($koneksi, "INSERT INTO tb_transaksi
                (id_pelanggan, username, nama_penerima, no_hp, alamat_pengiriman, catatan, kurir, ongkir, total, total_harga, status, tanggal, metode_pembayaran)
                VALUES (
                    '" . esc($koneksi,$id_pelanggan) . "',
                    '" . esc($koneksi,$username) . "',
                    '" . esc($koneksi,$nama_penerima) . "',
                    '" . esc($koneksi,$no_hp) . "',
                    '" . esc($koneksi,$alamat) . "',
                    '" . esc($koneksi,$catatan) . "',
                    '" . esc($koneksi,$nama_kurir) . "',
                    '" . esc($koneksi,$ongkir) . "',
                    '$total_produk',
                    '$total_harga',
                    'Menunggu',
                    NOW(),
                    '" . esc($koneksi,$metode_pembayaran) . "'
                )") or die("Error Query Transaksi: " . mysqli_error($koneksi));

            $id_transaksi = mysqli_insert_id($koneksi);

            if ($id_transaksi) {
                foreach ($items as $it) {$id_t = esc($koneksi,$id_transaksi);
                    $id_p = esc($koneksi, $it['id']);$jml  = esc($koneksi,$it['jumlah']);
                    
                    mysqli_query($koneksi, "INSERT INTO tb_detail (id_transaksi, id_produk, jumlah) VALUES ('$id_t', '$id_p', '$jml')");
                    mysqli_query($koneksi, "UPDATE tb_produk SET stok = stok - '$jml' WHERE id = '$id_p'");
                }

                $_SESSION['keranjang'] = [];
                header("Location: struk.php?id=" . urlencode($id_transaksi));
                exit;
            } else {
                $error = 'Terjadi kesalahan saat menyimpan pesanan.';
            }
        }
    }
}

$items = ambil_item_keranjang($koneksi);$total = 0;
foreach ($items as $it) {$total += $it['harga'] *$it['jumlah']; 
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Ajil Batik</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo-900: #0e1839; --indigo-700: #1b2a5e; --indigo-500: #3a4f9e; --indigo-50: #eef1fa;
            --gold: #c9973f; --paper: #f5f6fa; --ink: #1c2233;
        }
        body { background: var(--paper); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        .topbar { background: var(--indigo-900); border-bottom: 3px solid var(--gold); }
        .card-custom { background: #fff; border: 1px solid #e3e7f3; border-radius: 16px; box-shadow: 0 8px 24px rgba(14,24,57,.06); }
        .form-label { font-weight: 600; color: var(--indigo-900); font-size: .9rem; }
        .form-control, .form-select { border-radius: 10px; border: 1.5px solid #d9dff0; }
        .thumb { width: 52px; height: 52px; object-fit: contain; background: var(--indigo-50); border: 1px solid #e3e7f3; border-radius: 8px; padding: 3px; }
        .btn-primary { background: var(--indigo-700); border-color: var(--indigo-700); }
        .btn-primary:hover { background: var(--indigo-900); border-color: var(--indigo-900); }
        .item-row { border-bottom: 1px solid #eef1fa; padding: 10px 0; }
        .total-box { background: var(--indigo-50); border-radius: 12px; padding: 16px; }
    </style>
</head>
<body>

<header class="topbar py-3">
    <div class="container">
        <a href="index.php" class="navbar-brand text-decoration-none text-white">👘 Ajil Batik</a>
    </div>
</header>

<main class="container py-5">
    <h1 class="h3 fw-bold mb-1" style="color: var(--indigo-900);">Checkout Pesanan</h1>
    <p class="text-muted mb-4">Lengkapi data pengiriman, pilih kurir, dan metode pembayaran sebelum pesanan diproses.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card-custom p-4">
                    <h5 class="fw-bold mb-3" style="color: var(--indigo-900);">Data Pengiriman</h5>
                    <div class="mb-3">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" name="nama_penerima" value="<?= e($_POST['nama_penerima'] ?? $nama_default) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="tel" class="form-control" name="no_hp" value="<?= e($_POST['no_hp'] ?? $hp_default) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Pengiriman</label>
                        <textarea class="form-control" name="alamat" rows="3" required><?= e($_POST['alamat'] ?? $alamat_default) ?></textarea>
                    </div>

                    <!-- PILIHAN KURIR PENGIRIMAN -->
                    <div class="mb-3">
                        <label class="form-label">Pilih Kurir & Layanan Pengiriman <span class="text-danger">*</span></label>
                        <select name="kurir" id="pilihan_kurir" class="form-select" required onchange="hitungTotal()">
                            <option value="" data-ongkir="0">-- Pilih Kurir Pengiriman --</option>
                            <option value="JNE Regular|15000" data-ongkir="15000" <?= (isset($_POST['kurir']) &&$_POST['kurir'] === 'JNE Regular|15000') ? 'selected' : '' ?>>JNE Regular (3-4 Hari) - Rp 15.000</option>
                            <option value="J&T Express|18000" data-ongkir="18000" <?= (isset($_POST['kurir']) &&$_POST['kurir'] === 'J&T Express|18000') ? 'selected' : '' ?>>J&T Express (2-3 Hari) - Rp 18.000</option>
                            <option value="SiCepat HALU|12000" data-ongkir="12000" <?= (isset($_POST['kurir']) &&$_POST['kurir'] === 'SiCepat HALU|12000') ? 'selected' : '' ?>>SiCepat HALU (4-5 Hari) - Rp 12.000</option>
                            <option value="Ambil di Toko|0" data-ongkir="0" <?= (isset($_POST['kurir']) &&$_POST['kurir'] === 'Ambil di Toko|0') ? 'selected' : '' ?>>Ambil Langsung di Toko - Rp 0</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <input type="text" class="form-control" name="catatan" placeholder="Contoh: titip di satpam" value="<?= e($_POST['catatan'] ?? '') ?>">
                    </div>

                    <div class="mb-0 mt-4">
                        <label class="form-label mb-2">Metode Pembayaran <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check border p-3 rounded bg-light">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="qris" value="QRIS" <?= (isset($_POST['metode_pembayaran']) AND$_POST['metode_pembayaran'] === 'QRIS') ? 'checked' : '' ?> required>
                                <label class="form-check-label fw-semibold" for="qris">📱 QRIS (Scan QR Code)</label>
                            </div>
                            <div class="form-check border p-3 rounded bg-light">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="transfer" value="Transfer Bank" <?= (isset($_POST['metode_pembayaran']) AND$_POST['metode_pembayaran'] === 'Transfer Bank') ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="transfer">🏛️ Transfer Bank (BCA / Mandiri)</label>
                            </div>
                            <div class="form-check border p-3 rounded bg-light">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="cod" value="COD" <?= (isset($_POST['metode_pembayaran']) AND$_POST['metode_pembayaran'] === 'COD') ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="cod">💵 COD (Cash on Delivery)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card-custom p-4">
                    <h5 class="fw-bold mb-3" style="color: var(--indigo-900);">Ringkasan Pesanan</h5>
                    <?php foreach ($items as$it): ?>
                        <div class="item-row d-flex align-items-center gap-3">
                            <img src="img/<?= e($it['foto']) ?>" class="thumb" alt="">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= e($it['nama']) ?></div>
                                <div class="text-muted small"><?= $it['jumlah'] ?> x <?= rupiah($it['harga']) ?></div>
                            </div>
                            <div class="fw-semibold small text-nowrap"><?= rupiah($it['harga'] *$it['jumlah']) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                        <span>Total Produk</span>
                        <span><?= rupiah($total) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted small">
                        <span>Ongkos Kirim</span>
                        <span id="teks_ongkir" class="fw-semibold">Rp 0</span>
                    </div>

                    <div class="total-box d-flex justify-content-between align-items-center mt-3">
                        <span class="fw-bold">Total Keseluruhan</span>
                        <span class="fw-bold fs-5" id="teks_total" style="color: var(--indigo-700);"><?= rupiah($total) ?></span>
                    </div>

                    <button type="submit" name="konfirmasi" class="btn btn-primary w-100 rounded-pill py-2 mt-3 fw-semibold">
                        Konfirmasi Pesanan
                    </button>
                    <a href="index.php?view=keranjang" class="btn btn-outline-secondary w-100 rounded-pill py-2 mt-2">
                        &larr; Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </div>
    </form>
</main>

<!-- Script JavaScript untuk kalkulasi total otomatis saat kurir dipilih -->
<script>
    const totalProduk = <?= (int)$total; ?>;

    function hitungTotal() {
        const selectKurir = document.getElementById('pilihan_kurir');
        const selectedOption = selectKurir.options[selectKurir.selectedIndex];
        const ongkir = parseInt(selectedOption.getAttribute('data-ongkir')) || 0;
        const totalKeseluruhan = totalProduk + ongkir;

        document.getElementById('teks_ongkir').innerText = 'Rp ' + ongkir.toLocaleString('id-ID');
        document.getElementById('teks_total').innerText = 'Rp ' + totalKeseluruhan.toLocaleString('id-ID');
    }

    // Jalankan saat halaman selesai dimuat (untuk kondisi jika terjadi error validasi sebelumnya)
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotal();
    });
</script>
</body>
</html>
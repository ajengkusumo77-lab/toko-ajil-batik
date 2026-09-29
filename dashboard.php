<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "koneksi.php";
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* ================= FUNGSI BANTU ================= */
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function esc($k, $v) { return mysqli_real_escape_string($k, (string)$v); }
function flash($tipe, $pesan) { $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan]; }
function kembali($url) { header("Location: $url"); exit; }
function tabel_ada($k, $nama) {
    $r = mysqli_query($k, "SHOW TABLES LIKE '" . esc($k, $nama) . "'");
    return $r && mysqli_num_rows($r) > 0;
}
function kunci_tabel($k, $tabel, $cadangan) {
    $r = mysqli_query($k, "SHOW KEYS FROM `" . $tabel . "` WHERE Key_name = 'PRIMARY'");
    $row = $r ? mysqli_fetch_assoc($r) : null;
    return $row['Column_name'] ?? $cadangan;
}
function kolom_ada($k, $tabel, $kolom) {
    $r = mysqli_query($k, "SHOW COLUMNS FROM `" . $tabel . "` LIKE '" . esc($k, $kolom) . "'");
    return $r && mysqli_num_rows($r) > 0;
}
function kode_trx($id) { return 'TRX-' . str_pad($id, 4, '0', STR_PAD_LEFT); }
function potong($teks, $maks) {
    $teks = (string)$teks;
    if (function_exists('mb_strimwidth')) { return mb_strimwidth($teks, 0, $maks, '...'); }
    return strlen($teks) > $maks ? substr($teks, 0, $maks) . '...' : $teks;
}
function label_kolom($k) { return ucwords(str_replace('_', ' ', $k)); }

$menu = $_GET['menu'] ?? 'data_menu';
$aksi = $_GET['aksi'] ?? '';
$menu_valid = ['tambah_menu', 'data_menu', 'transaksi', 'pelanggan'];
if (!in_array($menu, $menu_valid, true)) { $menu = 'data_menu'; }

$daftar_status = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
$ada_transaksi = tabel_ada($koneksi, 'tb_transaksi') && tabel_ada($koneksi, 'tb_detail');
$pk_user = kunci_tabel($koneksi, 'tb_user', 'username');
$pk_trx  = $ada_transaksi ? kunci_tabel($koneksi, 'tb_transaksi', 'id_transaksi') : 'id_transaksi';
$ada_status = $ada_transaksi && kolom_ada($koneksi, 'tb_transaksi', 'status');

$peta_user = [];
if ($pk_user !== 'username') {
    $qu = mysqli_query($koneksi, "SELECT " . $pk_user . " AS k, username FROM tb_user");
    while ($qu && $ru = mysqli_fetch_assoc($qu)) { $peta_user[$ru['k']] = $ru['username']; }
}

function format_kolom($nama, $nilai) {
    global $pk_user, $peta_user;
    if ($nilai === null || $nilai === '') { return '<span class="text-muted">-</span>'; }
    if ($nama === 'status') { return badge_status($nilai); }
    if ($nama === $pk_user && isset($peta_user[$nilai])) { return e($peta_user[$nilai]); }
    if (preg_match('/total|harga|bayar|ongkir/i', $nama) && is_numeric($nilai)) { return rupiah($nilai); }
    return e(potong($nilai, 70));
}

/* ================= PROSES: SIMPAN / EDIT MENU ================= */
if (isset($_POST['simpan_menu'])) {
    $id_edit   = $_POST['id_edit'] ?? '';
    $nama      = esc($koneksi, trim($_POST['nama'] ?? ''));
    $harga     = (int)preg_replace('/\D/', '', $_POST['harga'] ?? '0');
    $stok      = (int)($_POST['stok'] ?? 0);
    $kategori  = esc($koneksi, $_POST['kategori'] ?? '');
    $deskripsi = esc($koneksi, trim($_POST['deskripsi'] ?? ''));
    $foto      = '';

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            flash('error', 'Format foto harus JPG, PNG, WEBP, atau GIF.');
            kembali('dashboard.php?menu=tambah_menu' . ($id_edit !== '' ? '&aksi=edit&id=' . urlencode($id_edit) : ''));
        }
        $foto = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['foto']['name']));
        move_uploaded_file($_FILES['foto']['tmp_name'], 'img/' . $foto);
    }

    if ($id_edit !== '') {
        if ($foto === '') { $foto = $_POST['foto_lama'] ?? 'default.jpg'; }
        $foto = esc($koneksi, $foto);
        $id_e = esc($koneksi, $id_edit);
        $ok = mysqli_query($koneksi, "UPDATE tb_produk SET nama='$nama', harga='$harga', stok='$stok', foto='$foto', id_kategori='$kategori', deskripsi='$deskripsi' WHERE id='$id_e'");
        flash($ok ? 'success' : 'error', $ok ? 'Menu berhasil diperbarui.' : 'Gagal memperbarui menu.');
    } else {
        if ($foto === '') { $foto = 'default.jpg'; }
        $foto = esc($koneksi, $foto);
        $ok = mysqli_query($koneksi, "INSERT INTO tb_produk (nama, harga, stok, foto, id_kategori, deskripsi) VALUES ('$nama', '$harga', '$stok', '$foto', '$kategori', '$deskripsi')");
        flash($ok ? 'success' : 'error', $ok ? 'Menu baru berhasil ditambahkan.' : 'Gagal menambahkan menu.');
    }
    kembali('dashboard.php?menu=data_menu');
}

/* ================= PROSES: RESET PASSWORD PELANGGAN ================= */
if (isset($_POST['reset_password_pelanggan'])) {
    $id_target     = esc($koneksi, $_POST['id_user'] ?? '');
    $password_baru = trim($_POST['password_baru'] ?? '');

    if ($password_baru === '') {
        flash('error', 'Password baru tidak boleh kosong.');
    } else {
        $password_simpan = $password_baru; 
        $ok = mysqli_query($koneksi, "UPDATE tb_user SET password = '$password_simpan' WHERE " . $pk_user . " = '$id_target' AND role = 'pelanggan'");
        flash($ok ? 'success' : 'error', $ok ? 'Password pelanggan berhasil direset.' : 'Gagal mereset password.');
    }
    kembali('dashboard.php?menu=pelanggan');
}

/* ================= PROSES: HAPUS ================= */
if ($aksi === 'hapus' && isset($_GET['id'])) {
    $id = esc($koneksi, $_GET['id']);

    if ($menu === 'data_menu') {
        $ok = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id='$id'");
        flash($ok ? 'success' : 'error', $ok ? 'Menu berhasil dihapus.' : 'Menu gagal dihapus.');
        kembali('dashboard.php?menu=data_menu');
    }
    if ($menu === 'transaksi' && $ada_transaksi) {
        mysqli_query($koneksi, "DELETE FROM tb_detail WHERE id_transaksi='$id'");
        $ok = mysqli_query($koneksi, "DELETE FROM tb_transaksi WHERE " . $pk_trx . "='$id'");
        flash($ok ? 'success' : 'error', $ok ? 'Transaksi berhasil dihapus.' : 'Transaksi gagal dihapus.');
        kembali('dashboard.php?menu=transaksi');
    }
    if ($menu === 'pelanggan') {
        $ok = mysqli_query($koneksi, "DELETE FROM tb_user WHERE " . $pk_user . "='$id' AND role='pelanggan'");
        flash($ok ? 'success' : 'error', $ok ? 'Data pelanggan berhasil dihapus.' : 'Data pelanggan gagal dihapus.');
        kembali('dashboard.php?menu=pelanggan');
    }
}

/* ================= PROSES: UBAH STATUS TRANSAKSI ================= */
if (isset($_POST['ubah_status']) && $ada_status) {
    $id = esc($koneksi, $_POST['id_transaksi'] ?? '');
    $status = in_array($_POST['status'] ?? '', $daftar_status, true) ? $_POST['status'] : 'Menunggu';
    mysqli_query($koneksi, "UPDATE tb_transaksi SET status='" . esc($koneksi, $status) . "' WHERE " . $pk_trx . "='$id'");
    flash('success', 'Status transaksi diperbarui.');
    kembali('dashboard.php?menu=transaksi&aksi=detail&id=' . urlencode($id));
}

/* ================= DATA UNTUK FORM MENU ================= */
$nama = $harga = $stok = $id_kategori = $deskripsi = $foto = '';
$mode_edit = false;
$id_edit_val = '';
if ($menu === 'tambah_menu' && $aksi === 'edit' && isset($_GET['id'])) {
    $id_edit_val = esc($koneksi, $_GET['id']);
    $q = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id='$id_edit_val'");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        $mode_edit   = true;
        $nama        = $row['nama'];
        $harga       = $row['harga'];
        $stok        = $row['stok'];
        $id_kategori = $row['id_kategori'];
        $deskripsi   = $row['deskripsi'];
        $foto        = $row['foto'];
    }
}

/* ================= FLASH ================= */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$judul_halaman = [
    'tambah_menu' => $mode_edit ? 'Edit Menu' : 'Tambah Menu',
    'data_menu'   => 'Data Menu',
    'transaksi'   => $aksi === 'detail' ? 'Detail Transaksi' : 'Data Transaksi',
    'pelanggan'   => $aksi === 'detail' ? 'Detail Pelanggan' : 'Data Pelanggan',
][$menu];

$nav = [
    'tambah_menu' => ['➕', 'Tambah Menu'],
    'data_menu'   => ['📋', 'Data Menu'],
    'transaksi'   => ['🧾', 'Data Transaksi'],
    'pelanggan'   => ['👥', 'Data Pelanggan'],
];

function badge_status($s) {
    $map = [
        'Menunggu'   => 'st-menunggu',
        'Diproses'   => 'st-diproses',
        'Dikirim'    => 'st-dikirim',
        'Selesai'    => 'st-selesai',
        'Dibatalkan' => 'st-batal',
    ];
    return '<span class="badge-status ' . ($map[$s] ?? 'st-menunggu') . '">' . e($s) . '</span>';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman) ?> - Dashboard Ajil Batik</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        }
        body { background: var(--paper); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        .topbar { background: var(--indigo-900); border-bottom: 3px solid var(--gold); }
        .topbar .navbar-brand { color: #fff; font-weight: 700; }
        .topbar .navbar-brand small { color: #f0c46c; font-size: .75rem; }
        .topbar .user-chip { display: flex; align-items: center; gap: 8px; color: #fff; font-size: .9rem; }
        .topbar .avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--gold); color: var(--indigo-900); font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .sidebar { background: var(--indigo-700); min-height: calc(100vh - 62px); }
        .sidebar .nav-link { color: rgba(255,255,255,.8); padding: 12px 20px; display: flex; align-items: center; gap: 12px; border-left: 4px solid transparent; font-weight: 500; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar .nav-link.active { background: rgba(201,151,63,.18); color: #fff; border-left-color: var(--gold); }
        .sidebar .nav-label { color: #f0c46c; font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; padding: 18px 20px 6px; }
        .offcanvas-lg.sidebar-off { background: var(--indigo-700); }
        .page-title { font-weight: 700; color: var(--indigo-900); }
        .card-custom { background: #fff; border: 1px solid #e3e7f3; border-radius: 14px; box-shadow: 0 8px 24px rgba(14,24,57,.06); }
        .table-custom thead th { background: var(--indigo-900); color: #fff; border: none; font-weight: 600; padding: 12px 14px; white-space: nowrap; }
        .table-custom tbody td { padding: 12px 14px; vertical-align: middle; }
        .table-custom tbody tr:hover { background: var(--indigo-50); }
        .thumb { width: 56px; height: 56px; object-fit: contain; background: var(--indigo-50); border: 1px solid #e3e7f3; border-radius: 8px; padding: 3px; }
        .btn-primary { background: var(--indigo-700); border-color: var(--indigo-700); }
        .btn-primary:hover { background: var(--indigo-900); border-color: var(--indigo-900); }
        .btn-gold { background: var(--gold); border-color: var(--gold); color: var(--indigo-900); font-weight: 600; }
        .btn-gold:hover { background: var(--gold-dark); border-color: var(--gold-dark); color: #fff; }
        .form-control, .form-select, .input-group-text { border-radius: 10px; }
        .badge-kat { background: var(--gold-soft); color: var(--gold-dark); font-weight: 600; padding: 4px 10px; border-radius: 999px; font-size: .78rem; white-space: nowrap; }
        .badge-stok-ok { background:#e6f4ea; color:#1e7a3c; font-weight:600; padding:4px 10px; border-radius:999px; font-size:.78rem; white-space:nowrap; }
        .badge-stok-sedikit { background:#fdecea; color:#b3261e; font-weight:600; padding:4px 10px; border-radius:999px; font-size:.78rem; white-space:nowrap; }
        .badge-status { font-weight:600; padding:5px 12px; border-radius:999px; font-size:.78rem; white-space:nowrap; }
        .st-menunggu { background: var(--gold-soft); color: var(--gold-dark); }
        .st-diproses { background: var(--indigo-50); color: var(--indigo-700); }
        .st-dikirim  { background: #e0f2fe; color: #075985; }
        .st-selesai  { background: #e6f4ea; color: #1e7a3c; }
        .st-batal    { background: #fdecea; color: #b3261e; }
        .dl-detail dt { color: #6b7590; font-weight: 500; font-size: .85rem; }
        .dl-detail dd { font-weight: 600; margin-bottom: 14px; }
        .empty-box { text-align: center; padding: 48px 16px; color: #6b7590; }
        .empty-box .ico { font-size: 3rem; }
        .ornament { color: var(--gold); letter-spacing: .3em; }
    </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar sticky-top">
    <nav class="navbar navbar-dark py-2">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center gap-2">
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Buka menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand mb-0 text-decoration-none" href="dashboard.php">👘 Ajil Batik <small>&nbsp;Admin</small></a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-sm btn-outline-light rounded-pill px-3 d-none d-sm-inline-block">Lihat Toko</a>
                <div class="user-chip">
                    <span class="avatar"><?= e(strtoupper(substr($_SESSION['admin'], 0, 1))) ?></span>
                    <span class="d-none d-md-inline"><?= e($_SESSION['admin']) ?></span>
                </div>
                <a href="logout.php" class="btn btn-sm btn-gold rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <aside class="col-lg-2 p-0 sidebar">
            <div class="offcanvas-lg offcanvas-start sidebar-off" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarLabel">
                <div class="offcanvas-header text-white">
                    <h5 class="offcanvas-title" id="sidebarLabel">Menu Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Tutup"></button>
                </div>
                <div class="offcanvas-body p-0 d-block">
                    <div class="nav-label">Kelola Toko</div>
                    <ul class="nav flex-column">
                        <?php foreach ($nav as $key => [$ikon, $label]): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $menu === $key ? 'active' : '' ?>" href="dashboard.php?menu=<?= $key ?>">
                                    <span><?= $ikon ?></span> <?= e($label) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="nav-label">Lainnya</div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="index.php"><span>🏬</span> Lihat Toko</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php"><span>🚪</span> Logout</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-lg-10 px-md-4 py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <h1 class="h2 page-title mb-0"><?= e($judul_halaman) ?></h1>
                <?php if ($menu === 'data_menu'): ?>
                    <a href="dashboard.php?menu=tambah_menu" class="btn btn-gold rounded-pill px-4">+ Tambah Menu</a>
                <?php endif; ?>
            </div>
            <div class="ornament mb-3">◆</div>

            <?php /* ============ TAMBAH / EDIT MENU ============ */ ?>
            <?php if ($menu === 'tambah_menu'): ?>
                <div class="card-custom p-4">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="id_edit" value="<?= $mode_edit ? e($id_edit_val) : '' ?>">
                        <input type="hidden" name="foto_lama" value="<?= e($foto) ?>">

                        <div class="col-12">
                            <label for="nama" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Kemeja Batik Parang Biru" value="<?= e($nama) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="harga" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" min="0" class="form-control" id="harga" name="harga" placeholder="150000" value="<?= e($harga) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" min="0" class="form-control" id="stok" name="stok" placeholder="Jumlah stok" value="<?= e($stok) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select id="kategori" class="form-select" name="kategori" required>
                                <?php
                                $result = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");
                                while ($list = mysqli_fetch_array($result)): ?>
                                    <option value="<?= e($list['id_kategori']) ?>" <?= (string)$id_kategori === (string)$list['id_kategori'] ? 'selected' : '' ?>>
                                        <?= e($list['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="foto" class="form-label">Foto Produk</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            <?php if (!empty($foto)): ?>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <img src="img/<?= e($foto) ?>" class="thumb" alt="Foto saat ini">
                                    <small class="text-muted">Foto saat ini. Kosongkan jika tidak ingin mengganti.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Bahan, motif, ukuran, dan keunggulan produk"><?= e($deskripsi) ?></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" name="simpan_menu" class="btn btn-primary rounded-pill px-4"><?= $mode_edit ? 'Simpan Perubahan' : 'Tambah Menu' ?></button>
                            <a href="dashboard.php?menu=data_menu" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                        </div>
                    </form>
                </div>

            <?php /* ============ DATA MENU ============ */ ?>
            <?php elseif ($menu === 'data_menu'): ?>
                <?php
                $hasil = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id DESC");
                ?>
                <div class="card-custom p-3">
                    <?php if (mysqli_num_rows($hasil) === 0): ?>
                        <div class="empty-box">
                            <div class="ico">👘</div>
                            <h5 class="fw-bold">Belum ada menu</h5>
                            <p>Tambahkan produk batik pertama untuk toko kamu.</p>
                            <a href="dashboard.php?menu=tambah_menu" class="btn btn-gold rounded-pill px-4">+ Tambah Menu</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th><th>Foto</th><th>Nama</th><th>Harga</th>
                                        <th>Stok</th><th>Kategori</th><th>Deskripsi</th><th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $no = 1; while ($data = mysqli_fetch_array($hasil)): ?>
                                    <tr>
                                        <td class="text-muted fw-semibold"><?= $no++ ?></td>
                                        <td><img src="img/<?= e($data['foto']) ?>" class="thumb" alt="<?= e($data['nama']) ?>"></td>
                                        <td class="fw-semibold"><?= e($data['nama']) ?></td>
                                        <td class="fw-semibold text-nowrap" style="color: var(--indigo-700);"><?= rupiah($data['harga']) ?></td>
                                        <td><span class="<?= (int)$data['stok'] <= 5 ? 'badge-stok-sedikit' : 'badge-stok-ok' ?>"><?= e($data['stok']) ?> pcs</span></td>
                                        <td><span class="badge-kat"><?= e($data['nama_kategori']) ?></span></td>
                                        <td class="text-secondary small" style="max-width: 240px;"><?= e(potong($data['deskripsi'] ?? '', 80)) ?></td>
                                        <td class="text-center text-nowrap">
                                            <a href="dashboard.php?menu=tambah_menu&aksi=edit&id=<?= urlencode($data['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                            <a href="dashboard.php?menu=data_menu&aksi=hapus&id=<?= urlencode($data['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus menu ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php /* ============ DATA TRANSAKSI ============ */ ?>
            <?php elseif ($menu === 'transaksi'): ?>
                <?php if (!$ada_transaksi): ?>
                    <div class="card-custom p-4">
                        <h5 class="fw-bold">Tabel transaksi tidak ditemukan</h5>
                        <p class="text-muted mb-0">Dashboard membutuhkan tabel <code>tb_transaksi</code> dan <code>tb_detail</code>.</p>
                    </div>
                <?php elseif ($aksi === 'detail' && isset($_GET['id'])): ?>
                    <?php
                    $id_t = esc($koneksi, $_GET['id']);
                    $qt = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE " . $pk_trx . "='$id_t'");
                    $t = $qt ? mysqli_fetch_assoc($qt) : null;
                    ?>
                    <?php if (!$t): ?>
                        <div class="card-custom empty-box"><div class="ico">🔎</div><h5 class="fw-bold">Transaksi tidak ditemukan</h5><a href="dashboard.php?menu=transaksi" class="btn btn-primary rounded-pill px-4 mt-2">Kembali</a></div>
                    <?php else: ?>
                        <?php
                        $items = mysqli_query($koneksi, "SELECT d.*, p.nama, p.foto, p.harga AS harga_satuan FROM tb_detail d LEFT JOIN tb_produk p ON d.id_produk = p.id WHERE d.id_transaksi='$id_t'");
                        ?>
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="card-custom p-4 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0"><?= e(kode_trx($t[$pk_trx])) ?></h5>
                                        <a href="struk.php?id=<?= urlencode($t[$pk_trx]) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">🖨️ Cetak Invoice</a>
                                    </div>
                                    <dl class="dl-detail mb-0">
                                        <?php foreach ($t as $kolom => $nilai): if ($kolom === $pk_trx) continue; ?>
                                            <dt><?= e($kolom === $pk_user ? 'Pelanggan' : label_kolom($kolom)) ?></dt>
                                            <dd><?= format_kolom($kolom, $nilai) ?></dd>
                                        <?php endforeach; ?>
                                    </dl>
                                    <?php if ($ada_status): ?>
                                        <form method="post" class="d-flex gap-2 mt-2">
                                            <input type="hidden" name="id_transaksi" value="<?= e($t[$pk_trx]) ?>">
                                            <select name="status" class="form-select" aria-label="Ubah status">
                                                <?php foreach ($daftar_status as $st): ?>
                                                    <option value="<?= e($st) ?>" <?= ($t['status'] ?? '') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="ubah_status" class="btn btn-primary rounded-pill px-3">Simpan</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="card-custom p-3">
                                    <h5 class="fw-bold px-2 pt-2">Produk yang Dibeli</h5>
                                    <div class="table-responsive">
                                        <table class="table table-custom align-middle mb-0">
                                            <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                                            <tbody>
                                            <?php $total = 0; while ($it = mysqli_fetch_assoc($items)):
                                                $sub = ($it['harga_satuan'] ?? 0) * $it['jumlah']; $total += $sub; ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="img/<?= e($it['foto'] ?? 'default.jpg') ?>" class="thumb" alt="">
                                                            <span class="fw-semibold"><?= e($it['nama'] ?? '(produk dihapus)') ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-nowrap"><?= rupiah($it['harga_satuan'] ?? 0) ?></td>
                                                    <td><?= e($it['jumlah']) ?> pcs</td>
                                                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($sub) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr><td colspan="3" class="text-end fw-bold fs-5 py-3">Total</td><td class="text-end fw-bold fs-5 py-3 text-nowrap" style="color: var(--indigo-700);"><?= rupiah($total) ?></td></tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="dashboard.php?menu=transaksi" class="btn btn-outline-secondary rounded-pill px-4">&larr; Kembali</a>
                            <a href="dashboard.php?menu=transaksi&aksi=hapus&id=<?= urlencode($t[$pk_trx]) ?>" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus Transaksi</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php
                    $hasil = mysqli_query($koneksi, "SELECT t.*, (SELECT COUNT(*) FROM tb_detail d WHERE d.id_transaksi = t." . $pk_trx . ") AS jml_item, (SELECT COALESCE(SUM(d.jumlah * p.harga), 0) FROM tb_detail d LEFT JOIN tb_produk p ON d.id_produk = p.id WHERE d.id_transaksi = t." . $pk_trx . ") AS total_hitung FROM tb_transaksi t ORDER BY t." . $pk_trx . " DESC");
                    $kolom_trx = [];
                    $punya_total = false;
                    foreach (mysqli_fetch_fields($hasil) as $f) {
                        if (in_array($f->name, [$pk_trx, 'jml_item', 'total_hitung'], true)) continue;
                        $kolom_trx[] = $f->name;
                        if (preg_match('/total|bayar/i', $f->name)) { $punya_total = true; }
                    }
                    ?>
                    <div class="card-custom p-3">
                        <?php if (mysqli_num_rows($hasil) === 0): ?>
                            <div class="empty-box"><div class="ico">🧾</div><h5 class="fw-bold">Belum ada transaksi</h5><p>Transaksi akan muncul di sini setelah pelanggan checkout.</p></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th><th>Kode</th>
                                            <?php foreach ($kolom_trx as $kol): ?><th><?= e($kol === $pk_user ? 'Pelanggan' : label_kolom($kol)) ?></th><?php endforeach; ?>
                                            <th>Item</th><?php if (!$punya_total): ?><th>Total</th><?php endif; ?><th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no = 1; while ($t = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td class="text-muted fw-semibold"><?= $no++ ?></td>
                                            <td class="fw-semibold text-nowrap"><?= e(kode_trx($t[$pk_trx])) ?></td>
                                            <?php foreach ($kolom_trx as $kol): ?><td><?= format_kolom($kol, $t[$kol]) ?></td><?php endforeach; ?>
                                            <td><?= e($t['jml_item']) ?></td>
                                            <?php if (!$punya_total): ?><td class="fw-semibold text-nowrap" style="color: var(--indigo-700);"><?= rupiah($t['total_hitung']) ?></td><?php endif; ?>
                                            <td class="text-center text-nowrap">
                                                <a href="dashboard.php?menu=transaksi&aksi=detail&id=<?= urlencode($t[$pk_trx]) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Detail</a>
                                                <a href="struk.php?id=<?= urlencode($t[$pk_trx]) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">🖨️ Cetak</a>
                                                <a href="dashboard.php?menu=transaksi&aksi=hapus&id=<?= urlencode($t[$pk_trx]) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php /* ============ DATA PELANGGAN ============ */ ?>
            <?php elseif ($menu === 'pelanggan'): ?>
                <?php
                $sembunyi = ['password', 'role'];

                if ($aksi === 'detail' && isset($_GET['id'])):
                    $id_p = esc($koneksi, $_GET['id']);
                    $qp = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE " . $pk_user . "='$id_p' AND role='pelanggan'");
                    $p = $qp ? mysqli_fetch_assoc($qp) : null;
                ?>
                    <?php if (!$p): ?>
                        <div class="card-custom empty-box"><div class="ico">🔎</div><h5 class="fw-bold">Pelanggan tidak ditemukan</h5><a href="dashboard.php?menu=pelanggan" class="btn btn-primary rounded-pill px-4 mt-2">Kembali</a></div>
                    <?php else: ?>
                        <div class="card-custom p-4" style="max-width: 640px;">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <span class="avatar" style="width:56px;height:56px;border-radius:50%;background:var(--gold);color:var(--indigo-900);font-weight:700;font-size:1.4rem;display:flex;align-items:center;justify-content:center;"><?= e(strtoupper(substr($p['username'] ?? '?', 0, 1))) ?></span>
                                <div>
                                    <h5 class="fw-bold mb-0"><?= e($p['nama_lengkap'] ?? $p['username'] ?? '-') ?></h5>
                                    <small class="text-muted">Pelanggan Ajil Batik</small>
                                </div>
                            </div>
                            <dl class="dl-detail row mb-0">
                                <?php foreach ($p as $kolom => $nilai): if (in_array($kolom, $sembunyi, true)) continue; ?>
                                    <dt class="col-sm-4"><?= e(label_kolom($kolom)) ?></dt>
                                    <dd class="col-sm-8"><?= ($nilai === null || $nilai === '') ? '<span class="text-muted">-</span>' : nl2br(e($nilai)) ?></dd>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="dashboard.php?menu=pelanggan" class="btn btn-outline-secondary rounded-pill px-4">&larr; Kembali</a>
                            <a href="dashboard.php?menu=pelanggan&aksi=hapus&id=<?= urlencode($p[$pk_user] ?? $p['username']) ?>" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">Hapus Pelanggan</a>
                        </div>
                    <?php endif; ?>

                <?php else:
                    $hasil = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE role='pelanggan' ORDER BY " . $pk_user . " DESC");
                    $kolom_tampil = [];
                    foreach (mysqli_fetch_fields($hasil) as $f) {
                        if (!in_array($f->name, $sembunyi, true) && $f->name !== $pk_user) { $kolom_tampil[] = $f->name; }
                    }
                ?>
                    <div class="card-custom p-3">
                        <?php if (mysqli_num_rows($hasil) === 0): ?>
                            <div class="empty-box"><div class="ico">👥</div><h5 class="fw-bold">Belum ada pelanggan</h5><p>Pelanggan yang mendaftar akan muncul di sini.</p></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <?php foreach ($kolom_tampil as $kol): ?><th><?= e(label_kolom($kol)) ?></th><?php endforeach; ?>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($hasil)): $idp = $row[$pk_user] ?? $row['username']; ?>
                                        <tr>
                                            <td class="text-muted fw-semibold"><?= $no++ ?></td>
                                            <?php foreach ($kolom_tampil as $kol): ?>
                                                <td><?= ($row[$kol] === null || $row[$kol] === '') ? '<span class="text-muted">-</span>' : e(potong($row[$kol], 60)) ?></td>
                                            <?php endforeach; ?>
                                            <td class="text-center text-nowrap">
                                                <a href="dashboard.php?menu=pelanggan&aksi=detail&id=<?= urlencode($idp) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Detail</a>
                                                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#modalReset<?= md5($idp) ?>">🔑 Reset PW</button>
                                                <a href="dashboard.php?menu=pelanggan&aksi=hapus&id=<?= urlencode($idp) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">Hapus</a>
                                            </td>
                                        </tr>

                                        <!-- MODAL FORM RESET PASSWORD -->
                                        <div class="modal fade" id="modalReset<?= md5($idp) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                                                    <form method="post">
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title fw-bold" style="color: var(--indigo-900);">Reset Password Pelanggan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_user" value="<?= e($idp) ?>">
                                                            <p class="text-muted small mb-3">Masukkan password baru untuk akun pelanggan: <b class="text-dark"><?= e($row['username'] ?? $idp) ?></b></p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Password Baru</label>
                                                                <input type="password" class="form-control" name="password_baru" placeholder="Masukkan password baru" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="reset_password_pelanggan" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark">Simpan Password</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END MODAL -->
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($flash): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $flash['tipe'] === 'success' ? 'success' : 'error' ?>',
            title: '<?= $flash['tipe'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
            text: '<?= addslashes($flash['pesan']) ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#1b2a5e'
        });
    });
</script>
<?php endif; ?>
</body>
</html>
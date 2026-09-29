<?php
session_start();
include 'koneksi.php';


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$swal_script = '';


$username_login = mysqli_real_escape_string($koneksi, $_SESSION['username']);
$query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$username_login' LIMIT 1");
$data = mysqli_fetch_array($query);

if (!$data) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Data profile tidak ditemukan!',
                    confirmButtonColor: '#0e1839'
                }).then(() => {
                    window.location = 'index.php';
                });
            });
          </script>";
    exit;
}

$db_nama = $username_login;
if (isset($data['nama'])) {
    $db_nama = $data['nama'];
} elseif (isset($data['nama_lengkap'])) {
    $db_nama = $data['nama_lengkap'];
} elseif (isset($data['name'])) {
    $db_nama = $data['name'];
} elseif (isset($data['fullname'])) {
    $db_nama = $data['fullname'];
}


if ($aksi == "edit") {
    $id = 0;
    if (isset($data['id'])) {
        $id = $data['id'];
    } elseif (isset($data['id_user'])) {
        $id = $data['id_user'];
    } elseif (isset($data['id_pelanggan'])) {
        $id = $data['id_pelanggan'];
    }

    $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id='$id' OR username='$username_login' LIMIT 1");
    $data = mysqli_fetch_array($query);

    if (isset($data['nama'])) {
        $db_nama = $data['nama'];
    } elseif (isset($data['nama_lengkap'])) {
        $db_nama = $data['nama_lengkap'];
    } elseif (isset($data['name'])) {
        $db_nama = $data['name'];
    } elseif (isset($data['fullname'])) {
        $db_nama = $data['fullname'];
    }
}


if (isset($_POST['simpan'])) {
    $id = 0;
    if (isset($data['id'])) {
        $id = $data['id'];
    } elseif (isset($data['id_user'])) {
        $id = $data['id_user'];
    } elseif (isset($data['id_pelanggan'])) {
        $id = $data['id_pelanggan'];
    }

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $no_hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    
    $kolom_nama = 'nama';
    $cek_1 = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_user LIKE 'nama_lengkap'");
    $cek_2 = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_user LIKE 'name'");
    
    if ($cek_1 && mysqli_num_rows($cek_1) > 0) {
        $kolom_nama = 'nama_lengkap';
    } elseif ($cek_2 && mysqli_num_rows($cek_2) > 0) {
        $kolom_nama = 'name';
    }

    $update = mysqli_query($koneksi, "UPDATE tb_user SET
        `$kolom_nama`='$nama',
        email='$email',
        username='$username',
        no_hp='$no_hp',
        alamat='$alamat'
        WHERE id='$id' OR username='$username_login'
    ");

    if ($update) {
        $_SESSION['username'] =$username;
        $swal_script = "
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = 'profile.php';
            });
        ";
    } else {
        $error_msg = mysqli_error($koneksi);$swal_script = "
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Profil gagal diubah: $error_msg',
                confirmButtonColor: '#0e1839'
            });
        ";
    }
}


$q_riwayat = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE username='$username_login' ORDER BY id_transaksi DESC");
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Ajil Batik</title>

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        }
        body { background-color: var(--paper); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, h5, .navbar-brand { font-family: 'Playfair Display', Georgia, serif; }
        .navbar-custom { background: var(--indigo-900) !important; border-bottom: 3px solid var(--gold); }
        .profile-section { min-height: 82vh; padding: 40px 20px; }
        
        /* Kartu menyesuaikan konten secara otomatis (tidak dipaksa sama tinggi) */
        .profile-card { width: 100%; background: #ffffff; border-radius: 24px; box-shadow: 0 12px 32px rgba(14, 24, 57, 0.08); border: 1px solid #e3e7f3; overflow: hidden; }
        
        .profile-header-bg { background: linear-gradient(135deg, var(--indigo-900), var(--indigo-700)); padding: 36px 30px 60px; text-align: center; position: relative; color: #fff; }
        .profile-avatar { width: 90px; height: 90px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--gold); color: var(--indigo-900); font-size: 38px; font-weight: 700; border: 4px solid #fff; box-shadow: 0 6px 16px rgba(0,0,0,0.15); position: absolute; bottom: -45px; left: 50%; transform: translateX(-50%); }
        .profile-body { padding: 60px 40px 40px; }
        .profile-title { text-align: center; font-size: 24px; font-weight: 700; color: var(--indigo-900); margin-bottom: 4px; }
        .profile-subtitle { text-align: center; color: #6b7590; font-size: 13px; margin-bottom: 24px; letter-spacing: 0.02em; }
        .profile-item { background: #fbfcfe; border: 1px solid #e3e7f3; border-radius: 14px; padding: 14px 18px; margin-bottom: 12px; transition: all 0.2s ease; }
        .profile-item:hover { border-color: var(--indigo-500); background: #fff; }
        .profile-item i { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--indigo-50); border-radius: 10px; color: var(--indigo-700); font-size: 18px; margin-right: 15px; flex-shrink: 0; }
        .profile-label { font-size: 11px; color: #6b7590; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 2px; }
        .profile-value { font-size: 15px; font-weight: 600; color: var(--ink); word-break: break-word; }
        .badge-role { background: var(--gold-soft); color: var(--gold-dark); padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; border: 1px solid rgba(201,151,63,0.3); }
        .btn-batik { background-color: var(--indigo-700); color: white; border-radius: 50px; padding: 11px 26px; font-weight: 600; border: none; transition: background 0.2s; font-family: 'Plus Jakarta Sans', sans-serif; }
        .btn-batik:hover { background-color: var(--indigo-900); color: white; }
        .btn-back { background: transparent; border: 1px solid #cbd5e1; color: #475569; border-radius: 50px; padding: 10px 24px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-back:hover { background: #f1f5f9; color: var(--indigo-900); border-color: var(--indigo-700); }
        .form-control, .form-select { border: 1px solid #cbd5e1; border-radius: 12px; padding: 12px 16px; font-size: 14px; background: #fbfcfe; font-family: 'Plus Jakarta Sans', sans-serif; }
        .form-control:focus { border-color: var(--indigo-500); box-shadow: 0 0 0 3px rgba(58, 79, 158, 0.12); background: #fff; }
        .form-label { font-weight: 600; font-size: 13px; color: var(--indigo-900); margin-bottom: 6px; }
        .ornament { color: var(--gold); letter-spacing: 0.3em; text-align: center; font-size: 14px; margin-bottom: 24px; }
    </style>
</head>

<body>
    <header>
        <div class="navbar navbar-dark navbar-custom shadow-sm py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <a href="index.php" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                    <span style="font-size: 1.5rem;">👘</span>
                    <strong class="fs-4 text-white">Ajil Batik</strong>
                </a>
                <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">&larr; Beranda</a>
            </div>
        </div>
    </header>

    <section class="profile-section">
        <div class="container-fluid px-lg-5">
            <div class="row g-4 justify-content-center align-items-start">
                
                <?php if ($aksi == "edit"): ?>
                    
                    <div class="col-lg-7 col-xl-6">
                        <div class="profile-card">
                            <div class="profile-header-bg">
                                <h3 class="mb-1 text-white" style="font-weight: 700;"><?= htmlspecialchars($db_nama) ?></h3>
                                <p class="text-white-50 mb-0 small">Pelanggan Setia Ajil Batik</p>
                                <div class="profile-avatar">
                                    <?= strtoupper(substr($db_nama, 0, 1)) ?>
                                </div>
                            </div>

                            <div class="profile-body">
                                <h2 class="profile-title">Edit Profil Saya</h2>
                                <p class="profile-subtitle">Kelola informasi data diri dan kontak akun Anda</p>
                                <div class="ornament">◆ ◆ ◆</div>

                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($db_nama) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <?php 
                                            $val_email = '';
                                            if (isset($data['email'])) { $val_email =$data['email']; }
                                        ?>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($val_email) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <?php 
                                            $val_username = '';
                                            if (isset($data['username'])) { $val_username =$data['username']; }
                                        ?>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($val_username) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">No. HP / WhatsApp</label>
                                        <?php 
                                            $val_nohp = '';
                                            if (isset($data['no_hp'])) { $val_nohp =$data['no_hp']; }
                                        ?>
                                        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($val_nohp) ?>" placeholder="Contoh: 081234567890">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alamat Pengiriman</label>
                                        <?php 
                                            $val_alamat = '';
                                            if (isset($data['alamat'])) { $val_alamat =$data['alamat']; }
                                        ?>
                                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap pengiriman pesanan"><?= htmlspecialchars($val_alamat) ?></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Role Akun</label>
                                        <?php 
                                            $val_role = '';
                                            if (isset($data['role'])) { $val_role =$data['role']; }
                                        ?>
                                        <input type="text" class="form-control bg-light text-muted" value="<?= htmlspecialchars($val_role) ?>" readonly>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 pt-2">
                                        <button type="submit" name="simpan" class="btn btn-batik shadow-sm">
                                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                                        </button>
                                        <a href="profile.php" class="btn-back">Batal</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    
                    <div class="col-lg-5 col-xl-5">
                        <div class="profile-card">
                            <div class="profile-header-bg">
                                <h3 class="mb-1 text-white" style="font-weight: 700;"><?= htmlspecialchars($db_nama) ?></h3>
                                <p class="text-white-50 mb-0 small">Pelanggan Setia Ajil Batik</p>
                                <div class="profile-avatar">
                                    <?= strtoupper(substr($db_nama, 0, 1)) ?>
                                </div>
                            </div>

                            <div class="profile-body">
                                <h2 class="profile-title">Informasi Akun</h2>
                                <p class="profile-subtitle">Kelola informasi data diri dan kontak akun Anda</p>
                                <div class="ornament">◆ ◆ ◆</div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person"></i>
                                        <div>
                                            <div class="profile-label">Nama Lengkap</div>
                                            <div class="profile-value"><?= htmlspecialchars($db_nama) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-at"></i>
                                        <div>
                                            <div class="profile-label">Username</div>
                                            <?php 
                                                $val_username = '-';
                                                if (isset($data['username'])) { $val_username =$data['username']; }
                                            ?>
                                            <div class="profile-value"><?= htmlspecialchars($val_username) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope"></i>
                                        <div>
                                            <div class="profile-label">Email</div>
                                            <?php 
                                                $val_email = '-';
                                                if (isset($data['email'])) { $val_email =$data['email']; }
                                            ?>
                                            <div class="profile-value"><?= htmlspecialchars($val_email) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-telephone"></i>
                                        <div>
                                            <div class="profile-label">No. HP / WhatsApp</div>
                                            <?php 
                                                $val_nohp = '-';
                                                if (isset($data['no_hp'])) { $val_nohp =$data['no_hp']; }
                                            ?>
                                            <div class="profile-value"><?= htmlspecialchars($val_nohp) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt"></i>
                                        <div>
                                            <div class="profile-label">Alamat Pengiriman</div>
                                            <?php 
                                                $val_alamat = '-';
                                                if (isset($data['alamat'])) { $val_alamat =$data['alamat']; }
                                            ?>
                                            <div class="profile-value"><?= nl2br(htmlspecialchars($val_alamat)) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shield-check"></i>
                                        <div>
                                            <div class="profile-label">Role Akun</div>
                                            <?php 
                                                $val_role = '-';
                                                if (isset($data['role'])) { $val_role =$data['role']; }
                                            ?>
                                            <div class="profile-value mt-1">
                                                <span class="badge-role"><?= htmlspecialchars($val_role) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4 pt-2">
                                    <a href="profile.php?aksi=edit" class="btn btn-batik shadow-sm mb-3">
                                        <i class="bi bi-pencil me-1"></i> Edit Profil
                                    </a>
                                    <div>
                                        <a href="index.php" class="btn-back">
                                            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

    
                    <div class="col-lg-7 col-xl-7">
                        <div class="profile-card">
                            <div class="p-4 p-md-5">
                                <h3 class="fw-bold mb-2" style="font-family: 'Playfair Display', Georgia, serif; color: var(--indigo-900);">📦 Riwayat Transaksi & Invoice</h3>
                                <p class="text-muted small mb-4">Daftar pesanan batik yang pernah Anda buat beserta pilihan cetak struk.</p>
                                
                                <?php 
                                    $jumlah_riwayat = 0;
                                    if ($q_riwayat) {
                                        $jumlah_riwayat = mysqli_num_rows($q_riwayat);
                                    }
                                ?>

                                <?php if ($jumlah_riwayat === 0): ?>
                                    <div class="text-center py-4 border rounded-4 bg-light">
                                        <p class="text-muted mb-0">Belum ada riwayat transaksi pembelian.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Penerima</th>
                                                    <th>Metode</th>
                                                    <th>Total</th>
                                                    <th class="text-center">Struk</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($trx = mysqli_fetch_assoc($q_riwayat)): ?>
                                                    <?php 
                                                        $id_trx = '';
                                                        if (isset($trx['id_transaksi'])) { $id_trx =$trx['id_transaksi']; } 
                                                        elseif (isset($trx['id'])) { $id_trx =$trx['id']; }

                                                        $nm_penerima = '-';
                                                        if (isset($trx['nama_penerima'])) { $nm_penerima =$trx['nama_penerima']; } 
                                                        elseif (isset($trx['nama'])) { $nm_penerima =$trx['nama']; }

                                                        $hp_penerima = '-';
                                                        if (isset($trx['no_hp'])) { $hp_penerima =$trx['no_hp']; }

                                                        $metode_byr = '-';
                                                        if (isset($trx['metode_pembayaran'])) { $metode_byr =$trx['metode_pembayaran']; } 
                                                        elseif (isset($trx['pembayaran'])) { $metode_byr =$trx['pembayaran']; }

                                                        $total_hrg = 0;
                                                        if (isset($trx['total_harga'])) { $total_hrg =$trx['total_harga']; } 
                                                        elseif (isset($trx['total'])) { $total_hrg =$trx['total']; }
                                                    ?>
                                                    <tr>
                                                        <td class="fw-bold text-primary">#<?= htmlspecialchars($id_trx) ?></td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($nm_penerima) ?></strong><br>
                                                            <small class="text-muted"><?= htmlspecialchars($hp_penerima) ?></small>
                                                        </td>
                                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($metode_byr) ?></span></td>
                                                        <td class="fw-bold text-dark">Rp <?= number_format((float)$total_hrg, 0, ',', '.') ?></td>
                                                        <td class="text-center">
                                                            <a href="struk.php?id=<?= htmlspecialchars($id_trx) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                                                📄 Cetak Struk
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <footer class="text-secondary py-4 bg-white border-top text-center">
        <div class="container">
            <p class="mb-0 small">&copy; <?= date('Y') ?> Ajil Batik. Elegan dan Berbudaya.</p>
        </div>
    </footer>

    <?php if (!empty($swal_script)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php echo $swal_script; ?>
        });
    </script>
    <?php endif; ?>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
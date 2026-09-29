<?php
include 'koneksi.php';

$swal_script = '';

if (!isset($_GET['id'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'ID produk tidak ditemukan!',
                confirmButtonColor: '#1b2a5e'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        });
    </script>";
    exit;
}

$id = intval($_GET['id']);

/* Ambil data produk */
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_produk WHERE id = $id"
);

if (!$query || mysqli_num_rows($query) == 0) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Produk tidak ditemukan!',
                confirmButtonColor: '#1b2a5e'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        });
    </script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

/* Jika tombol simpan ditekan */
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    $id_kategori = intval($_POST['id_kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $update = mysqli_query(
        $koneksi,
        "UPDATE tb_produk SET
            nama = '$nama',
            harga = '$harga',
            stok = '$stok',
            id_kategori = '$id_kategori',
            deskripsi = '$deskripsi'
         WHERE id = $id"
    );

    if ($update) {
        $swal_script = "
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data produk berhasil diperbarui.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = 'dashboard.php';
            });
        ";
    } else {
        $err_msg = addslashes(mysqli_error($koneksi));
        $swal_script = "
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal mengupdate data: $err_msg',
                confirmButtonColor: '#1b2a5e'
            });
        ";
    }
}

/* Ambil kategori */
$kategori = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_kategori"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Ajil Batik</title>
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #0e1839;
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        textarea {
            height: 100px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background: #22c55e;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #16a34a;
        }
        .kembali {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #555;
        }
        .kembali:hover {
            color: #000;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Produk</h2>

    <form method="POST">
        <label>Nama Produk</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

        <label>Harga</label>
        <input type="number" name="harga" value="<?= htmlspecialchars($data['harga']) ?>" required>

        <label>Stok</label>
        <input type="number" name="stok" value="<?= htmlspecialchars($data['stok']) ?>" required>

        <label>Kategori</label>
        <select name="id_kategori" required>
            <?php
            if ($kategori) {
                while ($k = mysqli_fetch_assoc($kategori)) {
            ?>
                <option value="<?= $k['id_kategori'] ?>" <?= ($k['id_kategori'] == $data['id_kategori']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
            <?php
                }
            }
            ?>
        </select>

        <label>Deskripsi</label>
        <textarea name="deskripsi" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>

        <button type="submit" name="update">Simpan Perubahan</button>
    </form>

    <a href="dashboard.php" class="kembali">← Kembali ke Dashboard</a>
</div>

<!-- SCRIPT MENJALANKAN SWEETALERT -->
<?php if ($swal_script !== ''): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?= $swal_script ?>
    });
</script>
<?php endif; ?>

</body>
</html>
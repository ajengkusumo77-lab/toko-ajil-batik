<?php
include 'koneksi.php';

$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if ($id == '') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'ID produk tidak ditemukan!',
                confirmButtonColor: '#0e1839'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        });
    </script>";
    exit;
}

$hapus = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id='$id'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Produk - Ajil Batik</title>
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($hapus) { ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: 'Data produk berhasil dihapus.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location = 'dashboard.php';
                });
            });
        </script>
    <?php } else { ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Data gagal dihapus: <?= mysqli_error($koneksi) ?>',
                    confirmButtonColor: '#0e1839'
                }).then(() => {
                    window.location = 'dashboard.php';
                });
            });
        </script>
    <?php } ?>
</body>
</html>
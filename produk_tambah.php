<?php
require_once 'config.php';
require_login();
$page_title = 'Jual Barang - Marketplace UNP';
$kategori_list = db_all($conn, 'SELECT * FROM kategori ORDER BY nama_kategori');
$err = [];
$f = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = validasi_produk($conn, $_POST, $err);
    $foto = '';
    if (!$err && !empty($_FILES['foto']['name'])) {
        $foto = uploadFoto($_FILES['foto']);
        if ($foto === false) { $foto = ''; $err[] = 'Foto tidak valid. Gunakan gambar JPG/PNG/GIF/WEBP maksimal 5 MB.'; }
    }
    if (!$err) {
        db_exec($conn,
            'INSERT INTO produk (nama_produk, id_kategori, harga, kondisi, deskripsi, foto, username_penjual) VALUES (?,?,?,?,?,?,?)',
            'sidssss', [$d['nama_produk'], $d['id_kategori'], $d['harga'], $d['kondisi'], $d['deskripsi'], $foto, $_SESSION['username']]);
        flash('success', 'Iklan berhasil dipasang.');
        redirect('produk_saya.php');
    }
}

$mode = 'tambah';
include 'header.php';
?>
<div class="page-header"><h1>Jual barang</h1><p>Pasang iklan barang bekas Anda.</p></div>
<?php foreach ($err as $m): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endforeach; ?>
<div class="card"><div class="card-body">
  <?php include '_produk_form.php'; ?>
</div></div>
<?php include 'footer.php'; ?>

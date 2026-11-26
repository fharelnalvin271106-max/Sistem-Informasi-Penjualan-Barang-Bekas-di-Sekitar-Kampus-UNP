<?php
require_once 'config.php';
require_login();
$page_title = 'Edit Produk - Marketplace UNP';
$me = $_SESSION['username'];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$produk = db_one($conn, 'SELECT * FROM produk WHERE id_produk = ?', 'i', [$id]);
if (!$produk || $produk['status'] === 'dihapus' || ($produk['username_penjual'] !== $me && !is_admin())) {
    flash('danger', 'Produk tidak ditemukan atau Anda tidak berhak mengubahnya.');
    redirect('produk_saya.php');
}

$kategori_list = db_all($conn, 'SELECT * FROM kategori ORDER BY nama_kategori');
$err = [];
$f = $produk;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = validasi_produk($conn, $_POST, $err);
    $status = ($_POST['status'] ?? '') === 'terjual' ? 'terjual' : 'tersedia';
    $foto = $produk['foto'];
    if (!$err && !empty($_FILES['foto']['name'])) {
        $baru = uploadFoto($_FILES['foto'], $produk['foto']);
        if ($baru === false) $err[] = 'Foto tidak valid. Gunakan gambar JPG/PNG/GIF/WEBP maksimal 5 MB.';
        else $foto = $baru;
    }
    if (!$err) {
        db_exec($conn,
            'UPDATE produk SET nama_produk=?, id_kategori=?, harga=?, kondisi=?, deskripsi=?, foto=?, status=? WHERE id_produk=?',
            'sidssssi', [$d['nama_produk'], $d['id_kategori'], $d['harga'], $d['kondisi'], $d['deskripsi'], $foto, $status, $id]);
        flash('success', 'Produk berhasil diperbarui.');
        redirect('produk_detail.php?id=' . $id);
    }
    $f = array_merge($produk, $_POST);
    $f['foto'] = $produk['foto'];
}

$mode = 'edit';
include 'header.php';
?>
<div class="page-header"><h1>Edit produk</h1><p><?= e($produk['nama_produk']) ?></p></div>
<?php foreach ($err as $m): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endforeach; ?>
<div class="card"><div class="card-body">
  <?php include '_produk_form.php'; ?>
</div></div>
<?php include 'footer.php'; ?>

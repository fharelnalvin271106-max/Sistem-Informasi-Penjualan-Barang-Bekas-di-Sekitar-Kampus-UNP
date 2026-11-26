<?php
require_once 'config.php';
require_login();
$page_title = 'Detail Produk - Marketplace UNP';
$me = $_SESSION['username'];

$id = (int)($_GET['id'] ?? 0);
$p = db_one($conn,
    "SELECT p.*, k.nama_kategori, k.icon, u.nama AS nama_penjual, u.no_hp
     FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori JOIN users u ON p.username_penjual = u.username
     WHERE p.id_produk = ?", 'i', [$id]);

$boleh_kelola = $p && ($p['username_penjual'] === $me || is_admin());
if ($p && $p['status'] === 'dihapus' && !$boleh_kelola) $p = null;

include 'header.php';

if (!$p) {
    echo '<div class="alert alert-danger">Produk tidak ditemukan atau sudah dihapus.</div><a href="produk.php" class="btn btn-outline">Kembali ke daftar produk</a>';
    include 'footer.php';
    exit;
}
$milik_sendiri = $p['username_penjual'] === $me;
?>
<div class="page-header split">
  <div><h1>Detail produk</h1></div>
  <a href="javascript:history.back()" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="detail-grid">
  <div class="card">
    <div class="card-body" style="text-align:center;">
      <?php if ($p['foto']): ?>
        <img src="uploads/produk/<?= e($p['foto']) ?>" alt="<?= e($p['nama_produk']) ?>" class="detail-foto">
      <?php else: ?>
        <div class="produk-noimg detail-noimg"><?= e($p['icon']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h2 class="detail-judul"><?= e($p['nama_produk']) ?></h2>
      <p class="produk-harga harga-besar"><?= rupiah($p['harga']) ?></p>
      <p style="margin:0 0 12px;">
        <span class="badge badge-diproses"><?= e(ucfirst($p['kondisi'])) ?></span>
        <span class="badge badge-admin"><?= e($p['icon']) ?> <?= e($p['nama_kategori']) ?></span>
        <span class="badge badge-<?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span>
      </p>
      <p class="muted" style="margin:0 0 16px;"><?= nl2br(e($p['deskripsi'] ?: 'Tidak ada deskripsi.')) ?></p>
      <hr class="sep">
      <p style="margin:0 0 4px;"><strong>Penjual:</strong> <?= e($p['nama_penjual']) ?> <span class="muted">(@<?= e($p['username_penjual']) ?>)</span></p>
      <p style="margin:0 0 4px;"><strong>Kontak:</strong> <?= e($p['no_hp']) ?></p>
      <p style="margin:0 0 16px;"><strong>Diiklankan:</strong> <?= e(tgl($p['created_at'])) ?></p>

      <div class="detail-actions">
        <?php if ($p['status'] === 'tersedia' && !$milik_sendiri): ?>
          <a href="pesanan_beli.php?id=<?= (int)$p['id_produk'] ?>" class="btn btn-success"><i class="fas fa-shopping-cart"></i> Beli sekarang</a>
          <a href="<?= e(wa_link($p['no_hp'])) ?>" target="_blank" rel="noopener" class="btn btn-outline"><i class="fab fa-whatsapp"></i> Chat penjual</a>
        <?php elseif ($p['status'] !== 'tersedia'): ?>
          <div class="alert alert-danger" style="margin:0;flex:1;">Produk ini sudah tidak tersedia.</div>
        <?php else: ?>
          <div class="alert alert-success" style="margin:0;flex:1;">Ini produk Anda sendiri.</div>
        <?php endif; ?>
      </div>

      <?php if ($boleh_kelola): ?>
      <hr class="sep">
      <div class="detail-actions">
        <a href="produk_edit.php?id=<?= (int)$p['id_produk'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen"></i> Edit produk</a>
        <?php if ($p['status'] !== 'dihapus') aksi_form('produk_hapus.php', ['id' => $p['id_produk'], 'kembali' => 'produk_saya.php'], 'Hapus produk', 'btn-danger', 'Hapus produk ini? Pesanan yang masih menunggu akan dibatalkan.'); ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>

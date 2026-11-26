<?php
require_once 'config.php';
require_login();
$page_title = 'Produk Saya - Marketplace UNP';
$me = $_SESSION['username'];

$rows = db_all($conn,
    "SELECT p.*, k.nama_kategori, k.icon FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori
     WHERE p.username_penjual = ? AND p.status <> 'dihapus' ORDER BY p.created_at DESC", 's', [$me]);

include 'header.php';
?>
<div class="page-header split">
  <div><h1>Produk saya</h1><p>Barang yang Anda iklankan.</p></div>
  <a href="produk_tambah.php" class="btn btn-primary"><i class="fas fa-plus"></i> Jual barang</a>
</div>

<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Produk</th><th>Harga</th><th>Kondisi</th><th>Status</th><th>Diiklankan</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $p): ?>
        <tr>
          <td>
            <a href="produk_detail.php?id=<?= (int)$p['id_produk'] ?>" class="cell-produk">
              <?php if ($p['foto']): ?><img src="uploads/produk/<?= e($p['foto']) ?>" alt="" class="thumb"><?php else: ?><span class="thumb thumb-ph"><?= e($p['icon']) ?></span><?php endif; ?>
              <span><strong><?= e($p['nama_produk']) ?></strong><br><span class="small muted"><?= e($p['nama_kategori']) ?></span></span>
            </a>
          </td>
          <td class="nowrap"><?= rupiah($p['harga']) ?></td>
          <td><span class="badge badge-diproses"><?= e(ucfirst($p['kondisi'])) ?></span></td>
          <td><span class="badge badge-<?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td>
          <td class="nowrap small muted"><?= e(tgl($p['created_at'])) ?></td>
          <td>
            <div class="actions">
              <a href="produk_edit.php?id=<?= (int)$p['id_produk'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <?php aksi_form('produk_hapus.php', ['id' => $p['id_produk'], 'kembali' => 'produk_saya.php'], 'Hapus', 'btn-danger', 'Hapus produk ini?'); ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="card-body"><div class="empty-state"><div class="empty-icon">🏷️</div><p>Belum ada produk. <a href="produk_tambah.php">Pasang iklan pertama</a>.</p></div></div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>

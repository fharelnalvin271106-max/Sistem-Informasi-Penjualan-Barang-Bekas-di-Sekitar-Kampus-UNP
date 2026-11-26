<?php
require_once 'config.php';
require_admin();
$page_title = 'Kelola Produk - Marketplace UNP';

$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$where = [];
$types = '';
$params = [];
if (in_array($status, ['tersedia', 'terjual', 'dihapus'], true)) { $where[] = 'p.status = ?'; $types .= 's'; $params[] = $status; }
if ($q !== '') {
    $where[] = '(p.nama_produk LIKE ? OR p.username_penjual LIKE ?)';
    $types .= 'ss';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$rows = db_all($conn,
    "SELECT p.*, k.nama_kategori, k.icon FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori"
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY p.created_at DESC', $types, $params);

$kembali = 'admin_produk.php' . ($_SERVER['QUERY_STRING'] ? '?' . preg_replace('/[^A-Za-z0-9_=&%.\-]/', '', $_SERVER['QUERY_STRING']) : '');
include 'header.php';
?>
<div class="page-header"><h1>Kelola produk</h1><p>Semua iklan dari semua penjual.</p></div>

<div class="card mb">
  <div class="card-body">
    <form method="GET" class="filter-bar">
      <div style="flex:1;min-width:200px;">
        <label class="form-label" for="q">Cari nama produk atau penjual</label>
        <input type="text" id="q" name="q" class="form-control" value="<?= e($q) ?>">
      </div>
      <div>
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-control" style="width:160px;">
          <option value="">Semua</option>
          <?php foreach (['tersedia', 'terjual', 'dihapus'] as $s): ?>
          <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
      <a href="admin_produk.php" class="btn btn-outline">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Produk</th><th>Penjual</th><th>Harga</th><th>Status</th><th>Diiklankan</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $p): ?>
        <tr>
          <td>
            <a href="produk_detail.php?id=<?= (int)$p['id_produk'] ?>" class="cell-produk">
              <?php if ($p['foto']): ?><img src="uploads/produk/<?= e($p['foto']) ?>" alt="" class="thumb"><?php else: ?><span class="thumb thumb-ph"><?= e($p['icon']) ?></span><?php endif; ?>
              <span><strong><?= e($p['nama_produk']) ?></strong><br><span class="small muted"><?= e($p['nama_kategori']) ?> · <?= e(ucfirst($p['kondisi'])) ?></span></span>
            </a>
          </td>
          <td>@<?= e($p['username_penjual']) ?></td>
          <td class="nowrap"><?= rupiah($p['harga']) ?></td>
          <td><span class="badge badge-<?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td>
          <td class="nowrap small muted"><?= e(tgl($p['created_at'])) ?></td>
          <td>
            <div class="actions">
              <?php if ($p['status'] !== 'dihapus'): ?>
                <a href="produk_edit.php?id=<?= (int)$p['id_produk'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <?php aksi_form('produk_hapus.php', ['id' => $p['id_produk'], 'kembali' => $kembali], 'Hapus', 'btn-danger', 'Hapus produk ini?'); ?>
              <?php else: ?>
                <span class="small muted">Sudah dihapus</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="card-body"><div class="empty-state"><div class="empty-icon">📦</div><p>Tidak ada produk yang cocok dengan filter ini.</p></div></div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>

<?php
require_once 'config.php';
require_login();
$page_title = 'Semua Produk - Marketplace UNP';

$kat = (int)($_GET['kategori'] ?? 0);
$q = trim($_GET['q'] ?? '');
$urut = $_GET['urut'] ?? 'terbaru';

$where = ["p.status = 'tersedia'"];
$types = '';
$params = [];
if ($kat > 0) { $where[] = 'p.id_kategori = ?'; $types .= 'i'; $params[] = $kat; }
if ($q !== '') { $where[] = 'p.nama_produk LIKE ?'; $types .= 's'; $params[] = '%' . $q . '%'; }
$order = ['termurah' => 'p.harga ASC', 'termahal' => 'p.harga DESC'][$urut] ?? 'p.created_at DESC';

$kategori = db_all($conn, 'SELECT * FROM kategori ORDER BY nama_kategori');
$produk = db_all($conn,
    "SELECT p.*, k.nama_kategori, k.icon FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori
     WHERE " . implode(' AND ', $where) . " ORDER BY $order", $types, $params);

include 'header.php';
?>
<div class="page-header"><h1>Semua produk</h1><p>Temukan barang bekas berkualitas di sekitar kampus.</p></div>

<div class="card mb">
  <div class="card-body">
    <form method="GET" class="filter-bar">
      <div style="flex:1;min-width:200px;">
        <label class="form-label" for="q">Cari produk</label>
        <input type="text" id="q" name="q" class="form-control" value="<?= e($q) ?>" placeholder="Nama produk...">
      </div>
      <div>
        <label class="form-label" for="kategori">Kategori</label>
        <select id="kategori" name="kategori" class="form-control" style="width:190px;">
          <option value="">Semua</option>
          <?php foreach ($kategori as $k): ?>
          <option value="<?= (int)$k['id_kategori'] ?>" <?= $kat === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['icon']) ?> <?= e($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label" for="urut">Urutkan</label>
        <select id="urut" name="urut" class="form-control" style="width:150px;">
          <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
          <option value="termurah" <?= $urut === 'termurah' ? 'selected' : '' ?>>Termurah</option>
          <option value="termahal" <?= $urut === 'termahal' ? 'selected' : '' ?>>Termahal</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
      <a href="produk.php" class="btn btn-outline">Reset</a>
    </form>
  </div>
</div>

<?php if ($produk): ?>
<div class="produk-grid">
  <?php foreach ($produk as $p) render_produk_card($p); ?>
</div>
<?php else: ?>
<div class="card"><div class="card-body"><div class="empty-state"><div class="empty-icon">🔍</div><p>Tidak ada produk yang cocok. Coba kata kunci atau kategori lain.</p></div></div></div>
<?php endif; ?>
<?php include 'footer.php'; ?>

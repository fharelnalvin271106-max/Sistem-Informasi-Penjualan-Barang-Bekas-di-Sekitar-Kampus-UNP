<?php
require_once 'config.php';
require_login();
$page_title = 'Beli Barang - Marketplace UNP';
$me = $_SESSION['username'];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$p = db_one($conn,
    "SELECT p.*, k.icon, u.nama AS nama_penjual, u.no_hp
     FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori JOIN users u ON p.username_penjual = u.username
     WHERE p.id_produk = ?", 'i', [$id]);

if (!$p || $p['status'] !== 'tersedia') {
    flash('danger', 'Produk tidak ditemukan atau sudah tidak tersedia.');
    redirect('produk.php');
}
if ($p['username_penjual'] === $me) {
    flash('danger', 'Anda tidak bisa membeli produk sendiri.');
    redirect('produk_detail.php?id=' . $id);
}

$sudah = db_one($conn, "SELECT id_pesanan FROM pesanan WHERE id_produk = ? AND username_pembeli = ? AND status IN ('pending','dikonfirmasi')", 'is', [$id, $me]);
if ($sudah) {
    flash('warning', 'Anda sudah punya pesanan aktif untuk produk ini.');
    redirect('pesanan_saya.php');
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $metode = $_POST['metode_bayar'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');
    if (!in_array($metode, ['cod', 'transfer'], true)) {
        $err = 'Pilih metode pembayaran.';
    } else {
        db_exec($conn,
            'INSERT INTO pesanan (id_produk, username_pembeli, jumlah, total_harga, metode_bayar, catatan) VALUES (?, ?, 1, ?, ?, ?)',
            'isdss', [$id, $me, $p['harga'], $metode, $catatan]);
        flash('success', 'Pesanan terkirim. Tunggu konfirmasi dari penjual.');
        redirect('pesanan_saya.php');
    }
}

include 'header.php';
?>
<div class="page-header"><h1>Konfirmasi pesanan</h1><p>Periksa barang dan pilih cara bayar.</p></div>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<div class="detail-grid">
  <div class="card">
    <div class="card-body">
      <div class="cell-produk" style="margin-bottom:14px;">
        <?php if ($p['foto']): ?><img src="uploads/produk/<?= e($p['foto']) ?>" alt="" class="thumb thumb-lg"><?php else: ?><span class="thumb thumb-lg thumb-ph"><?= e($p['icon']) ?></span><?php endif; ?>
        <div>
          <h3 style="margin:0 0 6px;font-family:var(--font-display);"><?= e($p['nama_produk']) ?></h3>
          <span class="badge badge-diproses"><?= e(ucfirst($p['kondisi'])) ?></span>
        </div>
      </div>
      <p class="produk-harga harga-besar"><?= rupiah($p['harga']) ?></p>
      <hr class="sep">
      <p style="margin:0 0 4px;"><strong>Penjual:</strong> <?= e($p['nama_penjual']) ?> <span class="muted">(@<?= e($p['username_penjual']) ?>)</span></p>
      <p style="margin:0;"><strong>Kontak:</strong> <?= e($p['no_hp']) ?></p>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$p['id_produk'] ?>">
        <div class="form-group">
          <label class="form-label" for="metode_bayar">Metode pembayaran</label>
          <select id="metode_bayar" name="metode_bayar" class="form-control" required>
            <option value="cod" <?= ($_POST['metode_bayar'] ?? 'cod') === 'cod' ? 'selected' : '' ?>>COD (bayar saat bertemu)</option>
            <option value="transfer" <?= ($_POST['metode_bayar'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="catatan">Catatan untuk penjual</label>
          <textarea id="catatan" name="catatan" class="form-control" rows="4" placeholder="Usul tempat dan waktu bertemu, pertanyaan soal barang..."><?= e($_POST['catatan'] ?? '') ?></textarea>
        </div>
        <div class="total-box"><span>Total bayar</span><strong><?= rupiah($p['harga']) ?></strong></div>
        <div class="detail-actions">
          <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Kirim pesanan</button>
          <a href="produk_detail.php?id=<?= (int)$p['id_produk'] ?>" class="btn btn-outline">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>

<?php
require_once 'config.php';
require_login();
$page_title = 'Dashboard - Marketplace UNP';
$me = $_SESSION['username'];

$total_produk  = (int)db_one($conn, "SELECT COUNT(*) AS c FROM produk WHERE status = 'tersedia'")['c'];
$produk_saya   = (int)db_one($conn, "SELECT COUNT(*) AS c FROM produk WHERE username_penjual = ? AND status = 'tersedia'", 's', [$me])['c'];
$pesanan_masuk = (int)db_one($conn, "SELECT COUNT(*) AS c FROM pesanan o JOIN produk p ON o.id_produk = p.id_produk WHERE p.username_penjual = ? AND o.status = 'pending'", 's', [$me])['c'];
$pesanan_saya  = (int)db_one($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE username_pembeli = ?", 's', [$me])['c'];
$total_akun    = is_admin() ? (int)db_one($conn, "SELECT COUNT(*) AS c FROM users")['c'] : 0;

// Peringatan jika admin masih memakai password bawaan
$pw_default = false;
if (is_admin()) {
    $row = db_one($conn, 'SELECT password FROM users WHERE username = ?', 's', [$me]);
    $pw_default = $row && password_verify('admin', $row['password']);
}

$recent = db_all($conn,
    "SELECT p.*, k.nama_kategori, k.icon FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori
     WHERE p.status = 'tersedia' ORDER BY p.created_at DESC LIMIT 6");

include 'header.php';
?>
<div class="page-header">
  <h1>Halo, <?= e($_SESSION['nama']) ?></h1>
  <p>Ringkasan aktivitas jual beli di marketplace kampus.</p>
</div>

<?php if ($pw_default): ?>
<div class="alert alert-warning"><i class="fas fa-triangle-exclamation"></i>
  Password admin masih bawaan ("admin"). <a href="profil.php">Ganti password sekarang</a>.</div>
<?php endif; ?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon blue">🛍️</div><div><div class="stat-value"><?= $total_produk ?></div><div class="stat-label">Produk tersedia</div></div></div>
  <div class="stat-card"><div class="stat-icon yellow">🏷️</div><div><div class="stat-value"><?= $produk_saya ?></div><div class="stat-label">Produk saya</div></div></div>
  <div class="stat-card"><div class="stat-icon red">📥</div><div><div class="stat-value"><?= $pesanan_masuk ?></div><div class="stat-label">Pesanan menunggu konfirmasi</div></div></div>
  <div class="stat-card"><div class="stat-icon green">🧾</div><div><div class="stat-value"><?= $pesanan_saya ?></div><div class="stat-label">Pesanan saya</div></div></div>
  <?php if (is_admin()): ?>
  <div class="stat-card"><div class="stat-icon blue">👥</div><div><div class="stat-value"><?= $total_akun ?></div><div class="stat-label">Total akun</div></div></div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Produk terbaru</span>
    <a href="produk.php" class="btn btn-sm btn-outline">Lihat semua</a>
  </div>
  <div class="card-body">
    <?php if ($recent): ?>
    <div class="produk-grid">
      <?php foreach ($recent as $p) render_produk_card($p); ?>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="empty-icon">📭</div><p>Belum ada produk. <a href="produk_tambah.php">Jual barang pertamamu</a>.</p></div>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>

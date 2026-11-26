<?php
require_once 'config.php';
require_admin();
$page_title = 'Kelola Pesanan - Marketplace UNP';

$status = $_GET['status'] ?? '';
$daftar = ['pending', 'dikonfirmasi', 'selesai', 'dibatalkan'];
if (in_array($status, $daftar, true)) {
    $rows = db_all($conn, SQL_PESANAN . ' WHERE o.status = ? ORDER BY o.created_at DESC', 's', [$status]);
} else {
    $status = '';
    $rows = db_all($conn, SQL_PESANAN . ' ORDER BY (o.status = \'pending\') DESC, o.created_at DESC');
}
$mode = 'admin';
$kembali = 'admin_pesanan.php' . ($status ? '?status=' . $status : '');
$pesan_kosong = 'Tidak ada pesanan dengan status ini.';

include 'header.php';
?>
<div class="page-header"><h1>Kelola pesanan</h1><p>Semua pesanan di marketplace.</p></div>

<div class="chips">
  <a href="admin_pesanan.php" class="chip <?= $status === '' ? 'active' : '' ?>">Semua</a>
  <?php foreach ($daftar as $s): ?>
  <a href="admin_pesanan.php?status=<?= $s ?>" class="chip <?= $status === $s ? 'active' : '' ?>"><?= ucfirst($s) ?></a>
  <?php endforeach; ?>
</div>

<div class="card"><?php include '_pesanan_tabel.php'; ?></div>
<?php include 'footer.php'; ?>

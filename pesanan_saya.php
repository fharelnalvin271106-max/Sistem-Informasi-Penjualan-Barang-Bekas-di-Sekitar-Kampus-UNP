<?php
require_once 'config.php';
require_login();
$page_title = 'Pesanan Saya - Marketplace UNP';
$me = $_SESSION['username'];

$rows = db_all($conn, SQL_PESANAN . ' WHERE o.username_pembeli = ? ORDER BY o.created_at DESC', 's', [$me]);
$mode = 'pembeli';
$kembali = 'pesanan_saya.php';
$pesan_kosong = 'Anda belum memesan apa pun. Mulai dari daftar produk.';

include 'header.php';
?>
<div class="page-header"><h1>Pesanan saya</h1><p>Barang yang Anda pesan dari penjual lain.</p></div>
<div class="card"><?php include '_pesanan_tabel.php'; ?></div>
<?php include 'footer.php'; ?>

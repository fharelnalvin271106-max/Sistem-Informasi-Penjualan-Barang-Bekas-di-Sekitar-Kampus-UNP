<?php
require_once 'config.php';
require_login();
$page_title = 'Pesanan Masuk - Marketplace UNP';
$me = $_SESSION['username'];

$rows = db_all($conn, SQL_PESANAN . ' WHERE p.username_penjual = ? ORDER BY (o.status = \'pending\') DESC, o.created_at DESC', 's', [$me]);
$mode = 'penjual';
$kembali = 'pesanan.php';
$pesan_kosong = 'Belum ada pesanan untuk barang Anda.';

include 'header.php';
?>
<div class="page-header"><h1>Pesanan masuk</h1><p>Pembeli yang memesan barang Anda. Konfirmasi lalu atur pertemuan lewat WhatsApp.</p></div>
<div class="card"><?php include '_pesanan_tabel.php'; ?></div>
<?php include 'footer.php'; ?>

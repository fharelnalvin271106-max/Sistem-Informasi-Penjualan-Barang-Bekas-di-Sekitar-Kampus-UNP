<?php
require_once 'config.php';
require_login();
$me = $_SESSION['username'];
$kembali = safe_back('produk_saya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect($kembali);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$p = db_one($conn, 'SELECT * FROM produk WHERE id_produk = ?', 'i', [$id]);

if (!$p || $p['status'] === 'dihapus' || ($p['username_penjual'] !== $me && !is_admin())) {
    flash('danger', 'Produk tidak ditemukan atau Anda tidak berhak menghapusnya.');
    redirect($kembali);
}

// Pesanan yang sudah dikonfirmasi harus diselesaikan atau dibatalkan lebih dulu
$aktif = db_one($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE id_produk = ? AND status = 'dikonfirmasi'", 'i', [$id])['c'];
if ($aktif > 0) {
    flash('danger', 'Produk punya pesanan yang sudah dikonfirmasi. Selesaikan atau batalkan pesanan itu dulu.');
    redirect($kembali);
}

// Hapus lunak: produk disembunyikan, riwayat pesanan tetap tersimpan
db_exec($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_produk = ? AND status = 'pending'", 'i', [$id]);
db_exec($conn, "UPDATE produk SET status = 'dihapus' WHERE id_produk = ?", 'i', [$id]);
flash('success', 'Produk berhasil dihapus.');
redirect($kembali);

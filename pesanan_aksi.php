<?php
/* Mengubah status pesanan: konfirmasi | selesai | batal */
require_once 'config.php';
require_login();
$me = $_SESSION['username'];
$kembali = safe_back('pesanan_saya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect($kembali);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';

$o = db_one($conn,
    'SELECT o.*, p.username_penjual, p.status AS status_produk
     FROM pesanan o JOIN produk p ON o.id_produk = p.id_produk WHERE o.id_pesanan = ?', 'i', [$id]);
if (!$o) { flash('danger', 'Pesanan tidak ditemukan.'); redirect($kembali); }

$penjual = $o['username_penjual'] === $me;
$pembeli = $o['username_pembeli'] === $me;
$admin = is_admin();
$kelola = $penjual || $admin;

try {
    $conn->begin_transaction();
    // Kunci baris produk agar dua penjual/admin tidak mengonfirmasi bersamaan
    $prod = db_one($conn, 'SELECT status FROM produk WHERE id_produk = ? FOR UPDATE', 'i', [$o['id_produk']]);
    $cur = db_one($conn, 'SELECT status FROM pesanan WHERE id_pesanan = ? FOR UPDATE', 'i', [$id]);
    $status = $cur['status'];
    $pesan = null;

    if ($aksi === 'konfirmasi' && $kelola && $status === 'pending') {
        if ($prod['status'] !== 'tersedia') {
            $conn->rollback();
            flash('danger', 'Produk sudah tidak tersedia, pesanan tidak bisa dikonfirmasi.');
            redirect($kembali);
        }
        db_exec($conn, "UPDATE pesanan SET status = 'dikonfirmasi' WHERE id_pesanan = ?", 'i', [$id]);
        db_exec($conn, "UPDATE produk SET status = 'terjual' WHERE id_produk = ?", 'i', [$o['id_produk']]);
        // Pesanan lain yang menunggu untuk produk yang sama otomatis dibatalkan
        db_exec($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_produk = ? AND id_pesanan <> ? AND status = 'pending'", 'ii', [$o['id_produk'], $id]);
        $pesan = 'Pesanan dikonfirmasi. Hubungi pembeli untuk mengatur pertemuan.';
    } elseif ($aksi === 'selesai' && $kelola && $status === 'dikonfirmasi') {
        db_exec($conn, "UPDATE pesanan SET status = 'selesai' WHERE id_pesanan = ?", 'i', [$id]);
        $pesan = 'Pesanan ditandai selesai.';
    } elseif ($aksi === 'batal' && (
            ($status === 'pending' && ($pembeli || $kelola)) ||
            ($status === 'dikonfirmasi' && $kelola))) {
        db_exec($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_pesanan = ?", 'i', [$id]);
        if ($status === 'dikonfirmasi' && $prod['status'] === 'terjual') {
            db_exec($conn, "UPDATE produk SET status = 'tersedia' WHERE id_produk = ?", 'i', [$o['id_produk']]);
        }
        $pesan = 'Pesanan dibatalkan.';
    }

    if ($pesan === null) {
        $conn->rollback();
        flash('danger', 'Aksi tidak diizinkan untuk pesanan ini.');
    } else {
        $conn->commit();
        flash('success', $pesan);
    }
} catch (Throwable $ex) {
    $conn->rollback();
    flash('danger', 'Terjadi kesalahan saat memproses pesanan. Coba lagi.');
}
redirect($kembali);

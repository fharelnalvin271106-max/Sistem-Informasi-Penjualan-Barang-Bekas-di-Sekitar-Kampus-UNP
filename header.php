<?php
require_once 'config.php';
require_login();
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
$me = $_SESSION['username'];

// Pesanan baru yang menunggu konfirmasi saya sebagai penjual
$notif_masuk = (int)db_one($conn,
    "SELECT COUNT(*) AS c FROM pesanan o JOIN produk p ON o.id_produk = p.id_produk
     WHERE p.username_penjual = ? AND o.status = 'pending'", 's', [$me])['c'];
// Semua pesanan pending (untuk admin)
$notif_admin = 0;
if ($role === 'admin') {
    $notif_admin = (int)db_one($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status = 'pending'")['c'];
}
$flash = get_flash();

function nav_link($file, $icon, $label, $current, $badge = 0) {
    $active = $current === $file ? 'active' : '';
    echo '<a href="' . $file . '" class="sidebar-link ' . $active . '"><i class="fas ' . $icon . '"></i> ' . $label;
    if ($badge > 0) echo '<span class="notif-badge">' . $badge . '</span>';
    echo '</a>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Marketplace UNP') ?></title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
  <a href="dashboard.php" class="navbar-brand"><span>🛒</span> Marketplace UNP</a>
  <div style="display:flex;align-items:center;gap:16px;">
    <span class="nav-user">👤 <?= e($_SESSION['nama']) ?>
      <span class="role-badge role-<?= e($role) ?>"><?= strtoupper(e($role)) ?></span>
    </span>
    <form method="POST" action="logout.php" style="margin:0;">
      <?= csrf_field() ?>
      <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
    </form>
  </div>
</nav>
<div class="page-wrapper">
<aside class="sidebar">
  <div class="sidebar-title">Menu utama</div>
  <?php
  nav_link('dashboard.php',      'fa-tachometer-alt', 'Dashboard',      $current_page);
  nav_link('produk.php',         'fa-shopping-bag',   'Semua Produk',   $current_page);
  nav_link('produk_tambah.php',  'fa-plus-circle',    'Jual Barang',    $current_page);
  nav_link('produk_saya.php',    'fa-store',          'Produk Saya',    $current_page);
  nav_link('pesanan.php',        'fa-inbox',          'Pesanan Masuk',  $current_page, $notif_masuk);
  nav_link('pesanan_saya.php',   'fa-receipt',        'Pesanan Saya',   $current_page);
  nav_link('profil.php',         'fa-user-gear',      'Profil Saya',    $current_page);
  if ($role === 'admin') {
      echo '<div class="sidebar-title" style="margin-top:10px;">Admin panel</div>';
      nav_link('admin_produk.php',   'fa-boxes',           'Kelola Produk',  $current_page);
      nav_link('admin_kategori.php', 'fa-tags',            'Kategori',       $current_page);
      nav_link('admin_pesanan.php',  'fa-clipboard-check', 'Kelola Pesanan', $current_page, $notif_admin);
      nav_link('admin_user.php',     'fa-users',           'Kelola Akun',    $current_page);
  }
  ?>
</aside>
<main class="content-area">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>

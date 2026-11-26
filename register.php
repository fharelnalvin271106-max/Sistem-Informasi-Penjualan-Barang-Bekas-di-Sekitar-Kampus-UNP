<?php
require_once 'config.php';
if (!empty($_SESSION['username'])) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';

    if ($username === '' || $nama === '' || $no_hp === '' || $password === '') {
        $error = 'Username, nama, no. HP, dan password wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter: huruf, angka, titik, atau garis bawah.';
    } elseif (!preg_match('/^\+?[0-9]{8,15}$/', $no_hp)) {
        $error = 'No. HP tidak valid (8-15 angka).';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (db_one($conn, 'SELECT username FROM users WHERE username = ?', 's', [$username])) {
        $error = 'Username sudah dipakai. Coba username lain.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_exec($conn,
            "INSERT INTO users (username, nama, no_hp, email, role, password) VALUES (?, ?, ?, ?, 'user', ?)",
            'sssss', [$username, $nama, $no_hp, $email, $hash]);
        redirect('index.php?registered=1');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar - Marketplace UNP</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
  <div class="login-card" style="max-width:480px;">
    <div class="login-logo"><div class="logo-icon">📝</div><h1>Daftar akun</h1><p>Gratis, cukup beberapa menit</p></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" required autocomplete="username"></div>
      <div class="form-group"><label class="form-label">Nama lengkap *</label><input type="text" name="nama" class="form-control" value="<?= e($_POST['nama'] ?? '') ?>" required></div>
      <div class="form-group"><label class="form-label">No. HP / WhatsApp *</label><input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= e($_POST['no_hp'] ?? '') ?>" required></div>
      <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Password * (minimal 6 karakter)</label><input type="password" name="password" class="form-control" required autocomplete="new-password"></div>
      <div class="form-group"><label class="form-label">Konfirmasi password *</label><input type="password" name="konfirmasi" class="form-control" required autocomplete="new-password"></div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-user-plus"></i> Daftar</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:.875rem;">Sudah punya akun? <a href="index.php">Masuk</a></p>
  </div>
</div>
</body>
</html>

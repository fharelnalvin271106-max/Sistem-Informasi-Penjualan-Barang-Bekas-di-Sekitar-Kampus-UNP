<?php
require_once 'config.php';
if (!empty($_SESSION['username'])) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $u = db_one($conn, 'SELECT * FROM users WHERE username = ?', 's', [$username]);
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $u['username'];
            $_SESSION['nama'] = $u['nama'];
            $_SESSION['role'] = $u['role'];
            redirect('dashboard.php');
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk - Marketplace UNP</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">🛒</div>
      <h1>Marketplace UNP</h1>
      <p>Jual beli barang bekas sekitar kampus UNP</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['registered'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Akun berhasil dibuat. Silakan masuk.</div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;"><i class="fas fa-sign-in-alt"></i> Masuk</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:.875rem;color:#64748b;">Belum punya akun? <a href="register.php" style="font-weight:600;">Daftar</a></p>
  </div>
</div>
</body>
</html>

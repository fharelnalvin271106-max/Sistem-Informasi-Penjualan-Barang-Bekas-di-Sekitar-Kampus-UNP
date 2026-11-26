<?php
require_once 'config.php';
require_login();
$page_title = 'Profil Saya - Marketplace UNP';
$me = $_SESSION['username'];
$err_profil = $err_pw = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['aksi'] ?? '') === 'profil') {
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nama === '' || $no_hp === '') $err_profil = 'Nama dan no. HP wajib diisi.';
        elseif (!preg_match('/^\+?[0-9]{8,15}$/', $no_hp)) $err_profil = 'No. HP tidak valid (8-15 angka).';
        elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err_profil = 'Format email tidak valid.';
        else {
            db_exec($conn, 'UPDATE users SET nama = ?, no_hp = ?, email = ? WHERE username = ?',
                'ssss', [$nama, $no_hp, $email, $me]);
            flash('success', 'Profil berhasil diperbarui.');
            redirect('profil.php');
        }
    } elseif (($_POST['aksi'] ?? '') === 'password') {
        $lama = $_POST['password_lama'] ?? '';
        $baru = $_POST['password_baru'] ?? '';
        $konf = $_POST['konfirmasi'] ?? '';
        $row = db_one($conn, 'SELECT password FROM users WHERE username = ?', 's', [$me]);
        if (!password_verify($lama, $row['password'])) $err_pw = 'Password lama salah.';
        elseif (strlen($baru) < 6) $err_pw = 'Password baru minimal 6 karakter.';
        elseif ($baru !== $konf) $err_pw = 'Konfirmasi password baru tidak sama.';
        else {
            db_exec($conn, 'UPDATE users SET password = ? WHERE username = ?', 'ss', [password_hash($baru, PASSWORD_DEFAULT), $me]);
            flash('success', 'Password berhasil diganti.');
            redirect('profil.php');
        }
    }
}

$u = db_one($conn, 'SELECT * FROM users WHERE username = ?', 's', [$me]);
include 'header.php';
?>
<div class="page-header"><h1>Profil saya</h1><p>Username <strong><?= e($u['username']) ?></strong> tidak bisa diubah.</p></div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><span class="card-title">Data diri</span></div>
    <div class="card-body">
      <?php if ($err_profil): ?><div class="alert alert-danger"><?= e($err_profil) ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="profil">
        <div class="form-group"><label class="form-label">Nama lengkap *</label><input type="text" name="nama" class="form-control" value="<?= e($_POST['nama'] ?? $u['nama']) ?>" required></div>
        <div class="form-group"><label class="form-label">No. HP / WhatsApp *</label><input type="text" name="no_hp" class="form-control" value="<?= e($_POST['no_hp'] ?? $u['no_hp']) ?>" required></div>
        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? $u['email']) ?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan perubahan</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Ganti password</span></div>
    <div class="card-body">
      <?php if ($err_pw): ?><div class="alert alert-danger"><?= e($err_pw) ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="password">
        <div class="form-group"><label class="form-label">Password lama *</label><input type="password" name="password_lama" class="form-control" required autocomplete="current-password"></div>
        <div class="form-group"><label class="form-label">Password baru * (minimal 6 karakter)</label><input type="password" name="password_baru" class="form-control" required autocomplete="new-password"></div>
        <div class="form-group"><label class="form-label">Konfirmasi password baru *</label><input type="password" name="konfirmasi" class="form-control" required autocomplete="new-password"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Ganti password</button>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>

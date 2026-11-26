<?php
require_once 'config.php';
require_admin();
$page_title = 'Kelola Akun - Marketplace UNP';
$me = $_SESSION['username'];
$err_tambah = '';

function jumlah_admin(mysqli $c): int {
    return (int)db_one($c, "SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")['c'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    $target = trim($_POST['username'] ?? '');

    if ($aksi === 'tambah') {
        $username = $target;
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        $pw = $_POST['password'] ?? '';
        $konf = $_POST['konfirmasi'] ?? '';

        if ($username === '' || $nama === '' || $no_hp === '' || $pw === '') $err_tambah = 'Username, nama, no. HP, dan password wajib diisi.';
        elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) $err_tambah = 'Username 3-30 karakter: huruf, angka, titik, atau garis bawah.';
        elseif (!preg_match('/^\+?[0-9]{8,15}$/', $no_hp)) $err_tambah = 'No. HP tidak valid (8-15 angka).';
        elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err_tambah = 'Format email tidak valid.';
        elseif (strlen($pw) < 6) $err_tambah = 'Password minimal 6 karakter.';
        elseif ($pw !== $konf) $err_tambah = 'Konfirmasi password tidak sama.';
        elseif (db_one($conn, 'SELECT username FROM users WHERE username = ?', 's', [$username])) $err_tambah = 'Username sudah dipakai.';
        else {
            db_exec($conn,
                'INSERT INTO users (username, nama, no_hp, email, role, password) VALUES (?,?,?,?,?,?)',
                'ssssss', [$username, $nama, $no_hp, $email, $role, password_hash($pw, PASSWORD_DEFAULT)]);
            flash('success', 'Akun ' . ($role === 'admin' ? 'admin ' : '') . $username . ' berhasil dibuat.');
            redirect('admin_user.php');
        }
    } else {
        $u = db_one($conn, 'SELECT * FROM users WHERE username = ?', 's', [$target]);
        if (!$u) {
            flash('danger', 'Akun tidak ditemukan.');
        } elseif ($aksi === 'role') {
            $baru = $u['role'] === 'admin' ? 'user' : 'admin';
            if ($u['username'] === $me) flash('danger', 'Anda tidak bisa mengubah role akun sendiri.');
            elseif ($baru === 'user' && jumlah_admin($conn) <= 1) flash('danger', 'Harus tersisa minimal satu admin.');
            else {
                db_exec($conn, 'UPDATE users SET role = ? WHERE username = ?', 'ss', [$baru, $u['username']]);
                flash('success', '@' . $u['username'] . ' sekarang ' . ($baru === 'admin' ? 'admin.' : 'pengguna biasa.'));
            }
        } elseif ($aksi === 'reset') {
            $pw = $_POST['password_baru'] ?? '';
            if (strlen($pw) < 6) flash('danger', 'Password baru minimal 6 karakter.');
            else {
                db_exec($conn, 'UPDATE users SET password = ? WHERE username = ?', 'ss', [password_hash($pw, PASSWORD_DEFAULT), $u['username']]);
                flash('success', 'Password @' . $u['username'] . ' berhasil direset.');
            }
        } elseif ($aksi === 'hapus') {
            if ($u['username'] === $me) flash('danger', 'Anda tidak bisa menghapus akun sendiri.');
            elseif ($u['role'] === 'admin' && jumlah_admin($conn) <= 1) flash('danger', 'Harus tersisa minimal satu admin.');
            else {
                // Hapus file foto milik akun ini; produk dan pesanan ikut terhapus oleh database
                foreach (db_all($conn, 'SELECT foto FROM produk WHERE username_penjual = ?', 's', [$u['username']]) as $r) hapusFoto($r['foto']);
                db_exec($conn, 'DELETE FROM users WHERE username = ?', 's', [$u['username']]);
                flash('success', 'Akun @' . $u['username'] . ' dihapus.');
            }
        }
        redirect('admin_user.php');
    }
}

$users = db_all($conn,
    'SELECT u.*, (SELECT COUNT(*) FROM produk p WHERE p.username_penjual = u.username AND p.status <> \'dihapus\') AS jml_produk
     FROM users u ORDER BY (u.role = \'admin\') DESC, u.created_at DESC');
include 'header.php';
?>
<div class="page-header"><h1>Kelola akun</h1><p>Tambah admin baru, ubah role, atau reset password pengguna.</p></div>

<div class="card mb">
  <div class="card-header"><span class="card-title">Tambah akun baru</span></div>
  <div class="card-body">
    <?php if ($err_tambah): ?><div class="alert alert-danger"><?= e($err_tambah) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="aksi" value="tambah">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" required autocomplete="off"></div>
        <div class="form-group"><label class="form-label">Role *</label>
          <select name="role" class="form-control">
            <option value="admin" <?= ($_POST['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>Pengguna biasa</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Nama lengkap *</label><input type="text" name="nama" class="form-control" value="<?= e($_POST['nama'] ?? '') ?>" required></div>
        <div class="form-group"><label class="form-label">No. HP *</label><input type="text" name="no_hp" class="form-control" value="<?= e($_POST['no_hp'] ?? '') ?>" required></div>
        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Password * (minimal 6 karakter)</label><input type="password" name="password" class="form-control" required autocomplete="new-password"></div>
        <div class="form-group"><label class="form-label">Konfirmasi password *</label><input type="password" name="konfirmasi" class="form-control" required autocomplete="new-password"></div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Buat akun</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Daftar akun (<?= count($users) ?>)</span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Username</th><th>Nama</th><th>Kontak</th><th>Role</th><th>Produk</th><th>Bergabung</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): $self = $u['username'] === $me; ?>
        <tr>
          <td><strong>@<?= e($u['username']) ?></strong><?= $self ? ' <span class="small muted">(Anda)</span>' : '' ?></td>
          <td><?= e($u['nama']) ?></td>
          <td class="small"><?= e($u['no_hp']) ?><?php if ($u['email']): ?><br><?= e($u['email']) ?><?php endif; ?></td>
          <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-diproses' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Pengguna' ?></span></td>
          <td><?= (int)$u['jml_produk'] ?></td>
          <td class="nowrap small muted"><?= e(tgl($u['created_at'])) ?></td>
          <td>
            <div class="actions">
              <?php if (!$self): ?>
                <?php aksi_form('admin_user.php', ['aksi' => 'role', 'username' => $u['username']],
                    $u['role'] === 'admin' ? 'Jadikan pengguna' : 'Jadikan admin', 'btn-outline',
                    $u['role'] === 'admin' ? 'Cabut hak admin @' . $u['username'] . '?' : 'Jadikan @' . $u['username'] . ' admin? Ia bisa mengelola semua data.'); ?>
                <?php aksi_form('admin_user.php', ['aksi' => 'hapus', 'username' => $u['username']], 'Hapus', 'btn-danger',
                    'Hapus akun @' . $u['username'] . '? Semua produk dan pesanannya ikut terhapus.'); ?>
              <?php endif; ?>
              <details class="reset">
                <summary class="btn btn-sm btn-outline">Reset password</summary>
                <form method="POST" class="reset-form">
                  <?= csrf_field() ?><input type="hidden" name="aksi" value="reset"><input type="hidden" name="username" value="<?= e($u['username']) ?>">
                  <input type="password" name="password_baru" class="form-control" placeholder="Password baru" minlength="6" required autocomplete="new-password">
                  <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </form>
              </details>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>

<?php
require_once 'config.php';
require_admin();
$page_title = 'Kategori - Marketplace UNP';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_kategori'] ?? '');
    $icon = trim($_POST['icon'] ?? '') ?: '📦';

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        if ($nama === '' || mb_strlen($nama) > 50) {
            flash('danger', 'Nama kategori wajib diisi (maksimal 50 karakter).');
        } elseif (mb_strlen($icon) > 4) {
            flash('danger', 'Ikon cukup satu emoji.');
        } elseif ($aksi === 'tambah') {
            db_exec($conn, 'INSERT INTO kategori (nama_kategori, icon) VALUES (?, ?)', 'ss', [$nama, $icon]);
            flash('success', 'Kategori ditambahkan.');
        } else {
            db_exec($conn, 'UPDATE kategori SET nama_kategori = ?, icon = ? WHERE id_kategori = ?', 'ssi', [$nama, $icon, $id]);
            flash('success', 'Kategori diperbarui.');
        }
    } elseif ($aksi === 'hapus') {
        $dipakai = (int)db_one($conn, 'SELECT COUNT(*) AS c FROM produk WHERE id_kategori = ?', 'i', [$id])['c'];
        if ($dipakai > 0) {
            flash('danger', "Kategori masih dipakai $dipakai produk. Pindahkan produknya dulu.");
        } else {
            db_exec($conn, 'DELETE FROM kategori WHERE id_kategori = ?', 'i', [$id]);
            flash('success', 'Kategori dihapus.');
        }
    }
    redirect('admin_kategori.php');
}

$rows = db_all($conn,
    'SELECT k.*, (SELECT COUNT(*) FROM produk p WHERE p.id_kategori = k.id_kategori) AS jml
     FROM kategori k ORDER BY k.nama_kategori');
include 'header.php';
?>
<div class="page-header"><h1>Kategori</h1><p>Kelompok barang yang bisa dipilih penjual.</p></div>

<div class="card mb">
  <div class="card-header"><span class="card-title">Tambah kategori</span></div>
  <div class="card-body">
    <form method="POST" class="filter-bar">
      <?= csrf_field() ?><input type="hidden" name="aksi" value="tambah">
      <div style="flex:1;min-width:200px;">
        <label class="form-label" for="nama_kategori">Nama kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" maxlength="50" required>
      </div>
      <div>
        <label class="form-label" for="icon">Ikon (emoji)</label>
        <input type="text" id="icon" name="icon" class="form-control" style="width:100px;" maxlength="4" placeholder="📦">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Ikon</th><th>Nama kategori</th><th>Produk</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $k): ?>
        <tr>
          <td colspan="2">
            <form method="POST" class="filter-bar" style="margin:0;">
              <?= csrf_field() ?><input type="hidden" name="aksi" value="ubah"><input type="hidden" name="id" value="<?= (int)$k['id_kategori'] ?>">
              <input type="text" name="icon" class="form-control" style="width:80px;" maxlength="4" value="<?= e($k['icon']) ?>" aria-label="Ikon">
              <input type="text" name="nama_kategori" class="form-control" style="flex:1;min-width:160px;" maxlength="50" value="<?= e($k['nama_kategori']) ?>" required aria-label="Nama kategori">
              <button type="submit" class="btn btn-sm btn-outline">Simpan</button>
            </form>
          </td>
          <td><?= (int)$k['jml'] ?></td>
          <td>
            <?php aksi_form('admin_kategori.php', ['aksi' => 'hapus', 'id' => $k['id_kategori']], 'Hapus', 'btn-danger', 'Hapus kategori ' . $k['nama_kategori'] . '?'); ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>

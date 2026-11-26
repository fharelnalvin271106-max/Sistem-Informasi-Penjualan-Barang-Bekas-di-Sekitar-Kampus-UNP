<?php
/* Bagian form produk (dipakai produk_tambah.php dan produk_edit.php)
   Variabel: $f (nilai), $kategori_list, $mode ('tambah' | 'edit') */
$f = $f ?? [];
?>
<form method="POST" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="form-group form-full">
      <label class="form-label" for="nama_produk">Nama produk *</label>
      <input type="text" id="nama_produk" name="nama_produk" class="form-control" maxlength="150" value="<?= e($f['nama_produk'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="id_kategori">Kategori *</label>
      <select id="id_kategori" name="id_kategori" class="form-control" required>
        <?php foreach ($kategori_list as $k): ?>
        <option value="<?= (int)$k['id_kategori'] ?>" <?= (int)($f['id_kategori'] ?? 0) === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['icon']) ?> <?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="harga">Harga (Rp) *</label>
      <input type="number" id="harga" name="harga" class="form-control" min="1" step="500" value="<?= isset($f['harga']) && $f['harga'] !== '' ? e((int)$f['harga']) : '' ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="kondisi">Kondisi *</label>
      <select id="kondisi" name="kondisi" class="form-control" required>
        <?php foreach (KONDISI_LIST as $kd): ?>
        <option value="<?= e($kd) ?>" <?= ($f['kondisi'] ?? 'bekas') === $kd ? 'selected' : '' ?>><?= e(ucfirst($kd)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($mode === 'edit'): ?>
    <div class="form-group">
      <label class="form-label" for="status">Status</label>
      <select id="status" name="status" class="form-control">
        <option value="tersedia" <?= ($f['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
        <option value="terjual" <?= ($f['status'] ?? '') === 'terjual' ? 'selected' : '' ?>>Terjual</option>
      </select>
    </div>
    <?php endif; ?>
    <div class="form-group form-full">
      <label class="form-label" for="foto"><?= $mode === 'edit' ? 'Ganti foto (kosongkan jika tidak diganti)' : 'Foto produk' ?></label>
      <?php if (!empty($f['foto'])): ?>
        <img src="uploads/produk/<?= e($f['foto']) ?>" alt="Foto saat ini" class="img-preview" style="display:block;">
      <?php endif; ?>
      <img id="foto-preview" alt="Pratinjau foto" class="img-preview" style="display:none;">
      <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
      <span class="form-hint">JPG, PNG, GIF, atau WEBP. Maksimal 5 MB.</span>
    </div>
    <div class="form-group form-full">
      <label class="form-label" for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5" placeholder="Spesifikasi, kondisi, alasan dijual, lokasi COD..."><?= e($f['deskripsi'] ?? '') ?></textarea>
    </div>
  </div>
  <div class="detail-actions">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $mode === 'edit' ? 'Simpan perubahan' : 'Pasang iklan' ?></button>
    <a href="javascript:history.back()" class="btn btn-outline">Batal</a>
  </div>
</form>

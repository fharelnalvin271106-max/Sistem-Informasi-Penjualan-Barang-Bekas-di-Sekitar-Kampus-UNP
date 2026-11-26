<?php
/* Tabel pesanan (dipakai pesanan_saya, pesanan, admin_pesanan)
   Variabel: $rows, $mode ('pembeli' | 'penjual' | 'admin'), $kembali */
?>
<?php if ($rows): ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>No.</th><th>Produk</th>
        <th><?= $mode === 'pembeli' ? 'Penjual' : ($mode === 'penjual' ? 'Pembeli' : 'Pembeli / Penjual') ?></th>
        <th>Total</th><th>Bayar</th><th>Status</th><th>Tanggal</th><th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $o): ?>
      <tr>
        <td>#<?= (int)$o['id_pesanan'] ?></td>
        <td>
          <a href="produk_detail.php?id=<?= (int)$o['id_produk'] ?>" class="cell-produk">
            <?php if ($o['foto']): ?><img src="uploads/produk/<?= e($o['foto']) ?>" alt="" class="thumb"><?php else: ?><span class="thumb thumb-ph">📦</span><?php endif; ?>
            <strong><?= e($o['nama_produk']) ?></strong>
          </a>
          <?php if ($o['catatan']): ?><div class="small muted" style="margin-top:4px;">Catatan: <?= e($o['catatan']) ?></div><?php endif; ?>
        </td>
        <td class="small">
          <?php if ($mode === 'pembeli' || $mode === 'admin'): ?>
            <?php if ($mode === 'admin'): ?>
              <div><span class="muted">Beli:</span> <?= e($o['nama_pembeli']) ?> <a href="<?= e(wa_link($o['hp_pembeli'])) ?>" target="_blank" rel="noopener"><?= e($o['hp_pembeli']) ?></a></div>
            <?php endif; ?>
            <div><?php if ($mode === 'admin'): ?><span class="muted">Jual:</span> <?php endif; ?><?= e($o['nama_penjual']) ?> <a href="<?= e(wa_link($o['hp_penjual'])) ?>" target="_blank" rel="noopener"><?= e($o['hp_penjual']) ?></a></div>
          <?php else: ?>
            <div><?= e($o['nama_pembeli']) ?> <span class="muted">(@<?= e($o['username_pembeli']) ?>)</span></div>
            <a href="<?= e(wa_link($o['hp_pembeli'])) ?>" target="_blank" rel="noopener"><?= e($o['hp_pembeli']) ?></a>
          <?php endif; ?>
        </td>
        <td class="nowrap"><?= rupiah($o['total_harga']) ?></td>
        <td><?= $o['metode_bayar'] === 'cod' ? 'COD' : 'Transfer' ?></td>
        <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
        <td class="nowrap small muted"><?= e(tgl($o['created_at'])) ?></td>
        <td>
          <div class="actions">
          <?php
          $f = ['id' => $o['id_pesanan'], 'kembali' => $kembali];
          if ($mode === 'pembeli') {
              if ($o['status'] === 'pending') aksi_form('pesanan_aksi.php', $f + ['aksi' => 'batal'], 'Batalkan', 'btn-danger', 'Batalkan pesanan ini?');
          } else {
              if ($o['status'] === 'pending') {
                  aksi_form('pesanan_aksi.php', $f + ['aksi' => 'konfirmasi'], 'Konfirmasi', 'btn-success');
                  aksi_form('pesanan_aksi.php', $f + ['aksi' => 'batal'], 'Tolak', 'btn-danger', 'Tolak pesanan ini?');
              } elseif ($o['status'] === 'dikonfirmasi') {
                  aksi_form('pesanan_aksi.php', $f + ['aksi' => 'selesai'], 'Selesai', 'btn-success');
                  aksi_form('pesanan_aksi.php', $f + ['aksi' => 'batal'], 'Batalkan', 'btn-danger', 'Batalkan pesanan ini? Produk akan tersedia lagi.');
              }
          }
          ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="card-body"><div class="empty-state"><div class="empty-icon">🧾</div><p><?= e($pesan_kosong ?? 'Belum ada pesanan.') ?></p></div></div>
<?php endif; ?>

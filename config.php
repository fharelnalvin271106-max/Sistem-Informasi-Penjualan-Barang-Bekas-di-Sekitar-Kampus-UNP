<?php
/* ==========================================================
   config.php - koneksi database, sesi, dan fungsi bantu
   ========================================================== */
ob_start();
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'marketplace_unp');

const KONDISI_LIST = ['baru', 'mulus', 'bekas', 'minus'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    die('<div style="background:#fee2e2;padding:24px;border:2px solid #ef4444;font-family:sans-serif;max-width:520px;margin:40px auto;border-radius:12px;">
        <h3 style="color:#dc2626;margin-top:0;">Koneksi database gagal</h3>
        <p>' . htmlspecialchars($ex->getMessage()) . '</p>
        <p>Pastikan MySQL di XAMPP sudah menyala dan <b>database.sql</b> sudah diimport ke phpMyAdmin.</p>
    </div>');
}

/* ---------- Umum ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function rupiah($angka) { return 'Rp ' . number_format((float)$angka, 0, ',', '.'); }

function tgl($ts) { return date('d M Y, H:i', strtotime($ts)); }

function redirect($url) { header('Location: ' . $url); exit; }

/* Hanya izinkan redirect ke halaman di folder ini (mencegah open redirect) */
function safe_back($default) {
    $b = $_POST['kembali'] ?? '';
    return preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9_=&%.\-]*)?$/', $b) ? $b : $default;
}

/* Nomor HP -> tautan WhatsApp */
function wa_link($hp) {
    $n = preg_replace('/[^0-9]/', '', $hp);
    if (strpos($n, '0') === 0) $n = '62' . substr($n, 1);
    return 'https://wa.me/' . $n;
}

/* ---------- Pesan singkat (flash) ---------- */
function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function get_flash() {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CSRF ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Sesi formulir tidak valid. Kembali ke halaman sebelumnya, muat ulang, lalu coba lagi.');
    }
}

/* ---------- Database (prepared statement) ---------- */
function db_exec(mysqli $c, string $sql, string $types = '', array $params = []): mysqli_stmt {
    $st = $c->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    return $st;
}
function db_all(mysqli $c, string $sql, string $types = '', array $params = []): array {
    return db_exec($c, $sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}
function db_one(mysqli $c, string $sql, string $types = '', array $params = []) {
    $r = db_all($c, $sql, $types, $params);
    return $r[0] ?? null;
}

/* ---------- Login & hak akses ---------- */
function require_login() {
    global $conn;
    if (empty($_SESSION['username'])) redirect('index.php');
    // Cek ulang ke database: akun dihapus atau role diubah berlaku langsung
    $u = db_one($conn, 'SELECT nama, role FROM users WHERE username = ?', 's', [$_SESSION['username']]);
    if (!$u) {
        session_unset();
        session_destroy();
        redirect('index.php');
    }
    $_SESSION['nama'] = $u['nama'];
    $_SESSION['role'] = $u['role'];
}
function is_admin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('danger', 'Halaman itu khusus admin.');
        redirect('dashboard.php');
    }
}

/* ---------- Foto produk ---------- */
function uploadFoto($file, $old = '') {
    if (empty($file['name'])) return $old;
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    if (@getimagesize($file['tmp_name']) === false) return false; // harus benar-benar gambar

    $dir = __DIR__ . '/uploads/produk/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $nama = 'p_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $nama)) {
        if ($old) hapusFoto($old);
        return $nama;
    }
    return false;
}
function hapusFoto($nama) {
    if (!$nama) return;
    $path = __DIR__ . '/uploads/produk/' . basename($nama);
    if (is_file($path)) @unlink($path);
}

/* ---------- Validasi form produk (dipakai tambah & edit) ---------- */
function validasi_produk(mysqli $c, array $in, array &$err): array {
    $d = [
        'nama_produk' => trim($in['nama_produk'] ?? ''),
        'id_kategori' => (int)($in['id_kategori'] ?? 0),
        'harga'       => (float)($in['harga'] ?? 0),
        'kondisi'     => $in['kondisi'] ?? '',
        'deskripsi'   => trim($in['deskripsi'] ?? ''),
    ];
    if ($d['nama_produk'] === '' || mb_strlen($d['nama_produk']) > 150) $err[] = 'Nama produk wajib diisi (maksimal 150 karakter).';
    if (!db_one($c, 'SELECT id_kategori FROM kategori WHERE id_kategori = ?', 'i', [$d['id_kategori']])) $err[] = 'Pilih kategori yang tersedia.';
    if ($d['harga'] <= 0 || $d['harga'] > 9999999999) $err[] = 'Harga harus lebih dari 0.';
    if (!in_array($d['kondisi'], KONDISI_LIST, true)) $err[] = 'Pilih kondisi barang.';
    return $d;
}

/* ---------- Tampilan kartu produk ---------- */
function render_produk_card(array $p) { ?>
  <a href="produk_detail.php?id=<?= (int)$p['id_produk'] ?>" class="produk-card">
    <?php if ($p['foto']): ?>
      <img src="uploads/produk/<?= e($p['foto']) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy">
    <?php else: ?>
      <div class="produk-noimg"><?= e($p['icon'] ?? '📦') ?></div>
    <?php endif; ?>
    <div class="produk-info">
      <h4><?= e($p['nama_produk']) ?></h4>
      <p class="produk-harga"><?= rupiah($p['harga']) ?></p>
      <p class="produk-meta">
        <span class="badge badge-diproses"><?= e(ucfirst($p['kondisi'])) ?></span>
        <?php if (!empty($p['nama_kategori'])): ?><span class="small muted"><?= e($p['nama_kategori']) ?></span><?php endif; ?>
      </p>
      <p class="produk-penjual">👤 <?= e($p['username_penjual']) ?></p>
    </div>
  </a>
<?php }

/* Tombol aksi kecil berbentuk form POST */
function aksi_form(string $action, array $fields, string $label, string $class = 'btn-outline', string $confirm = '') {
    echo '<form method="POST" action="' . e($action) . '" class="inline-form"' . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>';
    echo csrf_field();
    foreach ($fields as $k => $v) echo '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    echo '<button type="submit" class="btn btn-sm ' . e($class) . '">' . e($label) . '</button></form>';
}

/* Query dasar daftar pesanan (tambahkan WHERE / ORDER sendiri) */
const SQL_PESANAN = "SELECT o.*, p.nama_produk, p.foto, p.username_penjual,
    s.nama AS nama_penjual, s.no_hp AS hp_penjual,
    b.nama AS nama_pembeli, b.no_hp AS hp_pembeli
  FROM pesanan o
  JOIN produk p ON o.id_produk = p.id_produk
  JOIN users s ON p.username_penjual = s.username
  JOIN users b ON o.username_pembeli = b.username";

<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        $no = trim($_POST['table_no'] ?? '');
        if ($no === '' || mb_strlen($no) > 20) flash('Enter a table number/name.', 'danger');
        elseif (q('SELECT id FROM dining_tables WHERE table_no = ?', [$no])->fetch()) flash('That table already exists.', 'danger');
        else { q('INSERT INTO dining_tables (table_no, seats, token) VALUES (?,?,?)', [$no, max(1, (int)$_POST['seats']), bin2hex(random_bytes(16))]); flash('Table added.'); }
    } elseif ($a === 'regen') {
        q('UPDATE dining_tables SET token = ? WHERE id = ?', [bin2hex(random_bytes(16)), (int)$_POST['id']]);
        flash('New QR code generated. Print and replace the old one on that table.');
    } elseif ($a === 'delete') {
        try { q('DELETE FROM dining_tables WHERE id = ?', [(int)$_POST['id']]); flash('Table deleted.'); }
        catch (PDOException $e) { flash('This table has order history and cannot be deleted. Use "New QR" if you want to disable the old code.', 'warning'); }
    }
    redirect('admin/tables.php');
}

$title = 'Tables & QR'; $active = 'tables'; $page = 'admin-tables';
$extra_scripts = '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';
require __DIR__ . '/../includes/admin_header.php';
$tables = q('SELECT * FROM dining_tables ORDER BY LENGTH(table_no), table_no')->fetchAll();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
  <h1 class="brand-font h3 mb-0">Tables &amp; QR codes</h1>
  <div class="d-flex gap-2">
    <form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="action" value="add">
      <input class="form-control form-control-sm" name="table_no" placeholder="Table no." required style="width:110px">
      <input class="form-control form-control-sm" name="seats" type="number" value="4" min="1" style="width:80px">
      <button class="btn btn-sm btn-burg">Add table</button></form>
    <button class="btn btn-sm btn-outline-light" id="printQr"><i class="bi bi-printer"></i> Print all</button>
  </div>
</div>
<p class="text-muted small no-print">Print each QR and stick it on its table. Guests scan it with their phone camera, the menu opens and orders go straight to that table.
  <?php if (preg_match('/^(localhost|127\.|192\.168\.|10\.)/', $_SERVER['HTTP_HOST'] ?? '')): ?><br><span class="text-warning">You are on a local address (<?= e($_SERVER['HTTP_HOST']) ?>). Phones can only open these QR codes if they are on the same WiFi. For real customers, host the site online and regenerate the codes from the live domain.</span><?php endif; ?></p>
<div class="row g-3">
  <?php foreach ($tables as $t): $url = site_url() . '/scan.php?t=' . $t['token']; ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="qr-card">
        <div class="brand-font fs-4">Table <?= e($t['table_no']) ?></div>
        <div class="small text-muted"><?= (int)$t['seats'] ?> seats</div>
        <div class="qr-box" data-qr="<?= e($url) ?>" data-size="170"></div>
        <div class="small text-muted mb-2">Scan to view menu &amp; order</div>
        <div class="d-flex gap-1 justify-content-center no-print">
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="regen"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-sm btn-outline-light" onclick="return confirm('The old QR will stop working. Continue?')">New QR</button></form>
          <form method="post" onsubmit="return confirm('Delete this table?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>

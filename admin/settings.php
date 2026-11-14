<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $keys = ['restaurant_name', 'tagline', 'phone', 'address', 'tax_percent', 'wifi_ssid', 'wifi_password', 'wifi_security'];
    foreach ($keys as $k) {
        $v = trim($_POST[$k] ?? '');
        if ($k === 'tax_percent') $v = (string)max(0, min(50, (float)$v));
        if ($k === 'wifi_security' && !in_array($v, ['WPA', 'WEP', 'nopass'], true)) $v = 'WPA';
        q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
    }

    // ---- Logo upload / remove ----
    $uploadDir = __DIR__ . '/../assets/img/uploads/';
    $oldLogo = setting('logo');
    $removeOld = function () use ($uploadDir, $oldLogo) {
        if ($oldLogo !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $oldLogo) && is_file($uploadDir . $oldLogo)) @unlink($uploadDir . $oldLogo);
    };
    $logoMsg = null;
    if (!empty($_POST['remove_logo'])) {
        $removeOld();
        q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['logo', '']);
    } elseif (!empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fl = $_FILES['logo'];
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = ($fl['error'] === UPLOAD_ERR_OK && is_uploaded_file($fl['tmp_name'])) ? (new finfo(FILEINFO_MIME_TYPE))->file($fl['tmp_name']) : '';
        if ($fl['error'] !== UPLOAD_ERR_OK) {
            $logoMsg = 'Logo upload failed (file too big or upload error).';
        } elseif ($fl['size'] > 2 * 1024 * 1024) {
            $logoMsg = 'Logo must be under 2 MB.';
        } elseif (!isset($types[$mime]) || !@getimagesize($fl['tmp_name'])) {
            $logoMsg = 'Logo must be a JPG, PNG or WEBP image.';
        } else {
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $name = 'logo-' . time() . '.' . $types[$mime];
            if (@move_uploaded_file($fl['tmp_name'], $uploadDir . $name)) {
                $removeOld();
                q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['logo', $name]);
            } else {
                $logoMsg = 'Could not save the logo. Check write permission on assets/img/uploads/.';
            }
        }
    }
    if ($logoMsg) flash('Other settings saved, but: ' . $logoMsg, 'danger'); else flash('Settings saved.');
    redirect('admin/settings.php');
}

$title = 'Settings'; $active = 'settings';
$admin_roles = ['admin'];
require __DIR__ . '/../includes/admin_header.php';
$f = fn($k, $label, $type = 'text') => '<div class="mb-3"><label class="form-label">' . $label . '</label><input class="form-control" type="' . $type . '" name="' . $k . '" value="' . e(setting($k)) . '"></div>';
?>
<h1 class="brand-font h3 mb-4">Settings &amp; guest WiFi</h1>
<form method="post" enctype="multipart/form-data" class="row g-4"><?= csrf_field() ?>
  <div class="col-md-6"><div class="card-dark p-3">
    <h2 class="h6 mb-3">Restaurant</h2>
    <div class="mb-3"><label class="form-label">Logo</label>
      <div class="d-flex align-items-center gap-3 mb-2"><img src="<?= e(logo_url()) ?>" alt="" width="56" height="56" style="object-fit:cover;border-radius:50%"><span class="small text-muted">Current logo (shown in navbar and login page)</span></div>
      <input class="form-control" type="file" name="logo" accept="image/png,image/jpeg,image/webp">
      <div class="form-text">JPG, PNG or WEBP, under 2 MB. Then click Save settings.</div>
      <?php if (setting('logo') !== ''): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="rmlogo"><label class="form-check-label small" for="rmlogo">Remove uploaded logo (go back to default)</label></div><?php endif; ?>
    </div>
    <?= $f('restaurant_name', 'Restaurant name') ?><?= $f('tagline', 'Tagline') ?><?= $f('phone', 'Phone') ?><?= $f('address', 'Address') ?>
    <?= $f('tax_percent', 'Tax / GST % added to bills', 'number') ?>
  </div></div>
  <div class="col-md-6"><div class="card-dark p-3">
    <h2 class="h6 mb-3">Guest WiFi (shown on the website)</h2>
    <?= $f('wifi_ssid', 'Network name (SSID)') ?><?= $f('wifi_password', 'WiFi password') ?>
    <div class="mb-3"><label class="form-label">Security type</label>
      <select class="form-select" name="wifi_security">
        <?php foreach (['WPA' => 'WPA / WPA2 / WPA3', 'WEP' => 'WEP', 'nopass' => 'Open (no password)'] as $v => $l): ?><option value="<?= $v ?>" <?= setting('wifi_security') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
      </select></div>
    <p class="small text-muted mb-0">Customers see this on the WiFi page and can scan a QR code to join.</p>
  </div></div>
  <div class="col-12"><button class="btn btn-burg px-5">Save settings</button></div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
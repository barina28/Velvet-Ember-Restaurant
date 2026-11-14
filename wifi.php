<?php
$page = 'wifi';
$page_title = 'Guest WiFi';
require_once __DIR__ . '/includes/header.php';

$ssid = setting('wifi_ssid');
$pass = setting('wifi_password');
$sec  = setting('wifi_security', 'WPA');
$esc  = fn($s) => preg_replace('/([\\\\;,:"])/', '\\\\$1', $s);
$wifiString = 'WIFI:T:' . ($sec === 'nopass' ? 'nopass' : $sec) . ';S:' . $esc($ssid) . ';' . ($sec === 'nopass' ? '' : 'P:' . $esc($pass) . ';') . ';';
$extra_scripts = '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';
?>
<section class="page-section">
  <div class="container" style="max-width:520px">
    <h1 class="brand-font section-title text-center">Guest WiFi</h1>
    <div class="card-dark p-4 text-center">
      <i class="bi bi-wifi display-4 text-gold"></i>
      <div class="mt-3 text-muted small">Network name</div>
      <div class="fs-4 fw-semibold"><?= e($ssid) ?></div>

      <?php if ($sec !== 'nopass'): ?>
        <div class="mt-3 text-muted small">Password</div>
        <div class="d-flex justify-content-center align-items-center gap-2">
          <code class="fs-4 wifi-pass" id="wifiPass"><?= e($pass) ?></code>
          <button class="btn btn-sm btn-outline-light" id="copyWifi" data-copy="<?= e($pass) ?>"><i class="bi bi-clipboard"></i> Copy</button>
        </div>
      <?php else: ?>
        <div class="mt-3 text-muted">Open network - no password needed.</div>
      <?php endif; ?>

      <hr class="my-4">
      <p class="small text-muted mb-2">Scan with your phone camera to connect automatically</p>
      <div id="wifiQR" class="qr-box mx-auto" data-qr="<?= e($wifiString) ?>"></div>
    </div>
    <p class="text-center small text-muted mt-3">Android: Settings &rarr; WiFi &rarr; pick "<?= e($ssid) ?>" &rarr; paste the password.<br>iPhone: just scan the QR code with the Camera app.</p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

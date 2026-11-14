<?php
require_once __DIR__ . '/../includes/config.php';
$u = require_login();
$page = 'staff';
$rname = setting('restaurant_name');
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Live Board - <?= e($rname) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= BASE ?>/assets/css/main.css" rel="stylesheet">
<script>window.APP = <?= json_encode(['base' => BASE, 'csrf' => csrf(), 'currency' => CURRENCY], JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
</head>
<body data-page="staff">
<nav class="panel-nav py-2">
  <div class="container-fluid d-flex flex-wrap align-items-center gap-2">
    <span class="brand-font text-gold fs-5"><?= e($rname) ?></span>
    <span class="badge bg-dark border"><?= e($u['name']) ?> &middot; <?= e($u['role']) ?></span>
    <span class="text-muted small ms-2">Live &middot; synced <span id="lastSync">-</span></span>
    <div class="ms-auto d-flex gap-2">
      <?php if (in_array($u['role'], ['admin', 'manager'])): ?><a class="btn btn-sm btn-outline-light" href="<?= BASE ?>/admin/index.php">Admin panel</a><?php endif; ?>
      <a class="btn btn-sm btn-burg" href="<?= BASE ?>/logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid panel-wrap">
  <div class="row g-4">
    <div class="col-md-6 col-xl-3 board-col"><h2>New orders</h2><div id="colNew"></div></div>
    <div class="col-md-6 col-xl-3 board-col"><h2>In kitchen</h2><div id="colPrep"></div></div>
    <div class="col-md-6 col-xl-3 board-col"><h2>Ready to serve</h2><div id="colReady"></div></div>
    <div class="col-md-6 col-xl-3 board-col">
      <h2>Waiter calls</h2><div id="colCalls"></div>
      <h2 class="mt-4">Cash to collect</h2><div id="colCash"></div>
    </div>
  </div>
</div>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000" id="toasts"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE ?>/assets/js/main.js"></script>
</body>
</html>

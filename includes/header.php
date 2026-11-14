<?php
require_once __DIR__ . '/config.php';
$__t = cur_table();
$__c = cur_customer();
$page = $page ?? 'home';
$rname = setting('restaurant_name', 'Restaurant');
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= !empty($page_title) && $page_title !== $rname ? e($page_title) . ' | ' . e($rname) : e($rname) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= BASE ?>/assets/css/main.css?v=<?= filemtime(__DIR__ . '/../assets/css/main.css') ?>" rel="stylesheet">
<script>
window.APP = <?= json_encode([
    'base' => BASE, 'csrf' => csrf(), 'currency' => CURRENCY,
    'tax' => (float)setting('tax_percent', '0'),
    'table' => $__t['table_no'] ?? null,
    'customer' => $__c['name'] ?? null,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
</head>
<body data-page="<?= e($page) ?>">
<nav class="navbar navbar-expand-lg site-nav fixed-top">
  <div class="container">
    <a class="navbar-brand brand-font d-flex align-items-center gap-2" href="<?= BASE ?>/index.php">
      <img src="<?= e(logo_url()) ?>" width="56" height="56" alt="" style="object-fit:cover;border-radius:50%"> <?= e($rname) ?>
    </a>
    <?php if ($__t): ?><span class="badge badge-table ms-2"><i class="bi bi-geo-alt-fill"></i> Table <?= e($__t['table_no']) ?></span><?php endif; ?>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link <?= $page === 'home' ? 'active' : '' ?>" href="<?= BASE ?>/index.php#menu">Menu</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'reserve' ? 'active' : '' ?>" href="<?= BASE ?>/reserve.php"><i class="bi bi-calendar2-check text-gold"></i> Reserve Table</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'about' || $page === 'contact' ? 'active' : '' ?>" href="<?= BASE ?>/about.php">About & Contact</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'orders' ? 'active' : '' ?>" href="<?= BASE ?>/my_orders.php">My Orders</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'bill' ? 'active' : '' ?>" href="<?= BASE ?>/bill.php">Bill</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'feedback' ? 'active' : '' ?>" href="<?= BASE ?>/feedback.php"><i class="bi bi-chat-heart"></i> Feedback</a></li>
        <li class="nav-item"><a class="nav-link <?= $page === 'wifi' ? 'active' : '' ?>" href="<?= BASE ?>/wifi.php"><i class="bi bi-wifi"></i> WiFi</a></li>
        <li class="nav-item"><a class="nav-link small text-muted" href="<?= BASE ?>/login.php">Staff</a></li>
      </ul>
    </div>
  </div>
</nav>
<main>
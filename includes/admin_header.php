<?php
require_once __DIR__ . '/config.php';
$u = require_login($admin_roles ?? ['admin', 'manager']);
$page = $page ?? 'admin';
$rname = setting('restaurant_name');
$links = [
    'index' => ['Dashboard', 'index.php', ['admin', 'manager']],
    'orders' => ['Orders', 'orders.php', ['admin', 'manager']],
    'reservations' => ['Reservations', 'reservations.php', ['admin', 'manager']],
    'reports' => ['Reports & Analytics', 'reports.php', ['admin', 'manager']],
    'menu' => ['Menu', 'menu.php', ['admin', 'manager']],
    'tables' => ['Tables & QR', 'tables.php', ['admin', 'manager']],
    'customers' => ['Customers', 'customers.php', ['admin', 'manager']],
    'feedback' => ['Feedback', 'feedback.php', ['admin', 'manager']],
    'users' => ['Staff accounts', 'users.php', ['admin']],
    'settings' => ['Settings & WiFi', 'settings.php', ['admin']],
];
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Admin') ?> - <?= e($rname) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= BASE ?>/assets/css/main.css" rel="stylesheet">
<script>window.APP = <?= json_encode(['base' => BASE, 'csrf' => csrf(), 'currency' => CURRENCY], JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
</head>
<body data-page="<?= e($page) ?>">
<nav class="panel-nav py-2 no-print">
  <div class="container d-flex flex-wrap align-items-center gap-2">
    <span class="brand-font text-gold fs-5 me-2"><?= e($rname) ?></span>
    <?php foreach ($links as $key => [$label, $href, $roles]): if (!in_array($u['role'], $roles, true)) continue; ?>
      <a class="nav-link <?= ($active ?? '') === $key ? 'active' : '' ?>" href="<?= BASE ?>/admin/<?= $href ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <div class="ms-auto d-flex gap-2 align-items-center">
      <a class="btn btn-sm btn-outline-light" href="<?= BASE ?>/staff/index.php">Live board</a>
      <span class="small text-muted"><?= e($u['name']) ?> (<?= e($u['role']) ?>)</span>
      <a class="btn btn-sm btn-burg" href="<?= BASE ?>/logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container panel-wrap">
<?php if ($flash): ?><div class="alert alert-<?= e($flash[1]) ?> no-print"><?= e($flash[0]) ?></div><?php endif; ?>
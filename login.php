<?php
require_once __DIR__ . '/includes/config.php';

if (user()) redirect(in_array(user()['role'], ['admin', 'manager']) ? 'admin/index.php' : 'staff/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = q('SELECT * FROM users WHERE username = ? AND active = 1', [trim($_POST['username'] ?? '')])->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role']];
        redirect(in_array($u['role'], ['admin', 'manager']) ? 'admin/index.php' : 'staff/index.php');
    }
    usleep(700000);
    $error = 'Wrong username or password.';
}
$rname = setting('restaurant_name', 'Restaurant');
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Staff Login - <?= e($rname) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= BASE ?>/assets/css/main.css" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-card">
  <img src="<?= e(logo_url()) ?>" alt="" width="110" class="mb-3" style="height:110px;object-fit:cover;border-radius:50%">
  <h1 class="brand-font h3 mb-1"><?= e($rname) ?></h1>
  <p class="text-muted small mb-4">Staff &amp; Admin login</p>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <div class="mb-3 text-start"><label class="form-label">Username</label>
      <input name="username" id="loginUser" class="form-control" required autofocus></div>
    <div class="mb-4 text-start"><label class="form-label">Password</label>
      <input type="password" name="password" id="loginPass" class="form-control" required></div>
    <button class="btn btn-burg w-100 py-2">Sign in</button>
  </form>

  <!-- Demo Accounts Helper Box -->
  <div class="mt-4 p-3 rounded bg-dark border border-secondary text-start">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <strong class="text-gold small"><i class="bi bi-key-fill me-1"></i> Demo Accounts (Click to Fill)</strong>
      <span class="badge bg-secondary" style="font-size:0.65rem">Auto-Fill</span>
    </div>
    <div class="d-flex flex-wrap gap-1 mb-2">
      <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 demo-btn" data-u="admin" data-p="admin123" title="Full Access">Admin</button>
      <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 demo-btn" data-u="manager" data-p="manager123" title="Manager Panel">Manager</button>
      <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 demo-btn" data-u="kitchen" data-p="kitchen123" title="Kitchen Board">Kitchen</button>
      <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 demo-btn" data-u="cashier" data-p="cashier123" title="Cashier Board">Cashier</button>
      <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 demo-btn" data-u="waiter" data-p="waiter123" title="Waiter Board">Waiter</button>
    </div>
    <div class="small text-muted" style="font-size:0.75rem">
      Password for all demo accounts: <code class="text-light">&lt;username&gt;123</code> (e.g. <strong>admin / admin123</strong>)
    </div>
  </div>

  <script>
    document.querySelectorAll('.demo-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('loginUser').value = btn.dataset.u;
        document.getElementById('loginPass').value = btn.dataset.p;
      });
    });
  </script>
  <a href="<?= BASE ?>/index.php" class="d-block mt-4 small text-muted">&larr; Back to website</a>
</div>
</body>
</html>
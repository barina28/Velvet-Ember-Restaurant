<?php
require_once __DIR__ . '/../includes/config.php';
$me = require_login(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'add') {
        $name = trim($_POST['name'] ?? ''); $un = strtolower(trim($_POST['username'] ?? '')); $pw = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['admin', 'manager', 'waiter', 'kitchen', 'cashier'], true) || $name === '' || !preg_match('/^[a-z0-9_.]{3,30}$/', $un) || strlen($pw) < 8) {
            flash('Fill all fields. Username: 3-30 letters/numbers. Password: at least 8 characters.', 'danger');
        } elseif (q('SELECT id FROM users WHERE username = ?', [$un])->fetch()) {
            flash('That username is taken.', 'danger');
        } else {
            q('INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)', [$name, $un, password_hash($pw, PASSWORD_DEFAULT), $role]);
            flash('Account created.');
        }
    } elseif ($a === 'toggle' && $id !== $me['id']) {
        q('UPDATE users SET active = 1 - active WHERE id = ?', [$id]);
    } elseif ($a === 'reset') {
        $pw = $_POST['password'] ?? '';
        if (strlen($pw) < 8) flash('Password must be at least 8 characters.', 'danger');
        else { q('UPDATE users SET password = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $id]); flash('Password changed.'); }
    } elseif ($a === 'delete' && $id !== $me['id']) {
        q('DELETE FROM users WHERE id = ?', [$id]);
        flash('Account deleted.');
    }
    redirect('admin/users.php');
}

$title = 'Staff accounts'; $active = 'users';
$admin_roles = ['admin'];
require __DIR__ . '/../includes/admin_header.php';
$users = q('SELECT * FROM users ORDER BY id')->fetchAll();
?>
<h1 class="brand-font h3 mb-3">Staff accounts</h1>
<div class="alert alert-warning small">The demo accounts (admin/admin123, manager/manager123, waiter/waiter123, kitchen/kitchen123, cashier/cashier123) must be given new passwords before going live.</div>
<div class="row g-4">
  <div class="col-lg-4"><div class="card-dark p-3">
    <h2 class="h6 mb-3">New account</h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add">
      <input class="form-control mb-2" name="name" placeholder="Full name" required>
      <input class="form-control mb-2" name="username" placeholder="Username" required autocomplete="off">
      <input class="form-control mb-2" type="password" name="password" placeholder="Password (min 8)" required autocomplete="new-password">
      <select class="form-select mb-3" name="role">
        <option value="waiter">Waiter</option><option value="kitchen">Kitchen</option><option value="cashier">Cashier</option><option value="manager">Manager</option><option value="admin">Admin</option>
      </select>
      <button class="btn btn-burg w-100">Create</button>
    </form>
    <hr><small class="text-muted"><b>Admin</b>: everything &middot; <b>Manager</b>: menu, tables, orders, customers &middot; <b>Kitchen</b>: cook orders &middot; <b>Waiter</b>: serve, calls, cash &middot; <b>Cashier</b>: confirm cash</small>
  </div></div>
  <div class="col-lg-8">
    <div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
      <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($users as $x): ?>
        <tr>
          <td><?= e($x['name']) ?></td><td><?= e($x['username']) ?></td><td><span class="badge bg-dark border"><?= e($x['role']) ?></span></td>
          <td><?= $x['active'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Disabled</span>' ?></td>
          <td class="text-end text-nowrap">
            <form method="post" class="d-inline-flex gap-1"><?= csrf_field() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
              <input class="form-control form-control-sm" type="password" name="password" placeholder="New password" style="width:130px" autocomplete="new-password"><button class="btn btn-sm btn-outline-light">Set</button></form>
            <?php if ($x['id'] != $me['id']): ?>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="btn btn-sm btn-outline-warning"><?= $x['active'] ? 'Disable' : 'Enable' ?></button></form>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this account?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?></tbody></table></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>

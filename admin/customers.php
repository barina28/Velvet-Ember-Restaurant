<?php
$title = 'Customers'; $active = 'customers';
require_once __DIR__ . '/../includes/admin_header.php';

$s = trim($_GET['s'] ?? '');
$params = []; $where = '';
if ($s !== '') { $where = 'WHERE c.name LIKE ? OR c.phone LIKE ?'; $params = ["%$s%", "%$s%"]; }

$rows = q("SELECT c.*, COUNT(o.id) orders_count, COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN o.total END),0) spent
          FROM customers c LEFT JOIN orders o ON o.customer_id = c.id $where GROUP BY c.id ORDER BY c.id DESC LIMIT 300", $params)->fetchAll();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="brand-font h3 mb-0">Customers <small class="text-muted fs-6">(<?= count($rows) ?>)</small></h1>
  <form class="d-flex gap-2"><input class="form-control form-control-sm" name="s" value="<?= e($s) ?>" placeholder="Search name or phone"><button class="btn btn-sm btn-burg">Search</button></form>
</div>
<div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
  <thead><tr><th>Name</th><th>Phone</th><th>Visits</th><th>Orders</th><th class="text-end">Total spent</th><th>Last visit</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $c): ?>
    <tr><td><?= e($c['name']) ?></td><td><?= e($c['phone']) ?></td><td><?= (int)$c['visits'] ?></td><td><?= (int)$c['orders_count'] ?></td>
      <td class="text-end"><?= money($c['spent']) ?></td><td class="small"><?= $c['last_visit'] ? e(date('d M Y h:i A', strtotime($c['last_visit']))) : '-' ?></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="text-muted">No customers yet.</td></tr>'; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>

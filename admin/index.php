<?php
$title = 'Dashboard'; $active = 'index';
require_once __DIR__ . '/../includes/admin_header.php';

$today = q("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM orders WHERE DATE(created_at) = CURDATE() AND status <> 'cancelled'")->fetch();
$paidToday = q("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(paid_at) = CURDATE() AND payment_status = 'paid'")->fetchColumn();
$active_orders = q("SELECT COUNT(*) FROM orders WHERE status IN ('pending','preparing','ready')")->fetchColumn();
$customers = q('SELECT COUNT(*) FROM customers')->fetchColumn();
$unpaid = q("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status <> 'paid' AND status <> 'cancelled'")->fetchColumn();
$recent = q("SELECT o.id, o.status, o.payment_status, o.payment_method, o.order_type, o.total, o.created_at, t.table_no, c.name FROM orders o LEFT JOIN dining_tables t ON t.id = o.table_id JOIN customers c ON c.id = o.customer_id ORDER BY o.id DESC LIMIT 10")->fetchAll();
$top = q("SELECT name, SUM(qty) q FROM order_items GROUP BY name ORDER BY q DESC LIMIT 5")->fetchAll();
?>
<h1 class="brand-font h3 mb-4">Dashboard</h1>
<div class="row g-3 mb-4">
  <?php foreach ([['Orders today', $today['c']], ['Sales today', money($today['s'])], ['Cash+online received today', money($paidToday)], ['Active orders', $active_orders], ['Unpaid amount', money($unpaid)], ['Customers', $customers]] as [$l, $v]): ?>
    <div class="col-6 col-lg-2"><div class="stat-card h-100"><div class="small text-muted"><?= $l ?></div><div class="num"><?= $v ?></div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <h2 class="h6 text-uppercase text-muted">Latest orders</h2>
    <div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
      <thead><tr><th>#</th><th>Type / Table</th><th>Customer</th><th>Status</th><th>Payment</th><th class="text-end">Total</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $o): ?>
        <tr><td><?= (int)$o['id'] ?></td>
          <td>
            <?php if (!empty($o['table_no'])): ?>
              <span class="badge bg-secondary">Table <?= e($o['table_no']) ?></span>
            <?php else: ?>
              <span class="badge <?= ($o['order_type'] ?? '') === 'takeaway' ? 'bg-info text-dark' : 'bg-primary' ?>"><?= e(ucfirst($o['order_type'] ?? 'Delivery')) ?></span>
            <?php endif; ?>
          </td>
          <td><?= e($o['name']) ?></td>
          <td><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
          <td>
            <span class="pay-chip pay-<?= e($o['payment_status']) ?>"><?= e(str_replace('_', ' ', $o['payment_status'])) ?></span>
            <?php if (!empty($o['payment_method'])): ?>
              <span class="badge bg-dark border ms-1 small"><?= e(strtoupper($o['payment_method'])) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end"><?= money($o['total']) ?></td></tr>
      <?php endforeach; if (!$recent) echo '<tr><td colspan="6" class="text-muted">No orders yet.</td></tr>'; ?>
      </tbody></table></div>
  </div>
  <div class="col-lg-4">
    <h2 class="h6 text-uppercase text-muted">Top selling items</h2>
    <ul class="list-group">
      <?php foreach ($top as $t): ?><li class="list-group-item d-flex justify-content-between bg-transparent text-light"><?= e($t['name']) ?><span class="badge bg-dark border"><?= (int)$t['q'] ?></span></li><?php endforeach; if (!$top) echo '<li class="list-group-item bg-transparent text-muted">No data yet.</li>'; ?>
    </ul>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>

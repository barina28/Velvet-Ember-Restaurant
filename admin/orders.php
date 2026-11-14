<?php
$title = 'Orders'; $active = 'orders';
require_once __DIR__ . '/../includes/admin_header.php';

$status = $_GET['status'] ?? '';
$validStatus = ['pending', 'preparing', 'ready', 'served', 'cancelled'];
$where = ''; $params = [];
if (in_array($status, $validStatus, true)) { $where = 'WHERE o.status = ?'; $params[] = $status; }

$orders = q("SELECT o.*, t.table_no, c.name, c.phone FROM orders o LEFT JOIN dining_tables t ON t.id = o.table_id JOIN customers c ON c.id = o.customer_id $where ORDER BY o.id DESC LIMIT 150", $params)->fetchAll();
$items = order_items(array_column($orders, 'id'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="brand-font h3 mb-0">Orders</h1>
  <div class="btn-group btn-group-sm">
    <a class="btn btn-outline-light <?= $status === '' ? 'active' : '' ?>" href="orders.php">All</a>
    <?php foreach ($validStatus as $s): ?><a class="btn btn-outline-light <?= $status === $s ? 'active' : '' ?>" href="orders.php?status=<?= $s ?>"><?= ucfirst($s) ?></a><?php endforeach; ?>
  </div>
</div>
<div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
  <thead><tr><th>#</th><th>Date</th><th>Type / Table</th><th>Customer</th><th>Items</th><th>Status</th><th>Payment</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= (int)$o['id'] ?></td>
      <td class="small"><?= e(date('d M h:i A', strtotime($o['created_at']))) ?></td>
      <td>
        <?php if (!empty($o['table_no'])): ?>
          <span class="badge bg-secondary">Table <?= e($o['table_no']) ?></span>
        <?php else: ?>
          <span class="badge <?= ($o['order_type'] ?? '') === 'takeaway' ? 'bg-info text-dark' : 'bg-primary' ?>"><?= e(ucfirst($o['order_type'] ?? 'Delivery')) ?></span>
          <?php if (!empty($o['delivery_address'])): ?>
            <div class="small text-muted mt-1" style="max-width:180px;font-size:0.75rem"><?= e($o['delivery_address']) ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </td>
      <td><?= e($o['name']) ?><br><small class="text-muted"><?= e($o['phone']) ?></small></td>
      <td class="small"><?php foreach ($items[$o['id']] ?? [] as $l) echo (int)$l['qty'] . '&times; ' . e($l['name']) . '<br>'; ?></td>
      <td><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
      <td>
        <span class="pay-chip pay-<?= e($o['payment_status']) ?>"><?= e(str_replace('_', ' ', $o['payment_status'])) ?></span>
        <?php if (!empty($o['payment_method'])): ?>
          <div class="small text-muted mt-1"><?= e(strtoupper($o['payment_method'])) ?><?= !empty($o['bank_name']) ? ' (' . e($o['bank_name']) . ')' : '' ?></div>
        <?php endif; ?>
      </td>
      <td class="text-end"><?= money($o['total']) ?></td>
    </tr>
  <?php endforeach; if (!$orders) echo '<tr><td colspan="8" class="text-muted">No orders found.</td></tr>'; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>

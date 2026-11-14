<?php
$page = 'orders';
$page_title = 'My Orders';
require_once __DIR__ . '/includes/header.php';
ensure_orders_schema();

$orders = $__c ? visit_orders((int)$__c['id'], $__t ? (int)$__t['id'] : null) : [];
$items = order_items(array_column($orders, 'id'));
$labels = ['pending' => 'Received', 'preparing' => 'Being prepared', 'ready' => 'Ready - on its way', 'served' => 'Served', 'cancelled' => 'Cancelled'];
?>
<section class="page-section">
  <div class="container" style="max-width:820px">
    <h1 class="brand-font section-title text-center">My Orders</h1>

    <?php if (!$__c && !$orders): ?>
      <div class="card-dark p-4 text-center">
        <p class="text-muted">You haven't placed an order yet in this session.</p>
        <a href="<?= BASE ?>/index.php#menu" class="btn btn-burg">Browse Menu & Order</a>
      </div>
    <?php elseif (!$orders): ?>
      <div class="card-dark p-4 text-center">No orders placed recently. <a href="<?= BASE ?>/index.php#menu">Browse the menu</a></div>
    <?php else: foreach ($orders as $o): $lines = $items[$o['id']] ?? []; ?>
      <div class="card-dark p-3 p-md-4 mb-3 order-card" data-id="<?= (int)$o['id'] ?>">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h2 class="h5 mb-0">Order #<?= (int)$o['id'] ?></h2>
            <small class="text-muted"><?= e(date('d M, h:i A', strtotime($o['created_at']))) ?></small>
          </div>
          <div class="text-end">
            <span class="status status-<?= e($o['status']) ?>" data-role="status"><?= e($labels[$o['status']]) ?></span>
            <div class="mt-1"><span class="pay-chip pay-<?= e($o['payment_status']) ?>" data-role="pay"><?= $o['payment_status'] === 'paid' ? 'Paid' : ($o['payment_status'] === 'cash_pending' ? 'Cash - waiter coming' : 'Unpaid') ?></span></div>
          </div>
        </div>
        <ul class="list-unstyled my-3 order-lines">
          <?php foreach ($lines as $l): ?>
            <li class="d-flex justify-content-between"><span><?= (int)$l['qty'] ?> &times; <?= e($l['name']) ?></span><span><?= money($l['price'] * $l['qty']) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($o['notes']): ?><p class="small text-muted">Note: <?= e($o['notes']) ?></p><?php endif; ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <strong>Total <?= money($o['total']) ?></strong>
          <button class="btn btn-sm btn-outline-light reorder-btn" data-items='<?= e(json_encode(array_map(fn($l) => ['id' => (int)$l['item_id'], 'name' => $l['name'], 'qty' => (int)$l['qty']], $lines))) ?>'><i class="bi bi-arrow-repeat"></i> Order again</button>
        </div>
      </div>
    <?php endforeach; ?>
      <div class="text-center mt-4">
        <a href="<?= BASE ?>/index.php#menu" class="btn btn-outline-light me-2">Add more items</a>
        <a href="<?= BASE ?>/bill.php" class="btn btn-burg">View bill &amp; pay</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

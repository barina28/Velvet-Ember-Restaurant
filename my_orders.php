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

    <?php if (!empty($_GET['placed'])): ?>
      <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-check-circle-fill fs-3"></i>
        <div>
          <h5 class="alert-heading mb-1">Your order has been placed successfully!</h5>
          <p class="mb-0 small">Our kitchen is preparing your delicious meal. We will keep you updated.</p>
        </div>
      </div>
    <?php endif; ?>

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

        <!-- Live Order Tracking Bar & ETA -->
        <?php if ($o['status'] !== 'cancelled'): ?>
          <div class="my-3 p-3 rounded bg-dark border border-secondary-subtle">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-gold fw-bold"><i class="bi bi-clock-history me-1"></i> Live Order Tracking</span>
              <div class="eta-box small text-gold fw-bold">
                <?php if ($o['status'] === 'pending'): ?>
                  <i class="bi bi-hourglass-split"></i> Est. Time: ~35 - 45 Mins
                <?php elseif ($o['status'] === 'preparing'): ?>
                  <i class="bi bi-fire"></i> Est. Time: ~20 - 25 Mins
                <?php elseif ($o['status'] === 'ready'): ?>
                  <i class="bi bi-bicycle"></i> Out for Delivery / Ready
                <?php elseif ($o['status'] === 'served'): ?>
                  <i class="bi bi-check-all text-success"></i> Delivered / Completed
                <?php endif; ?>
              </div>
            </div>

            <!-- Steps Progress -->
            <div class="track-timeline">
              <div class="track-step <?= in_array($o['status'], ['pending', 'preparing', 'ready', 'served']) ? ($o['status'] === 'pending' ? 'active' : 'done') : '' ?>">
                <div class="track-dot"><i class="bi bi-receipt"></i></div>
                <div class="track-label">Confirmed</div>
              </div>
              <div class="track-step <?= in_array($o['status'], ['preparing', 'ready', 'served']) ? ($o['status'] === 'preparing' ? 'active' : 'done') : '' ?>">
                <div class="track-dot"><i class="bi bi-fire"></i></div>
                <div class="track-label">Cooking</div>
              </div>
              <div class="track-step <?= in_array($o['status'], ['ready', 'served']) ? ($o['status'] === 'ready' ? 'active' : 'done') : '' ?>">
                <div class="track-dot"><i class="bi bi-truck"></i></div>
                <div class="track-label">On Way</div>
              </div>
              <div class="track-step <?= $o['status'] === 'served' ? 'done' : '' ?>">
                <div class="track-dot"><i class="bi bi-check-circle"></i></div>
                <div class="track-label">Delivered</div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-danger py-2 my-3 small"><i class="bi bi-x-circle me-1"></i> This order was cancelled.</div>
        <?php endif; ?>
        <ul class="list-unstyled my-3 order-lines">
          <?php foreach ($lines as $l): ?>
            <li class="d-flex justify-content-between"><span><?= (int)$l['qty'] ?> &times; <?= e($l['name']) ?></span><span><?= money($l['price'] * $l['qty']) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($o['notes']): ?><p class="small text-muted mb-1">Note: <?= e($o['notes']) ?></p><?php endif; ?>
        <?php if (!empty($o['delivery_address'])): ?>
          <p class="small text-muted mb-1"><i class="bi bi-geo-alt text-gold"></i> Delivery: <?= e($o['delivery_address']) ?></p>
        <?php endif; ?>
        <?php if (!empty($o['payment_method'])): ?>
          <p class="small text-muted mb-2">
            <i class="bi bi-wallet2 text-gold"></i> Method: <strong><?= $o['payment_method'] === 'bank' ? 'Bank Transfer (' . e($o['bank_name'] ?? 'Bank') . ')' : 'Cash on Delivery (COD)' ?></strong>
          </p>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-2 border-top border-dark-subtle">
          <strong>Total <?= money($o['total']) ?></strong>
          <div class="d-flex gap-2">
            <?php if ($o['status'] === 'pending'): ?>
              <button class="btn btn-sm btn-outline-danger cancel-order-btn" data-id="<?= (int)$o['id'] ?>">
                <i class="bi bi-x-circle me-1"></i> Cancel Order
              </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-light reorder-btn" data-items='<?= e(json_encode(array_map(fn($l) => ['id' => (int)$l['item_id'], 'name' => $l['name'], 'qty' => (int)$l['qty']], $lines))) ?>'><i class="bi bi-arrow-repeat"></i> Order again</button>
          </div>
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

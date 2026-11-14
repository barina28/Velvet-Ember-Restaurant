<?php
$page = 'bill';
$page_title = 'Bill';
require_once __DIR__ . '/includes/header.php';

$receipt = null;
if (!empty($_GET['receipt']) && $__c) {
    $receipt = q('SELECT * FROM payments WHERE id = ? AND customer_id = ?', [(int)$_GET['receipt'], $__c['id']])->fetch() ?: null;
}

$unpaid = $pending = $paid = [];
if ($__c) {
    foreach (visit_orders((int)$__c['id'], $__t ? (int)$__t['id'] : null) as $o) {
        if ($o['status'] === 'cancelled') continue;
        if ($o['payment_status'] === 'unpaid') $unpaid[] = $o;
        elseif ($o['payment_status'] === 'cash_pending') $pending[] = $o;
        else $paid[] = $o;
    }
}
$items = order_items(array_column(array_merge($unpaid, $pending, $paid), 'id'));
$sum = fn($arr, $k) => array_sum(array_column($arr, $k));
?>
<section class="page-section">
  <div class="container" style="max-width:760px">
    <h1 class="brand-font section-title text-center">Your Bill</h1>

    <?php if ($receipt): ?>
      <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill fs-4"></i>
        <div><?= $receipt['status'] === 'paid' ? 'Payment received. Thank you!' : 'Cash payment requested. A waiter will collect it at your table.' ?>
          <div class="small">Receipt #<?= (int)$receipt['id'] ?> &middot; <?= money($receipt['amount']) ?> &middot; <?= e(ucfirst($receipt['method'])) ?></div></div>
      </div>
    <?php endif; ?>

    <?php if (!$__c): ?>
      <div class="card-dark p-4 text-center">Place an order from the menu to see your bill here. <br><a href="<?= BASE ?>/index.php#menu" class="btn btn-burg mt-3">Browse Menu</a></div>
    <?php elseif (!$unpaid && !$pending && !$paid): ?>
      <div class="card-dark p-4 text-center">No orders yet. <a href="<?= BASE ?>/index.php#menu">Browse the menu</a></div>
    <?php else: ?>

      <?php foreach ([['Unpaid', $unpaid], ['Cash payment pending', $pending], ['Paid', $paid]] as [$label, $arr]): if (!$arr) continue; ?>
        <div class="card-dark p-3 p-md-4 mb-3">
          <h2 class="h6 text-uppercase text-muted mb-3"><?= $label ?></h2>
          <?php foreach ($arr as $o): ?>
            <div class="mb-2"><small class="text-muted">Order #<?= (int)$o['id'] ?></small>
              <?php foreach ($items[$o['id']] ?? [] as $l): ?>
                <div class="d-flex justify-content-between"><span><?= (int)$l['qty'] ?> &times; <?= e($l['name']) ?></span><span><?= money($l['price'] * $l['qty']) ?></span></div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <hr>
          <div class="d-flex justify-content-between small"><span>Subtotal</span><span><?= money($sum($arr, 'subtotal')) ?></span></div>
          <div class="d-flex justify-content-between small"><span>Tax</span><span><?= money($sum($arr, 'tax')) ?></span></div>
          <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span><?= money($sum($arr, 'total')) ?></span></div>
          <?php if ($label === 'Unpaid'): ?>
            <a href="<?= BASE ?>/pay.php" class="btn btn-burg btn-lg w-100 mt-3"><i class="bi bi-credit-card"></i> Pay <?= money($sum($arr, 'total')) ?></a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <div class="text-center"><a href="<?= BASE ?>/index.php#menu" class="btn btn-outline-light">Order something else</a></div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

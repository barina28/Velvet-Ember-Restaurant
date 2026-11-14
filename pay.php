<?php
$page = 'pay';
$page_title = 'Pay';
require_once __DIR__ . '/includes/header.php';

$total = 0;
if ($__t && $__c) {
    $total = (float)q("SELECT COALESCE(SUM(total),0) FROM orders WHERE customer_id = ? AND table_id = ? AND payment_status = 'unpaid' AND status <> 'cancelled'", [$__c['id'], $__t['id']])->fetchColumn();
}
?>
<section class="page-section">
  <div class="container" style="max-width:560px">
    <h1 class="brand-font section-title text-center">Pay your bill</h1>

    <?php if ($total <= 0): ?>
      <div class="card-dark p-4 text-center">Nothing to pay right now. <a href="<?= BASE ?>/bill.php">Back to bill</a></div>
    <?php else: ?>
      <div class="card-dark p-4">
        <div class="text-center mb-4"><span class="text-muted small">Amount due</span><div class="display-6 brand-font"><?= money($total) ?></div></div>

        <div class="pay-methods" id="payMethods">
          <label><input type="radio" name="method" value="jazzcash" checked><span><i class="bi bi-phone"></i> JazzCash</span></label>
          <label><input type="radio" name="method" value="easypaisa"><span><i class="bi bi-phone"></i> Easypaisa</span></label>
          <label><input type="radio" name="method" value="card"><span><i class="bi bi-credit-card-2-front"></i> Card</span></label>
          <label><input type="radio" name="method" value="cash"><span><i class="bi bi-cash-coin"></i> Cash at table</span></label>
        </div>

        <div id="walletBox" class="mt-4">
          <label class="form-label">Mobile account number</label>
          <input id="wallet" class="form-control form-control-lg" inputmode="numeric" placeholder="03001234567" maxlength="11">
        </div>
        <div id="cardBox" class="mt-4 d-none small text-muted">You will be taken to the secure card payment page of our payment partner. (Demo mode: payment is approved instantly.)</div>
        <div id="cashBox" class="mt-4 d-none small text-muted">A waiter will come to your table to collect the cash and confirm your payment.</div>

        <div class="text-danger small mt-3" id="payErr"></div>
        <button class="btn btn-burg btn-lg w-100 mt-3" id="payBtn">Pay now</button>
        <p class="text-center small text-muted mt-3 mb-0"><i class="bi bi-lock-fill"></i> Demo payment mode &ndash; connect a real gateway in includes/payment.php</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

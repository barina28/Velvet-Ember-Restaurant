<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/payment.php';
api_csrf();

$t = cur_table();
$c = cur_customer();
if (!$t || !$c) json_out(['ok' => false, 'error' => 'Session expired. Please scan your table QR again.'], 403);

$in = json_in();
$method = $in['method'] ?? '';
if (!in_array($method, ['card', 'jazzcash', 'easypaisa', 'cash'], true)) json_out(['ok' => false, 'error' => 'Choose a payment method.'], 422);

$orders = q("SELECT id, total FROM orders WHERE customer_id = ? AND table_id = ? AND payment_status = 'unpaid' AND status <> 'cancelled'", [$c['id'], $t['id']])->fetchAll();
if (!$orders) json_out(['ok' => false, 'error' => 'Nothing to pay right now.'], 422);

$amount = array_sum(array_column($orders, 'total'));
$ids = array_column($orders, 'id');
$in_sql = implode(',', array_fill(0, count($ids), '?'));

$reference = '';
$wallet = preg_replace('/\D/', '', $in['wallet'] ?? '');
if (in_array($method, ['jazzcash', 'easypaisa'], true)) {
    if (!preg_match('/^03\d{9}$/', $wallet)) json_out(['ok' => false, 'error' => 'Enter a valid mobile number like 03001234567.'], 422);
    $reference = 'wallet ***' . substr($wallet, -4);
}

$pdo = db();
$pdo->beginTransaction();
try {
    if ($method === 'cash') {
        q("INSERT INTO payments (customer_id, table_id, amount, method, status) VALUES (?,?,?,?, 'pending')", [$c['id'], $t['id'], $amount, 'cash']);
        $pid = (int)$pdo->lastInsertId();
        q("UPDATE orders SET payment_status = 'cash_pending', payment_id = ? WHERE id IN ($in_sql)", array_merge([$pid], $ids));
        q("INSERT INTO waiter_calls (table_id, customer_id, reason) VALUES (?,?,?)", [$t['id'], $c['id'], 'Cash payment of ' . money($amount)]);
        $pdo->commit();
        json_out(['ok' => true, 'pending' => true, 'payment_id' => $pid, 'message' => 'A waiter is coming to collect the cash.']);
    }

    $g = process_gateway($method, (float)$amount, ['wallet' => $wallet]);
    if (empty($g['ok'])) { $pdo->rollBack(); json_out(['ok' => false, 'error' => $g['error'] ?? 'Payment failed.'], 402); }

    q("INSERT INTO payments (customer_id, table_id, amount, method, reference, status, paid_at) VALUES (?,?,?,?,?, 'paid', NOW())",
      [$c['id'], $t['id'], $amount, $method, trim($reference . ' ' . $g['ref'])]);
    $pid = (int)$pdo->lastInsertId();
    q("UPDATE orders SET payment_status = 'paid', paid_at = NOW(), payment_id = ? WHERE id IN ($in_sql)", array_merge([$pid], $ids));
    $pdo->commit();
    json_out(['ok' => true, 'payment_id' => $pid]);
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_out(['ok' => false, 'error' => 'Payment could not be recorded. Please try again.'], 500);
}

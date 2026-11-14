<?php
require_once __DIR__ . '/../includes/config.php';
api_csrf();

$c = cur_customer();
if (!$c) json_out(['ok' => false, 'error' => 'Please identify yourself first.'], 401);

$in = json_in();
$oid = (int)($in['order_id'] ?? 0);
if ($oid <= 0) json_out(['ok' => false, 'error' => 'Invalid order ID.'], 422);

$order = q('SELECT * FROM orders WHERE id = ? AND customer_id = ?', [$oid, $c['id']])->fetch();
if (!$order) json_out(['ok' => false, 'error' => 'Order not found.'], 404);

if ($order['status'] !== 'pending') {
    json_out(['ok' => false, 'error' => 'Order cannot be cancelled because the kitchen has already started preparing it.'], 400);
}

q("UPDATE orders SET status = 'cancelled' WHERE id = ?", [$oid]);
json_out(['ok' => true, 'message' => 'Your order #' . $oid . ' has been cancelled successfully.']);

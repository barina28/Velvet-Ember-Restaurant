<?php
require_once __DIR__ . '/../includes/config.php';
api_csrf();

ensure_orders_schema();

$t = cur_table();
$c = cur_customer();
if (!$c) json_out(['ok' => false, 'error' => 'identify'], 401);

$in    = json_in();
$order_type = in_array($in['order_type'] ?? '', ['dine_in', 'takeaway', 'delivery'], true) ? $in['order_type'] : ($t ? 'dine_in' : 'delivery');
$address = mb_substr(trim($in['address'] ?? ''), 0, 400);

if ($order_type === 'dine_in' && !$t) {
    // If dine in selected but no table, allow user to specify table number or default to takeaway/delivery
    $order_type = 'takeaway';
}

if ($order_type === 'delivery' && mb_strlen($address) < 5) {
    json_out(['ok' => false, 'error' => 'Please provide a valid delivery address.'], 422);
}

$items = $in['items'] ?? [];
$notes = mb_substr(trim($in['notes'] ?? ''), 0, 250);
if (!is_array($items) || !$items || count($items) > 50) json_out(['ok' => false, 'error' => 'Your cart is empty.'], 422);

$qtyById = [];
foreach ($items as $it) {
    $id = (int)($it['id'] ?? 0);
    $qty = max(1, min(20, (int)($it['qty'] ?? 1)));
    if ($id > 0) $qtyById[$id] = ($qtyById[$id] ?? 0) + $qty;
}
if (!$qtyById) json_out(['ok' => false, 'error' => 'Your cart is empty.'], 422);

// Prices always come from the database, never from the browser
$in_sql = implode(',', array_fill(0, count($qtyById), '?'));
$rows = q("SELECT id, name, price FROM menu_items WHERE available = 1 AND id IN ($in_sql)", array_keys($qtyById))->fetchAll();
if (!$rows) json_out(['ok' => false, 'error' => 'Those items are not available right now.'], 422);

$sub = 0;
foreach ($rows as $r) $sub += $r['price'] * min(20, $qtyById[$r['id']]);
[$sub, $tax, $total] = calc_totals($sub);

$pdo = db();
$pdo->beginTransaction();
try {
    $table_id = ($order_type === 'dine_in' && $t) ? $t['id'] : null;
    q('INSERT INTO orders (customer_id, table_id, order_type, subtotal, tax, total, notes, delivery_address) VALUES (?,?,?,?,?,?,?,?)',
      [$c['id'], $table_id, $order_type, $sub, $tax, $total, $notes, $address]);
    $oid = (int)$pdo->lastInsertId();
    foreach ($rows as $r) {
        q('INSERT INTO order_items (order_id, item_id, name, price, qty) VALUES (?,?,?,?,?)',
          [$oid, $r['id'], $r['name'], $r['price'], $qtyById[$r['id']]]);
    }
    $pdo->commit();
} catch (Throwable $ex) {
    $pdo->rollBack();
    json_out(['ok' => false, 'error' => 'Could not place the order, please try again.'], 500);
}
json_out(['ok' => true, 'order_id' => $oid, 'total' => $total]);

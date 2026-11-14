<?php
require_once __DIR__ . '/../includes/config.php';
$u = user();
if (!$u) json_out(['ok' => false, 'error' => 'Not logged in'], 401);

$orders = q("SELECT o.id, o.status, o.notes, o.created_at, t.table_no, c.name AS customer
             FROM orders o JOIN dining_tables t ON t.id = o.table_id JOIN customers c ON c.id = o.customer_id
             WHERE o.status IN ('pending','preparing','ready') ORDER BY o.id")->fetchAll();
$items = order_items(array_column($orders, 'id'));
foreach ($orders as &$o) { $o['id'] = (int)$o['id']; $o['items'] = $items[$o['id']] ?? []; }
unset($o);

$calls = q("SELECT w.id, w.reason, t.table_no, c.name AS customer
            FROM waiter_calls w JOIN dining_tables t ON t.id = w.table_id LEFT JOIN customers c ON c.id = w.customer_id
            WHERE w.status = 'open' ORDER BY w.id")->fetchAll();

$cash = q("SELECT p.id, p.amount, t.table_no, c.name AS customer
           FROM payments p JOIN dining_tables t ON t.id = p.table_id JOIN customers c ON c.id = p.customer_id
           WHERE p.status = 'pending' AND p.method = 'cash' ORDER BY p.id")->fetchAll();

json_out(['ok' => true, 'orders' => $orders, 'calls' => $calls, 'cash' => $cash,
          'can_cash' => in_array($u['role'], ['admin', 'manager', 'cashier', 'waiter'], true)]);

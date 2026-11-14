<?php
require_once __DIR__ . '/../includes/config.php';
api_csrf();

$t = cur_table();
if (!$t) json_out(['ok' => false, 'error' => 'Please scan the QR code on your table first.'], 403);

$allowed = ['Need assistance', 'Order issue', 'Water please', 'Bill / payment help', 'Cleaning needed', 'Other'];
$reason = json_in()['reason'] ?? 'Need assistance';
if (!in_array($reason, $allowed, true)) $reason = 'Need assistance';

$dup = q("SELECT id FROM waiter_calls WHERE table_id = ? AND status = 'open' AND created_at > (NOW() - INTERVAL 2 MINUTE)", [$t['id']])->fetch();
if (!$dup) {
    q('INSERT INTO waiter_calls (table_id, customer_id, reason) VALUES (?,?,?)', [$t['id'], $_SESSION['customer_id'] ?? null, $reason]);
}
json_out(['ok' => true, 'message' => $dup ? 'A waiter has already been notified and is on the way.' : 'Waiter called! Someone will be at your table shortly.']);

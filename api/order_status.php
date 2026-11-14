<?php
require_once __DIR__ . '/../includes/config.php';

$t = cur_table();
$c = cur_customer();
if (!$t || !$c) json_out(['ok' => true, 'orders' => []]);

$out = [];
foreach (visit_orders((int)$c['id'], (int)$t['id']) as $o) {
    $out[] = ['id' => (int)$o['id'], 'status' => $o['status'], 'payment_status' => $o['payment_status']];
}
json_out(['ok' => true, 'orders' => $out]);

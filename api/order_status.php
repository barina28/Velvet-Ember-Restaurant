<?php
require_once __DIR__ . '/../includes/config.php';

$c = cur_customer();
$t = cur_table();
if (!$c) json_out(['ok' => true, 'orders' => []]);

$out = [];
foreach (visit_orders((int)$c['id'], $t ? (int)$t['id'] : null) as $o) {
    $out[] = [
        'id' => (int)$o['id'],
        'status' => $o['status'],
        'payment_status' => $o['payment_status']
    ];
}
json_out(['ok' => true, 'orders' => $out]);

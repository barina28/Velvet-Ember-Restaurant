<?php
require_once __DIR__ . '/../includes/config.php';
api_csrf();

$in    = json_in();
$name  = trim($in['name'] ?? '');
$phone = preg_replace('/[^0-9+]/', '', $in['phone'] ?? '');

if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || strlen($phone) < 7 || strlen($phone) > 15) {
    json_out(['ok' => false, 'error' => 'Please enter a valid name and phone number.'], 422);
}

$c = q('SELECT id FROM customers WHERE phone = ?', [$phone])->fetch();
if ($c) {
    q('UPDATE customers SET name = ?, visits = visits + 1, last_visit = NOW() WHERE id = ?', [$name, $c['id']]);
    $id = (int)$c['id'];
} else {
    q('INSERT INTO customers (name, phone, last_visit) VALUES (?, ?, NOW())', [$name, $phone]);
    $id = (int)db()->lastInsertId();
}
$_SESSION['customer_id'] = $id;
json_out(['ok' => true, 'name' => $name]);

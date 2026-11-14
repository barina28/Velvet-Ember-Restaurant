<?php
// Landing point of the table QR code:  scan.php?t=<table token>
require_once __DIR__ . '/includes/config.php';

$token = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$t = $token ? q('SELECT * FROM dining_tables WHERE token = ?', [$token])->fetch() : null;

if ($t) {
    // A new table means a new visit: forget the previous customer
    if (($_SESSION['table_id'] ?? 0) != $t['id']) unset($_SESSION['customer_id']);
    $_SESSION['table_id'] = (int)$t['id'];
    redirect('index.php?welcome=1#menu');
}
http_response_code(404);
exit('Invalid or expired QR code. Please ask the staff for help.');

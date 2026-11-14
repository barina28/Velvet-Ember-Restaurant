<?php
require_once __DIR__ . '/../includes/config.php';
$u = require_login_api();   // any logged-in staff role + CSRF header

$in = json_in();
$action = $in['action'] ?? '';
$id = (int)($in['id'] ?? 0);
$role = $u['role'];

switch ($action) {
    case 'status':
        $new = $in['status'] ?? '';
        $cur = q('SELECT status FROM orders WHERE id = ?', [$id])->fetchColumn();
        $flow = ['pending' => 'preparing', 'preparing' => 'ready', 'ready' => 'served'];
        if (!$cur || ($flow[$cur] ?? null) !== $new) json_out(['ok' => false, 'error' => 'Invalid status change'], 422);
        // who may do which step
        $who = ['preparing' => ['kitchen', 'manager', 'admin'], 'ready' => ['kitchen', 'manager', 'admin'], 'served' => ['waiter', 'manager', 'admin']];
        if (!in_array($role, $who[$new], true)) json_out(['ok' => false, 'error' => 'Your role cannot do this step'], 403);
        q('UPDATE orders SET status = ? WHERE id = ?', [$new, $id]);
        break;

    case 'cancel':
        if (!in_array($role, ['waiter', 'manager', 'admin'], true)) json_out(['ok' => false, 'error' => 'Not allowed'], 403);
        q("UPDATE orders SET status = 'cancelled' WHERE id = ? AND status = 'pending'", [$id]);
        break;

    case 'call_done':
        q("UPDATE waiter_calls SET status = 'done' WHERE id = ?", [$id]);
        break;

    case 'confirm_cash':
        if (!in_array($role, ['cashier', 'waiter', 'manager', 'admin'], true)) json_out(['ok' => false, 'error' => 'Not allowed'], 403);
        $pdo = db();
        $pdo->beginTransaction();
        q("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ? AND status = 'pending'", [$id]);
        q("UPDATE orders SET payment_status = 'paid', paid_at = NOW() WHERE payment_id = ?", [$id]);
        $pdo->commit();
        break;

    default:
        json_out(['ok' => false, 'error' => 'Unknown action'], 422);
}
json_out(['ok' => true]);

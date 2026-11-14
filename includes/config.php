<?php
// ---------------------------------------------------------------
// Core configuration + helper functions
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// >>> EDIT THESE FOR YOUR SERVER <<<
const DB_HOST = 'localhost';
const DB_NAME = 'restaurant_db';
const DB_USER = 'root';
const DB_PASS = '';
const CURRENCY = 'Rs.';

date_default_timezone_set('Asia/Karachi');

// URL prefix of the project (e.g. "/restaurant" when in htdocs/restaurant)
$__root = str_replace('\\', '/', dirname(__DIR__));
$__doc  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
define('BASE', ($__doc !== '' && strpos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Check includes/config.php and import database.sql.');
        }
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function setting(string $k, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (q('SELECT k, v FROM settings') as $r) $cache[$r['k']] = $r['v'];
    }
    return $cache[$k] ?? $default;
}

// Logo: uploaded file (settings 'logo') or the default SVG
function logo_url(): string {
    $f = setting('logo');
    if ($f !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $f) && is_file(dirname(__DIR__) . '/assets/img/uploads/' . $f)) {
        return BASE . '/assets/img/uploads/' . $f . '?v=' . filemtime(dirname(__DIR__) . '/assets/img/uploads/' . $f);
    }
    return BASE . '/assets/img/picture/logo.svg';
}

// Creates the feedback table on first use 
function ensure_feedback_table(): void {
    q("CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        table_no VARCHAR(20) NULL,
        name VARCHAR(80) NOT NULL DEFAULT '',
        rating TINYINT NOT NULL,
        comment TEXT NOT NULL,
        reply TEXT NULL,
        replied_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // add reply column if missing
    try {
        q("ALTER TABLE feedback ADD COLUMN reply TEXT NULL AFTER comment, ADD COLUMN replied_at DATETIME NULL AFTER reply");
    } catch (Throwable $e) {}
}

function ensure_reservations_table(): void {
    q("CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(120) NULL,
        guests INT NOT NULL DEFAULT 2,
        reservation_date DATE NOT NULL,
        reservation_time TIME NOT NULL,
        special_requests TEXT NULL,
        status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensure_orders_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        q("ALTER TABLE orders MODIFY table_id INT NULL");
    } catch (Throwable $e) {}
    try {
        q("ALTER TABLE orders ADD COLUMN order_type VARCHAR(20) NOT NULL DEFAULT 'delivery' AFTER table_id");
    } catch (Throwable $e) {}
    try {
        q("ALTER TABLE orders ADD COLUMN delivery_address TEXT NULL AFTER notes");
    } catch (Throwable $e) {}
}

function money($n): string { return CURRENCY . ' ' . number_format((float)$n, 0); }

function redirect(string $path): never {
    header('Location: ' . BASE . '/' . ltrim($path, '/'));
    exit;
}

function site_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE;
}

function json_out(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_in(): array {
    $d = json_decode(file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}

function flash(string $m, string $t = 'success'): void { $_SESSION['flash'] = [$m, $t]; }

// ---------- CSRF ----------
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid or expired form. Go back and refresh the page.'); }
}
function api_csrf(): void {
    $t = $_SERVER['HTTP_X_CSRF'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) json_out(['ok' => false, 'error' => 'Session expired, please refresh the page.'], 400);
}

// ---------- Staff auth ----------
function user(): ?array { return $_SESSION['user'] ?? null; }

function require_login(array $roles = []): array {
    $u = user();
    if (!$u) redirect('login.php');
    if ($roles && !in_array($u['role'], $roles, true)) { http_response_code(403); exit('Access denied for your role.'); }
    return $u;
}
function require_login_api(array $roles = []): array {
    $u = user();
    if (!$u) json_out(['ok' => false, 'error' => 'Not logged in'], 401);
    if ($roles && !in_array($u['role'], $roles, true)) json_out(['ok' => false, 'error' => 'Not allowed'], 403);
    api_csrf();
    return $u;
}

// ---------- Customer session ----------
function cur_table(): ?array {
    if (empty($_SESSION['table_id'])) return null;
    return q('SELECT * FROM dining_tables WHERE id = ?', [$_SESSION['table_id']])->fetch() ?: null;
}
function cur_customer(): ?array {
    if (empty($_SESSION['customer_id'])) return null;
    return q('SELECT * FROM customers WHERE id = ?', [$_SESSION['customer_id']])->fetch() ?: null;
}
function calc_totals(float $sub): array {
    $tax = round($sub * (float)setting('tax_percent', '0') / 100);
    return [$sub, $tax, $sub + $tax];
}

// Orders of this customer during the last 24 hours (current visit or recent orders)
function visit_orders(int $customerId, ?int $tableId = null): array {
    if ($tableId) {
        return q("SELECT * FROM orders WHERE customer_id = ? AND table_id = ? AND created_at > (NOW() - INTERVAL 24 HOUR) ORDER BY id DESC", [$customerId, $tableId])->fetchAll();
    }
    return q("SELECT * FROM orders WHERE customer_id = ? AND created_at > (NOW() - INTERVAL 24 HOUR) ORDER BY id DESC", [$customerId])->fetchAll();
}
function order_items(array $orderIds): array {
    if (!$orderIds) return [];
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    $out = [];
    foreach (q("SELECT * FROM order_items WHERE order_id IN ($in)", array_values($orderIds)) as $r) $out[$r['order_id']][] = $r;
    return $out;
}
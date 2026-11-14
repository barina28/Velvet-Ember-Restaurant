<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);

$title = 'Sales Reports'; $active = 'reports';
require_once __DIR__ . '/../includes/admin_header.php';

// Date filter
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$sqlParams = [$from . ' 00:00:00', $to . ' 23:59:59'];

// Summary stats
$summary = q("SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total), 0) as total_sales,
    COALESCE(SUM(tax), 0) as total_tax,
    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) as paid_amount,
    COALESCE(SUM(CASE WHEN payment_status <> 'paid' THEN total ELSE 0 END), 0) as unpaid_amount
  FROM orders 
  WHERE created_at BETWEEN ? AND ? AND status <> 'cancelled'", $sqlParams)->fetch();

// Payment method breakdown
$pmBreakdown = q("SELECT 
    COALESCE(payment_method, 'unpaid') as method,
    COUNT(*) as cnt,
    COALESCE(SUM(total), 0) as total
  FROM orders 
  WHERE created_at BETWEEN ? AND ? AND status <> 'cancelled' AND payment_status = 'paid'
  GROUP BY payment_method", $sqlParams)->fetchAll();

// Top selling dishes in period
$topDishes = q("SELECT 
    oi.name,
    SUM(oi.qty) as total_qty,
    SUM(oi.price * oi.qty) as total_revenue
  FROM order_items oi
  JOIN orders o ON o.id = oi.order_id
  WHERE o.created_at BETWEEN ? AND ? AND o.status <> 'cancelled'
  GROUP BY oi.name
  ORDER BY total_qty DESC
  LIMIT 10", $sqlParams)->fetchAll();

// Daily breakdown
$daily = q("SELECT 
    DATE(created_at) as day,
    COUNT(*) as orders_count,
    COALESCE(SUM(total), 0) as day_sales
  FROM orders
  WHERE created_at BETWEEN ? AND ? AND status <> 'cancelled'
  GROUP BY DATE(created_at)
  ORDER BY day DESC", $sqlParams)->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
  <div>
    <h1 class="brand-font h3 mb-0">Sales & Performance Reports</h1>
    <p class="text-muted small mb-0">Revenue, order volume, and top item analytics.</p>
  </div>
  <button class="btn btn-outline-light btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print / Save PDF</button>
</div>

<!-- Filter Bar -->
<div class="card-dark p-3 mb-4 no-print">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-sm-4 col-md-3">
      <label class="form-label small text-muted">From Date</label>
      <input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>">
    </div>
    <div class="col-sm-4 col-md-3">
      <label class="form-label small text-muted">To Date</label>
      <input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>">
    </div>
    <div class="col-sm-4 col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-burg btn-sm flex-grow-1"><i class="bi bi-funnel"></i> Apply Filter</button>
      <a href="reports.php" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card h-100">
      <div class="small text-muted">Total Sales</div>
      <div class="num text-gold"><?= money($summary['total_sales']) ?></div>
      <div class="small text-muted mt-1"><?= (int)$summary['total_orders'] ?> completed orders</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card h-100">
      <div class="small text-muted">Total Collected (Paid)</div>
      <div class="num text-success"><?= money($summary['paid_amount']) ?></div>
      <div class="small text-muted mt-1">Cash & Online</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card h-100">
      <div class="small text-muted">Tax Collected</div>
      <div class="num text-light"><?= money($summary['total_tax']) ?></div>
      <div class="small text-muted mt-1">Government sales tax</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card h-100">
      <div class="small text-muted">Unpaid Orders</div>
      <div class="num text-danger"><?= money($summary['unpaid_amount']) ?></div>
      <div class="small text-muted mt-1">Pending payments</div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Top items -->
  <div class="col-lg-7">
    <div class="card-dark p-3 h-100">
      <h3 class="brand-font h5 mb-3 text-gold"><i class="bi bi-trophy"></i> Top Selling Dishes</h3>
      <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
          <thead>
            <tr>
              <th>Dish</th>
              <th class="text-center">Qty Sold</th>
              <th class="text-end">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topDishes as $td): ?>
              <tr>
                <td><strong><?= e($td['name']) ?></strong></td>
                <td class="text-center"><span class="badge bg-secondary"><?= (int)$td['total_qty'] ?></span></td>
                <td class="text-end text-gold"><?= money($td['total_revenue']) ?></td>
              </tr>
            <?php endforeach; if (!$topDishes) echo '<tr><td colspan="3" class="text-muted text-center py-3">No sales recorded for this period.</td></tr>'; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Daily Summary -->
  <div class="col-lg-5">
    <div class="card-dark p-3 h-100">
      <h3 class="brand-font h5 mb-3 text-gold"><i class="bi bi-calendar3"></i> Day-by-Day Revenue</h3>
      <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
        <table class="table table-dark-custom align-middle">
          <thead>
            <tr>
              <th>Date</th>
              <th class="text-center">Orders</th>
              <th class="text-end">Sales</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daily as $d): ?>
              <tr>
                <td><?= e(date('d M Y (D)', strtotime($d['day']))) ?></td>
                <td class="text-center"><?= (int)$d['orders_count'] ?></td>
                <td class="text-end fw-bold"><?= money($d['day_sales']) ?></td>
              </tr>
            <?php endforeach; if (!$daily) echo '<tr><td colspan="3" class="text-muted text-center py-3">No daily data available.</td></tr>'; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

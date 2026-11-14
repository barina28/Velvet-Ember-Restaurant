<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);
ensure_reservations_table();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_check();
    $id = (int)$_POST['id'];
    $act = $_POST['action'];
    if (in_array($act, ['confirmed', 'cancelled', 'pending'], true)) {
        q('UPDATE reservations SET status = ? WHERE id = ?', [$act, $id]);
        flash('Reservation updated to ' . ucfirst($act));
    }
    redirect('admin/reservations.php');
}

$title = 'Reservations'; $active = 'reservations';
require_once __DIR__ . '/../includes/admin_header.php';

$filter = $_GET['status'] ?? 'all';
$where = ''; $params = [];
if (in_array($filter, ['pending', 'confirmed', 'cancelled'], true)) {
    $where = 'WHERE status = ?';
    $params = [$filter];
}

$counts = [
    'all' => q('SELECT COUNT(*) FROM reservations')->fetchColumn(),
    'pending' => q("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn(),
    'confirmed' => q("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn(),
    'cancelled' => q("SELECT COUNT(*) FROM reservations WHERE status = 'cancelled'")->fetchColumn(),
];

$rows = q("SELECT * FROM reservations $where ORDER BY reservation_date DESC, reservation_time DESC LIMIT 200", $params)->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="brand-font h3 mb-0">Table Bookings & Reservations</h1>
  <div>
    <span class="badge bg-warning text-dark me-2 fs-6"><?= $counts['pending'] ?> Pending</span>
    <span class="badge bg-success fs-6"><?= $counts['confirmed'] ?> Confirmed</span>
  </div>
</div>

<div class="mb-3 d-flex flex-wrap gap-2">
  <a class="btn btn-sm <?= $filter === 'all' ? 'btn-burg' : 'btn-outline-light' ?>" href="reservations.php">All (<?= $counts['all'] ?>)</a>
  <a class="btn btn-sm <?= $filter === 'pending' ? 'btn-burg' : 'btn-outline-light' ?>" href="reservations.php?status=pending">Pending (<?= $counts['pending'] ?>)</a>
  <a class="btn btn-sm <?= $filter === 'confirmed' ? 'btn-burg' : 'btn-outline-light' ?>" href="reservations.php?status=confirmed">Confirmed (<?= $counts['confirmed'] ?>)</a>
  <a class="btn btn-sm <?= $filter === 'cancelled' ? 'btn-burg' : 'btn-outline-light' ?>" href="reservations.php?status=cancelled">Cancelled (<?= $counts['cancelled'] ?>)</a>
</div>

<div class="table-responsive">
  <table class="table table-dark-custom table-striped align-middle">
    <thead>
      <tr>
        <th>Date & Time</th>
        <th>Guest Name</th>
        <th>Contact</th>
        <th>Guests</th>
        <th>Special Notes</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <strong><?= e(date('d M Y', strtotime($r['reservation_date']))) ?></strong>
            <div class="small text-gold"><?= e(date('h:i A', strtotime($r['reservation_time']))) ?></div>
          </td>
          <td><strong><?= e($r['name']) ?></strong></td>
          <td>
            <a href="tel:<?= e($r['phone']) ?>" class="text-light text-decoration-none"><i class="bi bi-telephone text-muted"></i> <?= e($r['phone']) ?></a>
            <?php if (!empty($r['email'])): ?><div class="small text-muted"><?= e($r['email']) ?></div><?php endif; ?>
          </td>
          <td><span class="badge bg-secondary"><?= (int)$r['guests'] ?> Persons</span></td>
          <td class="small text-muted" style="max-width:250px"><?= !empty($r['special_requests']) ? e($r['special_requests']) : '-' ?></td>
          <td>
            <?php if ($r['status'] === 'confirmed'): ?>
              <span class="badge bg-success">Confirmed</span>
            <?php elseif ($r['status'] === 'cancelled'): ?>
              <span class="badge bg-danger">Cancelled</span>
            <?php else: ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <form method="post" action="" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <?php if ($r['status'] !== 'confirmed'): ?>
                <button type="submit" name="action" value="confirmed" class="btn btn-sm btn-outline-success me-1" title="Accept"><i class="bi bi-check-lg"></i></button>
              <?php endif; ?>
              <?php if ($r['status'] !== 'cancelled'): ?>
                <button type="submit" name="action" value="cancelled" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this reservation?')"><i class="bi bi-x-lg"></i></button>
              <?php endif; ?>
            </form>
          </td>
        </tr>
      <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="text-center py-4 text-muted">No reservations found.</td></tr>'; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

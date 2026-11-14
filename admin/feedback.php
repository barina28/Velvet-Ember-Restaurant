<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);
ensure_feedback_table();

// Delete one feedback (POST + CSRF only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_check();
    q('DELETE FROM feedback WHERE id = ?', [(int)$_POST['delete']]);
    flash('Feedback deleted.');
    redirect('admin/feedback.php');
}

$title = 'Feedback'; $active = 'feedback';
require_once __DIR__ . '/../includes/admin_header.php';

$r = (int)($_GET['r'] ?? 0);
$where = ''; $params = [];
if ($r >= 1 && $r <= 5) { $where = 'WHERE rating = ?'; $params = [$r]; }

$stats = q('SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(rating), 1), 0) AS avg_rating FROM feedback')->fetch();
$byStar = array_fill(1, 5, 0);
foreach (q('SELECT rating, COUNT(*) AS n FROM feedback GROUP BY rating') as $s) $byStar[(int)$s['rating']] = (int)$s['n'];

$rows = q("SELECT * FROM feedback $where ORDER BY id DESC LIMIT 300", $params)->fetchAll();
$stars = fn($n) => str_repeat('★', $n) . str_repeat('☆', 5 - $n);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="brand-font h3 mb-0">Customer Feedback</h1>
  <div class="text-end">
    <span class="fs-3 text-gold"><?= e($stats['avg_rating']) ?></span><span class="text-muted"> / 5</span>
    <div class="small text-muted"><?= (int)$stats['total'] ?> reviews</div>
  </div>
</div>

<div class="mb-3 d-flex flex-wrap gap-2">
  <a class="btn btn-sm <?= $r === 0 ? 'btn-burg' : 'btn-outline-light' ?>" href="feedback.php">All (<?= (int)$stats['total'] ?>)</a>
  <?php for ($i = 5; $i >= 1; $i--): ?>
    <a class="btn btn-sm <?= $r === $i ? 'btn-burg' : 'btn-outline-light' ?>" href="feedback.php?r=<?= $i ?>"><?= $i ?> ★ (<?= $byStar[$i] ?>)</a>
  <?php endfor; ?>
</div>

<div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
  <thead><tr><th>Date</th><th>Name</th><th>Table</th><th>Rating</th><th>Comment</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $f): ?>
    <tr>
      <td class="small text-nowrap"><?= e(date('d M Y h:i A', strtotime($f['created_at']))) ?></td>
      <td><?= $f['name'] !== '' ? e($f['name']) : '<span class="text-muted">Anonymous</span>' ?></td>
      <td><?= $f['table_no'] !== null ? e($f['table_no']) : '-' ?></td>
      <td class="text-gold text-nowrap"><?= $stars((int)$f['rating']) ?></td>
      <td style="min-width:220px"><?= $f['comment'] !== '' ? nl2br(e($f['comment'])) : '<span class="text-muted">-</span>' ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete this feedback?')"><?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= (int)$f['id'] ?>"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="text-muted">No feedback yet.</td></tr>'; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
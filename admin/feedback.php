<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);
ensure_feedback_table();

// Delete or Reply to feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['delete'])) {
        q('DELETE FROM feedback WHERE id = ?', [(int)$_POST['delete']]);
        flash('Feedback deleted.');
        redirect('admin/feedback.php');
    }
    if (isset($_POST['reply_id'], $_POST['reply_text'])) {
        $rText = trim($_POST['reply_text']);
        q('UPDATE feedback SET reply = ?, replied_at = NOW() WHERE id = ?', [$rText, (int)$_POST['reply_id']]);
        flash('Reply saved successfully.');
        redirect('admin/feedback.php');
    }
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
      <td style="min-width:260px">
        <div><?= $f['comment'] !== '' ? nl2br(e($f['comment'])) : '<span class="text-muted">-</span>' ?></div>
        <?php if (!empty($f['reply'])): ?>
          <div class="mt-2 p-2 rounded bg-dark border border-secondary-subtle small">
            <strong class="text-gold"><i class="bi bi-reply-fill"></i> Manager's Reply:</strong>
            <p class="mb-0 text-light"><?= nl2br(e($f['reply'])) ?></p>
            <div class="text-muted" style="font-size:0.75rem"><?= e(date('d M Y h:i A', strtotime($f['replied_at']))) ?></div>
          </div>
        <?php endif; ?>
      </td>
      <td class="text-nowrap text-end">
        <button type="button" class="btn btn-sm btn-outline-light me-1" data-bs-toggle="modal" data-bs-target="#replyModal<?= (int)$f['id'] ?>" title="Reply"><i class="bi bi-reply"></i> Reply</button>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this feedback?')"><?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= (int)$f['id'] ?>"><i class="bi bi-trash"></i></button>
        </form>

        <!-- Reply Modal -->
        <div class="modal fade text-start" id="replyModal<?= (int)$f['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="" class="modal-content card-dark">
              <?= csrf_field() ?>
              <input type="hidden" name="reply_id" value="<?= (int)$f['id'] ?>">
              <div class="modal-header border-0">
                <h5 class="modal-title brand-font">Reply to <?= e($f['name'] ?: 'Customer') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="text-muted small mb-2"><strong>Comment:</strong> "<?= e($f['comment']) ?>"</p>
                <div class="mb-3">
                  <label class="form-label small">Official Response / Internal Note:</label>
                  <textarea name="reply_text" class="form-control" rows="3" required placeholder="Type your response here..."><?= e($f['reply'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="modal-footer border-0">
                <button type="submit" class="btn btn-burg">Save Reply</button>
              </div>
            </form>
          </div>
        </div>
      </td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="text-muted">No feedback yet.</td></tr>'; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
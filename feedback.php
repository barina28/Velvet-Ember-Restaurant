<?php
require_once __DIR__ . '/includes/config.php';
ensure_feedback_table();

$t = cur_table();
$c = cur_customer();
$error = '';
$old = ['name' => $c['name'] ?? '', 'rating' => 0, 'comment' => ''];

// Works even if the mbstring extension is missing
$cut = function (string $s, int $n): string {
    return function_exists('mb_substr') ? mb_substr($s, 0, $n) : substr($s, 0, $n);
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $rating  = (int)($_POST['rating'] ?? 0);
    $name    = $cut(trim($_POST['name'] ?? ''), 80);
    $comment = $cut(trim($_POST['comment'] ?? ''), 1000);
    $old = ['name' => $name, 'rating' => $rating, 'comment' => $comment];

    if ($rating < 1 || $rating > 5) {
        $error = 'Please choose a star rating.';
    } elseif (time() - (int)($_SESSION['fb_last'] ?? 0) < 30) {
        $error = 'Please wait a few seconds before sending another feedback.';
    } else {
        q('INSERT INTO feedback (customer_id, table_no, name, rating, comment) VALUES (?, ?, ?, ?, ?)',
          [$c['id'] ?? null, $t['table_no'] ?? null, $name, $rating, $comment]);
        $_SESSION['fb_last'] = time();
        $_SESSION['fb_done'] = 1;
        redirect('feedback.php');
    }
}

$done = !empty($_SESSION['fb_done']);
unset($_SESSION['fb_done']);

$page = 'feedback';
$page_title = 'Feedback';
require_once __DIR__ . '/includes/header.php';
?>
<style>
.star-rate { display: inline-flex; flex-direction: row-reverse; gap: 8px; font-size: 2.2rem; }
.star-rate input { display: none; }
.star-rate label { color: #4a4a50; cursor: pointer; transition: color .15s; line-height: 1; }
.star-rate input:checked ~ label,
.star-rate label:hover,
.star-rate label:hover ~ label { color: #d4af6a; }
</style>

<section class="page-section">
  <div class="container" style="max-width:620px">
    <h1 class="brand-font section-title text-center">Your Feedback</h1>

    <?php if ($done): ?>
      <div class="card-dark p-4 p-md-5 text-center">
        <i class="bi bi-heart-fill text-gold" style="font-size:2.5rem"></i>
        <h2 class="brand-font h4 mt-3">Thank you!</h2>
        <p class="text-muted mb-4">Your feedback helps us serve you better.</p>
        <a href="<?= BASE ?>/index.php#menu" class="btn btn-burg">Back to menu</a>
      </div>
    <?php else: ?>
      <div class="card-dark p-4">
        <p class="text-muted text-center">How was your experience at <?= e(setting('restaurant_name')) ?>?</p>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="text-center mb-4">
            <div class="star-rate">
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="st<?= $i ?>" value="<?= $i ?>" <?= $old['rating'] === $i ? 'checked' : '' ?>>
                <label for="st<?= $i ?>" title="<?= $i ?> star"><i class="bi bi-star-fill"></i></label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Your name <span class="text-muted small">(optional)</span></label>
            <input class="form-control" name="name" maxlength="80" value="<?= e($old['name']) ?>">
          </div>
          <div class="mb-4">
            <label class="form-label">Comments <span class="text-muted small">(optional)</span></label>
            <textarea class="form-control" name="comment" rows="4" maxlength="1000" placeholder="Food, service, ambience... tell us anything"><?= e($old['comment']) ?></textarea>
          </div>
          <button class="btn btn-burg w-100 py-2">Send feedback</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
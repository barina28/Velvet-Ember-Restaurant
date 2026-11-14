<?php
$page = 'reserve';
$page_title = 'Table Reservation';
require_once __DIR__ . '/includes/header.php';
ensure_reservations_table();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $guests = max(1, min(20, (int)($_POST['guests'] ?? 2)));
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $requests = mb_substr(trim($_POST['requests'] ?? ''), 0, 400);

    if (mb_strlen($name) < 2 || strlen($phone) < 7 || !$date || !$time) {
        $error = 'Please fill in all required fields (Name, Phone, Date, Time).';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = 'Please select a future date for reservation.';
    } else {
        q("INSERT INTO reservations (name, phone, email, guests, reservation_date, reservation_time, special_requests)
           VALUES (?, ?, ?, ?, ?, ?, ?)", [$name, $phone, $email, $guests, $date, $time, $requests]);
        $success = true;
    }
}
?>

<section class="page-section py-5">
  <div class="container" style="max-width: 650px;">
    <div class="text-center mb-4">
      <p class="eyebrow mb-1">Book Your Experience</p>
      <h1 class="brand-font section-title">Table Reservation</h1>
      <p class="text-muted">Reserve your table in advance and enjoy a seamless dining experience.</p>
    </div>

    <?php if ($success): ?>
      <div class="card-dark p-4 text-center">
        <div class="text-success fs-1 mb-2"><i class="bi bi-check-circle-fill"></i></div>
        <h3 class="brand-font mb-2">Reservation Request Received!</h3>
        <p class="text-muted mb-4">Thank you, <strong><?= e($name) ?></strong>. We have received your booking request for <strong><?= $guests ?> guest(s)</strong> on <strong><?= e(date('D, d M Y', strtotime($date))) ?></strong> at <strong><?= e(date('h:i A', strtotime($time))) ?></strong>. Our team will contact you shortly to confirm.</p>
        <div class="d-flex justify-content-center gap-2">
          <a href="<?= BASE ?>/index.php#menu" class="btn btn-burg">Browse Menu</a>
          <a href="<?= BASE ?>/reserve.php" class="btn btn-outline-light">Book Another Table</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card-dark p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Ali Khan" required value="<?= e($_POST['name'] ?? ($__c['name'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" placeholder="03001234567" required value="<?= e($_POST['phone'] ?? ($__c['phone'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address (Optional)</label>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Number of Guests <span class="text-danger">*</span></label>
              <select name="guests" class="form-select" required>
                <?php for ($g = 1; $g <= 12; $g++): ?>
                  <option value="<?= $g ?>" <?= (int)($_POST['guests'] ?? 2) === $g ? 'selected' : '' ?>><?= $g ?> <?= $g === 1 ? 'Person' : 'Persons' ?></option>
                <?php endfor; ?>
                <option value="15">15+ (Large Gathering)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Reservation Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required value="<?= e($_POST['date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Preferred Time <span class="text-danger">*</span></label>
              <input type="time" name="time" class="form-control" required value="<?= e($_POST['time'] ?? '19:30') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Special Requests / Seating Preference (Optional)</label>
              <textarea name="requests" rows="3" class="form-control" placeholder="e.g. Birthday setup, rooftop/window corner, high chair needed..."><?= e($_POST['requests'] ?? '') ?></textarea>
            </div>
            <div class="col-12 mt-4">
              <button type="submit" class="btn btn-burg w-100 py-2 fs-5">Confirm Reservation</button>
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

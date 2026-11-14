<?php
$page = 'contact';
$page_title = 'About & Contact Us';
require_once __DIR__ . '/includes/header.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $sent = true;
}
?>

<section class="page-section py-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <p class="eyebrow mb-1">Our Story & Location</p>
      <h1 class="brand-font section-title">About Velvet Ember</h1>
      <p class="text-muted" style="max-width: 650px; margin: 0 auto;">
        Welcome to <strong><?= e($rname) ?></strong>, where culinary artistry meets unmatched hospitality. Every dish is crafted with fire, passion, and finest ingredients to offer you an unforgettable dining journey.
      </p>
    </div>

    <div class="row g-4 align-items-stretch">
      <!-- Info Column -->
      <div class="col-lg-5">
        <div class="card-dark p-4 h-100 d-flex flex-column justify-content-between">
          <div>
            <h3 class="brand-font h4 mb-4 text-gold"><i class="bi bi-clock-history"></i> Opening Hours</h3>
            <ul class="list-unstyled mb-4">
              <li class="d-flex justify-content-between py-2 border-bottom border-dark-subtle">
                <span class="text-muted">Monday – Thursday</span>
                <strong>12:00 PM – 12:00 AM</strong>
              </li>
              <li class="d-flex justify-content-between py-2 border-bottom border-dark-subtle">
                <span class="text-muted">Friday</span>
                <strong>03:00 PM – 01:00 AM</strong>
              </li>
              <li class="d-flex justify-content-between py-2 border-bottom border-dark-subtle">
                <span class="text-muted">Saturday – Sunday</span>
                <strong>12:00 PM – 01:30 AM</strong>
              </li>
            </ul>

            <h3 class="brand-font h4 mb-3 text-gold"><i class="bi bi-geo-alt"></i> Contact Details</h3>
            <p class="mb-2"><i class="bi bi-pin-map text-gold me-2"></i> <?= e(setting('address', 'Main Boulevard, Gulberg, Lahore, Pakistan')) ?></p>
            <p class="mb-2"><i class="bi bi-telephone text-gold me-2"></i> <a href="tel:<?= e(setting('phone', '+92 300 1234567')) ?>" class="text-light"><?= e(setting('phone', '+92 300 1234567')) ?></a></p>
            <p class="mb-3"><i class="bi bi-envelope text-gold me-2"></i> <a href="mailto:info@velvetember.com" class="text-light">info@velvetember.com</a></p>
          </div>

          <div class="pt-3 border-top border-dark-subtle">
            <h6 class="text-muted text-uppercase mb-2" style="letter-spacing:1px;font-size:0.75rem">Follow Us</h6>
            <div class="d-flex gap-3 fs-4">
              <a href="https://instagram.com" target="_blank" class="text-gold"><i class="bi bi-instagram"></i></a>
              <a href="https://facebook.com" target="_blank" class="text-gold"><i class="bi bi-facebook"></i></a>
              <a href="https://wa.me/923001234567" target="_blank" class="text-gold"><i class="bi bi-whatsapp"></i></a>
              <a href="https://tiktok.com" target="_blank" class="text-gold"><i class="bi bi-tiktok"></i></a>
            </div>
          </div>
        </div>
      </div>

      <!-- Map & Message Column -->
      <div class="col-lg-7">
        <div class="card-dark p-4 h-100">
          <h3 class="brand-font h4 mb-3 text-gold"><i class="bi bi-map"></i> Find Us On Map</h3>
          <div class="ratio ratio-16x9 rounded overflow-hidden mb-4 border border-dark-subtle">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d108843.5135245899!2d74.24056263593895!3d31.51470438186407!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39190483e58107d9%3A0xc23abe6ccc7e2462!2sLahore%2C%20Punjab%2C%20Pakistan!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s" 
              style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>

          <?php if ($sent): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> Thank you! Your message has been received. We will get back to you shortly.</div>
          <?php endif; ?>

          <h4 class="brand-font h5 mb-3">Send us a direct message</h4>
          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="row g-2">
              <div class="col-md-6">
                <input type="text" name="cname" class="form-control" placeholder="Your Name" required>
              </div>
              <div class="col-md-6">
                <input type="tel" name="cphone" class="form-control" placeholder="Phone or Email" required>
              </div>
              <div class="col-12">
                <textarea name="cmessage" rows="3" class="form-control" placeholder="How can we help you?" required></textarea>
              </div>
              <div class="col-12 mt-2">
                <button type="submit" class="btn btn-burg px-4">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

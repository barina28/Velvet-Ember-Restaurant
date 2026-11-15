</main>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000" id="toasts"></div>

<!-- ============ PROFESSIONAL FOOTER ============ -->
<footer class="site-footer pt-5 pb-4">
  <div class="container">
    <div class="row g-4 mb-5">
      <!-- Col 1: Brand & About -->
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img src="<?= e(logo_url()) ?>" width="44" height="44" alt="" style="object-fit:cover;border-radius:50%">
          <span class="brand-font fs-4 fw-bold text-light"><?= e($rname) ?></span>
        </div>
        <p class="text-muted small pe-lg-3">
          <?= e(setting('tagline', 'Where fire meets finesse')) ?>. Experience the authentic taste of premium culinary perfection, artisan grills, and genuine Pakistani hospitality.
        </p>
        <div class="d-flex gap-3 fs-5 mt-3 footer-socials">
          <a href="https://instagram.com" target="_blank" title="Instagram" class="text-gold"><i class="bi bi-instagram"></i></a>
          <a href="https://facebook.com" target="_blank" title="Facebook" class="text-gold"><i class="bi bi-facebook"></i></a>
          <a href="https://wa.me/923001234567" target="_blank" title="WhatsApp" class="text-gold"><i class="bi bi-whatsapp"></i></a>
          <a href="https://tiktok.com" target="_blank" title="TikTok" class="text-gold"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>

      <!-- Col 2: Quick Links -->
      <div class="col-lg-2 col-md-6 col-6">
        <h5 class="brand-font h6 text-gold text-uppercase mb-3" style="letter-spacing:1px">Explore</h5>
        <ul class="list-unstyled footer-links small">
          <li class="mb-2"><a href="<?= BASE ?>/index.php#menu">Our Menu</a></li>
          <li class="mb-2"><a href="<?= BASE ?>/reserve.php">Book a Table</a></li>
          <li class="mb-2"><a href="<?= BASE ?>/about.php">About Us</a></li>
          <li class="mb-2"><a href="<?= BASE ?>/index.php#deals">Special Deals</a></li>
          <li class="mb-2"><a href="<?= BASE ?>/feedback.php">Customer Reviews</a></li>
        </ul>
      </div>

      <!-- Col 3: Dining Hours -->
      <div class="col-lg-3 col-md-6 col-6">
        <h5 class="brand-font h6 text-gold text-uppercase mb-3" style="letter-spacing:1px">Opening Hours</h5>
        <ul class="list-unstyled text-muted small mb-0">
          <li class="mb-2 d-flex justify-content-between">
            <span>Mon – Thu:</span>
            <strong class="text-light">12:00 PM – 12:00 AM</strong>
          </li>
          <li class="mb-2 d-flex justify-content-between">
            <span>Friday:</span>
            <strong class="text-light">03:00 PM – 01:00 AM</strong>
          </li>
          <li class="mb-2 d-flex justify-content-between">
            <span>Sat – Sun:</span>
            <strong class="text-light">12:00 PM – 01:30 AM</strong>
          </li>
          <li class="mt-3 text-gold">
            <i class="bi bi-wifi"></i> Free Guest WiFi Available
          </li>
        </ul>
      </div>

      <!-- Col 4: Contact & Location -->
      <div class="col-lg-3 col-md-6">
        <h5 class="brand-font h6 text-gold text-uppercase mb-3" style="letter-spacing:1px">Get In Touch</h5>
        <ul class="list-unstyled text-muted small mb-3">
          <li class="mb-2 d-flex align-items-start gap-2">
            <i class="bi bi-geo-alt-fill text-gold mt-1"></i>
            <span><?= e(setting('address', 'Main Boulevard, Gulberg, Lahore')) ?></span>
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-telephone-fill text-gold"></i>
            <a href="tel:<?= e(setting('phone')) ?>" class="text-light text-decoration-none"><?= e(setting('phone', '+92 300 1234567')) ?></a>
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-envelope-fill text-gold"></i>
            <span class="text-light">info@velvetember.com</span>
          </li>
        </ul>
        <a href="<?= BASE ?>/reserve.php" class="btn btn-sm btn-burg w-100 py-2"><i class="bi bi-calendar2-check me-1"></i> Reserve Table</a>
      </div>
    </div>

    <!-- Bottom Copyright & Badges -->
    <div class="pt-4 border-top border-dark-subtle d-flex flex-wrap justify-content-between align-items-center gap-2 small text-muted">
      <div>
        &copy; <?= date('Y') ?> <strong><?= e($rname) ?></strong>. All rights reserved.
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE ?>/wifi.php" class="text-muted text-decoration-none hover-gold"><i class="bi bi-wifi"></i> WiFi Details</a>
        <span>&middot;</span>
        <a href="<?= BASE ?>/login.php" class="text-muted text-decoration-none hover-gold"><i class="bi bi-shield-lock"></i> Staff Portal</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $extra_scripts ?? '' ?>
<script src="<?= BASE ?>/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
</body>
</html>

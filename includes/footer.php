</main>

<!-- Floating "Call waiter" button -->
<button class="fab-waiter" data-bs-toggle="modal" data-bs-target="#waiterModal" title="Call waiter">
  <i class="bi bi-bell-fill"></i><span class="d-none d-sm-inline ms-2">Call waiter</span>
</button>

<div class="modal fade" id="waiterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card-dark">
      <div class="modal-header border-0"><h5 class="modal-title brand-font">Need a hand?</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="text-muted small">Your order always goes through the website. Use this only if something is wrong or you need the waiter at your table.</p>
        <div class="d-grid gap-2" id="waiterReasons">
          <?php foreach (['Need assistance', 'Order issue', 'Water please', 'Bill / payment help', 'Cleaning needed', 'Other'] as $r): ?>
            <button class="btn btn-outline-light text-start" data-reason="<?= e($r) ?>"><?= e($r) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000" id="toasts"></div>

<footer class="site-footer text-center py-4">
  <div class="container small text-muted">
    <?= e(setting('restaurant_name')) ?> &middot; <?= e(setting('address')) ?> &middot; <?= e(setting('phone')) ?>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $extra_scripts ?? '' ?>
<script src="<?= BASE ?>/assets/js/main.js"></script>
</body>
</html>

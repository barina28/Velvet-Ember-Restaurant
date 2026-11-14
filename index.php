<?php
$page = 'home';
require_once __DIR__ . '/includes/header.php';

$cats = q('SELECT * FROM categories ORDER BY sort_order, id')->fetchAll();
$items = q('SELECT * FROM menu_items WHERE available = 1 ORDER BY id')->fetchAll();
$byCat = [];
foreach ($items as $i) $byCat[$i['category_id']][] = $i;
$ring = array_slice($items, 0, 6);
$img = fn($f) => BASE . '/assets/img/picture/' . rawurlencode($f);
?>

<!-- ============ HERO (3D) ============ -->
<section class="hero" style="background-image:url('<?= $img('hero.jpg') ?>')">
  <div class="container">
    <div class="row align-items-center min-vh-100 pt-5">
      <div class="col-lg-6 text-center text-lg-start hero-text">
        <p class="eyebrow">Welcome to</p>
        <h1 class="brand-font display-3 fw-bold"><?= e(setting('restaurant_name')) ?></h1>
        <p class="lead text-muted mb-4"><?= e(setting('tagline')) ?></p>
        <?php if ($__t): ?>
          <p class="mb-4"><span class="badge badge-table fs-6"><i class="bi bi-geo-alt-fill"></i> You are at Table <?= e($__t['table_no']) ?></span></p>
          <a href="#menu" class="btn btn-burg btn-lg px-4">Order now <i class="bi bi-arrow-down"></i></a>
        <?php else: ?>
          <a href="<?= BASE ?>/scan_qr.php" class="scan-note text-decoration-none" style="color:inherit">
            <i class="bi bi-qr-code-scan fs-3"></i>
            <div><strong>Scan the QR code on your table</strong><br><span class="small text-muted">Tap here to open the scanner. You can browse the menu now, but ordering needs your table's QR code.</span></div>
            <i class="bi bi-chevron-right ms-2"></i>
          </a>
          <a href="#menu" class="btn btn-burg btn-lg px-4 mt-3">Browse menu</a>
        <?php endif; ?>
      </div>
      <div class="col-lg-6">
        <div class="stage" id="stage">
          <div class="ring">
            <?php foreach ($ring as $n => $r): ?>
              <div class="face" style="--i:<?= $n ?>"><img src="<?= $img($r['image']) ?>" alt="<?= e($r['name']) ?>"><span><?= e($r['name']) ?></span></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ MENU ============ -->
<section id="menu" class="py-5">
  <div class="container">
    <h2 class="brand-font text-center section-title">Our Menu</h2>

    <!-- Menu Live Search -->
    <div class="row justify-content-center mb-4">
      <div class="col-md-6 col-lg-5">
        <div class="input-group search-bar-box">
          <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
          <input type="text" id="menuSearch" class="form-control border-start-0 ps-0" placeholder="Search delicious dishes..." autocomplete="off">
          <button class="btn btn-outline-secondary border-start-0 text-muted" type="button" id="clearSearch" style="display:none"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
    </div>

    <div class="cat-tabs" id="catTabs">
      <button class="active" data-cat="all">All</button>
      <?php foreach ($cats as $c): if (empty($byCat[$c['id']])) continue; ?>
        <button data-cat="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-4 mt-1" id="menuGrid">
      <?php foreach ($items as $i): ?>
        <div class="col-6 col-md-4 col-lg-3 menu-col" data-cat="<?= (int)$i['category_id'] ?>">
          <div class="dish tilt">
            <img src="<?= $img($i['image']) ?>" alt="<?= e($i['name']) ?>" loading="lazy">
            <div class="dish-body">
              <h3><?= e($i['name']) ?></h3>
              <p><?= e($i['description']) ?></p>
              <div class="d-flex justify-content-between align-items-center mt-auto">
                <span class="price"><?= money($i['price']) ?></span>
                <button class="btn btn-sm btn-burg add-btn" data-id="<?= (int)$i['id'] ?>" data-name="<?= e($i['name']) ?>" data-price="<?= (float)$i['price'] ?>"><i class="bi bi-plus-lg"></i> Add</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ SPECIAL OFFERS & DEALS ============ -->
<section id="deals" class="py-5" style="background: linear-gradient(180deg, rgba(20,20,22,0.85) 0%, rgba(10,10,11,1) 100%);">
  <div class="container">
    <div class="text-center mb-4">
      <p class="eyebrow mb-1">Chef's Specials & Discounts</p>
      <h2 class="brand-font section-title">Exclusive Deals & Offers</h2>
      <p class="text-muted">Handpicked combos and limited-time savings crafted for food lovers.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card-dark p-4 h-100 border border-gold-subtle position-relative overflow-hidden">
          <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill">Save 25%</span>
          <h3 class="brand-font h4 mb-2 text-gold">Royal Feast Combo</h3>
          <p class="text-muted small mb-3">1 Chicken Biryani + 1 Chicken Karahi + 2 Garlic Naan + 2 Soft Drinks.</p>
          <div class="d-flex align-items-baseline gap-2 mb-3">
            <span class="fs-4 fw-bold text-light"><?= money(2450) ?></span>
            <span class="text-muted text-decoration-line-through small"><?= money(3200) ?></span>
          </div>
          <a href="#menu" class="btn btn-burg w-100"><i class="bi bi-cart-plus me-1"></i> View In Menu</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card-dark p-4 h-100 border border-gold-subtle position-relative overflow-hidden">
          <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fw-bold">Popular</span>
          <h3 class="brand-font h4 mb-2 text-gold">BBQ Platter Delight</h3>
          <p class="text-muted small mb-3">Seekh Kabab (4 pcs) + Malai Boti + Chicken Tikka + Raita & 3 Tandoori Rotis.</p>
          <div class="d-flex align-items-baseline gap-2 mb-3">
            <span class="fs-4 fw-bold text-light"><?= money(2199) ?></span>
            <span class="text-muted text-decoration-line-through small"><?= money(2750) ?></span>
          </div>
          <a href="#menu" class="btn btn-burg w-100"><i class="bi bi-cart-plus me-1"></i> View In Menu</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card-dark p-4 h-100 border border-gold-subtle position-relative overflow-hidden">
          <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill">Sweet Deal</span>
          <h3 class="brand-font h4 mb-2 text-gold">Dessert & Drink Pair</h3>
          <p class="text-muted small mb-3">Gulab Jamun (warm) or Shahi Kheer + 2 Mint Margaritas at special price.</p>
          <div class="d-flex align-items-baseline gap-2 mb-3">
            <span class="fs-4 fw-bold text-light"><?= money(850) ?></span>
            <span class="text-muted text-decoration-line-through small"><?= money(1100) ?></span>
          </div>
          <a href="#menu" class="btn btn-burg w-100"><i class="bi bi-cart-plus me-1"></i> View In Menu</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Cart button -->
<button class="cart-fab" id="cartBtn" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer">
  <i class="bi bi-bag-fill"></i> <span id="cartCount">0</span> &middot; <span id="cartTotalBtn"><?= CURRENCY ?> 0</span>
</button>

<!-- Cart drawer -->
<div class="offcanvas offcanvas-end card-dark" tabindex="-1" id="cartDrawer">
  <div class="offcanvas-header">
    <h5 class="brand-font mb-0">Your order</h5>
    <button class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div id="cartLines" class="flex-grow-1"></div>
    <textarea id="orderNotes" class="form-control my-3" rows="2" maxlength="250" placeholder="Notes for the kitchen (optional) - e.g. less spicy"></textarea>
    <div class="totals">
      <div class="d-flex justify-content-between"><span>Subtotal</span><span id="cSub"></span></div>
      <div class="d-flex justify-content-between"><span>Tax (<?= e(setting('tax_percent')) ?>%)</span><span id="cTax"></span></div>
      <div class="d-flex justify-content-between fw-bold fs-5 mt-1"><span>Total</span><span id="cTotal"></span></div>
    </div>
    <button class="btn btn-burg btn-lg w-100 mt-3" id="placeOrder">Place order</button>
    <a href="<?= BASE ?>/my_orders.php" class="btn btn-link text-muted w-100 mt-1">View my orders</a>
  </div>
</div>

<!-- Identify modal -->
<div class="modal fade" id="identModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content card-dark" id="identForm">
      <div class="modal-header border-0"><h5 class="modal-title brand-font">Before we start</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="text-muted small">We save your details so we can serve you better and so you can order again easily.</p>
        <div class="mb-3"><label class="form-label">Your name</label><input class="form-control" name="name" required maxlength="80"></div>
        <div class="mb-2"><label class="form-label">Mobile number</label><input class="form-control" name="phone" inputmode="tel" placeholder="03001234567" required></div>
        <div class="text-danger small" id="identErr"></div>
      </div>
      <div class="modal-footer border-0"><button class="btn btn-burg w-100">Continue</button></div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

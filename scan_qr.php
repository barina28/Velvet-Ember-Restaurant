<?php
$page = 'scanqr';
$page_title = 'Scan table QR';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-section">
  <div class="container" style="max-width:520px">
    <h1 class="brand-font section-title text-center">Scan Table QR</h1>

    <?php if ($__t): ?>
      <div class="alert alert-success text-center">You are already at <strong>Table <?= e($__t['table_no']) ?></strong>. Scan again only if you moved to another table.</div>
    <?php endif; ?>

    <div class="card-dark p-3 text-center">
      <p class="text-muted small mb-3">Point your camera at the QR code placed on your table.</p>

      <div id="camBox" class="position-relative mb-3" style="border-radius:14px;overflow:hidden;background:#000;display:none">
        <video id="cam" playsinline muted style="width:100%;display:block"></video>
        <div style="position:absolute;inset:18%;border:3px solid #d4af6a;border-radius:16px;box-shadow:0 0 0 999px rgba(0,0,0,.35);pointer-events:none"></div>
      </div>

      <div id="msg" class="small mb-3 text-muted">Starting camera...</div>

      <button type="button" id="startBtn" class="btn btn-burg mb-2 w-100" style="display:none"><i class="bi bi-camera-fill"></i> Open camera</button>

      <label class="btn btn-outline-light w-100 mb-1">
        <i class="bi bi-image"></i> Or take / choose a photo of the QR
        <input type="file" id="photo" accept="image/*" capture="environment" hidden>
      </label>
    </div>

    <p class="text-center small text-muted mt-3">Can't scan? Ask our staff to help you.</p>
    <p class="text-center"><a href="<?= BASE ?>/index.php#menu">&larr; Back to menu</a></p>
  </div>
</section>

<canvas id="cv" hidden></canvas>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
<script>
(function () {
  var BASE = <?= json_encode(BASE) ?>;
  var video = document.getElementById('cam'), box = document.getElementById('camBox');
  var msg = document.getElementById('msg'), startBtn = document.getElementById('startBtn');
  var cv = document.getElementById('cv'), ctx = cv.getContext('2d', { willReadFrequently: true });
  var stream = null, running = false, found = false;

  function say(t, cls) { msg.className = 'small mb-3 ' + (cls || 'text-muted'); msg.textContent = t; }

  // Only accept this restaurant's table QR (contains ?t=<32 hex chars>)
  function handle(text) {
    var m = /[?&]t=([a-f0-9]{32})(?:&|$)/i.exec(text || '');
    if (!m) { say('This is not a table QR code. Please scan the QR on your table.', 'text-warning'); return false; }
    found = true; stop();
    say('Table found! Opening menu...', 'text-success');
    location.href = BASE + '/scan.php?t=' + m[1].toLowerCase();
    return true;
  }

  function stop() {
    running = false;
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
  }

  function tick() {
    if (!running || found) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth) {
      var w = Math.min(640, video.videoWidth), h = Math.round(video.videoHeight * w / video.videoWidth);
      cv.width = w; cv.height = h;
      ctx.drawImage(video, 0, 0, w, h);
      var res = jsQR(ctx.getImageData(0, 0, w, h).data, w, h, { inversionAttempts: 'dontInvert' });
      if (res && res.data && handle(res.data)) return;
    }
    requestAnimationFrame(tick);
  }

  function startCamera() {
    startBtn.style.display = 'none';
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      say('Live camera needs a secure (https) website. Please use the "take / choose a photo" button below.', 'text-warning');
      return;
    }
    say('Starting camera...');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false })
      .then(function (s) {
        stream = s; video.srcObject = s; box.style.display = 'block';
        return video.play();
      })
      .then(function () {
        running = true; say('Point the camera at the QR code...'); requestAnimationFrame(tick);
      })
      .catch(function () {
        box.style.display = 'none'; startBtn.style.display = 'block';
        say('Camera could not start. Allow camera permission, or use the photo button below.', 'text-warning');
      });
  }

  // Photo fallback (works even without https)
  document.getElementById('photo').addEventListener('change', function (e) {
    var f = e.target.files && e.target.files[0]; if (!f) return;
    say('Reading QR from photo...');
    var img = new Image(), url = URL.createObjectURL(f);
    img.onload = function () {
      var scale = Math.min(1, 1200 / Math.max(img.width, img.height));
      cv.width = Math.round(img.width * scale); cv.height = Math.round(img.height * scale);
      ctx.drawImage(img, 0, 0, cv.width, cv.height);
      URL.revokeObjectURL(url);
      var res = jsQR(ctx.getImageData(0, 0, cv.width, cv.height).data, cv.width, cv.height, { inversionAttempts: 'attemptBoth' });
      if (!res) say('No QR code found in that photo. Try again, closer and steady.', 'text-warning');
      else handle(res.data);
    };
    img.onerror = function () { say('Could not read that image.', 'text-warning'); };
    img.src = url;
    e.target.value = '';
  });

  startBtn.addEventListener('click', startCamera);
  window.addEventListener('pagehide', stop);
  startCamera();
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
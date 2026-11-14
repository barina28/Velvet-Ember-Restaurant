/* =========================================================
   Restaurant system - front-end logic
   Sections: helpers, 3D effects, customer (cart/identify/waiter/orders/pay/wifi),
             staff board, admin QR tables
   ========================================================= */
(function () {
  'use strict';

  const APP = window.APP || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const money = n => (APP.currency || 'Rs.') + ' ' + Math.round(n).toLocaleString('en-US');
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const page = document.body.dataset.page;

  async function api(path, data, method) {
    const opt = { method: method || (data ? 'POST' : 'GET'), headers: { 'X-CSRF': APP.csrf || '' } };
    if (data) { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(data); }
    try {
      const r = await fetch((APP.base || '') + '/' + path, opt);
      return await r.json();
    } catch (e) { return { ok: false, error: 'Network problem. Please try again.' }; }
  }

  function toast(msg, type) {
    const box = $('#toasts'); if (!box || !window.bootstrap) return alert(msg);
    const el = document.createElement('div');
    el.className = 'toast align-items-center border-0 text-bg-' + (type || 'dark');
    el.innerHTML = '<div class="d-flex"><div class="toast-body">' + esc(msg) + '</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    box.appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 3500 }); t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  }
  window.toast = toast;

  /* ---------- 3D effects ---------- */
  function initTilt() {
    $$('.tilt').forEach(card => {
      card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        card.style.setProperty('--ry', (x * 14) + 'deg');
        card.style.setProperty('--rx', (-y * 14) + 'deg');
      });
      card.addEventListener('mouseleave', () => { card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg'); });
    });
    const stage = $('#stage');
    if (stage) {
      document.addEventListener('mousemove', e => {
        const x = e.clientX / innerWidth - .5, y = e.clientY / innerHeight - .5;
        stage.style.transform = 'rotateX(' + (-y * 12) + 'deg) rotateY(' + (x * 18) + 'deg)';
      });
    }
  }

  /* ---------- Waiter call (all customer pages) ---------- */
  function initWaiter() {
    $$('#waiterReasons [data-reason]').forEach(b => b.addEventListener('click', async () => {
      const res = await api('api/call_waiter.php', { reason: b.dataset.reason });
      const m = bootstrap.Modal.getInstance($('#waiterModal')); if (m) m.hide();
      toast(res.ok ? res.message : (res.error || 'Could not call the waiter'), res.ok ? 'success' : 'danger');
    }));
  }

  /* ---------- Cart ---------- */
  const CART_KEY = 'rest_cart_v1';
  const cart = {
    get() { try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; } catch (e) { return {}; } },
    set(c) { localStorage.setItem(CART_KEY, JSON.stringify(c)); },
    add(id, name, price, qty) {
      const c = this.get(); id = String(id);
      c[id] = c[id] || { id: +id, name, price, qty: 0 };
      c[id].qty = Math.min(20, c[id].qty + (qty || 1)); this.set(c);
    },
    change(id, d) { const c = this.get(); if (!c[id]) return; c[id].qty += d; if (c[id].qty <= 0) delete c[id]; this.set(c); },
    clear() { localStorage.removeItem(CART_KEY); }
  };

  function initMenu() {
    let currentCat = 'all';
    let searchQuery = '';

    function filterMenu() {
      const q = searchQuery.toLowerCase().trim();
      let visibleCount = 0;
      $$('.menu-col').forEach(c => {
        const catMatch = (currentCat === 'all' || c.dataset.cat === currentCat);
        const name = (c.querySelector('h3')?.textContent || '').toLowerCase();
        const desc = (c.querySelector('p')?.textContent || '').toLowerCase();
        const searchMatch = !q || name.includes(q) || desc.includes(q);
        const show = catMatch && searchMatch;
        c.style.display = show ? '' : 'none';
        if (show) visibleCount++;
      });

      let noRes = $('#noMenuResults');
      if (visibleCount === 0) {
        if (!noRes) {
          noRes = document.createElement('div');
          noRes.id = 'noMenuResults';
          noRes.className = 'col-12 text-center py-5 text-muted';
          noRes.innerHTML = '<i class="bi bi-search fs-1 d-block mb-2"></i>No dishes found matching your search.';
          $('#menuGrid').appendChild(noRes);
        }
        noRes.style.display = 'block';
      } else if (noRes) {
        noRes.style.display = 'none';
      }
    }

    // category filter
    $$('#catTabs button').forEach(b => b.addEventListener('click', () => {
      $$('#catTabs button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      currentCat = b.dataset.cat;
      filterMenu();
    }));

    // live search
    const sInput = $('#menuSearch');
    const sClear = $('#clearSearch');
    if (sInput) {
      sInput.addEventListener('input', () => {
        searchQuery = sInput.value;
        if (sClear) sClear.style.display = searchQuery ? 'block' : 'none';
        filterMenu();
      });
      if (sClear) {
        sClear.addEventListener('click', () => {
          sInput.value = '';
          searchQuery = '';
          sClear.style.display = 'none';
          filterMenu();
          sInput.focus();
        });
      }
    }

    // price lookup so "order again" can show prices
    const prices = {}; $$('.add-btn').forEach(b => prices[b.dataset.id] = { name: b.dataset.name, price: +b.dataset.price });

    function render() {
      const c = cart.get(), ids = Object.keys(c);
      let sub = 0, count = 0, html = '';
      ids.forEach(id => {
        const it = c[id]; if (prices[id]) { it.price = prices[id].price; it.name = prices[id].name; }
        sub += it.price * it.qty; count += it.qty;
        html += '<div class="cart-line"><div><div>' + esc(it.name) + '</div><small class="text-muted">' + money(it.price) + '</small></div>' +
          '<div class="qty"><button data-d="-1" data-id="' + id + '">&minus;</button><span>' + it.qty + '</span><button data-d="1" data-id="' + id + '">+</button></div></div>';
      });
      $('#cartLines').innerHTML = html || '<p class="text-muted text-center mt-4">Your cart is empty.</p>';
      const tax = Math.round(sub * (APP.tax || 0) / 100);
      $('#cSub').textContent = money(sub); $('#cTax').textContent = money(tax); $('#cTotal').textContent = money(sub + tax);
      $('#cartCount').textContent = count; $('#cartTotalBtn').textContent = money(sub + tax);
      $('#cartBtn').classList.toggle('show', count > 0);
    }

    document.addEventListener('click', e => {
      const add = e.target.closest('.add-btn');
      if (add) { cart.add(add.dataset.id, add.dataset.name, +add.dataset.price); render(); toast(add.dataset.name + ' added', 'success'); }
      const q = e.target.closest('.qty button');
      if (q) { cart.change(q.dataset.id, +q.dataset.d); render(); }
    });

    // order type toggle
    const typeRadios = $$('input[name="orderType"]');
    const addrBox = $('#deliveryAddressBox');
    typeRadios.forEach(r => r.addEventListener('change', () => {
      if (addrBox) addrBox.style.display = (r.value === 'delivery') ? 'block' : 'none';
    }));

    // Payment method toggle in checkout modal
    const payRadios = $$('input[name="chkPaymentMethod"]');
    const bankBox = $('#bankSelectionBox');
    payRadios.forEach(r => r.addEventListener('change', () => {
      if (bankBox) bankBox.style.display = (r.value === 'bank') ? 'block' : 'none';
    }));

    // Open Checkout Confirmation Chart / Modal
    function openCheckoutModal() {
      const c = cart.get(), items = Object.values(c);
      if (!items.length) return toast('Your cart is empty', 'warning');

      // Populate summary totals
      let sub = 0;
      items.forEach(it => sub += (it.price * it.qty));
      const tax = Math.round(sub * (APP.tax || 0) / 100);
      const total = sub + tax;

      $('#chkSubtotal').textContent = money(sub);
      $('#chkTax').textContent = money(tax);
      $('#chkTotalPayable').textContent = money(total);

      const oType = $('input[name="orderType"]:checked')?.value || 'delivery';
      const typeLabel = oType === 'dine_in' ? 'Dine-In' : (oType === 'takeaway' ? 'Takeaway (Self Pick)' : 'Home Delivery');
      $('#chkOrderTypeBadge').textContent = typeLabel;

      const chkAddrField = $('#chkAddressField');
      if (chkAddrField) {
        chkAddrField.style.display = (oType === 'delivery') ? 'block' : 'none';
        if (oType === 'delivery') {
          $('#chkAddress').value = $('#orderAddress')?.value?.trim() || $('#chkAddress').value || '';
        }
      }

      // Hide cart drawer and show checkout modal
      const off = bootstrap.Offcanvas.getInstance($('#cartDrawer'));
      if (off) off.hide();

      new bootstrap.Modal($('#checkoutModal')).show();
    }

    $('#placeOrder').addEventListener('click', () => {
      openCheckoutModal();
    });

    // Handle final order submission with Bank / COD confirmation
    $('#checkoutForm').addEventListener('submit', async e => {
      e.preventDefault();
      const errBox = $('#chkError');
      errBox.textContent = '';

      const name = $('#chkName').value.trim();
      const phone = $('#chkPhone').value.trim();
      const email = $('#chkEmail')?.value?.trim() || '';
      const oType = $('input[name="orderType"]:checked')?.value || 'delivery';
      const address = $('#chkAddress')?.value?.trim() || '';
      const payMethod = $('input[name="chkPaymentMethod"]:checked')?.value || 'cod';
      const bankName = (payMethod === 'bank') ? $('#chkBankSelect')?.value : '';

      if (name.length < 2) { errBox.textContent = 'Please enter your full name.'; return; }
      if (phone.length < 7) { errBox.textContent = 'Please enter a valid phone number.'; return; }
      if (oType === 'delivery' && address.length < 5) {
        errBox.textContent = 'Please provide complete delivery address.';
        return;
      }

      const btn = $('#btnConfirmFinalOrder');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing Order...';

      // 1. Identify customer
      const idRes = await api('api/identify.php', { name, phone });
      if (!idRes.ok) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm & Place Order';
        errBox.textContent = idRes.error || 'Failed to verify customer details.';
        return;
      }
      APP.customer = idRes.name;

      // 2. Place order with bank/cod
      const c = cart.get(), items = Object.values(c).map(i => ({ id: i.id, qty: i.qty }));
      const res = await api('api/place_order.php', {
        items,
        order_type: oType,
        address,
        pay_method: payMethod,
        bank_name: bankName,
        notes: $('#orderNotes')?.value || ''
      });

      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm & Place Order';

      if (res.ok) {
        cart.clear();
        bootstrap.Modal.getInstance($('#checkoutModal')).hide();
        location.href = APP.base + '/my_orders.php?placed=1';
      } else {
        errBox.textContent = res.error || 'Could not place order, please try again.';
      }
    });

    render();
    if (location.hash === '#cart' || sessionStorage.getItem('openCart')) {
      sessionStorage.removeItem('openCart');
      setTimeout(() => new bootstrap.Offcanvas($('#cartDrawer')).show(), 300);
    }
  }

  /* ---------- My orders: live status + order again ---------- */
  function initOrders() {
    $$('.reorder-btn').forEach(b => b.addEventListener('click', () => {
      let items = []; try { items = JSON.parse(b.dataset.items); } catch (e) { }
      items.forEach(i => { if (i.id) cart.add(i.id, i.name, 0, i.qty); });
      sessionStorage.setItem('openCart', '1');
      location.href = APP.base + '/index.php#menu';
    }));
    const labels = { pending: 'Received', preparing: 'Being prepared', ready: 'Ready - on its way', served: 'Served', cancelled: 'Cancelled' };
    const payLabels = { paid: 'Paid', cash_pending: 'Cash - waiter coming', unpaid: 'Unpaid' };
    async function poll() {
      const res = await api('api/order_status.php'); if (!res.ok) return;
      res.orders.forEach(o => {
        const card = $('.order-card[data-id="' + o.id + '"]'); if (!card) return;
        const s = $('[data-role="status"]', card), p = $('[data-role="pay"]', card);
        s.className = 'status status-' + o.status; s.textContent = labels[o.status];
        p.className = 'pay-chip pay-' + o.payment_status; p.textContent = payLabels[o.payment_status];
      });
    }
    setInterval(poll, 6000);
  }

  /* ---------- Pay page ---------- */
  function initPay() {
    const radios = $$('input[name="method"]');
    function sync() {
      const m = $('input[name="method"]:checked').value;
      $('#walletBox').classList.toggle('d-none', !(m === 'jazzcash' || m === 'easypaisa'));
      $('#cardBox').classList.toggle('d-none', m !== 'card');
      $('#cashBox').classList.toggle('d-none', m !== 'cash');
      $('#payBtn').textContent = m === 'cash' ? 'Request cash payment' : 'Pay now';
    }
    radios.forEach(r => r.addEventListener('change', sync)); sync();
    $('#payBtn').addEventListener('click', async () => {
      const btn = $('#payBtn'), m = $('input[name="method"]:checked').value;
      $('#payErr').textContent = ''; btn.disabled = true;
      const res = await api('api/pay.php', { method: m, wallet: $('#wallet').value });
      btn.disabled = false;
      if (res.ok) location.href = APP.base + '/bill.php?receipt=' + res.payment_id;
      else $('#payErr').textContent = res.error || 'Payment failed';
    });
  }

  /* ---------- QR rendering (wifi page + admin tables) ---------- */
  function initQR() {
    if (!window.QRCode) return;
    $$('[data-qr]').forEach(el => {
      el.innerHTML = '';
      new QRCode(el, { text: el.dataset.qr, width: el.dataset.size ? +el.dataset.size : 200, height: el.dataset.size ? +el.dataset.size : 200, correctLevel: QRCode.CorrectLevel.M });
    });
    const cp = $('#copyWifi');
    if (cp) cp.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(cp.dataset.copy); toast('Password copied', 'success'); }
      catch (e) { const r = document.createRange(); r.selectNode($('#wifiPass')); getSelection().removeAllRanges(); getSelection().addRange(r); toast('Press copy on your keyboard', 'secondary'); }
    });
  }

  /* ---------- Staff board ---------- */
  function initStaff() {
    const nextLabel = { pending: 'Start cooking', preparing: 'Mark ready', ready: 'Mark served' };
    const nextStatus = { pending: 'preparing', preparing: 'ready', ready: 'served' };
    let known = new Set(), first = true;

    async function act(action, id, extra) {
      const res = await api('api/staff_action.php', Object.assign({ action, id }, extra || {}));
      if (!res.ok) toast(res.error || 'Action failed', 'danger');
      load();
    }
    document.addEventListener('click', e => {
      const b = e.target.closest('[data-act]'); if (!b) return;
      if (b.dataset.act === 'cancel' && !confirm('Cancel this order?')) return;
      act(b.dataset.act, +b.dataset.id, b.dataset.status ? { status: b.dataset.status } : null);
    });

    function ticket(o) {
      const lines = o.items.map(i => '<li>' + i.qty + ' &times; ' + esc(i.name) + '</li>').join('');
      const mins = Math.max(0, Math.round((Date.now() - new Date(o.created_at.replace(' ', 'T')).getTime()) / 60000));
      const isNew = !known.has('o' + o.id) && !first;
      return '<div class="ticket ' + (o.status === 'ready' ? 'ready' : '') + (isNew ? ' new-flash' : '') + '">' +
        '<div class="d-flex justify-content-between"><span class="tbl">Table ' + esc(o.table_no) + ' &middot; #' + o.id + '</span><small class="text-muted">' + mins + ' min</small></div>' +
        '<small class="text-muted">' + esc(o.customer) + '</small><ul>' + lines + '</ul>' +
        (o.notes ? '<div class="small text-warning mb-2">Note: ' + esc(o.notes) + '</div>' : '') +
        '<div class="d-flex gap-2"><button class="btn btn-sm btn-burg flex-grow-1" data-act="status" data-id="' + o.id + '" data-status="' + nextStatus[o.status] + '">' + nextLabel[o.status] + '</button>' +
        (o.status === 'pending' ? '<button class="btn btn-sm btn-outline-danger" data-act="cancel" data-id="' + o.id + '">Cancel</button>' : '') + '</div></div>';
    }

    async function load() {
      const d = await api('api/staff_data.php'); if (!d.ok) return;
      const col = (sel, arr, fn, empty) => { $(sel).innerHTML = arr.length ? arr.map(fn).join('') : '<p class="text-muted small">' + empty + '</p>'; };
      col('#colNew', d.orders.filter(o => o.status === 'pending'), ticket, 'No new orders');
      col('#colPrep', d.orders.filter(o => o.status === 'preparing'), ticket, 'Nothing cooking');
      col('#colReady', d.orders.filter(o => o.status === 'ready'), ticket, 'Nothing waiting to be served');
      col('#colCalls', d.calls, c => '<div class="ticket call' + (!known.has('c' + c.id) && !first ? ' new-flash' : '') + '"><div class="tbl">Table ' + esc(c.table_no) + '</div><div>' + esc(c.reason) + '</div><small class="text-muted">' + esc(c.customer || '') + '</small>' +
        '<button class="btn btn-sm btn-outline-light w-100 mt-2" data-act="call_done" data-id="' + c.id + '">Done</button></div>', 'No waiter calls');
      col('#colCash', d.cash, p => '<div class="ticket call"><div class="tbl">Table ' + esc(p.table_no) + ' &middot; ' + money(p.amount) + '</div><small class="text-muted">' + esc(p.customer) + ' &middot; cash</small>' +
        (d.can_cash ? '<button class="btn btn-sm btn-burg w-100 mt-2" data-act="confirm_cash" data-id="' + p.id + '">Cash received</button>' : '') + '</div>', 'No cash pending');

      const ids = new Set([...d.orders.map(o => 'o' + o.id), ...d.calls.map(c => 'c' + c.id)]);
      const fresh = [...ids].filter(x => !known.has(x));
      if (!first && fresh.length) toast('New activity: ' + fresh.length, 'warning');
      known = ids; first = false;
      $('#lastSync').textContent = new Date().toLocaleTimeString();
    }
    load(); setInterval(load, 5000);
  }

  /* ---------- Boot ---------- */
  document.addEventListener('DOMContentLoaded', () => {
    initTilt();
    if ($('#waiterReasons')) initWaiter();
    if (page === 'home') initMenu();
    if (page === 'orders') initOrders();
    if (page === 'pay') initPay();
    if (page === 'wifi' || page === 'admin-tables') initQR();
    if (page === 'staff') initStaff();
    const pr = $('#printQr'); if (pr) pr.addEventListener('click', () => window.print());
  });
})();

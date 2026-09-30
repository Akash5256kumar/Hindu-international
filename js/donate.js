/* =====================================================================
   DONATE PAGE — amount selection, payment method, submission and the
   "complete your payment" step. Settings come from content → donate.form
   (amounts, UPI ID, payee name, QR image, messages) — edit in the admin panel.

   Payments: UPI works directly (QR code / UPI ID / "pay with UPI app" link).
   Card, Net Banking and Wallet need a payment gateway (e.g. Razorpay) with the
   organisation's merchant account; until it is connected those donations are
   recorded and the donor is told a secure payment link will follow.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var form = document.querySelector('[data-donation-form]');
  if (!form) return;

  var F = ((window.SITE_CONTENT || {}).donate || {}).form || {};
  var done = document.getElementById('donate-done');
  var amountInput = form.querySelector('#amount');
  var chipsWrap = form.querySelector('[data-amount-options]');
  var otherChip = form.querySelector('[data-amount-other]');
  var status = form.querySelector('.form__status');
  var submit = form.querySelector('button[type="submit"]');
  var submitHTML = submit.innerHTML;

  var MIN = 1;
  var MAX = 10000000;   // ₹1,00,00,000
  var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
  function formatINR(n) { return '₹ ' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 2 }); }
  function isLocalMedia(p) { return typeof p === 'string' && /^(assets|uploads)\/[\w\-./]+$/.test(p) && p.indexOf('..') === -1; }

  /* ---------- UPI details (form box + payment step) ---------- */
  var upiId = String(F.upiId || '').trim();
  $$('[data-upi-id-line]').forEach(function (el) { el.hidden = !upiId; });
  $$('[data-upi-qr]').forEach(function (img) {
    if (isLocalMedia(F.upiQr)) { img.src = F.upiQr; img.hidden = false; }
  });

  /* ---------- Amount chips ---------- */
  var chips = [];
  (Array.isArray(F.amounts) ? F.amounts : []).forEach(function (a) {
    var value = parseFloat(String(a && a.value).replace(/[^\d.]/g, ''));
    if (!(value >= MIN)) return;
    var chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'r-chip';
    chip.textContent = formatINR(value);
    chip.setAttribute('aria-pressed', 'false');
    chip.addEventListener('click', function () {
      amountInput.value = String(value);
      press(chip);
      clearError(amountInput);
    });
    chipsWrap.appendChild(chip);
    chips.push(chip);
  });

  function press(active) {
    chips.concat(otherChip).forEach(function (c) { c.setAttribute('aria-pressed', String(c === active)); });
  }

  otherChip.setAttribute('aria-pressed', 'false');
  otherChip.addEventListener('click', function () {
    press(otherChip);
    amountInput.value = '';
    amountInput.focus();
  });
  amountInput.addEventListener('input', function () {
    press(otherChip);
    clearError(amountInput);
  });

  /* ---------- Payment method ---------- */
  function method() { return (form.querySelector('input[name="method"]:checked') || {}).value || 'upi'; }
  function showMethodPanel() {
    var upi = method() === 'upi';
    form.querySelector('[data-method-panel="upi"]').hidden = !upi;
    form.querySelector('[data-method-panel="other"]').hidden = upi;
  }
  $$('input[name="method"]', form).forEach(function (r) { r.addEventListener('change', showMethodPanel); });
  showMethodPanel();

  /* ---------- Validation ---------- */
  function parseAmount() {
    var raw = amountInput.value.replace(/[,\s₹]/g, '');
    if (!/^\d+(\.\d{1,2})?$/.test(raw)) return NaN;
    return parseFloat(raw);
  }

  function setError(input, message) {
    var wrap = input.closest('.field');
    var out = wrap && wrap.querySelector('.field__error');
    input.setAttribute('aria-invalid', message ? 'true' : 'false');
    if (wrap) wrap.classList.toggle('has-error', !!message);
    if (out) out.textContent = message || '';
    return !message;
  }
  function clearError(input) { setError(input, ''); }

  function validate() {
    var ok = true;
    var amount = parseAmount();
    ok = setError(amountInput, !(amount >= MIN) ? 'Please choose or enter an amount (minimum ₹' + MIN + ').'
      : amount > MAX ? 'For amounts above ' + formatINR(MAX) + ', please contact us.' : '') && ok;

    var name = form.querySelector('#d-name');
    ok = setError(name, name.value.trim() ? '' : 'Please enter your name.') && ok;

    var email = form.querySelector('#d-email');
    ok = setError(email, /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim()) ? '' : 'Please enter a valid email address.') && ok;

    var phone = form.querySelector('#d-phone');
    var digits = phone.value.replace(/\D/g, '');
    ok = setError(phone, /^[+\d\s()-]+$/.test(phone.value.trim()) && digits.length >= 7 && digits.length <= 15 ? '' : 'Please enter a valid phone number.') && ok;
    return ok;
  }

  $$('#d-name, #d-email, #d-phone', form).forEach(function (input) {
    input.addEventListener('input', function () { if (input.getAttribute('aria-invalid') === 'true') validate(); });
  });

  /* ---------- Submit ---------- */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validate()) {
      var bad = form.querySelector('[aria-invalid="true"]');
      if (bad) bad.focus();
      status.className = 'form__status is-error';
      status.textContent = 'Please check the highlighted fields.';
      return;
    }

    var amount = parseAmount();
    var data = new FormData(form);
    data.set('amount', amount.toFixed(2));

    submit.disabled = true;
    submit.textContent = 'Sending…';
    status.className = 'form__status';
    status.textContent = '';

    fetch(form.getAttribute('action'), { method: 'POST', body: data })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (res) {
        if (!res.ok) throw new Error(res.message || 'failed');
        showPaymentStep(amount, method());
      })
      .catch(function (err) {
        status.className = 'form__status is-error';
        status.textContent = err && !(err instanceof TypeError) && err.message && err.message !== 'failed'
          ? err.message
          : 'Sorry, we could not send this right now. Please try again, or contact us by phone / WhatsApp.';
      })
      .then(function () {
        submit.disabled = false;
        submit.innerHTML = submitHTML;
      });
  });

  /* ---------- Payment step ---------- */
  function showPaymentStep(amount, chosen) {
    var upi = chosen === 'upi' && (upiId || isLocalMedia(F.upiQr));
    done.querySelector('[data-pay-amount]').textContent = formatINR(amount);
    done.querySelector('[data-pay-upi]').hidden = !upi;
    done.querySelector('[data-pay-other]').hidden = upi;

    var link = done.querySelector('[data-upi-link]');
    if (upi && upiId && isMobile) {
      link.href = 'upi://pay?' + [
        'pa=' + encodeURIComponent(upiId),
        'pn=' + encodeURIComponent(F.upiName || ''),
        'am=' + amount.toFixed(2),
        'cu=INR',
        'tn=' + encodeURIComponent('Donation')
      ].join('&');
      link.hidden = false;
    } else {
      link.hidden = true;
    }

    form.hidden = true;
    done.hidden = false;
    done.focus();
    done.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  var copyBtn = done.querySelector('[data-copy-upi]');
  copyBtn.addEventListener('click', function () {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(upiId).then(function () {
      copyBtn.textContent = 'Copied';
      setTimeout(function () { copyBtn.textContent = 'Copy'; }, 2000);
    });
  });

  done.querySelector('[data-donate-again]').addEventListener('click', function () {
    form.reset();
    press(null);
    showMethodPanel();
    done.hidden = true;
    form.hidden = false;
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});

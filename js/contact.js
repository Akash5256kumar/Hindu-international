/* =====================================================================
   CONTACT PAGE — "Select Service" choices from Admin → Contact, and the
   FAQ accordion (smooth open / close, one + / − per question).
   Load after js/script.js (which fills the page from the content).
   ===================================================================== */
(function () {
  'use strict';

  function start() {
    /* Service choices: Admin → Contact → Form — "Select Service" choices */
    var select = document.getElementById('c-service');
    var page = (window.SITE_CONTENT || {}).contactPage || {};
    var choices = Array.isArray(page.serviceOptions)
      ? page.serviceOptions.map(function (o) { return o && String(o.text || '').trim(); }).filter(Boolean)
      : [];
    if (select && choices.length) {
      Array.prototype.slice.call(select.options, 1).forEach(function (o) { o.remove(); });
      choices.forEach(function (text) {
        var o = document.createElement('option');
        o.textContent = text;
        o.value = text;
        select.appendChild(o);
      });
    }
    /* the placeholder looks like the other fields until a choice is made */
    if (select) {
      var mark = function () { select.classList.toggle('is-empty', !select.value); };
      select.addEventListener('change', mark);
      mark();
    }

    /* FAQ: click a question to open or close its answer */
    var list = document.querySelector('.cx-faq__list');
    if (!list) return;
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('.cx-q__btn');
      if (!btn || !list.contains(btn)) return;
      var item = btn.closest('.cx-q');
      var open = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      item.classList.toggle('is-open', open);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

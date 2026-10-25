/* =====================================================================
   HINDU.INTERNATIONAL — SITE BEHAVIOUR
   Content:        content/content.js  (generated from content/content.json
                   by the admin panel — window.SITE_CONTENT)
   Header/footer:  js/components.js
   ===================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var C = window.SITE_CONTENT || {};
  var CONTACT = C.contact || {};
  var MEDIA = C.media || {};
  var EVENTS = Array.isArray(C.events) ? C.events : [];
  var FORM_ENDPOINT = 'forms/submit.php';
  var icon = window.vpsIcon;
  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };

  /* ---------- Helpers ---------- */
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function lookup(obj, path) {
    return path.split('.').reduce(function (o, k) { return o == null ? undefined : o[k]; }, obj);
  }

  /* "event.x" is a shortcut for the first event; "@x" is relative to the current list item */
  function resolve(path, item) {
    if (path.charAt(0) === '@') return lookup(item, path.slice(1));
    if (path.indexOf('event.') === 0) path = 'events.0.' + path.slice(6);
    return lookup(C, path);
  }

  /* Text with line breaks (\n -> <br>), always inserted as text, never as HTML */
  function setText(el, value) {
    el.textContent = '';
    String(value).split('\n').forEach(function (line, i) {
      if (i) el.appendChild(document.createElement('br'));
      el.appendChild(document.createTextNode(line));
    });
  }

  /* Blank lines separate paragraphs */
  function setParagraphs(el, value) {
    el.textContent = '';
    String(value).split(/\n\s*\n/).forEach(function (para) {
      para = para.trim();
      if (!para) return;
      var p = document.createElement('p');
      setText(p, para);
      el.appendChild(p);
    });
  }

  function isLocalMedia(path) { return /^(assets|uploads)\/[\w\-./]+$/.test(path) && path.indexOf('..') === -1; }

  function whatsappUrl() {
    if (!CONTACT.whatsappNumber) return '';
    return 'https://wa.me/' + String(CONTACT.whatsappNumber).replace(/\D/g, '') +
      (CONTACT.whatsappMessage ? '?text=' + encodeURIComponent(CONTACT.whatsappMessage) : '');
  }

  /* ---------- 0. Page layout: section order + on/off ----------
     <main data-layout="faith.layout"> holds one element per section, each marked
     data-block="name". The layout list from the admin panel decides which sections
     are shown and in what order; "custom" entries are built from
     <template data-block-template="custom"> with the entry's own fields.
     [data-block-num] inside a shown section gets its position: 01, 02, …           */
  function applyLayout(main) {
    var layout = resolve(main.getAttribute('data-layout'));
    if (!Array.isArray(layout) || !layout.length) return;
    var blocks = {};
    $$('[data-block]', main).forEach(function (el) {
      if (el.parentNode !== main) return;
      blocks[el.getAttribute('data-block')] = el;
      el.remove();
    });
    var custom = $('template[data-block-template="custom"]', main);
    var used = {};
    layout.forEach(function (entry) {
      if (!entry || entry.show === false) return;
      var el;
      if (entry.block === 'custom' && custom) {
        el = custom.content.firstElementChild.cloneNode(true);
        el.classList.add(entry.style === 'tinted' ? 'is-tinted' : 'is-plain');
        if (entry.imageSide === 'left') el.classList.add('is-image-left');
        bindScope(el, entry);
      } else if (blocks[entry.block]) {
        el = used[entry.block] ? blocks[entry.block].cloneNode(true) : blocks[entry.block];
        if (used[entry.block]) $$('[id]', el).concat([el]).forEach(function (n) { n.removeAttribute('id'); });
        used[entry.block] = true;
      }
      if (el) main.appendChild(el);
    });
  }
  $$('[data-layout]').forEach(applyLayout);

  /* ---------- 1. Fill the page from content ----------
     data-bind="path"          text (line breaks kept)
     data-bind-optional        hide the element when the value is empty
     data-bind-paras="path"    paragraphs
     data-bind-src / -alt      image source + alt text
     data-bind-href="path"     link (page.html, #part, https://, mailto:, tel:)
     data-show-if="path"       hide the element when the value is empty / off
     data-bind-list="path"     repeat the <template> inside for each item ("@field");
                               items with show: false are skipped
     data-link="tel|mailto|whatsapp"                                          */
  function bindScope(root, item) {
    $$('[data-bind-list]', root).forEach(function (list) {
      var tpl = $('template', list);
      var items = resolve(list.getAttribute('data-bind-list'), item);
      if (!tpl || !Array.isArray(items) || !items.length) return;
      Array.prototype.slice.call(list.children).forEach(function (child) { if (child !== tpl) child.remove(); });
      items.forEach(function (entry) {
        if (entry && entry.show === false) return;
        var frag = tpl.content.cloneNode(true);
        bindScope(frag, entry);
        list.appendChild(frag);
      });
    });

    $$('[data-bind]', root).forEach(function (el) {
      var v = resolve(el.getAttribute('data-bind'), item);
      if (v == null) return;
      if (el.hasAttribute('data-bind-optional')) {
        el.hidden = !String(v).trim();
        if (el.hidden) return;
      }
      setText(el, v);
    });

    $$('[data-show-if]', root).forEach(function (el) {
      var v = resolve(el.getAttribute('data-show-if'), item);
      if (v === undefined) return;          // not in the content yet: keep the page default
      el.hidden = v === false || v === null || !String(v).trim();
    });

    $$('[data-bind-href]', root).forEach(function (el) {
      var v = resolve(el.getAttribute('data-bind-href'), item);
      if (v == null || !String(v).trim()) return;
      var href = safeLink(v, el.getAttribute('href'));
      el.setAttribute('href', href);
      if (/^https?:/i.test(href)) { el.target = '_blank'; el.rel = 'noopener'; }
      else { el.removeAttribute('target'); el.removeAttribute('rel'); }
    });

    $$('[data-bind-paras]', root).forEach(function (el) {
      var v = resolve(el.getAttribute('data-bind-paras'), item);
      if (v != null && String(v).trim()) setParagraphs(el, v);
    });

    /* data-bind-icon="path" on a <use>: picks one of the built-in icons */
    $$('[data-bind-icon]', root).forEach(function (el) {
      var name = resolve(el.getAttribute('data-bind-icon'), item);
      if (typeof name === 'string' && (window.vpsIconNames || []).indexOf(name) !== -1) {
        el.setAttribute('href', '#i-' + name);
      }
    });

    /* data-bind-color="path": sets the --c colour used by .r-icon / .r-badge */
    $$('[data-bind-color]', root).forEach(function (el) {
      var color = resolve(el.getAttribute('data-bind-color'), item);
      if (typeof color === 'string' && /^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(color)) {
        el.style.setProperty('--c', color);
      }
    });

    $$('[data-bind-src]', root).forEach(function (el) {
      var src = resolve(el.getAttribute('data-bind-src'), item);
      if (typeof src === 'string' && isLocalMedia(src) && el.getAttribute('src') !== src) {
        el.removeAttribute('width');     // uploaded images may have a different shape
        el.removeAttribute('height');
        /* phones get a smaller copy made by media.php; wider screens keep the original */
        if (el.tagName === 'IMG' && /\.(jpe?g|png|webp)$/i.test(src)) {
          el.sizes = '(max-width: 768px) 100vw, 1600px';
          el.srcset = [480, 828].map(function (w) {
            return 'media.php?src=' + encodeURIComponent(src) + '&w=' + w + ' ' + w + 'w';
          }).concat(src + ' 1600w').join(', ');
        }
        el.src = src;
      }
      var altPath = el.getAttribute('data-bind-alt');
      var alt = altPath ? resolve(altPath, item) : null;
      if (typeof alt === 'string' && alt) el.alt = alt;
    });
  }
  bindScope(document);

  /* Section numbers follow the shown order (hidden labels are skipped) */
  $$('[data-layout]').forEach(function (main) {
    $$('[data-block-num]', main)
      .filter(function (el) { return !el.closest('[hidden]'); })
      .forEach(function (el, i) { el.textContent = (i < 9 ? '0' : '') + (i + 1); });
  });

  /* Browser tab title + description: <body data-page-meta="faith.page"> */
  var metaPath = document.body.getAttribute('data-page-meta');
  var meta = metaPath ? resolve(metaPath) : null;
  if (meta && meta.title) document.title = meta.title;
  if (meta && meta.description) {
    var desc = $('meta[name="description"]');
    if (desc) desc.setAttribute('content', meta.description);
  }

  $$('[data-link]').forEach(function (el) {
    var type = el.getAttribute('data-link');
    var href = '';
    if (type === 'tel' && CONTACT.phoneLink) href = 'tel:' + String(CONTACT.phoneLink).replace(/[^\d+]/g, '');
    if (type === 'mailto' && /^[^\s@\[]+@[^\s@]+\.[^\s@]+$/.test(CONTACT.email || '')) href = 'mailto:' + CONTACT.email;
    if (type === 'whatsapp') href = whatsappUrl();
    if (href) {
      el.href = href;
      if (type === 'whatsapp') { el.target = '_blank'; el.rel = 'noopener'; }
    }
  });

  /* Footer social icons (built with DOM APIs; URLs are validated by the admin panel) */
  var social = $('[data-social]');
  if (social && icon) {
    var S = C.social || {};
    [
      ['facebook', S.facebook, 'Facebook'],
      ['x', S.x, 'X'],
      ['youtube', S.youtube, 'YouTube'],
    ].forEach(function (i) {
      var url = i[1];
      if (!url || !/^(https?:\/\/|#$|contact(?:-us)?(?:\.html)?$)/.test(url)) return;
      var li = document.createElement('li');
      var a = document.createElement('a');
      a.className = 'social social--' + i[0];
      a.href = url;
      a.setAttribute('aria-label', i[2]);
      if (/^https?:/.test(url)) { a.target = '_blank'; a.rel = 'noopener'; }
      a.insertAdjacentHTML('afterbegin', icon(i[0]));   // icon markup is static, from components.js
      li.appendChild(a);
      social.appendChild(li);
    });
  }

  $$('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });

  /* ---------- 2. Header: mobile menu + scrolled state ---------- */
  var header = $('.site-header');
  var toggle = $('.nav-toggle');
  var nav = $('#site-nav');

  function setMenu(open) {
    if (!toggle) return;
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    /* the menu opens right under the header, which sits lower while the Ganesh strip is in view */
    if (open) document.documentElement.style.setProperty('--menu-top', Math.max(0, header.getBoundingClientRect().bottom) + 'px');
    header.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      setMenu(toggle.getAttribute('aria-expanded') !== 'true');
    });
    $$('a', nav).forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('is-open')) { setMenu(false); toggle.focus(); }
    });
    window.addEventListener('resize', function () { if (window.innerWidth > 1240) setMenu(false); });
  }
  function onScroll() { if (header) header.classList.toggle('is-scrolled', window.scrollY > 8); }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- 3. "Watch How It Works" video ---------- */
  $$('[data-video]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var videoUrl = MEDIA.howItWorksVideo;
      if (!videoUrl || !/^https:\/\//.test(videoUrl)) return; // falls back to its href (How It Works page)
      e.preventDefault();
      var modal = document.createElement('div');
      modal.className = 'modal';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-label', 'How Virtual Punya Snan works — video');
      modal.innerHTML =
        '<div class="modal__box">' +
          '<button class="modal__close" type="button" aria-label="Close video">' + icon('close') + '</button>' +
          '<div class="modal__video"></div>' +
        '</div>';
      var frame = document.createElement('iframe');
      frame.src = videoUrl;
      frame.title = 'How It Works video';
      frame.allow = 'autoplay; encrypted-media; picture-in-picture';
      frame.allowFullscreen = true;
      $('.modal__video', modal).appendChild(frame);
      document.body.appendChild(modal);
      document.body.classList.add('menu-open');
      var closeBtn = $('.modal__close', modal);
      closeBtn.focus();
      function close() {
        modal.remove();
        document.body.classList.remove('menu-open');
        document.removeEventListener('keydown', esc);
        btn.focus();
      }
      function esc(ev) { if (ev.key === 'Escape') close(); }
      closeBtn.addEventListener('click', close);
      modal.addEventListener('click', function (ev) { if (ev.target === modal) close(); });
      document.addEventListener('keydown', esc);
    });
  });

  /* ---------- 3b. Home hero background video (optional) ---------- */
  var heroMedia = $('.hero__media');
  var HV = { mp4: MEDIA.heroVideoMp4, webm: MEDIA.heroVideoWebm };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var saveData = navigator.connection && navigator.connection.saveData;
  if (heroMedia && (HV.mp4 || HV.webm) && !reduceMotion && !saveData) {
    var poster = $('img', heroMedia);
    var video = document.createElement('video');
    video.className = 'hero__video';
    video.muted = true;
    video.loop = true;
    video.autoplay = true;
    video.playsInline = true;
    video.setAttribute('playsinline', '');
    video.setAttribute('aria-hidden', 'true');
    if (poster) video.poster = poster.currentSrc || poster.src;
    [['webm', 'video/webm'], ['mp4', 'video/mp4']].forEach(function (s) {
      if (!HV[s[0]] || !isLocalMedia(HV[s[0]])) return;
      var src = document.createElement('source');
      src.src = HV[s[0]];
      src.type = s[1];
      video.appendChild(src);
    });
    video.addEventListener('playing', function () { heroMedia.classList.add('has-video'); });
    heroMedia.appendChild(video);
    var p = video.play();
    if (p && p.catch) p.catch(function () { /* autoplay blocked: photo stays visible */ });
  }

  /* ---------- 4. Booking: event dropdown from config ---------- */
  var eventSelect = $('#event');
  if (eventSelect && EVENTS.length) {
    EVENTS.forEach(function (ev) {
      var opt = document.createElement('option');
      opt.value = ev.name;
      opt.textContent = ev.name + (ev.dates ? ' — ' + ev.dates : '');
      eventSelect.appendChild(opt);
    });
    if (EVENTS.length === 1) eventSelect.selectedIndex = 1;
  }

  /* ---------- 4b. Contact: "Enquire About Puja" (Jyotirlinga pages) fills in the temple's name ---------- */
  var contactMessage = $('#c-message');
  if (contactMessage && !contactMessage.value) {
    var enquiry = '';
    try { enquiry = (new URLSearchParams(location.search).get('enquiry') || '').slice(0, 120); } catch (e) {}
    if (enquiry) contactMessage.value = 'I would like to enquire about darshan and puja seva at ' + enquiry + '.\n\n';
  }

  /* ---------- 5. Photo upload (click or drag & drop, with preview) ---------- */
  var MAX_MB = 5;
  var TYPES = ['image/jpeg', 'image/png'];

  $$('[data-upload]').forEach(function (zone) {
    var input = $('input[type="file"]', zone);
    var preview = $('.upload__preview', zone);
    var img = $('img', preview);
    var nameEl = $('.upload__name', preview);

    function show(file) {
      if (!file) { zone.classList.remove('has-file'); return; }
      nameEl.textContent = file.name;
      var reader = new FileReader();
      reader.onload = function (e) { img.src = e.target.result; };
      reader.readAsDataURL(file);
      zone.classList.add('has-file');
    }
    input.addEventListener('change', function () { show(input.files[0]); validateField(input); });

    ['dragenter', 'dragover'].forEach(function (t) {
      zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
    });
    ['dragleave', 'drop'].forEach(function (t) {
      zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); });
    });
    zone.addEventListener('drop', function (e) {
      if (!e.dataTransfer.files.length) return;
      try {
        var dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
      } catch (err) { /* very old browsers: user can still click to choose */ }
      show(input.files[0]);
      validateField(input);
    });
  });

  /* ---------- 6. Form validation + submission ---------- */
  var MESSAGES = {
    required: 'This field is required.',
    email: 'Please enter a valid email address.',
    phone: 'Please enter a valid phone / WhatsApp number.',
    fileType: 'Please upload a JPG or PNG image.',
    fileSize: 'The photograph must be smaller than ' + MAX_MB + ' MB.',
    fileRequired: 'Please upload your photograph.'
  };

  function errorFor(field) {
    var v = (field.value || '').trim();
    if (field.type === 'file') {
      var file = field.files && field.files[0];
      if (!file) return field.required ? MESSAGES.fileRequired : '';
      if (TYPES.indexOf(file.type) === -1) return MESSAGES.fileType;
      if (file.size > MAX_MB * 1024 * 1024) return MESSAGES.fileSize;
      return '';
    }
    if (field.required && !v) return MESSAGES.required;
    if (v && field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return MESSAGES.email;
    if (v && field.type === 'tel') {
      var digits = v.replace(/\D/g, '');
      if (!/^[+\d\s()-]+$/.test(v) || digits.length < 7 || digits.length > 15) return MESSAGES.phone;
    }
    return '';
  }

  function validateField(field) {
    var msg = errorFor(field);
    var wrap = field.closest('.field');
    var out = wrap && $('.field__error', wrap);
    field.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (wrap) wrap.classList.toggle('has-error', !!msg);
    if (out) out.textContent = msg;
    return !msg;
  }

  $$('form[data-form]').forEach(function (form) {
    var fields = $$('input:not([type="hidden"]):not([name="website"]), select, textarea', form);
    var status = $('.form__status', form);
    var submit = $('button[type="submit"]', form);
    var submitHTML = submit.innerHTML;

    fields.forEach(function (f) {
      f.addEventListener('blur', function () { if (f.type !== 'file') validateField(f); });
      f.addEventListener('input', function () { if (f.getAttribute('aria-invalid') === 'true') validateField(f); });
    });

    function setStatus(type, text) {
      status.className = 'form__status is-' + type;
      status.textContent = text;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var firstBad = null;
      fields.forEach(function (f) { if (!validateField(f) && !firstBad) firstBad = f; });
      if (firstBad) {
        setStatus('error', 'Please check the highlighted fields.');
        firstBad.focus();
        return;
      }

      submit.disabled = true;
      submit.textContent = 'Sending…';
      status.className = 'form__status';
      status.textContent = '';

      fetch(form.getAttribute('action') || FORM_ENDPOINT, { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (res) {
          if (!res.ok) throw new Error(res.message || 'failed');
          var done = $('.form__success', form.parentNode);
          if (done) {
            form.hidden = true;
            done.hidden = false;
            done.focus();
          } else {
            setStatus('success', 'Thank you! Your message has been sent.');
            form.reset();
          }
        })
        .catch(function (err) {
          // Network errors (TypeError) get the generic message; server validation messages are shown as-is
          var msg = err && !(err instanceof TypeError) && err.message && err.message !== 'failed'
            ? err.message
            : 'Sorry, we could not send this right now. Please try again, or contact us by phone / WhatsApp.';
          setStatus('error', msg);
        })
        .then(function () {
          submit.disabled = false;
          submit.innerHTML = submitHTML;
        });
    });
  });
});

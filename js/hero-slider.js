/* Home hero slider: crossfade + Ken Burns images, word-by-word heading reveal.
   Timings live in css/hero-slider.css; this file only switches classes. */
(function () {
  'use strict';

  var hero = document.querySelector('.hero--slider');
  if (!hero) return;

  var SLIDE_MS = 5000;
  var IMG_LEAVE_MS = 1250;
  var TEXT_LEAVE_MS = 500;

  /* Slides saved in the admin (Home page → Hero slider) replace the built-in ones */
  (function buildFromContent() {
    var home = (window.SITE_CONTENT || {}).home;
    var isMedia = function (p) { return /^(assets|uploads)\/[\w\-./]+$/.test(p || '') && p.indexOf('..') === -1; };
    var slides = home && Array.isArray(home.heroSlides)
      ? home.heroSlides.filter(function (s) { return s && String(s.title || '').trim() && isMedia(s.image); })
      : [];
    var box = hero.querySelector('.hero__slides');
    var media = hero.querySelector('.hero__media');
    if (!slides.length || !box || !media) return;

    var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };
    var SVG_NS = 'http://www.w3.org/2000/svg';

    function button(cls, text, link, fallback, arrow) {
      text = String(text || '').trim();
      if (!text) return null;
      var a = document.createElement('a');
      a.className = 'btn ' + cls;
      var href = safeLink(link, fallback);
      a.setAttribute('href', href);
      if (/^https?:/i.test(href)) { a.target = '_blank'; a.rel = 'noopener'; }
      var span = document.createElement('span');
      span.textContent = text;
      a.appendChild(span);
      if (arrow) {
        a.appendChild(document.createTextNode(' '));
        var svg = document.createElementNS(SVG_NS, 'svg');
        svg.setAttribute('class', 'icon icon--arrow');
        svg.setAttribute('aria-hidden', 'true');
        var use = document.createElementNS(SVG_NS, 'use');
        use.setAttribute('href', '#i-arrow');
        svg.appendChild(use);
        a.appendChild(svg);
      }
      return a;
    }

    [].slice.call(media.querySelectorAll('.hero__img')).forEach(function (n) { n.remove(); });
    box.textContent = '';

    slides.forEach(function (s, i) {
      var slide = document.createElement('div');
      slide.className = 'hero__slide' + (i === 0 ? ' is-active' : '');
      if (i) { slide.setAttribute('inert', ''); slide.setAttribute('aria-hidden', 'true'); }

      var h = document.createElement(i === 0 ? 'h1' : 'h2');
      h.className = 'hero__title';
      if (i === 0) h.id = 'hero-title';
      String(s.title).split('*').forEach(function (part, k) {
        if (!part) return;
        if (k % 2) { var em = document.createElement('em'); em.textContent = part; h.appendChild(em); }
        else h.appendChild(document.createTextNode(part));
      });
      slide.appendChild(h);

      if (String(s.text || '').trim()) {
        var p = document.createElement('p');
        p.className = 'hero__text';
        p.textContent = String(s.text).trim();
        slide.appendChild(p);
      }

      var actions = document.createElement('div');
      actions.className = 'hero__actions';
      var b1 = button('btn--primary', s.primaryText, s.primaryLink, 'services', true);
      var b2 = button('btn--secondary', s.secondaryText, s.secondaryLink, 'contact-us', false);
      if (b1) actions.appendChild(b1);
      if (b2) actions.appendChild(b2);
      if (actions.children.length) slide.appendChild(actions);
      box.appendChild(slide);

      var img = document.createElement('img');
      img.className = 'hero__img' + (i === 0 ? ' is-active' : '');
      img.width = 1821; img.height = 864;
      img.alt = String(s.imageAlt || '');
      img.style.setProperty('--pos', '72% 50%');
      img.style.setProperty('--pos-m', '80% 50%');
      var srcset = /\.(jpe?g|png|webp)$/i.test(s.image)
        ? [480, 828, 1200].map(function (w) { return 'media.php?src=' + encodeURIComponent(s.image) + '&w=' + w + ' ' + w + 'w'; }).concat(s.image + ' 1820w').join(', ')
        : '';
      if (i === 0) {
        img.setAttribute('fetchpriority', 'high');
        if (srcset) { img.sizes = '100vw'; img.srcset = srcset; }
        img.src = s.image;
      } else {
        img.decoding = 'async';
        img.setAttribute('data-src', s.image);
        if (srcset) img.setAttribute('data-srcset', srcset);
      }
      media.appendChild(img);
    });
  })();

  var imgs = [].slice.call(hero.querySelectorAll('.hero__img'));
  var texts = [].slice.call(hero.querySelectorAll('.hero__slide'));
  var count = Math.min(imgs.length, texts.length);
  if (!count) return;

  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var current = 0;
  var timer = null;

  /* Wrap every heading word in <span class="w"><span>word</span></span> */
  texts.forEach(function (slide) {
    var heading = slide.querySelector('.hero__title');
    if (!heading) return;
    heading.setAttribute('aria-label', heading.textContent.replace(/\s+/g, ' ').trim());
    var index = 0;
    (function walk(node) {
      [].slice.call(node.childNodes).forEach(function (child) {
        if (child.nodeType === 3) {
          var frag = document.createDocumentFragment();
          child.nodeValue.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            var w = document.createElement('span');
            w.className = 'w';
            w.setAttribute('aria-hidden', 'true');
            w.style.setProperty('--i', index++);
            var inner = document.createElement('span');
            inner.textContent = part;
            w.appendChild(inner);
            frag.appendChild(w);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1) {
          walk(child);
        }
      });
    })(heading);
  });

  function isLoaded(img) { return img.complete && img.naturalWidth > 0; }

  function whenLoaded(img, fn) {
    if (isLoaded(img)) { fn(); return; }
    img.addEventListener('load', fn, { once: true });
    img.addEventListener('error', fn, { once: true });
  }

  function setActiveText(slide, on) {
    if (on) { slide.removeAttribute('inert'); slide.removeAttribute('aria-hidden'); }
    else { slide.setAttribute('inert', ''); slide.setAttribute('aria-hidden', 'true'); }
  }

  function schedule() {
    clearTimeout(timer);
    if (reduce || document.hidden) return;
    timer = setTimeout(function () { go((current + 1) % count); }, SLIDE_MS);
  }

  function swap(next) {
    var prevImg = imgs[current], prevText = texts[current];
    prevImg.classList.remove('is-active');
    prevImg.classList.add('is-leaving');
    prevText.classList.remove('is-active');
    prevText.classList.add('is-leaving');
    setActiveText(prevText, false);

    imgs[next].classList.remove('is-leaving');
    texts[next].classList.remove('is-leaving');
    imgs[next].classList.add('is-active');
    texts[next].classList.add('is-active');
    setActiveText(texts[next], true);

    setTimeout(function () { prevImg.classList.remove('is-leaving'); }, IMG_LEAVE_MS);
    setTimeout(function () { prevText.classList.remove('is-leaving'); }, TEXT_LEAVE_MS);

    current = next;
    schedule();
  }

  function go(next) {
    if (next === current) return;
    clearTimeout(timer);
    whenLoaded(imgs[next], function () { swap(next); });
  }

  /* Other slide images load only after the first one is on screen */
  function loadRest() {
    imgs.forEach(function (img, i) {
      if (i === 0 || !img.getAttribute('data-src')) return;
      if (img.getAttribute('data-srcset')) {
        img.sizes = '100vw';
        img.srcset = img.getAttribute('data-srcset');
      }
      img.src = img.getAttribute('data-src');
      img.removeAttribute('data-src');
      img.removeAttribute('data-srcset');
    });
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) clearTimeout(timer); else schedule();
  });

  whenLoaded(imgs[0], function () {
    loadRest();
    if (!reduce) schedule();
  });
})();

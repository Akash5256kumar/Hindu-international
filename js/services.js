/* =====================================================================
   SERVICES — the Services page cards and every service detail page.
   Content: content/content.json → "services" (Admin → Services). Until the
   admin has saved it, content/services.default.json is used instead.
     services.html                  <body data-services="list">
     service.html?name=<slug>       <body data-services="detail">
   Load after js/components.js and before js/script.js.
   ===================================================================== */

(function () {
  'use strict';

  /* ---------- Extra icons for the Services pages (same style as js/components.js) ---------- */
  var EXTRA_ICONS = {
    snan: '<path d="M12 3c1.6 1.8 2.3 3.1 2.3 4.4a2.3 2.3 0 0 1-4.6 0C9.7 6.1 10.4 4.8 12 3z"/><path d="M3 14c1.5 0 1.5-1.2 3-1.2s1.5 1.2 3 1.2 1.5-1.2 3-1.2 1.5 1.2 3 1.2 1.5-1.2 3-1.2 1.5 1.2 3 1.2"/><path d="M3 18.5c1.5 0 1.5-1.2 3-1.2s1.5 1.2 3 1.2 1.5-1.2 3-1.2 1.5 1.2 3 1.2 1.5-1.2 3-1.2 1.5 1.2 3 1.2"/>',
    cart: '<path d="M2.5 4h2.6l2.3 10.5h10.3l2.1-7.5H6.3"/><circle cx="9" cy="19" r="1.6"/><circle cx="16.5" cy="19" r="1.6"/><path d="M10 10.5h6"/>',
    flame: '<path d="M12 21c-3.9 0-6.5-2.6-6.5-6.2 0-3.4 2.4-5.6 3.6-8.3.9 1.5 1.5 2.7 1.5 4.1 1.4-1.2 2.4-3.6 2.2-6.6 3.4 2.4 5.7 6.2 5.7 10.6 0 3.7-2.6 6.4-6.5 6.4z"/><path d="M12 21c-1.6 0-2.8-1.2-2.8-2.8 0-1.7 1.3-2.6 2.8-4.4 1.5 1.8 2.8 2.7 2.8 4.4 0 1.6-1.2 2.8-2.8 2.8z"/>',
    pandit: '<circle cx="12" cy="7.5" r="3.5"/><path d="M12 2.6v1.4"/><path d="M4.5 21v-1.5a7.5 7.5 0 0 1 15 0V21"/><path d="M9 13.3l3 4.2 3-4.2"/>',
    namaste: '<path d="M11.2 4.5c-.9.4-1.4 1.3-1.4 2.3v6.4l-3.3 3.3a2 2 0 0 0-.5 2l.6 2"/><path d="M12.8 4.5c.9.4 1.4 1.3 1.4 2.3v6.4l3.3 3.3a2 2 0 0 1 .5 2l-.6 2"/><path d="M11.2 4.5L12 3.5l.8 1"/><path d="M11.2 4.5v9.5M12.8 4.5v9.5"/><path d="M8 11.5L5 14.5M16 11.5l3 3"/>'
  };
  (function addIcons() {
    var symbols = Object.keys(EXTRA_ICONS).map(function (k) {
      return '<symbol id="i-' + k + '" viewBox="0 0 24 24">' + EXTRA_ICONS[k] + '</symbol>';
    }).join('');
    var wrap = document.createElement('div');
    wrap.setAttribute('aria-hidden', 'true');
    wrap.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden';
    wrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg">' + symbols + '</svg>';
    document.body.insertBefore(wrap, document.body.firstChild);
    if (window.vpsIconNames) Array.prototype.push.apply(window.vpsIconNames, Object.keys(EXTRA_ICONS));
  })();

  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };
  var SITE = 'Hindu.International';

  /* ---------- Small DOM helpers (all text is inserted as text, never as HTML) ---------- */
  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null && text !== '') node.textContent = text;
    return node;
  }
  function str(v) { return v == null ? '' : String(v).trim(); }
  function isMedia(path) { return /^(assets|uploads)\/[\w\-./]+$/.test(path || '') && path.indexOf('..') === -1; }
  function icon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('class', 'icon');
    svg.setAttribute('aria-hidden', 'true');
    var use = document.createElementNS(ns, 'use');
    use.setAttribute('href', '#i-' + (/^[a-z]+$/.test(name || '') ? name : 'lotus'));
    svg.appendChild(use);
    return svg;
  }
  function image(path, alt, fallback) {
    var src = isMedia(path) ? path : (isMedia(fallback) ? fallback : '');
    if (!src) return null;
    var img = el('img');
    img.src = src;
    img.alt = str(alt);
    img.loading = 'lazy';
    img.decoding = 'async';
    return img;
  }
  /* Text with line breaks kept */
  function lines(node, value) {
    str(value).split('\n').forEach(function (line, i) {
      if (i) node.appendChild(el('br'));
      node.appendChild(document.createTextNode(line));
    });
    return node;
  }
  /* Heading: navy text + orange ending */
  function heading(tag, title, accent, cls) {
    var h = el(tag, cls);
    title = str(title); accent = str(accent);
    if (title) h.appendChild(document.createTextNode(title + (accent ? ' ' : '')));
    if (accent) h.appendChild(el('span', 'sd-accent', accent));
    return h;
  }
  function lotusRule() {
    var d = el('div', 'sd-lotus');
    d.setAttribute('aria-hidden', 'true');
    d.appendChild(icon('lotus'));
    return d;
  }
  function list(items) { return Array.isArray(items) ? items.filter(function (x) { return x && typeof x === 'object'; }) : []; }
  function detailHref(item) {
    return item.status === 'published' && str(item.slug)
      ? 'service?name=' + encodeURIComponent(str(item.slug))
      : safeLink(item.link, 'booking');
  }

  /* ---------- Content: admin content first, otherwise the default file ---------- */
  /* Card fields stay as the admin saved them; everything else is the detail page */
  var CARD_KEYS = { show: 1, slug: 1, number: 1, icon: 1, title: 1, text: 1, image: 1, imageAlt: 1, link: 1 };
  function emptyDetail(s) {
    return !str(s.aboutTitle) && !str(s.aboutText1) && !list(s.processSteps).length && !list(s.receive).length && !list(s.faqs).length;
  }
  /* A service whose detail page the admin has not filled in yet takes it from the default file
     (same rule as ContentStore::load in the admin, so both show the same page) */
  function fillFromDefaults(c, d) {
    var defaults = {}, saved = {};
    var known = Array.isArray(c.knownDefaults) ? c.knownDefaults : null;
    list(d && d.items).forEach(function (x) { if (str(x.slug) && !emptyDetail(x)) defaults[str(x.slug)] = x; });
    list(c.items).forEach(function (s) {
      saved[str(s.slug)] = true;
      var def = defaults[str(s.slug)];
      if (!def || !emptyDetail(s)) return;
      Object.keys(def).forEach(function (k) { if (!CARD_KEYS[k]) s[k] = def[k]; });
    });
    /* a service only in the default file is added at the end, unless the admin has already
       seen it (knownDefaults) and so it was deleted on purpose */
    list(d && d.items).forEach(function (x) {
      var slug = str(x.slug);
      if (slug && !saved[slug] && (!known || known.indexOf(slug) < 0)) c.items.push(x);
    });
    return c;
  }
  /* While the default file loads, the cards written in services.html stay on screen,
     so their links must match the published service pages */
  function loadServices(done) {
    var c = (window.SITE_CONTENT || {}).services;
    if (c && Array.isArray(c.items) && c.items.length) {
      if (!window.fetch) return done(c);
      return fetch('content/services.default.json', { cache: 'no-cache' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) { done(fillFromDefaults(c, d)); }, function () { done(c); });
    }
    if (!window.fetch) return done(null);
    fetch('content/services.default.json', { cache: 'no-cache' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { done(d && Array.isArray(d.items) ? d : null); }, function () { done(null); });
  }

  /* =================================================================
     SERVICES PAGE — cards
     ================================================================= */
  function renderList(data) {
    var grid = document.querySelector('[data-services-grid]');
    if (!grid || !data) return;
    /* The card number is the service's position in the admin list, so hidden services leave a gap
       instead of shifting the numbers of the services after them */
    var items = list(data.items).map(function (s, i) { return { s: s, n: i + 1 }; })
      .filter(function (x) { return x.s.show !== false && str(x.s.title); });
    if (!items.length) return;

    var cards = items.map(function (x) {
      var s = x.s;
      var card = el('article', 'svc-card');
      var media = el('div', 'svc-card__media');
      var img = image(s.image, s.imageAlt || s.title);
      if (img) media.appendChild(img);
      card.appendChild(media);

      var body = el('div', 'svc-card__body');
      var head = el('div', 'svc-card__head');
      var ic = el('span', 'svc-card__icon');
      ic.appendChild(icon(s.icon));
      head.appendChild(ic);
      head.appendChild(el('span', 'svc-card__num', 'Service ' + (x.n < 10 ? '0' : '') + x.n));
      body.appendChild(head);
      body.appendChild(el('h2', null, str(s.title)));
      if (str(s.text)) body.appendChild(el('p', null, str(s.text)));
      var a = el('a', 'svc-card__btn', 'View Details ');
      a.href = detailHref(s);
      if (/^https?:/i.test(a.getAttribute('href'))) { a.target = '_blank'; a.rel = 'noopener'; }
      a.appendChild(icon('arrow'));
      body.appendChild(a);
      card.appendChild(body);
      return card;
    });
    paginate(grid, cards);
  }

  /* ---------- Pages of 6 cards with a Previous / Next bar; the page is kept in ?page=N ---------- */
  var PER_PAGE = 6;
  function paginate(grid, cards) {
    var pages = Math.ceil(cards.length / PER_PAGE);
    var page = Math.min(pages, Math.max(1, parseInt(new URLSearchParams(location.search).get('page'), 10) || 1));
    var bar = null, count, prev, next;
    if (pages > 1) {
      bar = el('nav', 'container svc-pager');
      bar.setAttribute('aria-label', 'Service pages');
      count = el('p', 'svc-pager__count');
      count.setAttribute('aria-live', 'polite');
      prev = el('button', 'svc-pager__btn svc-pager__btn--prev');
      prev.type = 'button';
      prev.appendChild(icon('arrow'));
      prev.appendChild(document.createTextNode('Previous'));
      next = el('button', 'svc-pager__btn svc-pager__btn--next', 'Next');
      next.type = 'button';
      next.appendChild(icon('arrow'));
      var btns = el('div', 'svc-pager__btns');
      btns.appendChild(prev);
      btns.appendChild(next);
      bar.appendChild(count);
      bar.appendChild(btns);
      prev.addEventListener('click', function () { go(page - 1); });
      next.addEventListener('click', function () { go(page + 1); });
      grid.parentNode.insertBefore(bar, grid.nextSibling);
    }

    function draw() {
      var first = (page - 1) * PER_PAGE;
      var shown = cards.slice(first, first + PER_PAGE);
      grid.textContent = '';
      shown.forEach(function (c) { grid.appendChild(c); });
      if (!bar) return;
      count.textContent = '';
      count.appendChild(document.createTextNode('Showing '));
      count.appendChild(el('b', null, shown.length > 1 ? (first + 1) + '–' + (first + shown.length) : String(first + 1)));
      count.appendChild(document.createTextNode(' of '));
      count.appendChild(el('b', null, String(cards.length)));
      count.appendChild(document.createTextNode(' services · Page ' + page + ' of ' + pages));
      prev.disabled = page === 1;
      next.disabled = page === pages;
    }

    function go(n) {
      if (n < 1 || n > pages || n === page) return;
      page = n;
      draw();
      var url = new URL(location.href);
      if (page === 1) url.searchParams.delete('page'); else url.searchParams.set('page', page);
      history.replaceState(null, '', url);
      var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
      grid.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    }

    draw();
  }

  /* =================================================================
     SERVICE DETAIL PAGE
     ================================================================= */
  function show(node, on) { if (node) node.hidden = !on; }
  function fill(id, build) {
    var box = document.getElementById(id);
    if (!box) return null;
    box.textContent = '';
    var ok = build(box);
    box.hidden = ok === false;
    return box;
  }

  function setMeta(selector, attr, value) {
    var node = document.head.querySelector(selector);
    if (node && value) node.setAttribute(attr, value);
  }
  function applySeo(s) {
    var title = str(s.seoTitle) || (str(s.title) + ' | ' + SITE);
    var desc = str(s.seoDescription) || str(s.text) || str(s.aboutText1);
    var url = location.origin + location.pathname.replace(/\.html$/, '') + '?name=' + encodeURIComponent(str(s.slug));
    document.title = title;
    setMeta('meta[name="description"]', 'content', desc);
    setMeta('meta[property="og:title"]', 'content', title);
    setMeta('meta[property="og:description"]', 'content', desc);
    setMeta('meta[property="og:url"]', 'content', url);
    if (isMedia(s.bannerImage || s.image)) setMeta('meta[property="og:image"]', 'content', location.origin + '/' + (s.bannerImage || s.image));
    setMeta('link[rel="canonical"]', 'href', url);
  }

  function renderBanner(s, fallbackImage) {
    fill('sd-banner', function (box) {
      var media = el('div', 'sd-banner__media');
      media.setAttribute('aria-hidden', 'true');
      var img = image(s.bannerImage, '', s.image || fallbackImage);
      if (img) { img.loading = 'eager'; img.setAttribute('fetchpriority', 'high'); media.appendChild(img); }
      box.appendChild(media);
      var inner = el('div', 'container sd-banner__inner');

      /* Up to 3 sliding texts on the same photo; without them, just the heading */
      var slides = list(s.bannerSlides).filter(function (x) { return str(x.title); });
      if (!slides.length) slides = [{ title: str(s.bannerTitle) || str(s.title), accent: s.bannerAccent }];

      var stack = el('div', 'sd-slides');
      slides.forEach(function (x, i) {
        var slide = el('div', 'sd-slide' + (i === 0 ? ' is-on' : ''));
        if (i) slide.setAttribute('aria-hidden', 'true');
        if (str(x.label)) slide.appendChild(el('p', 'sd-slide__label', str(x.label)));
        var h = el(i === 0 ? 'h1' : 'p', 'sd-banner__title');
        if (i === 0) h.id = 'page-title';
        titleLines(h, str(x.title), str(x.accent));
        slide.appendChild(h);
        if (str(x.text)) slide.appendChild(el('p', 'sd-slide__text', str(x.text)));
        if (str(x.buttonText)) {
          var a = el('a', 'btn btn--primary sd-slide__btn', str(x.buttonText) + ' ');
          a.href = safeLink(x.buttonLink, 'booking');
          if (/^https?:/i.test(a.getAttribute('href'))) { a.target = '_blank'; a.rel = 'noopener'; }
          a.appendChild(icon('arrow'));
          slide.appendChild(a);
        }
        stack.appendChild(slide);
      });
      inner.appendChild(stack);
      inner.appendChild(lotusRule());
      box.appendChild(inner);
      if (slides.length > 1) rotateSlides(stack);
    });
  }

  /* Heading text with the orange words; Enter in the admin starts a new line */
  function titleLines(h, title, accent) {
    title.split('\n').forEach(function (line, i) {
      if (i) h.appendChild(el('br'));
      var at = accent ? line.indexOf(accent) : -1;
      if (at < 0) { h.appendChild(document.createTextNode(line)); return; }
      h.appendChild(document.createTextNode(line.slice(0, at)));
      h.appendChild(el('span', 'sd-accent', accent));
      h.appendChild(document.createTextNode(line.slice(at + accent.length)));
    });
  }

  /* Every 4 seconds the next banner text rises in; after the last it starts again */
  var SLIDE_DELAY = 4000;
  function rotateSlides(stack) {
    var slides = stack.children, idx = 0;
    setInterval(function () {
      if (document.hidden) return;
      var old = slides[idx];
      idx = (idx + 1) % slides.length;
      Array.prototype.forEach.call(slides, function (sl) { sl.classList.remove('is-out'); });
      old.classList.remove('is-on');
      old.classList.add('is-out');
      old.setAttribute('aria-hidden', 'true');
      slides[idx].classList.add('is-on');
      slides[idx].removeAttribute('aria-hidden');
    }, SLIDE_DELAY);
  }

  function renderAbout(s) {
    fill('sd-about', function (box) {
      var texts = [s.aboutText1, s.aboutText2].map(str).filter(Boolean);
      var shloka = s.showShloka && str(s.shloka);
      if (!str(s.aboutTitle) && !texts.length) return false;
      var wrap = el('div', 'container sd-about');
      var text = el('div', 'sd-about__text');
      if (str(s.aboutLabel)) text.appendChild(el('p', 'sd-label', str(s.aboutLabel)));
      if (str(s.aboutTitle)) text.appendChild(el('h2', 'sd-title', str(s.aboutTitle)));
      texts.forEach(function (t) { text.appendChild(el('p', 'sd-about__para', t)); });
      if (shloka) {
        text.appendChild(lotusRule());
        var q = lines(el('p', 'sd-shloka'), s.shloka);
        q.lang = 'sa';
        text.appendChild(q);
      }
      wrap.appendChild(text);
      var img = image(s.aboutImage, s.aboutImageAlt);
      if (img) {
        var fig = el('div', 'sd-about__media');
        fig.appendChild(img);
        wrap.appendChild(fig);
      } else {
        wrap.classList.add('sd-about--solo');
      }
      box.appendChild(wrap);
    });
  }

  function renderProcess(s, data) {
    var p = data.process || {};
    var head = document.getElementById('sd-process-head');
    if (!head) return;
    head.textContent = '';
    head.appendChild(heading('h2', str(p.title) || 'Our', p.title || p.titleAccent ? p.titleAccent : 'Process', 'sd-title'));
    var sub = str(s.processSubtitle) || str(p.subtitle);
    if (sub) head.appendChild(el('p', 'sd-sub', sub.replace(/\{service\}/g, str(s.title))));

    /* Steps of this service only; otherwise the shared steps written in the page stay */
    var steps = list(s.processSteps).filter(function (x) { return str(x.title); });
    var ol = document.querySelector('#sd-process .steps');
    if (!steps.length || !ol) return;
    ol.textContent = '';
    steps.forEach(function (x, i) {
      var li = el('li', 'step card');
      var badge = el('span', 'badge');
      badge.appendChild(icon(x.icon));
      li.appendChild(badge);
      li.appendChild(el('span', 'step__num', isV2(s) ? String(i + 1) : (i < 9 ? '0' : '') + (i + 1)));
      li.appendChild(el('h3', null, str(x.title)));
      if (str(x.text)) li.appendChild(el('p', null, str(x.text)));
      ol.appendChild(li);
    });
  }

  function renderReceive(s) {
    fill('sd-receive', function (box) {
      var cards = list(s.receive).filter(function (c) { return str(c.title); });
      if (!cards.length) return false;
      var wrap = el('div', 'container');
      var head = el('div', 'sd-head');
      head.appendChild(heading('h2', s.receiveTitle, s.receiveAccent, 'sd-title'));
      if (str(s.receiveSubtitle)) head.appendChild(el('p', 'sd-sub', str(s.receiveSubtitle)));
      wrap.appendChild(head);
      var grid = el('ul', 'sd-receive');
      cards.forEach(function (c) {
        var li = el('li', 'sd-receive__card');
        var media = el('div', 'sd-receive__media');
        var img = image(c.image, c.imageAlt);
        if (img) media.appendChild(img); else media.classList.add('is-empty');
        li.appendChild(media);
        var body = el('div', 'sd-receive__body');
        var ic = el('span', 'sd-receive__icon');
        ic.appendChild(icon(c.icon));
        body.appendChild(ic);
        var t = el('div');
        t.appendChild(el('h3', null, str(c.title)));
        if (str(c.text)) t.appendChild(el('p', null, str(c.text)));
        body.appendChild(t);
        li.appendChild(body);
        grid.appendChild(li);
      });
      wrap.appendChild(grid);
      box.appendChild(wrap);
    });
  }

  function whyBlock(s) {
    var items = list(s.why).filter(function (b) { return str(b.title); });
    if (!items.length) return null;
    var col = el('div', 'sd-why');
    col.appendChild(heading('h2', s.whyTitle, s.whyAccent, 'sd-title'));
    var grid = el('ul', 'sd-why__grid');
    items.forEach(function (b) {
      var li = el('li', 'sd-why__item');
      var ic = el('span', 'sd-why__icon');
      ic.appendChild(icon(b.icon));
      li.appendChild(ic);
      var t = el('div');
      t.appendChild(el('h3', null, str(b.title)));
      if (str(b.text)) t.appendChild(el('p', null, str(b.text)));
      li.appendChild(t);
      grid.appendChild(li);
    });
    col.appendChild(grid);
    return col;
  }

  function whoBlock(s) {
    var points = list(s.whoList).filter(function (w) { return str(w.text); });
    if (!points.length && !str(s.whoIntro) && !str(s.whoClosing)) return null;
    var col = el('div', 'sd-who');
    col.appendChild(heading('h2', s.whoTitle, s.whoAccent, 'sd-title'));
    if (str(s.whoIntro)) col.appendChild(el('p', 'sd-who__intro', str(s.whoIntro)));
    if (points.length) {
      var ul = el('ul', 'sd-who__list');
      points.forEach(function (w) {
        var li = el('li');
        li.appendChild(icon('check'));
        li.appendChild(el('span', null, str(w.text)));
        ul.appendChild(li);
      });
      col.appendChild(ul);
    }
    if (str(s.whoClosing)) col.appendChild(el('p', 'sd-who__closing', str(s.whoClosing)));
    return col;
  }

  function placesBlock(s) {
    var places = list(s.places).filter(function (p) { return str(p.name); });
    if (!s.showPlaces || !places.length) return null;
    var col = el('div', 'sd-places');
    var head = el('div', 'sd-places__head');
    var titles = el('div');
    titles.appendChild(el('h2', 'sd-title', str(s.placesTitle) || 'Sacred Places'));
    if (str(s.placesSubtitle)) titles.appendChild(el('p', 'sd-sub', str(s.placesSubtitle)));
    head.appendChild(titles);
    var nav = el('div', 'sd-places__nav');
    var prev = el('button', 'sd-arrow sd-arrow--prev');
    var next = el('button', 'sd-arrow sd-arrow--next');
    [prev, next].forEach(function (b, i) {
      b.type = 'button';
      b.setAttribute('aria-label', i ? 'Next places' : 'Previous places');
      b.appendChild(icon('arrow'));
      nav.appendChild(b);
    });
    head.appendChild(nav);
    col.appendChild(head);
    if (str(s.placesIntro)) col.appendChild(el('p', 'sd-places__intro', str(s.placesIntro)));

    var track = el('ul', 'sd-places__track');
    track.setAttribute('tabindex', '0');
    track.setAttribute('aria-label', str(s.placesTitle) || 'Sacred Places');
    places.forEach(function (p) {
      var li = el('li', 'sd-place');
      var media = el('div', 'sd-place__media');
      var img = image(p.image, p.imageAlt || p.name);
      if (img) media.appendChild(img); else media.classList.add('is-empty');
      li.appendChild(media);
      var body = el('div', 'sd-place__body');
      body.appendChild(el('h3', null, str(p.name)));
      if (str(p.location)) body.appendChild(el('p', 'sd-place__loc', str(p.location)));
      if (str(p.text)) body.appendChild(el('p', 'sd-place__text', str(p.text)));
      li.appendChild(body);
      track.appendChild(li);
    });
    if (isV2(s)) {
      /* new design: arrows at both ends of the row */
      var row = el('div', 'sd-places__row');
      row.appendChild(prev);
      row.appendChild(track);
      row.appendChild(next);
      col.appendChild(row);
    } else {
      col.appendChild(track);
    }
    if (str(s.placesNote)) col.appendChild(el('p', 'sd-places__note', str(s.placesNote)));

    /* one card at a time; in the new design a whole screen of cards (the next 4) */
    function step() {
      var card = track.querySelector('.sd-place');
      var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
      if (isV2(s)) return track.clientWidth + gap;
      return card ? card.getBoundingClientRect().width + gap : track.clientWidth;
    }
    function update() {
      var max = track.scrollWidth - track.clientWidth - 2;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= max;
    }
    prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
    track.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
    window.addEventListener('resize', update);
    setTimeout(update, 0);
    window.addEventListener('load', update);
    return col;
  }

  function faqBlock(s) {
    var faqs = list(s.faqs).filter(function (f) { return str(f.q) && str(f.a); });
    if (!faqs.length) return null;
    var col = el('div', 'sd-faq');
    col.appendChild(heading('h2', str(s.faqTitle) || 'Frequently Asked Questions', s.faqAccent, 'sd-title'));
    var box = el('div', 'faq sd-faq__list');
    faqs.forEach(function (f, i) {
      var d = el('details');
      var sum = el('summary');
      sum.appendChild(el('span', 'sd-faq__num', isV2(s) ? (i + 1) + '.' : (i < 9 ? '0' : '') + (i + 1)));
      sum.appendChild(el('span', 'sd-faq__q', str(f.q)));
      d.appendChild(sum);
      d.appendChild(lines(el('p'), f.a));
      box.appendChild(d);
    });
    col.appendChild(box);
    return col;
  }

  /* Two blocks side by side; one alone takes the full width */
  function pair(id, left, right) {
    fill(id, function (box) {
      if (!left && !right) return false;
      var wrap = el('div', 'container sd-pair' + (left && right ? '' : ' sd-pair--single'));
      if (left) wrap.appendChild(left);
      if (right) wrap.appendChild(right);
      box.appendChild(wrap);
    });
  }

  function renderCta(s) {
    fill('sd-cta', function (box) {
      if (!str(s.ctaTitle)) return false;
      /* new design: the banner is a rounded card inside the page width */
      if (isV2(s)) {
        var outer = el('div', 'container');
        var card = el('div', 'sd-cta__card');
        outer.appendChild(card);
        box.appendChild(outer);
        box = card;
      }
      var media = el('div', 'sd-cta__media');
      media.setAttribute('aria-hidden', 'true');
      var img = image(s.ctaImage, '');
      if (img) media.appendChild(img);
      box.appendChild(media);
      var inner = el('div', (isV2(s) ? '' : 'container ') + 'sd-cta__inner');
      inner.appendChild(el('h2', 'sd-cta__title', str(s.ctaTitle)));
      if (str(s.ctaText)) inner.appendChild(el('p', 'sd-cta__text', str(s.ctaText)));
      if (str(s.ctaButton)) {
        var a = el('a', 'btn btn--primary sd-cta__btn', str(s.ctaButton) + ' ');
        a.href = safeLink(s.ctaLink, 'booking');
        if (/^https?:/i.test(a.getAttribute('href'))) { a.target = '_blank'; a.rel = 'noopener'; }
        a.appendChild(icon('arrow'));
        inner.appendChild(a);
      }
      box.appendChild(inner);
    });
  }

  /* Shown for a draft page or an unknown address */
  function renderNotice(title, text, href, button) {
    fill('sd-notice', function (box) {
      var wrap = el('div', 'container sd-notice');
      wrap.appendChild(el('h2', 'sd-title', title));
      if (text) wrap.appendChild(el('p', null, text));
      var a = el('a', 'btn btn--primary', button + ' ');
      a.href = href;
      a.appendChild(icon('arrow'));
      wrap.appendChild(a);
      box.appendChild(wrap);
    });
  }

  function renderDetail(data) {
    var slug = '';
    try { slug = (new URLSearchParams(location.search).get('name') || '').toLowerCase(); } catch (e) {}
    var items = data ? list(data.items) : [];
    var s = items.filter(function (x) { return str(x.slug) === slug && x.show !== false; })[0];
    var fallbackImage = data && data.banner ? data.banner.image : '';

    if (!s) {
      document.title = 'Service not found | ' + SITE;
      renderBanner({ title: 'Our Services' }, fallbackImage);
      renderNotice('This service page could not be found', 'Please choose a service from the list of our services.', 'services', 'View all services');
      show(document.getElementById('sd-process'), false);
      return;
    }

    applySeo(s);
    document.body.classList.toggle('sd-v2', isV2(s));
    renderBanner(s, fallbackImage);
    renderProcess(s, data);

    if (s.status !== 'published') {
      renderNotice('Full details are coming soon', str(s.text), safeLink(s.link, 'booking'), 'Enquire about this service');
      return;
    }
    renderAbout(s);
    renderReceive(s);
    if (isV2(s)) {
      /* new design: the scrolling cards right after the process, Why Choose across the full
         width, then Who Can Use beside the FAQs */
      pair('sd-places', placesBlock(s), null);
      pair('sd-why-who', whyBlock(s), null);
      pair('sd-places-faq', whoBlock(s), faqBlock(s));
    } else {
      pair('sd-why-who', whyBlock(s), whoBlock(s));
      pair('sd-places-faq', placesBlock(s), faqBlock(s));
    }
    renderCta(s);
  }

  /* Admin → Services → “Page design”: the newer layout, chosen per service */
  function isV2(s) { return s && s.design === 'v2'; }

  function start() {
    var mode = document.body.getAttribute('data-services');
    if (mode === 'list') loadServices(renderList);
    if (mode === 'detail') loadServices(function (data) {
      renderDetail(data);
      document.body.classList.add('is-ready');
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

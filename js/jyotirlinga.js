/* =====================================================================
   JYOTIRLINGA PAGES — one template for every Jyotirlinga:
     jyotirlinga.html?name=<slug>   (e.g. jyotirlinga?name=somnath)
   Content: content/content.json → "jyotirlingas" (Admin → 12 Jyotirlinga pages).
   Until the admin has saved it, content/jyotirlinga.default.json is used instead.
   Sections left empty in the admin are not shown.
   Load after js/components.js and before js/script.js.
   ===================================================================== */

(function () {
  'use strict';

  /* ---------- Icons drawn only on this page (same style as js/components.js) ---------- */
  var EXTRA_ICONS = {
    trishul: '<path d="M12 4v17"/><path d="M12 2.5l-1.8 3h3.6z"/><path d="M5.5 5c0 4.2 2.6 6.5 6.5 6.5s6.5-2.3 6.5-6.5"/><path d="M5.5 5L4.4 7M5.5 5l1.1 2M18.5 5l-1.1 2M18.5 5l1.1 2"/><path d="M9.5 14.5h5"/>',
    car: '<path d="M4 16.5v-4l2.2-4.8h11.6l2.2 4.8v4z"/><path d="M4 12.5h16"/><circle cx="7.8" cy="16.5" r="1.7"/><circle cx="16.2" cy="16.5" r="1.7"/>'
  };
  (function injectExtraIcons() {
    var symbols = Object.keys(EXTRA_ICONS).map(function (k) {
      return '<symbol id="i-' + k + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' + EXTRA_ICONS[k] + '</symbol>';
    }).join('');
    var wrap = document.createElement('div');
    wrap.setAttribute('aria-hidden', 'true');
    wrap.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden';
    wrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg">' + symbols + '</svg>';
    document.body.insertBefore(wrap, document.body.firstChild);
  })();

  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };
  var SITE = 'Hindu.International';
  var LIST_PAGE = 'service?name=12-jyotirlinga-darshan-puja';

  /* ---------- Small DOM helpers (all text is inserted as text, never as HTML) ---------- */
  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null && text !== '') node.textContent = text;
    return node;
  }
  function str(v) { return v == null ? '' : String(v).trim(); }
  function list(v) { return Array.isArray(v) ? v : []; }
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
  function image(path, alt, eager) {
    if (!isMedia(path)) return null;
    var img = el('img');
    /* phones get a smaller copy made by media.php; wider screens keep the original */
    if (/\.(jpe?g|png|webp)$/i.test(path)) {
      img.sizes = '(max-width: 768px) 100vw, 1400px';
      img.srcset = [480, 828, 1200].map(function (w) {
        return 'media.php?src=' + encodeURIComponent(path) + '&w=' + w + ' ' + w + 'w';
      }).concat(path + ' 1600w').join(', ');
    }
    img.src = path;
    img.alt = str(alt);
    if (!eager) img.loading = 'lazy';
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
  /* Gold line — trishul — gold line, under every heading */
  function divider() {
    var d = el('div', 'jl-divider');
    d.setAttribute('aria-hidden', 'true');
    d.appendChild(icon('trishul'));
    return d;
  }
  function head(title, text) {
    var box = el('div', 'jl-head');
    box.appendChild(el('h2', 'jl-title', str(title)));
    box.appendChild(divider());
    if (str(text)) box.appendChild(el('p', 'jl-lead', str(text)));
    return box;
  }
  /* The enquiry button opens the Contact form with the temple's name filled in */
  function enquiryLink(link, name) {
    var href = safeLink(link, 'contact');
    if (/^contact-us(#|$)/.test(href) && name) href = 'contact-us?enquiry=' + encodeURIComponent(name) + '#enquiry';
    return href;
  }
  function button(text, link, name) {
    if (!str(text)) return null;
    var a = el('a', 'jl-btn', str(text));
    a.href = enquiryLink(link, name);
    a.appendChild(icon('arrow'));
    return a;
  }
  function fill(id, build) {
    var box = document.getElementById(id);
    if (!box) return;
    box.textContent = '';
    var shown = build(box) !== false;
    box.hidden = !shown;
  }

  /* ---------- Content: admin first, then the default file (same rules as ContentStore::load) ---------- */
  function fillFromDefaults(c, d) {
    var known = Array.isArray(c.knownDefaults) ? c.knownDefaults : null;
    var saved = {};
    list(c.items).forEach(function (j) { saved[str(j.slug)] = true; });
    list(d && d.items).forEach(function (x) {
      var slug = str(x.slug);
      if (slug && !saved[slug] && (!known || known.indexOf(slug) < 0)) c.items.push(x);
    });
    return c;
  }
  function load(done) {
    var c = (window.SITE_CONTENT || {}).jyotirlingas;
    var get = window.fetch
      ? fetch('content/jyotirlinga.default.json', { cache: 'no-cache' }).then(function (r) { return r.ok ? r.json() : null; })
      : Promise.resolve(null);
    get.then(function (d) {
      if (c && Array.isArray(c.items)) return done(fillFromDefaults({ items: c.items.slice(), knownDefaults: c.knownDefaults }, d));
      done(d && Array.isArray(d.items) ? d : { items: [] });
    }, function () { done(c && Array.isArray(c.items) ? c : { items: [] }); });
  }

  /* ---------- SEO for the chosen temple ---------- */
  function setMeta(selector, attr, value) {
    var node = document.head.querySelector(selector);
    if (!node && value && selector.indexOf('link') === 0) {
      node = document.createElement('link');
      node.rel = 'canonical';
      document.head.appendChild(node);
    }
    if (node && value) node.setAttribute(attr, value);
  }
  function applySeo(j) {
    var title = str(j.seoTitle) || (str(j.name) + ' | ' + SITE);
    var desc = str(j.seoDescription) || str(j.heroText) || str(j.aboutText1);
    var url = location.origin + location.pathname.replace(/\.html$/, '') + '?name=' + encodeURIComponent(str(j.slug));
    document.title = title;
    setMeta('meta[name="description"]', 'content', desc);
    setMeta('meta[property="og:title"]', 'content', title);
    setMeta('meta[property="og:description"]', 'content', desc);
    setMeta('link[rel="canonical"]', 'href', url);
  }

  /* =================================================================
     SECTIONS — in the order of the page
     ================================================================= */
  function renderHero(j) {
    fill('jl-hero', function (box) {
      var media = el('div', 'jl-hero__media');
      var img = image(j.heroImage, j.heroImageAlt, true);
      if (img) { img.setAttribute('fetchpriority', 'high'); media.appendChild(img); }
      box.appendChild(media);

      var inner = el('div', 'container jl-hero__inner');

      var h1 = lines(el('h1', 'jl-hero__title'), str(j.heroTitle) || str(j.name));
      h1.id = 'page-title';
      inner.appendChild(h1);
      if (str(j.heroSubtitle)) inner.appendChild(el('p', 'jl-hero__sub', str(j.heroSubtitle)));
      inner.appendChild(divider());
      if (str(j.heroText)) inner.appendChild(el('p', 'jl-hero__text', str(j.heroText)));
      var b = button(j.heroButton, j.heroLink, j.name);
      if (b) { b.classList.add('jl-btn--lg'); inner.appendChild(b); }
      box.appendChild(inner);
    });
  }

  function renderAbout(j) {
    fill('jl-about', function (box) {
      if (!str(j.aboutTitle) && !str(j.aboutText1)) return false;
      var inner = el('div', 'container jl-about__inner');
      var text = el('div', 'jl-about__text');
      text.appendChild(head(j.aboutTitle));
      [j.aboutText1, j.aboutText2].forEach(function (p) { if (str(p)) text.appendChild(el('p', '', str(p))); });
      inner.appendChild(text);
      var img = image(j.aboutImage, j.aboutImageAlt);
      if (img) { var fig = el('figure', 'jl-about__media'); fig.appendChild(img); inner.appendChild(fig); }
      box.appendChild(inner);
    });
  }

  function renderSeva(j) {
    fill('jl-seva', function (box) {
      var cards = list(j.seva).filter(function (c) { return str(c.title); });
      if (!str(j.sevaTitle) && !cards.length) return false;
      var img = image(j.sevaImage, j.sevaImageAlt);
      if (img) { var media = el('div', 'jl-seva__media'); media.appendChild(img); box.appendChild(media); }
      box.classList.toggle('has-media', !!img);
      var inner = el('div', 'container jl-seva__inner');
      inner.appendChild(head(j.sevaTitle, j.sevaText));
      if (cards.length) {
        var grid = el('ul', 'jl-seva__cards');
        cards.forEach(function (c) {
          var li = el('li', 'jl-card');
          var ic = el('span', 'jl-card__icon');
          ic.appendChild(icon(c.icon));
          li.appendChild(ic);
          li.appendChild(el('h3', 'jl-card__title', str(c.title)));
          if (str(c.text)) li.appendChild(el('p', 'jl-card__text', str(c.text)));
          grid.appendChild(li);
        });
        inner.appendChild(grid);
      }
      box.appendChild(inner);
    });
  }

  function renderVisit(j) {
    fill('jl-visit', function (box) {
      var rows = [
        ['pin', 'Location', str(j.location), ''],
        ['clock', 'Temple Timings', str(j.timings), str(j.timingsNote)],
        ['car', 'How to Reach', str(j.reach), '']
      ].filter(function (r) { return r[2]; });
      if (!rows.length) return false;
      var inner = el('div', 'container');
      inner.appendChild(head(j.visitTitle || 'Temple Visit Information'));
      var grid = el('div', 'jl-visit__grid');
      var card = el('ul', 'jl-visit__card');
      rows.forEach(function (r) {
        var li = el('li');
        var ic = el('span', 'jl-visit__icon');
        ic.appendChild(icon(r[0]));
        li.appendChild(ic);
        var t = el('div');
        t.appendChild(el('h3', 'jl-visit__label', r[1]));
        t.appendChild(lines(el('p', 'jl-visit__value'), r[2]));
        if (r[3]) t.appendChild(el('p', 'jl-visit__note', r[3]));
        li.appendChild(t);
        card.appendChild(li);
      });
      grid.appendChild(card);
      var img = image(j.visitImage, j.visitImageAlt);
      if (img) { var fig = el('figure', 'jl-visit__media'); fig.appendChild(img); grid.appendChild(fig); }
      inner.appendChild(grid);
      box.appendChild(inner);
    });
  }

  function renderFaq(j) {
    fill('jl-faq', function (box) {
      var faqs = list(j.faqs).filter(function (f) { return str(f.q) && str(f.a); });
      if (!faqs.length) return false;
      var inner = el('div', 'container');
      inner.appendChild(head(j.faqTitle || 'Frequently Asked Questions'));
      /* two columns that fill top to bottom, like the reference (1–3 left, 4–6 right) */
      var half = Math.ceil(faqs.length / 2);
      var grid = el('div', 'jl-faq__grid');
      [faqs.slice(0, half), faqs.slice(half)].forEach(function (col, c) {
        var column = el('div', 'jl-faq__col');
        col.forEach(function (f, i) {
          var d = el('details', 'jl-faq__item');
          var s = el('summary');
          s.appendChild(el('span', 'jl-faq__num', (c * half + i + 1) + '.'));
          s.appendChild(el('span', 'jl-faq__q', str(f.q)));
          var plus = el('span', 'jl-faq__plus');
          plus.setAttribute('aria-hidden', 'true');
          s.appendChild(plus);
          d.appendChild(s);
          d.appendChild(el('p', 'jl-faq__a', str(f.a)));
          column.appendChild(d);
        });
        grid.appendChild(column);
      });
      inner.appendChild(grid);
      box.appendChild(inner);
    });
  }

  function renderCta(j) {
    fill('jl-cta', function (box) {
      if (!str(j.ctaTitle) && !str(j.ctaAccent)) return false;
      var img = image(j.ctaImage, '');
      if (img) { var media = el('div', 'jl-cta__media'); media.setAttribute('aria-hidden', 'true'); media.appendChild(img); box.appendChild(media); }
      var om = el('div', 'jl-cta__om');
      om.setAttribute('aria-hidden', 'true');
      box.appendChild(om);
      var inner = el('div', 'container jl-cta__inner');
      var h = el('h2', 'jl-cta__title');
      if (str(j.ctaTitle)) h.appendChild(document.createTextNode(str(j.ctaTitle)));
      if (str(j.ctaAccent)) h.appendChild(el('span', 'jl-cta__accent', str(j.ctaAccent)));
      inner.appendChild(h);
      if (str(j.ctaText)) inner.appendChild(el('p', 'jl-cta__text', str(j.ctaText)));
      var b = button(j.ctaButton, j.ctaLink, j.name);
      if (b) inner.appendChild(b);
      box.appendChild(inner);
    });
  }

  function renderNotFound() {
    document.title = 'Jyotirlinga page not found | ' + SITE;
    fill('jl-hero', function (box) {
      var inner = el('div', 'container jl-hero__inner');
      var h1 = el('h1', 'jl-hero__title', 'This page could not be found');
      h1.id = 'page-title';
      inner.appendChild(h1);
      inner.appendChild(divider());
      inner.appendChild(el('p', 'jl-hero__text', 'Please choose a Jyotirlinga from the list of all twelve.'));
      var a = el('a', 'jl-btn jl-btn--lg', 'View the 12 Jyotirlingas');
      a.href = LIST_PAGE;
      a.appendChild(icon('arrow'));
      inner.appendChild(a);
      box.appendChild(inner);
    });
    document.getElementById('jl-hero').classList.add('jl-hero--plain');
  }

  function render(data) {
    var slug = '';
    try { slug = (new URLSearchParams(location.search).get('name') || '').toLowerCase(); } catch (e) {}
    var j = list(data.items).filter(function (x) { return str(x.slug) === slug && x.show !== false; })[0];
    if (!j) return renderNotFound();
    applySeo(j);
    renderHero(j);
    renderAbout(j);
    renderSeva(j);
    renderVisit(j);
    renderFaq(j);
    renderCta(j);
  }

  function start() { load(function (data) { render(data); document.body.classList.add('is-ready'); }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

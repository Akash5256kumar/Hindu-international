/* =====================================================================
   JAAP & PAATH PAGES — one template for every service:
     /<slug>   (e.g. /mahamrityunjay-jaap; jaap.html?name=<slug> still works)
   Content: content/content.json → "jaapServices" (Admin → Jaap & Paath pages).
   Until the admin has saved it, content/jaap.default.json is used instead.
   Sections left empty in the admin are not shown.
   Load after js/components.js and before js/script.js.
   ===================================================================== */

(function () {
  'use strict';

  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };
  var SITE = 'Hindu.International';
  var LIST_PAGE = 'jaap-path-anushthan';
  /* Page addresses: /somnath-jyotirlinga, /mahamrityunjay-jaap (old ?name= links are converted) */
  var itemSlug = window.vpsItemSlug || function (t, x) { return String(x || '').toLowerCase(); };
  function currentSlug(type) {
    var q = '';
    try { q = new URLSearchParams(location.search).get('name') || ''; } catch (e) {}
    if (q) return itemSlug(type, q);
    var p = location.pathname.replace(/^\/+|\/+$/g, '').replace(/\.html$/i, '').toLowerCase();
    return /^[a-z0-9-]+$/.test(p) && p !== type ? p : '';
  }

  /* ---------- Small DOM helpers ---------- */
  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null && text !== '') node.textContent = text;
    return node;
  }
  function str(v) { return v == null ? '' : String(v).trim(); }
  function list(v) { return Array.isArray(v) ? v : []; }
  function isMedia(path) { return /^(assets|uploads)\/[\w\-./]+$/.test(path || '') && path.indexOf('..') === -1; }

  /* Line icons (24×24, drawn with the text colour). Unknown names fall back to the lotus. */
  var ICONS = {
    shield: '<path d="M12 2.8 19 5.6v5.6c0 4.6-3 8.4-7 10-4-1.6-7-5.4-7-10V5.6z"/><path d="M12 7.6c-1.5 1.5-2.3 3-2.3 4.6 0 1.5 1 2.6 2.3 2.6s2.3-1.1 2.3-2.6c0-1.6-.8-3.1-2.3-4.6zM12 14.8v2.6"/>',
    heart: '<path d="M12 20s-7.6-4.6-7.6-10.3A4.3 4.3 0 0 1 12 7a4.3 4.3 0 0 1 7.6 2.7C19.6 15.4 12 20 12 20z"/><path d="M5.6 12.6h3.1l1.4-2.6 2.1 4.6 1.5-2h4.6"/>',
    lotus: '<path d="M12 5.8c-1.8 2-2.7 4.1-2.7 6.3 0 2.6 1.2 4.6 2.7 5.9 1.5-1.3 2.7-3.3 2.7-5.9 0-2.2-.9-4.3-2.7-6.3z"/><path d="M9.4 10.9C7.7 9.8 5.8 9.4 3.8 9.6c.2 3.7 2.4 7.1 6.6 8.2M14.6 10.9c1.7-1.1 3.6-1.5 5.6-1.3-.2 3.7-2.4 7.1-6.6 8.2"/><path d="M3.8 18.6c2.5 1 5.2 1.2 8.2 0 3 1.2 5.7 1 8.2 0"/>',
    sprout: '<path d="M12 21v-9.5"/><path d="M12 11.5C12 8 9.6 5.5 5.4 5.5c0 3.8 2.6 6 6.6 6z"/><path d="M12 14c0-3.6 2.5-6.6 6.6-6.6 0 4-2.6 6.6-6.6 6.6z"/><path d="M8.2 21h7.6"/>',
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2.6v2.5M12 18.9v2.5M2.6 12h2.5M18.9 12h2.5M5.4 5.4l1.8 1.8M16.8 16.8l1.8 1.8M5.4 18.6l1.8-1.8M16.8 7.2l1.8-1.8"/>',
    infinity: '<path d="M12 12c-1.9-2.5-3.5-3.7-5.1-3.7a3.7 3.7 0 0 0 0 7.4c1.6 0 3.2-1.2 5.1-3.7zm0 0c1.9 2.5 3.5 3.7 5.1 3.7a3.7 3.7 0 0 0 0-7.4c-1.6 0-3.2 1.2-5.1 3.7z"/>',
    family: '<circle cx="12" cy="6.6" r="2.5" fill="currentColor"/><circle cx="5.6" cy="8.8" r="2.1" fill="currentColor"/><circle cx="18.4" cy="8.8" r="2.1" fill="currentColor"/><path d="M7.9 19.6v-3.8a4.1 4.1 0 0 1 8.2 0v3.8zM2.4 18.6v-2.4a3.2 3.2 0 0 1 4.9-2.7M21.6 18.6v-2.4a3.2 3.2 0 0 0-4.9-2.7" fill="currentColor"/>',
    user: '<circle cx="12" cy="7.6" r="4" fill="currentColor"/><path d="M4.2 21v-1.2a6.6 6.6 0 0 1 6.6-6.6h2.4a6.6 6.6 0 0 1 6.6 6.6V21z" fill="currentColor"/>',
    monitor: '<rect x="3" y="4" width="18" height="12.4" rx="1.6"/><path d="M8.6 20.4h6.8M12 16.4v4"/><path d="M10.4 7.8v5.2l4.2-2.6z" fill="currentColor"/>',
    calendar: '<rect x="3.6" y="5" width="16.8" height="15.6" rx="2"/><path d="M3.6 9.6h16.8M8 3v4M16 3v4"/><path d="M7.6 13h1.8M11.1 13h1.8M14.6 13h1.8M7.6 16.6h1.8M11.1 16.6h1.8M14.6 16.6h1.8"/>',
    form: '<path d="M6 3h8.6L19 7.4V21H6z"/><path d="M14.2 3v4.8H19"/><path d="M9 11.6h7M9 14.8h7M9 18h4.4"/>',
    chat: '<path d="M4 4.8h16v11.4h-9.6L5.6 19.8v-3.6H4z"/><path d="M8.6 10.5h.01M12 10.5h.01M15.4 10.5h.01" stroke-width="2.6"/>',
    check: '<path d="M20 10.6A8.4 8.4 0 1 1 15.6 4.4"/><path d="M8.2 11.6l3.2 3.2 8.8-9.6"/>',
    trishul: '<path d="M12 6v15.5"/><path d="M12 2.4l-1.5 3h3z" fill="currentColor"/><path d="M6 4.6c-.3 4.2 1.6 6.9 6 6.9s6.3-2.7 6-6.9"/><path d="M6 4.6 4.9 6.2M6 4.6l1.3 1.3M18 4.6l1.1 1.6M18 4.6l-1.3 1.3"/><path d="M9.6 14.2h4.8"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    plus: '<path d="M12 5v14M5 12h14"/>'
  };
  function icon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var wrap = document.createElement('div');
    wrap.innerHTML = '<svg xmlns="' + ns + '" viewBox="0 0 24 24" class="jp-icon" aria-hidden="true" focusable="false">' +
      (ICONS[name] || ICONS.lotus) + '</svg>';
    return wrap.firstChild;
  }
  function image(path, alt, eager) {
    if (!isMedia(path)) return null;
    var img = el('img');
    if (/\.(jpe?g|png|webp)$/i.test(path)) {
      img.sizes = '(max-width: 900px) 100vw, 55vw';
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
  function lines(node, value) {
    str(value).split('\n').forEach(function (line, i) {
      if (i) node.appendChild(el('br'));
      node.appendChild(document.createTextNode(line));
    });
    return node;
  }
  /* line — trishul — line */
  function rule() {
    var d = el('div', 'jp-rule');
    d.setAttribute('aria-hidden', 'true');
    d.appendChild(icon('trishul'));
    return d;
  }
  /* Heading with the trishul line under it */
  function headWithRule(text, id) {
    var head = el('div', 'jp-head');
    var h = el('h2', 'jp-title', text);
    if (id) h.id = id;
    head.appendChild(h);
    head.appendChild(rule());
    return head;
  }
  /* Heading with the trishul beside it */
  function headWithMark(text, id) {
    var h = el('h2', 'jp-title jp-title--mark');
    if (id) h.id = id;
    h.appendChild(el('span', null, text));
    h.appendChild(icon('trishul'));
    return h;
  }
  function fill(id, build) {
    var box = document.getElementById(id);
    if (!box) return;
    box.textContent = '';
    var shown = build(box) !== false;
    box.hidden = !shown;
  }

  /* ---------- Content: admin first, then the default file ---------- */
  /* A page saved before the current design (no "heroText" field yet) takes its content from the
     default file; its on/off switch, address and SEO texts stay as saved. */
  var KEEP = ['show', 'slug', 'name', 'seoTitle', 'seoDescription'];
  function upgrade(item, defaults) {
    if (!item || Object.prototype.hasOwnProperty.call(item, 'heroText')) return item;
    var d = list(defaults && defaults.items).filter(function (x) { return str(x.slug) === str(item.slug); })[0];
    if (!d) return item;
    var out = {};
    Object.keys(d).forEach(function (k) { out[k] = d[k]; });
    KEEP.forEach(function (k) { if (item[k] != null && item[k] !== '') out[k] = item[k]; });
    return out;
  }
  function fillFromDefaults(c, d) {
    var known = Array.isArray(c.knownDefaults) ? c.knownDefaults : null;
    var saved = {};
    c.items = c.items.map(function (j) { saved[str(j.slug)] = true; return upgrade(j, d); });
    list(d && d.items).forEach(function (x) {
      var slug = str(x.slug);
      if (slug && !saved[slug] && (!known || known.indexOf(slug) < 0)) c.items.push(x);
    });
    return c;
  }
  function load(done) {
    var c = (window.SITE_CONTENT || {}).jaapServices;
    var get = window.fetch
      ? fetch('content/jaap.default.json', { cache: 'no-cache' }).then(function (r) { return r.ok ? r.json() : null; })
      : Promise.resolve(null);
    get.then(function (d) {
      if (c && Array.isArray(c.items)) return done(fillFromDefaults({ items: c.items.slice(), knownDefaults: c.knownDefaults }, d));
      done(d && Array.isArray(d.items) ? d : { items: [] });
    }, function () { done(c && Array.isArray(c.items) ? c : { items: [] }); });
  }

  /* ---------- SEO (js/page-seo.js) ---------- */
  function applySeo(j) {
    if (!window.vpsPageSeo) return;
    var origin = window.vpsSeoOrigin;
    var title = str(j.seoTitle) || (str(j.name) + ' Online Booking | ' + SITE);
    var desc = str(j.seoDescription) || str(j.heroText) || str(j.heroSubtitle);
    var url = origin + '/' + itemSlug('jaap', j.slug);
    window.vpsPageSeo({
      title: title,
      description: desc,
      url: url,
      image: j.heroImage,
      breadcrumb: [
        ['Home', origin + '/'],
        ['Services', origin + '/services'],
        ['Jaap & Path Anushthan', origin + '/' + LIST_PAGE],
        [str(j.name), url]
      ],
      entity: { '@type': 'Service', name: str(j.name), serviceType: 'Hindu Jaap and Paath', areaServed: 'Worldwide' },
      faqs: list(j.jaapFaqs)
    });
  }

  /* =================================================================
     SECTIONS — in page order
     ================================================================= */
  function renderHero(j) {
    fill('jp-hero', function (box) {
      var img = image(j.heroImage, j.heroImageAlt, true);
      if (img) {
        img.setAttribute('fetchpriority', 'high');
        var media = el('div', 'jp-hero__media');
        media.appendChild(img);
        box.appendChild(media);
      }
      var inner = el('div', 'container jp-hero__inner');
      var text = el('div', 'jp-hero__text');
      var h1 = lines(el('h1', 'jp-hero__title'), str(j.heroTitle) || str(j.name));
      h1.id = 'page-title';
      text.appendChild(h1);
      if (str(j.heroSubtitle)) text.appendChild(el('p', 'jp-hero__sub', str(j.heroSubtitle)));
      text.appendChild(rule());
      if (str(j.heroText)) text.appendChild(el('p', 'jp-hero__lead', str(j.heroText)));
      var btn = el('a', 'jp-btn', str(j.heroButton) || 'Book This Service');
      btn.href = safeLink(j.heroLink, 'booking');
      btn.appendChild(icon('arrow'));
      text.appendChild(btn);
      inner.appendChild(text);
      box.appendChild(inner);
    });
  }

  function renderAbout(j) {
    fill('jp-about', function (box) {
      if (!str(j.jaapIntroText) && !str(j.jaapMantra)) return false;
      var wrap = el('div', 'container jp-about');
      var left = el('div', 'jp-about__text');
      if (str(j.jaapIntroHeading)) left.appendChild(headWithRule(str(j.jaapIntroHeading), 'jp-about-title'));
      str(j.jaapIntroText).split(/\n\s*\n/).forEach(function (para) {
        if (para.trim()) left.appendChild(el('p', 'jp-lead', para.trim()));
      });
      wrap.appendChild(left);
      if (str(j.jaapMantra)) {
        var card = el('div', 'jp-mantra');
        var om = el('span', 'jp-mantra__om', 'ॐ');
        om.setAttribute('aria-hidden', 'true');
        card.appendChild(om);
        var mt = lines(el('p', 'jp-mantra__text'), j.jaapMantra);
        mt.lang = 'sa';
        card.appendChild(mt);
        wrap.appendChild(card);
      }
      box.appendChild(wrap);
    });
  }

  function renderBenefits(j) {
    fill('jp-benefits', function (box) {
      var items = list(j.jaapBenefits).filter(function (x) { return str(x.text); });
      if (!items.length) return false;
      var wrap = el('div', 'container');
      if (str(j.jaapBenefitsTitle)) wrap.appendChild(headWithRule(str(j.jaapBenefitsTitle)));
      if (str(j.jaapBenefitsIntro)) wrap.appendChild(el('p', 'jp-lead', str(j.jaapBenefitsIntro)));
      var grid = el('ul', 'jp-benefits');
      grid.setAttribute('role', 'list');
      items.forEach(function (x) {
        var li = el('li', 'jp-benefit');
        var ic = el('span', 'jp-benefit__icon');
        ic.appendChild(icon(str(x.icon)));
        li.appendChild(ic);
        li.appendChild(el('p', null, str(x.text)));
        grid.appendChild(li);
      });
      wrap.appendChild(grid);
      box.appendChild(wrap);
    });
  }

  function renderServices(j) {
    fill('jp-services', function (box) {
      var cards = list(j.jaapOfferings).filter(function (c) { return str(c.title); });
      if (!cards.length) return false;
      var wrap = el('div', 'container');
      if (str(j.jaapOfferingsTitle)) wrap.appendChild(headWithMark(str(j.jaapOfferingsTitle)));
      if (str(j.jaapOfferingsSubtitle)) wrap.appendChild(el('p', 'jp-lead', str(j.jaapOfferingsSubtitle)));
      var grid = el('div', 'jp-services');
      cards.forEach(function (c) {
        var card = el('div', 'jp-service');
        var ic = el('span', 'jp-service__icon');
        ic.appendChild(icon(str(c.icon)));
        card.appendChild(ic);
        var body = el('div');
        body.appendChild(el('h3', null, str(c.title)));
        if (str(c.text)) body.appendChild(el('p', null, str(c.text)));
        card.appendChild(body);
        grid.appendChild(card);
      });
      wrap.appendChild(grid);
      box.appendChild(wrap);
    });
  }

  function renderBooking(j) {
    fill('jp-booking', function (box) {
      var steps = list(j.jaapBookingSteps).filter(function (x) { return str(x.title); });
      if (!steps.length) return false;
      var wrap = el('div', 'container');
      if (str(j.jaapBookingTitle)) wrap.appendChild(headWithMark(str(j.jaapBookingTitle)));
      if (str(j.jaapBookingSubtitle)) wrap.appendChild(el('p', 'jp-lead', str(j.jaapBookingSubtitle)));
      var ol = el('ol', 'jp-steps');
      steps.forEach(function (x, i) {
        if (i) {
          var arrow = el('li', 'jp-step__arrow');
          arrow.setAttribute('aria-hidden', 'true');
          arrow.appendChild(icon('arrow'));
          ol.appendChild(arrow);
        }
        var li = el('li', 'jp-step');
        var top = el('div', 'jp-step__top');
        top.appendChild(el('span', 'jp-step__num', String(i + 1)));
        var ic = el('span', 'jp-step__icon');
        ic.appendChild(icon(str(x.icon) || ['form', 'chat', 'calendar', 'check'][i]));
        top.appendChild(ic);
        li.appendChild(top);
        li.appendChild(el('h3', null, str(x.title)));
        if (str(x.text)) li.appendChild(el('p', null, str(x.text)));
        ol.appendChild(li);
      });
      wrap.appendChild(ol);
      box.appendChild(wrap);
    });
  }

  function renderFaq(j) {
    fill('jp-faq', function (box) {
      var faqs = list(j.jaapFaqs).filter(function (f) { return str(f.q) && str(f.a); });
      if (!faqs.length) return false;
      var wrap = el('div', 'container');
      if (str(j.jaapFaqTitle)) wrap.appendChild(headWithMark(str(j.jaapFaqTitle)));
      if (str(j.jaapFaqIntro)) wrap.appendChild(el('p', 'jp-lead', str(j.jaapFaqIntro)));
      var grid = el('div', 'jp-faq');
      var half = Math.ceil(faqs.length / 2);
      [faqs.slice(0, half), faqs.slice(half)].forEach(function (col, c) {
        if (!col.length) return;
        var column = el('div', 'jp-faq__col');
        col.forEach(function (f, i) {
          var d = el('details');
          var sum = el('summary');
          sum.appendChild(el('span', 'jp-faq__num', (c * half + i + 1) + '.'));
          sum.appendChild(el('span', 'jp-faq__q', str(f.q)));
          sum.appendChild(icon('plus'));
          d.appendChild(sum);
          d.appendChild(lines(el('p'), f.a));
          column.appendChild(d);
        });
        grid.appendChild(column);
      });
      wrap.appendChild(grid);
      box.appendChild(wrap);
    });
  }

  function renderCta(j) {
    fill('jp-cta', function (box) {
      if (!str(j.ctaTitle) && !str(j.ctaText)) return false;
      var img = image(j.ctaImage, '');
      if (img) {
        img.sizes = '100vw';
        var media = el('div', 'jp-cta__media');
        media.setAttribute('aria-hidden', 'true');
        media.appendChild(img);
        box.appendChild(media);
      }
      var inner = el('div', 'container jp-cta__inner');
      if (str(j.ctaTitle)) inner.appendChild(el('h2', 'jp-cta__title', str(j.ctaTitle)));
      if (str(j.ctaText)) inner.appendChild(el('p', 'jp-cta__text', str(j.ctaText)));
      if (str(j.ctaButton)) {
        var btn = el('a', 'jp-btn', str(j.ctaButton));
        btn.href = safeLink(j.ctaLink, 'booking');
        btn.appendChild(icon('arrow'));
        inner.appendChild(btn);
      }
      box.appendChild(inner);
    });
  }

  function renderNotFound() {
    document.title = 'Jaap & Paath page not found | ' + SITE;
    if (window.vpsPageNoindex) window.vpsPageNoindex();
    fill('jp-hero', function (box) {
      var inner = el('div', 'container jp-hero__inner');
      var text = el('div', 'jp-hero__text');
      var h1 = el('h1', 'jp-hero__title', 'This page could not be found');
      h1.id = 'page-title';
      text.appendChild(h1);
      text.appendChild(rule());
      text.appendChild(el('p', 'jp-hero__lead', 'Please choose a service from our Jaap & Paath services list.'));
      var a = el('a', 'jp-btn', 'View All Jaap & Paath Services');
      a.href = LIST_PAGE;
      a.appendChild(icon('arrow'));
      text.appendChild(a);
      inner.appendChild(text);
      box.appendChild(inner);
    });
    document.getElementById('jp-hero').classList.add('jp-hero--plain');
  }

  function render(data) {
    var slug = currentSlug('jaap');
    var j = list(data.items).filter(function (x) { return slug && itemSlug('jaap', x.slug) === slug && x.show !== false; })[0];
    if (!j) return renderNotFound();
    applySeo(j);
    renderHero(j);
    renderAbout(j);
    renderBenefits(j);
    renderServices(j);
    renderBooking(j);
    renderFaq(j);
    renderCta(j);
  }

  function start() { load(function (data) { render(data); document.body.classList.add('is-ready'); }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

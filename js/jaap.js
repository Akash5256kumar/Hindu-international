/* =====================================================================
   JAAP & PAATH PAGES — one template for every service:
     jaap.html?name=<slug>   (e.g. jaap?name=mahamrityunjay-jaap)
   Content: content/content.json → "jaapServices" (Admin → Jaap & Paath pages).
   Until the admin has saved it, content/jaap.default.json is used instead.
   Sections left empty in the admin are not shown.
   Load after js/components.js and before js/script.js.
   ===================================================================== */

(function () {
  'use strict';

  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };
  var SITE = 'Hindu.International';
  var LIST_PAGE = 'service?name=jaap-paath-services';

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
  function lines(node, value) {
    str(value).split('\n').forEach(function (line, i) {
      if (i) node.appendChild(el('br'));
      node.appendChild(document.createTextNode(line));
    });
    return node;
  }
  function divider() {
    var d = el('div', 'jp-divider');
    d.setAttribute('aria-hidden', 'true');
    d.appendChild(icon('lotus'));
    return d;
  }
  function fill(id, build) {
    var box = document.getElementById(id);
    if (!box) return;
    box.textContent = '';
    var shown = build(box) !== false;
    box.hidden = !shown;
  }

  /* ---------- Content: admin first, then the default file ---------- */
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
    var desc = str(j.seoDescription) || str(j.heroSubtitle) || str(j.jaapIntroText);
    var url = origin + '/jaap?name=' + encodeURIComponent(str(j.slug));
    window.vpsPageSeo({
      title: title,
      description: desc,
      url: url,
      image: j.heroImage,
      breadcrumb: [
        ['Home', origin + '/'],
        ['Services', origin + '/services'],
        ['Jaap & Path Anushthan', origin + '/service?name=jaap-path-anushthan'],
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
      var media = el('div', 'jp-hero__media');
      var img = image(j.heroImage, j.heroImageAlt, true);
      if (img) { img.setAttribute('fetchpriority', 'high'); media.appendChild(img); }
      box.appendChild(media);

      var inner = el('div', 'container jp-hero__inner');
      var h1 = lines(el('h1', 'jp-hero__title'), str(j.heroTitle) || str(j.name));
      h1.id = 'page-title';
      inner.appendChild(h1);
      inner.appendChild(divider());
      if (str(j.heroSubtitle)) inner.appendChild(el('p', 'jp-hero__sub', str(j.heroSubtitle)));

      var actions = el('div', 'jp-hero__actions');
      var btn = el('a', 'jp-btn jp-btn--primary', str(j.heroButton) || 'Book This Service');
      btn.href = safeLink(j.heroLink, 'booking');
      btn.appendChild(icon('arrow'));
      actions.appendChild(btn);
      var back = el('a', 'jp-btn jp-btn--ghost', 'All Jaap & Paath Services');
      back.href = LIST_PAGE;
      back.appendChild(icon('arrow'));
      actions.appendChild(back);
      inner.appendChild(actions);
      box.appendChild(inner);
    });
  }

  function renderIntro(j) {
    fill('jp-intro', function (box) {
      if (!str(j.jaapIntroText) && !str(j.jaapMantra)) return false;
      var wrap = el('div', 'container jp-intro__wrap');
      var left = el('div', 'jp-intro__text');
      if (str(j.jaapIntroHeading)) left.appendChild(el('h2', 'jp-title', str(j.jaapIntroHeading)));
      if (str(j.jaapIntroText)) {
        j.jaapIntroText.split('\n\n').forEach(function (para) {
          if (para.trim()) left.appendChild(el('p', 'jp-intro__para', para.trim()));
        });
      }
      wrap.appendChild(left);
      if (str(j.jaapMantra)) {
        var right = el('div', 'jp-intro__mantra');
        if (str(j.jaapMantraTitle)) right.appendChild(el('p', 'jp-mantra__label', str(j.jaapMantraTitle)));
        var mt = lines(el('p', 'jp-mantra__text'), j.jaapMantra);
        mt.lang = 'sa';
        right.appendChild(mt);
        if (str(j.jaapMeaningTitle)) right.appendChild(el('p', 'jp-mantra__meaning-label', str(j.jaapMeaningTitle)));
        if (str(j.jaapMeaning)) right.appendChild(el('p', 'jp-mantra__meaning', str(j.jaapMeaning)));
        wrap.appendChild(right);
      }
      box.appendChild(wrap);
    });
  }

  function render3col(j) {
    fill('jp-3col', function (box) {
      var cols = [
        { t: j.jaapCol1Title, b: j.jaapCol1Text, ic: 'bookopen' },
        { t: j.jaapCol2Title, b: j.jaapCol2Text, ic: 'lotus' },
        { t: j.jaapCol3Title, b: j.jaapCol3Text, ic: 'temple' }
      ].filter(function (c) { return str(c.t) || str(c.b); });
      if (!cols.length) return false;
      var wrap = el('div', 'container');
      var grid = el('div', 'jp-3col');
      cols.forEach(function (c) {
        var card = el('div', 'jp-3col__card');
        var ic = el('span', 'jp-3col__icon');
        ic.appendChild(icon(c.ic));
        card.appendChild(ic);
        if (str(c.t)) card.appendChild(el('h3', 'jp-3col__title', str(c.t)));
        if (str(c.b)) card.appendChild(el('p', 'jp-3col__text', str(c.b)));
        grid.appendChild(card);
      });
      wrap.appendChild(grid);
      box.appendChild(wrap);
    });
  }

  function renderBullets(id, title, items) {
    fill(id, function (box) {
      var pts = list(items).filter(function (x) { return str(x.text); });
      if (!pts.length && !str(title)) return false;
      var wrap = el('div', 'container jp-bullets');
      if (str(title)) wrap.appendChild(el('h2', 'jp-title jp-bullets__title', str(title)));
      var ul = el('ul', 'jp-bullets__list');
      pts.forEach(function (x) {
        var li = el('li');
        li.appendChild(icon('check'));
        li.appendChild(el('span', null, str(x.text)));
        ul.appendChild(li);
      });
      wrap.appendChild(ul);
      box.appendChild(wrap);
    });
  }

  function renderOfferings(j) {
    fill('jp-offerings', function (box) {
      var cards = list(j.jaapOfferings).filter(function (c) { return str(c.title); });
      if (!cards.length) return false;
      if (isMedia(j.jaapOfferingsBgImage)) box.style.backgroundImage = 'url(' + j.jaapOfferingsBgImage + ')';
      var overlay = el('div', 'jp-offerings__overlay');
      var wrap = el('div', 'container jp-offerings');
      var head = el('div', 'jp-offerings__head');
      if (str(j.jaapOfferingsTitle)) head.appendChild(el('h2', 'jp-title jp-offerings__title', str(j.jaapOfferingsTitle)));
      if (str(j.jaapOfferingsSubtitle)) head.appendChild(el('p', 'jp-offerings__sub', str(j.jaapOfferingsSubtitle)));
      wrap.appendChild(head);
      var grid = el('div', 'jp-offerings__grid');
      cards.forEach(function (c) {
        var card = el('div', 'jp-offerings__card');
        var ic = el('span', 'jp-offerings__icon');
        ic.appendChild(icon(c.icon || 'lotus'));
        card.appendChild(ic);
        card.appendChild(el('h3', null, str(c.title)));
        if (str(c.text)) card.appendChild(el('p', null, str(c.text)));
        grid.appendChild(card);
      });
      wrap.appendChild(grid);
      overlay.appendChild(wrap);
      box.appendChild(overlay);
    });
  }

  function renderBooking(j) {
    fill('jp-booking', function (box) {
      var steps = list(j.jaapBookingSteps).filter(function (x) { return str(x.title); });
      if (!steps.length) return false;
      var wrap = el('div', 'container jp-booking');
      var head = el('div', 'jp-booking__head');
      if (str(j.jaapBookingTitle)) head.appendChild(el('h2', 'jp-title', str(j.jaapBookingTitle)));
      if (str(j.jaapBookingSubtitle)) head.appendChild(el('p', 'jp-booking__sub', str(j.jaapBookingSubtitle)));
      wrap.appendChild(head);
      var ol = el('ol', 'jp-booking__steps');
      steps.forEach(function (x, i) {
        var li = el('li', 'jp-booking__step');
        li.appendChild(el('span', 'jp-booking__num', (i < 9 ? '0' : '') + (i + 1)));
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
      var wrap = el('div', 'container jp-faq');
      if (str(j.jaapFaqTitle)) wrap.appendChild(el('h2', 'jp-title jp-faq__title', str(j.jaapFaqTitle)));
      var accordion = el('div', 'faq jp-faq__list');
      faqs.forEach(function (f, i) {
        var d = el('details');
        var sum = el('summary');
        sum.appendChild(el('span', 'jp-faq__num', (i < 9 ? '0' : '') + (i + 1)));
        sum.appendChild(el('span', 'jp-faq__q', str(f.q)));
        d.appendChild(sum);
        d.appendChild(lines(el('p'), f.a));
        accordion.appendChild(d);
      });
      wrap.appendChild(accordion);
      box.appendChild(wrap);
    });
  }

  function renderCta(j) {
    fill('jp-cta', function (box) {
      if (!str(j.ctaTitle) && !str(j.ctaAccent)) return false;
      var img = image(j.ctaImage, '');
      if (img) { var media = el('div', 'jp-cta__media'); media.setAttribute('aria-hidden', 'true'); media.appendChild(img); box.appendChild(media); }
      var inner = el('div', 'container jp-cta__inner');
      var h = el('h2', 'jp-cta__title');
      if (str(j.ctaTitle)) h.appendChild(document.createTextNode(str(j.ctaTitle)));
      if (str(j.ctaAccent)) h.appendChild(el('span', 'jp-cta__accent', str(j.ctaAccent)));
      inner.appendChild(h);
      if (str(j.ctaText)) inner.appendChild(el('p', 'jp-cta__text', str(j.ctaText)));
      if (str(j.ctaButton)) {
        var btn = el('a', 'jp-btn jp-btn--primary', str(j.ctaButton));
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
      var h1 = el('h1', 'jp-hero__title', 'This page could not be found');
      h1.id = 'page-title';
      inner.appendChild(h1);
      inner.appendChild(divider());
      inner.appendChild(el('p', 'jp-hero__sub', 'Please choose a service from our Jaap & Paath services list.'));
      var a = el('a', 'jp-btn jp-btn--primary', 'View All Jaap & Paath Services');
      a.href = LIST_PAGE;
      a.appendChild(icon('arrow'));
      inner.appendChild(a);
      box.appendChild(inner);
    });
    document.getElementById('jp-hero').classList.add('jp-hero--plain');
  }

  function render(data) {
    var slug = '';
    try { slug = (new URLSearchParams(location.search).get('name') || '').toLowerCase(); } catch (e) {}
    var j = list(data.items).filter(function (x) { return str(x.slug) === slug && x.show !== false; })[0];
    if (!j) return renderNotFound();
    applySeo(j);
    renderHero(j);
    renderIntro(j);
    render3col(j);
    renderBullets('jp-benefits', str(j.jaapBenefitsTitle), j.jaapBenefits);
    renderBullets('jp-occasions', str(j.jaapOccasionsTitle), j.jaapOccasions);
    renderOfferings(j);
    renderBooking(j);
    renderFaq(j);
    renderCta(j);
  }

  function start() { load(function (data) { render(data); document.body.classList.add('is-ready'); }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

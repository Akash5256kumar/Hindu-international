/* =====================================================================
   HOW IT WORKS — banner + body sections, all from Admin → How It Works
   (content.json → "howPage"; until saved, content/how.default.json).
   Section types: heading · step · wide · help. Hidden sections are skipped.
   Load after js/components.js and before js/script.js.
   ===================================================================== */

(function () {
  'use strict';

  var safeLink = window.vpsSafeLink || function (v, f) { return f || '#'; };

  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null && text !== '') node.textContent = text;
    return node;
  }
  function str(v) { return v == null ? '' : String(v).trim(); }
  function list(v) { return Array.isArray(v) ? v : []; }
  function pick(v, allowed, fallback) { return allowed.indexOf(v) !== -1 ? v : fallback; }
  function isMedia(p) { return /^(assets|uploads)\/[\w\-./]+$/.test(p || '') && p.indexOf('..') === -1; }
  function isHex(c) { return /^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(c || ''); }

  function image(path, alt, eager) {
    if (!isMedia(path)) return null;
    var img = el('img');
    if (/\.(jpe?g|png|webp)$/i.test(path)) {
      img.sizes = '(max-width: 768px) 100vw, 760px';
      img.srcset = [480, 828].map(function (w) {
        return 'media.php?src=' + encodeURIComponent(path) + '&w=' + w + ' ' + w + 'w';
      }).concat(path + ' 1600w').join(', ');
    }
    img.src = path;
    img.alt = str(alt);
    if (!eager) img.loading = 'lazy';
    img.decoding = 'async';
    return img;
  }
  function paras(box, text) {
    str(text).split(/\n\s*\n/).forEach(function (p) {
      if (!p.trim()) return;
      var node = el('p');
      p.trim().split('\n').forEach(function (line, i) {
        if (i) node.appendChild(el('br'));
        node.appendChild(document.createTextNode(line));
      });
      box.appendChild(node);
    });
    return box;
  }
  var ICONS = { calendar: 'icon-calendar.png', help: 'icon-help.png', lotus: 'lotus-label.png' };
  function iconImg(name, cls) {
    if (!ICONS[name]) return null;
    var img = el('img', cls);
    img.src = 'assets/images/how/' + ICONS[name];
    img.alt = '';
    img.setAttribute('aria-hidden', 'true');
    return img;
  }
  function ornament(cls) {
    var d = el('div', 'hw-orn' + (cls ? ' ' + cls : ''));
    d.setAttribute('aria-hidden', 'true');
    d.appendChild(el('span', 'hw-orn__line'));
    var img = el('img');
    img.src = 'assets/images/how/ornament.png';
    img.alt = '';
    d.appendChild(img);
    d.appendChild(el('span', 'hw-orn__line'));
    return d;
  }

  /* ---------- Content: admin first, then the default file ---------- */
  function merge(saved, def) {
    if (!saved || typeof saved !== 'object') return def;
    var out = {};
    Object.keys(def || {}).forEach(function (k) { out[k] = def[k]; });
    Object.keys(saved).forEach(function (k) { if (saved[k] !== undefined) out[k] = saved[k]; });
    return out;
  }
  function load(done) {
    var c = (window.SITE_CONTENT || {}).howPage;
    if (c && c.banner && Array.isArray(c.sections) && c.closing) {
      return done({ banner: c.banner, sections: c.sections, showFaq: c.showFaq === true, closing: c.closing });
    }
    var get = window.fetch
      ? fetch('content/how.default.json', { cache: 'no-cache' }).then(function (r) { return r.ok ? r.json() : null; })
      : Promise.resolve(null);
    get.then(function (d) {
      d = d || {};
      if (!c) return done(d);
      done({
        banner: merge(c.banner, d.banner),
        sections: Array.isArray(c.sections) ? c.sections : d.sections,
        showFaq: c.showFaq === true,
        closing: merge(c.closing, d.closing)
      });
    }, function () { done(c || {}); });
  }

  /* =================================================================
     BANNER
     ================================================================= */
  function renderBanner(b) {
    var box = document.getElementById('hw-banner');
    if (!box || !b) return;
    box.textContent = '';
    var pos = pick(b.imagePosition, ['right', 'left'], 'right');
    var align = pick(b.textAlign, ['left', 'center'], 'left');
    var height = pick(b.height, ['small', 'medium', 'large'], 'medium');
    box.className = 'hw-banner hw-banner--img-' + pos + ' hw-banner--' + align + ' hw-banner--' + height;
    if (isHex(b.bgColor)) box.style.setProperty('--hw-banner-bg', b.bgColor);
    if (isMedia(b.bgImage)) box.style.backgroundImage = 'url("' + b.bgImage + '")';

    var img = image(b.image, b.imageAlt, true);
    if (img) {
      img.setAttribute('fetchpriority', 'high');
      var media = el('div', 'hw-banner__media');
      media.appendChild(img);
      box.appendChild(media);
    }
    var inner = el('div', 'container hw-banner__inner');
    var content = el('div', 'hw-banner__content');
    if (str(b.label)) {
      var lab = el('p', 'hw-banner__label');
      var lotus = iconImg('lotus', 'hw-banner__lotus');
      if (lotus) lab.appendChild(lotus);
      lab.appendChild(el('span', null, str(b.label)));
      lab.appendChild(el('span', 'hw-banner__label-line'));
      content.appendChild(lab);
    }
    var h1 = el('h1', 'hw-banner__title', str(b.title) || 'How It Works');
    h1.id = 'page-title';
    content.appendChild(h1);
    if (str(b.text)) content.appendChild(el('p', 'hw-banner__text', str(b.text)));
    var foot = el('span', 'hw-banner__rule');
    foot.setAttribute('aria-hidden', 'true');
    content.appendChild(foot);
    inner.appendChild(content);
    box.appendChild(inner);
  }

  /* =================================================================
     BODY SECTIONS
     ================================================================= */
  function sectionHeading(s) {
    var wrap = el('section', 'hw-heading');
    var c = el('div', 'container');
    c.appendChild(ornament('hw-orn--top'));
    var row = el('div', 'hw-heading__row');
    row.appendChild(el('span', 'hw-heading__line'));
    row.appendChild(el('h2', 'hw-heading__title', str(s.title)));
    row.appendChild(el('span', 'hw-heading__line hw-heading__line--r'));
    c.appendChild(row);
    wrap.appendChild(c);
    return wrap;
  }

  function mediaBox(s, cls) {
    var img = image(s.image, s.imageAlt);
    if (!img) return null;
    var m = el('div', 'hw-media ' + cls);
    m.appendChild(img);
    return m;
  }

  function stepSection(s) {
    var side = pick(s.imageSide, ['right', 'left'], 'right');
    var wrap = el('section', 'hw-step-wrap');
    var c = el('div', 'container');
    var card = el('article', 'hw-panel hw-step hw-img-' + side + ' hw-bg-' + pick(s.background, ['ivory', 'cream', 'white', 'peach'], 'ivory') + ' hw-align-' + pick(s.align, ['left', 'center'], 'left'));
    var media = mediaBox(s, 'hw-step__media');
    if (!media) card.classList.add('hw-no-img');
    var body = el('div', 'hw-step__body');
    var head = el('div', 'hw-step__head');
    if (str(s.number)) head.appendChild(el('span', 'hw-num', str(s.number)));
    head.appendChild(el('h3', 'hw-step__title', str(s.title)));
    body.appendChild(head);
    body.appendChild(paras(el('div', 'hw-text'), s.text));
    card.appendChild(body);
    if (media) card.appendChild(media);
    c.appendChild(card);
    wrap.appendChild(c);
    return wrap;
  }

  function wideSection(s) {
    var side = pick(s.imageSide, ['right', 'left'], 'left');
    var wrap = el('section', 'hw-wide hw-img-' + side + ' hw-bg-' + pick(s.background, ['ivory', 'cream', 'white', 'peach'], 'cream') + ' hw-align-' + pick(s.align, ['left', 'center'], 'left'));
    var media = mediaBox(s, 'hw-wide__media');
    if (!media) wrap.classList.add('hw-no-img');
    if (media) wrap.appendChild(media);
    var c = el('div', 'container hw-wide__inner');
    var body = el('div', 'hw-wide__body');
    var h = el('h2', 'hw-wide__title');
    var ic = iconImg(s.icon, 'hw-wide__icon');
    if (ic) h.appendChild(ic);
    h.appendChild(el('span', null, str(s.title)));
    body.appendChild(h);
    body.appendChild(paras(el('div', 'hw-text'), s.text));
    c.appendChild(body);
    wrap.appendChild(c);
    return wrap;
  }

  function helpSection(s) {
    var side = pick(s.imageSide, ['right', 'left'], 'right');
    var wrap = el('section', 'hw-help-wrap');
    var c = el('div', 'container');
    var card = el('article', 'hw-panel hw-help hw-img-' + side + ' hw-bg-' + pick(s.background, ['ivory', 'cream', 'white', 'peach'], 'peach') + ' hw-align-' + pick(s.align, ['left', 'center'], 'left'));
    var ic = iconImg(s.icon, 'hw-help__icon');
    if (ic) {
      var icBox = el('div', 'hw-help__iconbox');
      icBox.appendChild(ic);
      card.appendChild(icBox);
    } else {
      card.classList.add('hw-no-icon');
    }
    var body = el('div', 'hw-help__body');
    body.appendChild(el('h2', 'hw-help__title', str(s.title)));
    body.appendChild(paras(el('div', 'hw-text'), s.text));
    card.appendChild(body);
    var media = mediaBox(s, 'hw-help__media');
    if (media) card.appendChild(media); else card.classList.add('hw-no-img');
    c.appendChild(card);
    wrap.appendChild(c);
    return wrap;
  }

  function faqSection() {
    var faq = ((window.SITE_CONTENT || {}).how || {}).faq || {};
    var items = list(faq.items).filter(function (f) { return str(f.q) && str(f.a); });
    if (!items.length) return null;
    var wrap = el('section', 'hw-faq');
    wrap.id = 'faq';
    var c = el('div', 'container hw-faq__inner');
    c.appendChild(el('h2', 'hw-heading__title hw-faq__title', str(faq.title) || 'Frequently Asked Questions'));
    var box = el('div', 'faq');
    items.forEach(function (f) {
      var d = el('details');
      d.appendChild(el('summary', null, str(f.q)));
      d.appendChild(el('p', null, str(f.a)));
      box.appendChild(d);
    });
    c.appendChild(box);
    wrap.appendChild(c);
    return wrap;
  }

  /* Closing banner: sunset scene, brass decorations at the sides, frosted-glass panel in the middle */
  function decor(path, cls) {
    if (!isMedia(path)) return null;
    var img = el('img', cls);
    img.src = path;
    img.alt = '';
    img.loading = 'lazy';
    img.decoding = 'async';
    img.setAttribute('aria-hidden', 'true');
    return img;
  }
  function ctaSection(cta) {
    if (!cta || cta.show === false || (!str(cta.title) && !str(cta.text))) return null;
    if (cta.showDesktop === false && cta.showMobile === false) return null;
    var wrap = el('section', 'hw-cta');
    if (cta.showDesktop === false) wrap.classList.add('hw-hide-desktop');
    if (cta.showMobile === false) wrap.classList.add('hw-hide-mobile');
    var st = wrap.style;
    if (isHex(cta.bgColor)) st.setProperty('--cta-bg', cta.bgColor);
    if (isMedia(cta.bgImage)) st.backgroundImage = 'url("' + cta.bgImage + '")';
    st.backgroundPosition = pick(cta.bgPosition, ['center', 'top', 'bottom'], 'center');
    st.backgroundSize = { cover: 'cover', contain: 'contain', stretch: '100% 100%' }[pick(cta.bgSize, ['cover', 'contain', 'stretch'], 'cover')];
    st.setProperty('--glass-op', String(pick(str(cta.glassOpacity), ['30', '40', '50', '60', '70', '80'], '50') / 100));
    st.setProperty('--glass-blur', pick(str(cta.glassBlur), ['4', '8', '12', '16', '24'], '12') + 'px');
    st.setProperty('--glass-w', pick(str(cta.panelWidth), ['45', '50', '55', '60', '65', '70'], '55') + '%');
    st.setProperty('--glass-h', pick(str(cta.panelHeight), ['auto', '220', '260', '300', '340'], 'auto') === 'auto' ? '0px' : cta.panelHeight + 'px');
    st.setProperty('--glass-r', pick(str(cta.panelRadius), ['12', '20', '28', '36'], '28') + 'px');
    var bc = isHex(cta.borderColor) ? cta.borderColor : '#FFFFFF';
    var bo = pick(str(cta.borderOpacity), ['0', '30', '50', '70', '90'], '70') / 100;
    var n = parseInt(bc.length === 4 ? bc.replace(/^#(.)(.)(.)$/, '$1$1$2$2$3$3') : bc.slice(1), 16);
    st.setProperty('--glass-border', 'rgba(' + (n >> 16 & 255) + ',' + (n >> 8 & 255) + ',' + (n & 255) + ',' + bo + ')');
    st.setProperty('--glass-shadow', { none: 'none', soft: '0 10px 30px rgba(90, 40, 60, .12)', medium: '0 16px 40px rgba(90, 40, 60, .2)', strong: '0 22px 54px rgba(90, 40, 60, .3)' }[pick(cta.shadow, ['none', 'soft', 'medium', 'strong'], 'soft')]);

    if (cta.showSideImages !== false) {
      var left = decor(cta.leftImage, 'hw-cta__side hw-cta__side--l');
      var right = decor(cta.rightImage, 'hw-cta__side hw-cta__side--r');
      if (left) wrap.appendChild(left);
      if (right) wrap.appendChild(right);
    }
    var panel = el('div', 'hw-cta__glass');
    if (cta.showLotus !== false) {
      var orn = el('div', 'hw-cta__orn');
      orn.setAttribute('aria-hidden', 'true');
      orn.appendChild(el('span', 'hw-cta__orn-line'));
      var lotus = decor(str(cta.lotusImage) || 'assets/images/how/cta-lotus.png', 'hw-cta__lotus');
      if (lotus) { lotus.loading = 'eager'; orn.appendChild(lotus); }
      orn.appendChild(el('span', 'hw-cta__orn-line hw-cta__orn-line--r'));
      panel.appendChild(orn);
    }
    if (str(cta.title)) panel.appendChild(el('h2', 'hw-cta__title', str(cta.title)));
    if (str(cta.text)) panel.appendChild(el('p', 'hw-cta__text', str(cta.text)));
    var rule = el('span', 'hw-cta__rule');
    rule.setAttribute('aria-hidden', 'true');
    panel.appendChild(rule);
    var actions = el('div', 'hw-cta__actions');
    if (str(cta.primaryText)) {
      var a = el('a', 'hw-btn hw-btn--fill', str(cta.primaryText) + ' →');
      a.href = safeLink(cta.primaryLink, 'services');
      actions.appendChild(a);
    }
    if (str(cta.secondaryText)) {
      var b = el('a', 'hw-btn hw-btn--line', str(cta.secondaryText));
      b.href = safeLink(cta.secondaryLink, 'contact-us');
      actions.appendChild(b);
    }
    if (actions.children.length) panel.appendChild(actions);
    wrap.appendChild(panel);
    return wrap;
  }

  var BUILD = { heading: sectionHeading, step: stepSection, wide: wideSection, help: helpSection };

  function render(data) {
    renderBanner(data.banner);
    var body = document.getElementById('hw-body');
    if (!body) return;
    body.textContent = '';
    list(data.sections).forEach(function (s) {
      if (!s || s.show === false) return;
      var build = BUILD[s.type] || BUILD.step;
      if (!str(s.title) && !str(s.text)) return;
      body.appendChild(build(s));
    });
    if (data.showFaq) {
      var faq = faqSection();
      if (faq) body.appendChild(faq);
    }
    var cta = ctaSection(data.closing);
    if (cta) body.appendChild(cta);
    if (location.hash) {
      var target = document.getElementById(location.hash.slice(1));
      if (target) target.scrollIntoView();
    }
  }

  function start() { load(function (data) { render(data || {}); document.body.classList.add('is-ready'); }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();

/* =====================================================================
   SHARED COMPONENTS — icon set, header and footer.
   Edit the header / footer once here and every page updates.
   Labels, logo text, tagline and theme colours come from content/content.js
   (window.SITE_CONTENT, loaded in <head>), so they are editable in the admin panel.
   Each page contains:
     <div id="site-header" data-page="home"></div>   (page key for active link)
     <div id="site-footer"></div>
   ===================================================================== */

(function () {
  'use strict';

  /* ---------- Icon sprite (line icons, 24×24, stroke = currentColor) ---------- */
  var ICONS = {
    lotus: '<path d="M12 4c2.5 3 2.5 9 0 13c-2.5-4-2.5-10 0-13z"/><path d="M12 17c-1-4.5-4-7.5-7-8c0 4 2.5 7 7 8z"/><path d="M12 17c1-4.5 4-7.5 7-8c0 4-2.5 7-7 8z"/><path d="M12 17c-4 0-8-2-10-4.5c3-1 6 0 8 2"/><path d="M12 17c4 0 8-2 10-4.5c-3-1-6 0-8 2"/><path d="M5 19.5c4 1.5 10 1.5 14 0"/>',
    shield: '<path d="M12 3l7 3v5c0 4.5-3 8.5-7 10c-4-1.5-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
    video: '<rect x="2.5" y="6" width="13.5" height="12" rx="2"/><path d="M16 10.5l5.5-3v9l-5.5-3"/><path d="M8 9.8v4.4l3.6-2.2z"/>',
    heart: '<path d="M12 20s-7.2-4.4-9-9.2A4.8 4.8 0 0 1 12 7.6a4.8 4.8 0 0 1 9 3.2C19.2 15.6 12 20 12 20z"/>',
    doc: '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M9 11h6M9 14.5h6M9 18h4"/>',
    share: '<circle cx="18" cy="5.5" r="2.8"/><circle cx="6" cy="12" r="2.8"/><circle cx="18" cy="18.5" r="2.8"/><path d="M8.5 10.7l7-3.9M8.5 13.3l7 3.9"/>',
    idcard: '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10.5" r="2.2"/><path d="M5.8 16c.7-1.6 1.9-2.4 3.2-2.4s2.5.8 3.2 2.4M14.5 10h4M14.5 13.5h4"/>',
    image: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="9.5" r="1.8"/><path d="M21 15.5l-5-5L7 20"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M7.5 14h2M11 14h2M14.5 14h2M7.5 17.5h2M11 17.5h2"/>',
    edit: '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
    phone: '<path d="M5 3.5h3.2l1.8 4.7-2.3 1.4a11.5 11.5 0 0 0 6.7 6.7l1.4-2.3 4.7 1.8V19a2 2 0 0 1-2.2 2A17 17 0 0 1 3 5.7a2 2 0 0 1 2-2.2z"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>',
    pin: '<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    diya: '<path d="M3.5 13.5h17c-.5 3.6-4 6-8.5 6s-8-2.4-8.5-6z"/><path d="M12 3.5c1.9 2.1 2.6 3.6 2.6 5a2.6 2.6 0 0 1-5.2 0c0-1.4.7-2.9 2.6-5z"/><path d="M9 21h6"/>',
    upload: '<path d="M12 15.5V4M7.5 8.5L12 4l4.5 4.5"/><path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5M12 7.8v.1"/>',
    check: '<circle cx="12" cy="12" r="9"/><path d="M8.3 12.4l2.5 2.5 4.9-5.2"/>',
    camera: '<path d="M3.5 8h3.2l2-2.5h6.6l2 2.5h3.2v11h-17z"/><circle cx="12" cy="13.2" r="3.4"/>',
    menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
    close: '<path d="M6 6l12 12M18 6L6 18"/>',
    whatsapp: '<path d="M4 20l1.2-3.9A8.5 8.5 0 1 1 8.1 19z"/><path d="M9.2 8.3c.3-.5.8-.5 1-.1l.8 1.8c.1.3 0 .6-.2.8l-.5.5c.6 1.3 1.6 2.3 2.9 2.9l.5-.5c.2-.2.5-.3.8-.2l1.8.8c.4.2.4.7-.1 1-.9.7-2 .9-3.1.4a8.4 8.4 0 0 1-4.3-4.3c-.5-1.1-.3-2.2.4-3.1z"/>',
    facebook: '<path d="M14.5 8.5H17V5h-2.8C11.6 5 10.5 6.6 10.5 9v1.8H8v3.4h2.5V21h3.6v-6.8h2.6l.5-3.4h-3.1V9.3c0-.5.3-.8.9-.8z" fill="currentColor" stroke="none"/>',
    instagram: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".8" fill="currentColor" stroke="none"/>',
    x: '<path d="M18.9 3h2.8l-6.1 7 7.2 11h-5.6l-4.4-6.7L7 21H4.2l6.5-7.5L3.8 3h5.8l4 6.1L18.9 3zm-1 16h1.5L8.5 4.9H6.9L17.9 19z" fill="currentColor" stroke="none"/>',
    youtube: '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10.2 9.2v5.6l4.6-2.8z" fill="currentColor"/>',
    play: '<circle cx="12" cy="12" r="10" fill="currentColor" stroke="none"/><path d="M10 8.3v7.4l5.8-3.7z" fill="#fff" stroke="none"/>',
    book: '<path d="M12 6.5C10 5 7 4.5 3.5 5v13c3.5-.5 6.5 0 8.5 1.5 2-1.5 5-2 8.5-1.5V5C17 4.5 14 5 12 6.5z"/><path d="M12 6.5v13M6 9h3M6 12h3M15 9h3M15 12h3"/>',
    medical: '<path d="M9 3.5h6v5.5h5.5v6H15v5.5H9V15H3.5V9H9z"/>',
    bowl: '<path d="M3 11.5h18c0 4.8-4 8.5-9 8.5s-9-3.7-9-8.5z"/><path d="M9 3.5c-1 1.2 1 2.3 0 3.5M12 3.5c-1 1.2 1 2.3 0 3.5M15 3.5c-1 1.2 1 2.3 0 3.5"/>',
    sprout: '<path d="M12 21v-9"/><path d="M12 12c0-4 3-7 8-7 0 4.5-3 7-8 7z"/><path d="M12 14.5c0-3.5-2.5-6-7-6 0 4 2.5 6 7 6z"/>',
    people: '<circle cx="12" cy="7.5" r="3"/><path d="M6.5 20v-1.5a5.5 5.5 0 0 1 11 0V20"/><circle cx="5" cy="9.5" r="2.2"/><path d="M1.8 19v-1a3.8 3.8 0 0 1 4.2-3.8"/><circle cx="19" cy="9.5" r="2.2"/><path d="M22.2 19v-1a3.8 3.8 0 0 0-4.2-3.8"/>',
    skill: '<circle cx="9" cy="7" r="3.5"/><path d="M2.5 20v-1a6.5 6.5 0 0 1 9.6-5.7"/><circle cx="17.5" cy="17" r="2.2"/><path d="M17.5 12.8v1.6M17.5 19.6v1.6M13.3 17h1.6M20.1 17h1.6M14.6 14.1l1.1 1.1M19.3 18.8l1.1 1.1M14.6 19.9l1.1-1.1M19.3 15.2l1.1-1.1"/>',
    handshake: '<path d="M2 11l3.5-3.5 4 1.2L12 7l3.5 1.5L22 12"/><path d="M5.5 7.5V14l5 5c.8.8 2 .8 2.8 0l5.7-5.7"/><path d="M10 9.5l-2.3 2.3a1.5 1.5 0 0 0 2.1 2.1l2.2-2.2 3.5 3.5"/>',
    eye: '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    hearthand: '<path d="M12 11.5s-3.5-2.1-3.5-4.6A2 2 0 0 1 12 5.7a2 2 0 0 1 3.5 1.2c0 2.5-3.5 4.6-3.5 4.6z"/><path d="M3 14.5h3.5l3.2 1.6h3.8a1.4 1.4 0 0 1 0 2.8H10"/><path d="M13.5 18.9l5.3-2.6a1.5 1.5 0 0 1 1.7 2.4L15 22H7l-4-2"/>',
    lock: '<rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
    coins: '<ellipse cx="9" cy="7" rx="6" ry="2.6"/><path d="M3 7v4c0 1.4 2.7 2.6 6 2.6s6-1.2 6-2.6V7"/><path d="M9 13.6v4c0 1.4 2.7 2.6 6 2.6s6-1.2 6-2.6v-4c0-1.4-2.7-2.6-6-2.6"/><path d="M9 17.6c0 1.4 2.7 2.6 6 2.6"/>',
    school: '<path d="M3 21h18M5 21V10.5l7-4.5 7 4.5V21"/><path d="M12 6V2.8h3.5V4.6H12"/><path d="M9.5 21v-4.5h5V21"/><path d="M8 12h2M14 12h2"/>',
    alert: '<path d="M12 3.5L21.5 20h-19z"/><path d="M12 10v4.5M12 17.2v.1"/>',
    scale: '<path d="M12 3.5v17M7.5 20.5h9M5 6.5h14M12 5.2a1.3 1.3 0 1 0 0-.1"/><path d="M5 6.5L2.5 13a2.5 2.5 0 0 0 5 0z"/><path d="M19 6.5L16.5 13a2.5 2.5 0 0 0 5 0z"/>',
    heartpulse: '<path d="M12 20s-7.2-4.4-9-9.2A4.8 4.8 0 0 1 12 7.6a4.8 4.8 0 0 1 9 3.2C19.2 15.6 12 20 12 20z"/><path d="M5.5 12.5h3.2l1.5-2.6 2.2 4.6 1.6-3h4.5"/>',
    bookopen: '<path d="M12 7c-2-1.6-5-2.2-9-1.8v13c4-.4 7 .2 9 1.8 2-1.6 5-2.2 9-1.8v-13c-4-.4-7 .2-9 1.8z"/><path d="M12 7v13"/>',
    temple: '<path d="M12 2.5v2M8.5 9.5C8.5 6.5 10 5 12 4.5c2 .5 3.5 2 3.5 5"/><path d="M6.5 9.5h11M7.5 9.5V20M16.5 9.5V20M4 20.5h16M10.5 20v-4.5a1.5 1.5 0 0 1 3 0V20"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.7 5.6 3.7 9s-1.2 6.4-3.7 9c-2.5-2.6-3.7-5.6-3.7-9S9.5 5.6 12 3z"/>'
  };
  window.vpsIconNames = Object.keys(ICONS);

  function injectSprite() {
    var symbols = Object.keys(ICONS).map(function (k) {
      return '<symbol id="i-' + k + '" viewBox="0 0 24 24">' + ICONS[k] + '</symbol>';
    }).join('');
    var wrap = document.createElement('div');
    wrap.setAttribute('aria-hidden', 'true');
    wrap.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden';
    wrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg">' + symbols + '</svg>';
    document.body.insertBefore(wrap, document.body.firstChild);
  }

  /* Reusable icon markup: icon('lotus', 'extra-class') */
  function icon(name, cls) {
    return '<svg class="icon' + (cls ? ' ' + cls : '') + '" aria-hidden="true" focusable="false"><use href="#i-' + name + '"></use></svg>';
  }
  window.vpsIcon = icon;

  /* ---------- Content (editable in the admin panel) ---------- */
  var CONTENT = window.SITE_CONTENT || {};
  var SITE = CONTENT.site || {};

  function esc(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function escLines(str) { return esc(str).replace(/\n/g, '<br>'); }

  /* Theme colours -> CSS custom properties (applied before the page paints) */
  (function applyTheme() {
    var theme = CONTENT.theme || {};
    var vars = {
      primary: '--saffron', primaryDark: '--saffron-deep', gold: '--gold',
      navy: '--roots-ink', background: '--cream', footer: '--navy'
    };
    Object.keys(vars).forEach(function (key) {
      if (/^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(theme[key] || '')) {
        document.documentElement.style.setProperty(vars[key], theme[key]);
      }
    });
  })();

  /* Safe link: page on this site (page, page#part, #part), https://, mailto: or tel:
     (the admin panel validates the same rule; anything else falls back) */
  var LINK_RE = /^(https?:\/\/[^\s"'<>]+|mailto:[^\s"'<>]+|tel:\+?[\d\s()-]{3,20}|#[\w-]*|\/|[\w-]+(?:\.html)?(#[\w-]*)?)$/i;
  function safeLink(value, fallback) {
    value = String(value || '').trim();
    if (!LINK_RE.test(value)) return fallback || '#';
    value = value.replace(/\.html(?=#|$)/i, '');
    if (/^index(?:#|$)/i.test(value)) return '/' + value.slice(5);
    return value || '/';
  }
  window.vpsSafeLink = safeLink;
  function linkAttrs(href) {
    return 'href="' + esc(href) + '"' + (/^https?:/i.test(href) ? ' target="_blank" rel="noopener"' : '');
  }

  /* ---------- Navigation (single source for header + footer) ----------
     Comes from Admin → Header, footer & colours → Menu. The defaults below are only
     used when the content has no menu yet (or an older menu without links). */
  var NAV_DEFAULT = [
    { label: 'Home',              link: '/' },
    { label: 'About Us',          link: 'about' },
    { label: 'How It Works',      link: 'how-it-works' },
    { label: 'Protect Our Faith', link: 'protect-our-faith' },
    { label: 'Event Information', link: 'event' },
    { label: 'Services',          link: 'services' },
    { label: 'Contact Us',        link: 'contact' }
  ];
  var NAV = (Array.isArray(SITE.nav) && SITE.nav.length ? SITE.nav : NAV_DEFAULT)
    .map(function (item, i) {
      item = item || {};
      var def = NAV_DEFAULT[i] || {};
      return {
        label: item.label || def.label || '',
        href: safeLink(item.link || def.link, def.link || '#'),
        show: item.show !== false
      };
    })
    .filter(function (n) { return n.show && n.label; });

  /* Current page: "protect-our-faith" etc. ("/" → home) */
  var currentFile = (location.pathname.split('/').pop() || 'index').replace(/\.html$/i, '').toLowerCase();
  function isCurrent(href, fallbackFile) {
    var file = href.split('#')[0].replace(/\.html$/i, '').toLowerCase();
    if (file === '/') file = 'index';
    return !!file && (file === currentFile || file === fallbackFile);
  }

  /* Logo: uploaded image, or the lotus mark + text (the first "." is highlighted)
     with the tagline underneath. Size is set in the admin panel. */
  var logoText = SITE.logoText || 'Hindu.International';
  var logoTagline = SITE.logoTagline == null ? 'Faith · Culture · Community · Worldwide' : SITE.logoTagline;
  var logoSize = /^(md|lg|xl)$/.test(SITE.logoSize || '') ? SITE.logoSize : 'lg';
  var logoFontFamily = SITE.logoFontFamily === 'sans' ? 'sans' : 'serif';
  var logoFontSize = /^(20|22|24|26|28|30)$/.test(String(SITE.logoFontSize || '')) ? String(SITE.logoFontSize) : '26';
  var logoFontWeight = SITE.logoBold === false ? 'regular' : 'bold';
  var logoTextClass = 'logo__text--' + logoFontFamily + ' logo__text--' + logoFontSize + ' logo__text--' + logoFontWeight;
  function isMedia(path) { return /^(assets|uploads)\/[\w\-./]+$/.test(path || '') && path.indexOf('..') === -1; }

  function logoMarkup(imagePath) {
    var dot = logoText.indexOf('.');
    var mark = isMedia(imagePath)
      ? '<img class="logo__img" src="' + esc(imagePath) + '" alt="">'
      : '<span class="logo__mark">' + icon('lotus') + '</span>';
    return mark + '<span class="logo__words">' +
        '<span class="logo__text ' + logoTextClass + '">' + (dot > 0
          ? esc(logoText.slice(0, dot)) + '<span class="logo__dot">.</span>' + esc(logoText.slice(dot + 1))
          : esc(logoText)) + '</span>' +
        (logoTagline ? '<span class="logo__tagline">' + esc(logoTagline) + '</span>' : '') +
      '</span>';
  }

  function renderHeader(el) {
    var fallback = (el.getAttribute('data-nav') || '').toLowerCase();
    var anyCurrent = NAV.some(function (n) { return isCurrent(n.href); });
    var links = NAV.map(function (n) {
      var current = isCurrent(n.href, anyCurrent ? '' : fallback) ? ' aria-current="page"' : '';
      return '<li><a class="nav__link" ' + linkAttrs(n.href) + current + '>' + esc(n.label) + '</a></li>';
    }).join('');
    var ctaText = SITE.headerCta == null ? 'Donate Us' : SITE.headerCta;
    var cta = ctaText
      ? '<a class="btn btn--primary btn--sm nav__cta" ' + linkAttrs(safeLink(SITE.headerCtaLink, 'donate')) + '>' +
          esc(ctaText) + icon('arrow', 'icon--arrow') + '</a>'
      : '';

    var swastik = '<svg class="blessing__swastik" viewBox="0 0 24 24" aria-hidden="true">' +
      '<path d="M12 12V3h8M12 12h9v8M12 12v9H4M12 12H3V4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="square"/>' +
      '<circle cx="16.5" cy="7.5" r="1.5"/><circle cx="16.5" cy="16.5" r="1.5"/><circle cx="7.5" cy="16.5" r="1.5"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>';

    /* Shri Ganesh blessing strip above the header; it scrolls away, the header stays stuck */
    el.outerHTML =
      '<div class="blessing">' +
        '<div class="blessing__row"><img class="blessing__ganesh" src="assets/images/brand/ganesh-blessing.png" width="114" height="120" alt="Shri Ganesh"></div>' +
        '<p class="blessing__mantra" lang="sa">' + swastik + 'ॐ गणेशाय नमः' + swastik + '</p>' +
        '<div class="blessing__line" aria-hidden="true"></div>' +
      '</div>' +
      '<header class="site-header" id="top">' +
        '<div class="container header__inner">' +
          '<a class="logo logo--' + logoSize + '" href="/" aria-label="' + esc(logoText) + ' — Home">' + logoMarkup(SITE.logoImage) + '</a>' +
          '<nav class="nav" id="site-nav" aria-label="Main">' +
            '<ul class="nav__list">' + links + '</ul>' + cta +
          '</nav>' +
          '<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Open menu">' +
            icon('menu', 'nav-toggle__open') + icon('close', 'nav-toggle__close') +
          '</button>' +
        '</div>' +
      '</header>';
  }

  var FOOTER_LINKS_DEFAULT = [
    { label: 'Virtual Punya Snan', link: 'booking' },
    { label: 'Event Details', link: 'event' },
    { label: 'Donate', link: 'donate' },
    { label: 'FAQs', link: 'how-it-works#faq' },
    { label: 'Privacy Policy', link: 'privacy' },
    { label: 'Terms & Conditions', link: 'terms' }
  ];

  function linkList(items) {
    return items.filter(function (n) { return n && n.show !== false && n.label; }).map(function (n) {
      return '<li><a ' + linkAttrs(n.href || safeLink(n.link)) + '>' + esc(n.label) + '</a></li>';
    }).join('');
  }

  function renderFooter(el) {
    var extra = Array.isArray(SITE.footerLinks) && SITE.footerLinks.length ? SITE.footerLinks : FOOTER_LINKS_DEFAULT;
    var footerLogo = isMedia(SITE.footerLogoImage) ? SITE.footerLogoImage : SITE.logoImage;   // header logo when no footer version
    el.outerHTML =
      '<footer class="site-footer">' +
        '<div class="container footer__grid">' +
          '<div class="footer__brand">' +
            '<a class="logo logo--light logo--' + logoSize + '" href="/" aria-label="' + esc(logoText) + ' — Home">' + logoMarkup(footerLogo) + '</a>' +
            '<p class="footer__tagline">' + escLines(SITE.footerTagline || 'Your Faith. Our Service.\nDivine Blessings, Always.') + '</p>' +
            '<ul class="footer__social" data-social></ul>' +
          '</div>' +
          '<div class="footer__col">' +
            '<h2 class="footer__title">' + esc(SITE.footerQuickTitle || 'Quick Links') + '</h2>' +
            '<ul class="footer__links">' + linkList(NAV) + '</ul>' +
          '</div>' +
          '<div class="footer__col">' +
            '<h2 class="footer__title">' + esc(SITE.footerLinksTitle || 'Our Service') + '</h2>' +
            '<ul class="footer__links">' + linkList(extra) + '</ul>' +
          '</div>' +
        '</div>' +
        '<div class="container">' +
          '<div class="footer__bottom"><p>&copy; <span data-year>2025</span> ' + esc(logoText) + '. ' +
            esc(SITE.copyright == null ? 'All rights reserved.' : SITE.copyright) + '</p></div>' +
        '</div>' +
      '</footer>';
  }

  /* This script is loaded right after the header placeholder, so the header
     renders immediately (no flash); the footer renders once the page is parsed. */
  injectSprite();

  /* Devanagari font for the blessing strip — only the letters it uses, so the file is tiny */
  var deva = document.createElement('link');
  deva.rel = 'stylesheet';
  deva.href = 'https://fonts.googleapis.com/css2?family=Noto+Serif+Devanagari:wght@600&display=swap&text=' + encodeURIComponent('ॐ गणेशाय नमः');
  document.head.appendChild(deva);

  var h = document.getElementById('site-header');
  if (h) renderHeader(h);

  function footer() {
    var f = document.getElementById('site-footer');
    if (f) renderFooter(f);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', footer);
  } else {
    footer();
  }
})();

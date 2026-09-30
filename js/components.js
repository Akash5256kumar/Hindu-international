/* =====================================================================
   SHARED COMPONENTS — icon set, header and footer.
   Edit the header / footer once here and every page updates.
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
    youtube: '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10.2 9.2v5.6l4.6-2.8z" fill="currentColor"/>',
    play: '<circle cx="12" cy="12" r="10" fill="currentColor" stroke="none"/><path d="M10 8.3v7.4l5.8-3.7z" fill="#fff" stroke="none"/>'
  };

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

  /* ---------- Navigation (single source for header + footer) ---------- */
  var NAV = [
    { key: 'home',    label: 'Home',              href: 'index.html' },
    { key: 'about',   label: 'About Us',          href: 'about.html' },
    { key: 'how',     label: 'How It Works',      href: 'how-it-works.html' },
    { key: 'roots',   label: 'Preserve Our Roots', href: 'preserve-our-roots.html' },
    { key: 'event',   label: 'Event Information', href: 'event.html' },
    { key: 'booking', label: 'Service / Booking', href: 'booking.html' },
    { key: 'contact', label: 'Contact Us',        href: 'contact.html' }
  ];

  var LOGO =
    '<span class="logo__mark">' + icon('lotus') + '</span>' +
    '<span class="logo__text">Hindu<span class="logo__dot">.</span>International</span>';

  function renderHeader(el) {
    var page = el.getAttribute('data-page');
    var links = NAV.map(function (n) {
      var current = n.key === page ? ' aria-current="page"' : '';
      return '<li><a class="nav__link" href="' + n.href + '"' + current + '>' + n.label + '</a></li>';
    }).join('');

    el.outerHTML =
      '<header class="site-header" id="top">' +
        '<div class="container header__inner">' +
          '<a class="logo" href="index.html" aria-label="Hindu.International — Home">' + LOGO + '</a>' +
          '<nav class="nav" id="site-nav" aria-label="Main">' +
            '<ul class="nav__list">' + links + '</ul>' +
            '<a class="btn btn--primary btn--sm nav__cta" href="booking.html">Request Service</a>' +
          '</nav>' +
          '<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Open menu">' +
            icon('menu', 'nav-toggle__open') + icon('close', 'nav-toggle__close') +
          '</button>' +
        '</div>' +
      '</header>';
  }

  function renderFooter(el) {
    var quick = NAV.map(function (n) {
      return '<li><a href="' + n.href + '">' + n.label + '</a></li>';
    }).join('');

    el.outerHTML =
      '<footer class="site-footer">' +
        '<div class="container footer__grid">' +
          '<div class="footer__brand">' +
            '<a class="logo logo--light" href="index.html" aria-label="Hindu.International — Home">' + LOGO + '</a>' +
            '<p class="footer__tagline">Your Faith. Our Service.<br>Divine Blessings, Always.</p>' +
            '<ul class="footer__social" data-social></ul>' +
          '</div>' +
          '<div class="footer__col">' +
            '<h2 class="footer__title">Quick Links</h2>' +
            '<ul class="footer__links">' + quick + '</ul>' +
          '</div>' +
          '<div class="footer__col">' +
            '<h2 class="footer__title">Our Service</h2>' +
            '<ul class="footer__links">' +
              '<li><a href="booking.html">Virtual Punya Snan</a></li>' +
              '<li><a href="event.html">Event Details</a></li>' +
              '<li><a href="how-it-works.html#faq">FAQs</a></li>' +
              '<li><a href="privacy.html">Privacy Policy</a></li>' +
              '<li><a href="terms.html">Terms &amp; Conditions</a></li>' +
            '</ul>' +
          '</div>' +
        '</div>' +
        '<div class="container">' +
          '<div class="footer__bottom"><p>&copy; <span data-year>2025</span> Hindu.International. All rights reserved.</p></div>' +
        '</div>' +
      '</footer>';
  }

  /* This script is loaded right after the header placeholder, so the header
     renders immediately (no flash); the footer renders once the page is parsed. */
  injectSprite();
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

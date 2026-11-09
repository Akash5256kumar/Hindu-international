/* JSON-LD structured data — injected per page type for Google rich results */
(function () {
  'use strict';

  var BASE = 'https://hindu.international';
  var LOGO = BASE + '/assets/images/brand/favicon-192.png';
  var EMAIL = 'service@hindu.international';

  function inject(obj) {
    var s = document.createElement('script');
    s.type = 'application/ld+json';
    s.text = JSON.stringify(obj);
    document.head.appendChild(s);
  }

  /* Organisation — on every page */
  inject({
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Hindu.International',
    url: BASE,
    logo: { '@type': 'ImageObject', url: LOGO },
    description: 'Hindu.International brings Hindu religious and spiritual services together on one platform: online puja, jaap and paath, pandit booking, havan, pind daan, temple darshan and 12 Jyotirlinga puja seva.',
    contactPoint: { '@type': 'ContactPoint', email: EMAIL, contactType: 'customer service' },
    sameAs: sameAs()
  });

  /* Social profiles from Admin → Social links (only real https addresses) */
  function sameAs() {
    var social = (window.SITE_CONTENT || {}).social;
    var out = [];
    (function walk(v) {
      if (typeof v === 'string') { if (/^https:\/\/[^\s"<>]+$/i.test(v.trim()) && out.indexOf(v.trim()) === -1) out.push(v.trim()); }
      else if (v && typeof v === 'object') Object.keys(v).forEach(function (k) { walk(v[k]); });
    })(social);
    return out;
  }

  var page = (document.querySelector('meta[name="seo-page"]') || {}).content || '';

  /* Breadcrumb trail for the fixed pages (data only — nothing is shown on the page) */
  var CRUMBS = {
    about: 'About Us', how: 'How It Works', services: 'Services', faith: 'Protect Our Faith',
    donate: 'Donate', event: 'Event Information', booking: 'Book Virtual Punya Snan', contact: 'Contact Us'
  };
  if (CRUMBS[page]) {
    var canonical = (document.querySelector('link[rel="canonical"]') || {}).href || location.href;
    inject({
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Home', item: BASE + '/' },
        { '@type': 'ListItem', position: 2, name: CRUMBS[page], item: canonical }
      ]
    });
  }

  /* Home */
  if (page === 'home') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Hindu.International',
      url: BASE + '/',
      inLanguage: 'en'
    });
  }

  /* Booking */
  if (page === 'booking') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'Service',
      name: 'Virtual Punya Snan',
      serviceType: 'Religious Ritual Service',
      description: 'Submit your details and photograph. Our team performs the Punya Snan ritual at the sacred event and shares the recorded video.',
      provider: { '@type': 'Organization', name: 'Hindu.International', url: BASE },
      areaServed: { '@type': 'Country', name: 'Worldwide' },
      offers: { '@type': 'Offer', availability: 'https://schema.org/InStock', url: BASE + '/booking' }
    });
  }

  /* Services listing */
  if (page === 'services') {
    var svc = ((window.SITE_CONTENT || {}).services || {}).items;
    var shown = (Array.isArray(svc) ? svc : []).filter(function (s) {
      return s && s.show !== false && s.status === 'published' && s.design !== 'jaap' && /^[a-z0-9-]+$/.test(s.slug || '') && s.title;
    });
    var list = {
      '@context': 'https://schema.org',
      '@type': 'ItemList',
      name: 'Hindu.International Services',
      description: 'Hindu religious services: online puja, pandit booking, havan, pind daan, Graha Shanti, Jaap and Paath, temple darshan and 12 Jyotirlinga puja seva.',
      url: BASE + '/services'
    };
    if (shown.length) {
      list.numberOfItems = shown.length;
      list.itemListElement = shown.map(function (s, i) {
        return { '@type': 'ListItem', position: i + 1, name: s.title, url: BASE + '/' + (window.vpsItemSlug ? window.vpsItemSlug('service', s.slug) : s.slug) };
      });
    }
    inject(list);
  }

  /* Jyotirlinga */
  if (page === 'jyotirlinga') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'Service',
      name: 'Jyotirlinga Darshan & Puja Seva',
      serviceType: 'Religious Ritual Service',
      description: 'Darshan and puja seva at the twelve sacred Jyotirlingas of Lord Shiva arranged by Hindu.International.',
      provider: { '@type': 'Organization', name: 'Hindu.International', url: BASE }
    });
  }

  /* Contact */
  if (page === 'contact') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'ContactPage',
      name: 'Contact Hindu.International',
      url: BASE + '/contact-us',
      description: 'Contact Hindu.International for questions about pujas, jaap, paath, pind daan and other religious services.'
    });
  }

  /* Donate */
  if (page === 'donate') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'DonateAction',
      name: 'Donate to Hindu.International',
      description: 'Support education, healthcare, food and community development for vulnerable Hindu communities.',
      recipient: { '@type': 'Organization', name: 'Hindu.International', url: BASE }
    });
  }

  /* How It Works — FAQ */
  if (page === 'how') {
    var content = window.SITE_CONTENT || {};
    var howPage = content.howPage || {};
    var faqItems = howPage.showFaq === true && content.how && content.how.faq && content.how.faq.items;
    if (faqItems && faqItems.length) {
      inject({
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: faqItems.map(function (item) {
          return {
            '@type': 'Question',
            name: item.q,
            acceptedAnswer: { '@type': 'Answer', text: item.a }
          };
        })
      });
    }
    var howSteps = (Array.isArray(howPage.sections) ? howPage.sections : []).filter(function (s) {
      return s && s.show !== false && s.type === 'step' && s.title;
    });
    if (howSteps.length) {
      inject({
        '@context': 'https://schema.org',
        '@type': 'HowTo',
        name: (howPage.banner && howPage.banner.title) || 'How It Works',
        description: (howPage.banner && howPage.banner.text) || '',
        step: howSteps.map(function (s, i) {
          return { '@type': 'HowToStep', position: i + 1, name: s.title, text: String(s.text || '').replace(/\s*\n\s*/g, ' ').trim() };
        })
      });
    }
  }

  /* Event */
  if (page === 'event') {
    var evContent = window.SITE_CONTENT;
    var ev = evContent && evContent.events && evContent.events[0];
    if (ev && ev.name && ev.name.indexOf('[') === -1) {
      var schema = {
        '@context': 'https://schema.org',
        '@type': 'Event',
        name: ev.name,
        description: ev.significance || ev.shortDescription || '',
        organizer: { '@type': 'Organization', name: 'Hindu.International', url: BASE },
        eventAttendanceMode: 'https://schema.org/OnlineEventAttendanceMode',
        eventStatus: 'https://schema.org/EventScheduled'
      };
      if (ev.location) schema.location = { '@type': 'Place', name: ev.location };
      inject(schema);
    }
  }

  /* Faith */
  if (page === 'faith') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'Article',
      headline: 'Protect Our Faith — Hinduism Is Our Heritage',
      description: 'Learn about anti-conversion awareness, the protection of Hindu heritage and how to support freedom of faith.',
      author: { '@type': 'Organization', name: 'Hindu.International' },
      publisher: { '@type': 'Organization', name: 'Hindu.International', logo: { '@type': 'ImageObject', url: LOGO } }
    });
  }

  /* About */
  if (page === 'about') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'AboutPage',
      name: 'About Hindu.International',
      url: BASE + '/about-us',
      description: 'Hindu.international brings pujas, jaap, paath, temple services and Hindu traditions together on one platform.'
    });
  }

})();

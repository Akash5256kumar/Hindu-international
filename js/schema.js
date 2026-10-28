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
    description: 'Hindu.International performs sacred Hindu rituals on behalf of devotees who cannot attend in person, and shares the recorded video.',
    contactPoint: { '@type': 'ContactPoint', email: EMAIL, contactType: 'customer service' },
    sameAs: []
  });

  var page = (document.querySelector('meta[name="seo-page"]') || {}).content || '';

  /* Home */
  if (page === 'home') {
    inject({
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Hindu.International',
      url: BASE
    });
    inject({
      '@context': 'https://schema.org',
      '@type': 'Service',
      name: 'Virtual Punya Snan',
      serviceType: 'Religious Ritual Service',
      description: 'We perform the holy Punya Snan ritual on your behalf using your photograph and share the complete recorded video with you.',
      provider: { '@type': 'Organization', name: 'Hindu.International', url: BASE },
      areaServed: { '@type': 'Country', name: 'Worldwide' },
      offers: { '@type': 'Offer', availability: 'https://schema.org/InStock', url: BASE + '/booking' }
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
    inject({
      '@context': 'https://schema.org',
      '@type': 'ItemList',
      name: 'Hindu.International Services',
      description: 'Spiritual services offered by Hindu.International — Virtual Punya Snan, puja seva, Jyotirlinga darshan and more.',
      url: BASE + '/services'
    });
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
      description: 'Contact Hindu.International for questions about Virtual Punya Snan or general enquiries.'
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
    var content = window.SITE_CONTENT;
    var faqItems = content && content.about && content.about.how && content.about.how.faq && content.about.how.faq.items;
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
    inject({
      '@context': 'https://schema.org',
      '@type': 'HowTo',
      name: 'How to Request Virtual Punya Snan',
      description: 'A simple process to connect you with divine blessings through Virtual Punya Snan.',
      step: [
        { '@type': 'HowToStep', name: 'Submit Request', text: 'Fill in your details and upload your photograph on the booking page.' },
        { '@type': 'HowToStep', name: 'We Perform Ritual', text: 'Our team performs the holy Punya Snan ritual at the sacred site using your photograph.' },
        { '@type': 'HowToStep', name: 'Video Recording', text: 'The complete ritual is recorded from start to finish.' },
        { '@type': 'HowToStep', name: 'Receive Video', text: 'We share the full video recording with you.' }
      ]
    });
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
      description: 'Hindu.International helps devotees participate in sacred rituals from anywhere in the world.'
    });
  }

})();

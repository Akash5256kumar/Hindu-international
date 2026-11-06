/* =====================================================================
   PAGE SEO for pages built from content (service, jyotirlinga, jaap):
   title, description, canonical, Open Graph / Twitter tags and JSON-LD
   (breadcrumb trail, the page's main entity and its FAQs).
   Usage: window.vpsPageSeo({ title, description, url, image, breadcrumb: [[name, url], …],
                               entity: {…schema.org object…}, faqs: [{ q, a }, …] })
   ===================================================================== */
(function () {
  'use strict';

  var ORIGIN = 'https://hindu.international';
  var ORG = { '@type': 'Organization', name: 'Hindu.International', url: ORIGIN };

  function str(v) { return v == null ? '' : String(v).replace(/\s+/g, ' ').trim(); }
  function abs(path) {
    var p = str(path);
    if (!p) return '';
    if (/^https?:\/\//i.test(p)) return p;
    return /^(assets|uploads)\/[\w\-./]+$/.test(p) && p.indexOf('..') === -1 ? ORIGIN + '/' + p : '';
  }
  function tag(kind, key, value) {
    if (!value) return;
    var isLink = kind === 'rel';
    var node = document.head.querySelector(isLink ? 'link[rel="' + key + '"]' : 'meta[' + kind + '="' + key + '"]');
    if (!node) {
      node = document.createElement(isLink ? 'link' : 'meta');
      if (isLink) node.rel = key; else node.setAttribute(kind, key);
      document.head.appendChild(node);
    }
    node.setAttribute(isLink ? 'href' : 'content', value);
  }
  function jsonLd(id, data) {
    var node = document.getElementById(id);
    if (!node) {
      node = document.createElement('script');
      node.type = 'application/ld+json';
      node.id = id;
      document.head.appendChild(node);
    }
    node.text = JSON.stringify(data);
  }

  window.vpsPageSeo = function (o) {
    var title = str(o.title);
    var desc = str(o.description);
    if (desc.length > 300) desc = desc.slice(0, 297).replace(/\s+\S*$/, '') + '…';
    var url = str(o.url);
    var image = abs(o.image);

    if (title) document.title = title;
    tag('name', 'description', desc);
    tag('rel', 'canonical', url);
    tag('property', 'og:type', 'website');
    tag('property', 'og:title', title);
    tag('property', 'og:description', desc);
    tag('property', 'og:url', url);
    tag('property', 'og:image', image);
    tag('name', 'twitter:card', image ? 'summary_large_image' : 'summary');
    tag('name', 'twitter:title', title);
    tag('name', 'twitter:description', desc);
    tag('name', 'twitter:image', image);

    var crumbs = (o.breadcrumb || []).filter(function (c) { return c && str(c[0]); });
    if (crumbs.length) {
      jsonLd('ld-breadcrumb', {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: crumbs.map(function (c, i) {
          var item = { '@type': 'ListItem', position: i + 1, name: str(c[0]) };
          if (c[1]) item.item = c[1];
          return item;
        })
      });
    }
    if (o.entity) {
      var entity = { '@context': 'https://schema.org' };
      Object.keys(o.entity).forEach(function (k) { entity[k] = o.entity[k]; });
      if (!entity.url) entity.url = url;
      if (!entity.description && desc) entity.description = desc;
      if (!entity.image && image) entity.image = image;
      if (entity['@type'] === 'Service' && !entity.provider) entity.provider = ORG;
      jsonLd('ld-entity', entity);
    }
    var faqs = (o.faqs || []).filter(function (f) { return f && str(f.q) && str(f.a); });
    if (faqs.length) {
      jsonLd('ld-faq', {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: faqs.map(function (f) {
          return { '@type': 'Question', name: str(f.q), acceptedAnswer: { '@type': 'Answer', text: str(f.a) } };
        })
      });
    }
  };
  /* "Page not found" views must not be indexed */
  window.vpsPageNoindex = function () { tag('name', 'robots', 'noindex,follow'); };
  window.vpsSeoOrigin = ORIGIN;
})();

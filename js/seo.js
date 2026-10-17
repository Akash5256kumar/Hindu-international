/* Apply admin-managed page metadata as soon as the content is available. */
(function () {
  'use strict';

  var page = document.querySelector('meta[name="seo-page"]');
  var all = window.SITE_CONTENT || {};
  var seo = page && all.seo && all.seo.pages
    ? all.seo.pages[page.content]
    : null;
  if (!seo) return;

  function setMeta(attribute, name, value) {
    var node = document.querySelector('meta[' + attribute + '="' + name + '"]');
    if (!value) {
      if (node) node.remove();
      return;
    }
    if (!node) {
      node = document.createElement('meta');
      node.setAttribute(attribute, name);
      document.head.appendChild(node);
    }
    node.content = value;
  }

  function absoluteHttpUrl(value) {
    try {
      var url = new URL(value, location.origin);
      return /^https?:$/.test(url.protocol) ? url.href : '';
    } catch (error) {
      return '';
    }
  }

  var title = String(seo.title || '').trim();
  var description = String(seo.description || '').trim();
  var canonical = absoluteHttpUrl(String(seo.canonical || '').trim());
  var index = seo.index !== false;
  var follow = seo.follow !== false;
  var socialTitle = String(seo.ogTitle || '').trim() || title;
  var socialDescription = String(seo.ogDescription || '').trim() || description;
  var socialImage = absoluteHttpUrl(String(seo.ogImage || '').trim());

  if (title) document.title = title;
  if (canonical) {
    var link = document.querySelector('link[rel="canonical"]');
    if (!link) {
      link = document.createElement('link');
      link.rel = 'canonical';
      document.head.appendChild(link);
    }
    link.href = canonical;
  }

  setMeta('name', 'description', description);
  setMeta('name', 'keywords', String(seo.keywords || '').trim());
  setMeta('name', 'robots', (index ? 'index' : 'noindex') + ',' + (follow ? 'follow' : 'nofollow'));
  setMeta('property', 'og:title', socialTitle);
  setMeta('property', 'og:description', socialDescription);
  setMeta('property', 'og:url', canonical);
  setMeta('property', 'og:image', socialImage);
  setMeta('name', 'twitter:card', socialImage ? 'summary_large_image' : 'summary');
  setMeta('name', 'twitter:title', socialTitle);
  setMeta('name', 'twitter:description', socialDescription);
  setMeta('name', 'twitter:image', socialImage);
})();

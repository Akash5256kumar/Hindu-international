/* Home hero slider: crossfade + Ken Burns images, word-by-word heading reveal.
   Timings live in css/hero-slider.css; this file only switches classes. */
(function () {
  'use strict';

  var hero = document.querySelector('.hero--slider');
  if (!hero) return;

  var SLIDE_MS = 5000;
  var IMG_LEAVE_MS = 1250;
  var TEXT_LEAVE_MS = 500;

  var imgs = [].slice.call(hero.querySelectorAll('.hero__img'));
  var texts = [].slice.call(hero.querySelectorAll('.hero__slide'));
  var count = Math.min(imgs.length, texts.length);
  if (!count) return;

  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var current = 0;
  var timer = null;

  /* Wrap every heading word in <span class="w"><span>word</span></span> */
  texts.forEach(function (slide) {
    var heading = slide.querySelector('.hero__title');
    if (!heading) return;
    heading.setAttribute('aria-label', heading.textContent.replace(/\s+/g, ' ').trim());
    var index = 0;
    (function walk(node) {
      [].slice.call(node.childNodes).forEach(function (child) {
        if (child.nodeType === 3) {
          var frag = document.createDocumentFragment();
          child.nodeValue.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            var w = document.createElement('span');
            w.className = 'w';
            w.setAttribute('aria-hidden', 'true');
            w.style.setProperty('--i', index++);
            var inner = document.createElement('span');
            inner.textContent = part;
            w.appendChild(inner);
            frag.appendChild(w);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1) {
          walk(child);
        }
      });
    })(heading);
  });

  function isLoaded(img) { return img.complete && img.naturalWidth > 0; }

  function whenLoaded(img, fn) {
    if (isLoaded(img)) { fn(); return; }
    img.addEventListener('load', fn, { once: true });
    img.addEventListener('error', fn, { once: true });
  }

  function setActiveText(slide, on) {
    if (on) { slide.removeAttribute('inert'); slide.removeAttribute('aria-hidden'); }
    else { slide.setAttribute('inert', ''); slide.setAttribute('aria-hidden', 'true'); }
  }

  function schedule() {
    clearTimeout(timer);
    if (reduce || document.hidden) return;
    timer = setTimeout(function () { go((current + 1) % count); }, SLIDE_MS);
  }

  function swap(next) {
    var prevImg = imgs[current], prevText = texts[current];
    prevImg.classList.remove('is-active');
    prevImg.classList.add('is-leaving');
    prevText.classList.remove('is-active');
    prevText.classList.add('is-leaving');
    setActiveText(prevText, false);

    imgs[next].classList.remove('is-leaving');
    texts[next].classList.remove('is-leaving');
    imgs[next].classList.add('is-active');
    texts[next].classList.add('is-active');
    setActiveText(texts[next], true);

    setTimeout(function () { prevImg.classList.remove('is-leaving'); }, IMG_LEAVE_MS);
    setTimeout(function () { prevText.classList.remove('is-leaving'); }, TEXT_LEAVE_MS);

    current = next;
    schedule();
  }

  function go(next) {
    if (next === current) return;
    clearTimeout(timer);
    whenLoaded(imgs[next], function () { swap(next); });
  }

  /* Other slide images load only after the first one is on screen */
  function loadRest() {
    imgs.forEach(function (img, i) {
      if (i === 0 || !img.getAttribute('data-src')) return;
      if (img.getAttribute('data-srcset')) {
        img.sizes = '100vw';
        img.srcset = img.getAttribute('data-srcset');
      }
      img.src = img.getAttribute('data-src');
      img.removeAttribute('data-src');
      img.removeAttribute('data-srcset');
    });
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) clearTimeout(timer); else schedule();
  });

  whenLoaded(imgs[0], function () {
    loadRest();
    if (!reduce) schedule();
  });
})();

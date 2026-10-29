/* Havan slide fire animation — Level 3 (High).
   Canvas overlay on slide-2-havan: ghee drip + fire glow flicker + diya glow sway.
   Only runs while that slide is the active hero slide. */
(function () {
  'use strict';

  var CFG = {
    spoonTip:  { x: 0.763, y: 0.644 },
    fireEntry: { x: 0.780, y: 0.715 },
    fireBox:   { x: 0.740, y: 0.620, w: 0.090, h: 0.200 },
    diyaFlame: { x: 0.941, y: 0.481 },
  };

  var CYCLE = 5.2, GROW_END = 1.0, FALL_END = 2.6, IMPACT_END = 3.2;
  /* Level 3 brightness */
  var FIRE_BASE_A = 0.46, FIRE_IMP_A = 0.50, DIYA_BASE_A = 0.54;

  var cv, ctx, W = 0, H = 0, raf = 0, startTs = null, havanImg = null;

  function noise(t) {
    return Math.sin(t * 1.7) * 0.50 + Math.sin(t * 3.1) * 0.30 + Math.sin(t * 6.3) * 0.20;
  }

  function getImpact(t) {
    var ph = t % CYCLE;
    if (ph < FALL_END || ph >= IMPACT_END) return 0;
    var p = (ph - FALL_END) / (IMPACT_END - FALL_END);
    return p < 0.3 ? p / 0.3 : 1 - (p - 0.3) / 0.7;
  }

  /* ── Ghee drop ── */
  function drawDrop(x, y, r, elong, alpha) {
    ctx.save();
    ctx.globalAlpha = alpha;
    var grd = ctx.createRadialGradient(x - r * 0.15, y - r * 0.15, 0, x, y, r + elong);
    grd.addColorStop(0,   'rgba(255,245,200,0.95)');
    grd.addColorStop(0.4, 'rgba(255,215,100,0.88)');
    grd.addColorStop(0.8, 'rgba(220,170,50,0.72)');
    grd.addColorStop(1,   'rgba(180,130,30,0)');
    ctx.beginPath();
    ctx.ellipse(x, y, r, r + elong, 0, 0, Math.PI * 2);
    ctx.fillStyle = grd;
    ctx.fill();
    ctx.restore();
  }

  function drawGheeDrop(t) {
    var ph = t % CYCLE;
    var spx = CFG.spoonTip.x  * W, spy = CFG.spoonTip.y  * H;
    var fex = CFG.fireEntry.x * W, fey = CFG.fireEntry.y * H;

    if (ph < GROW_END) {
      var p = ph / GROW_END, r = 3 * p, b = r * (0.5 + 0.5 * p);
      drawDrop(spx, spy + b, r, b, 0.85 + 0.15 * p);

    } else if (ph < FALL_END) {
      var p = (ph - GROW_END) / (FALL_END - GROW_END);
      var dropX = spx + (fex - spx) * p;
      var dropY = spy + (fey - spy) * p * p;
      var ta = Math.max(0, 1 - p * 2);
      if (ta > 0) {
        ctx.save();
        ctx.globalAlpha = ta * 0.65;
        ctx.beginPath();
        ctx.moveTo(spx, spy + 3);
        ctx.lineTo(dropX, dropY - 3);
        ctx.strokeStyle = '#f5c060';
        ctx.lineWidth = 1.2;
        ctx.stroke();
        ctx.restore();
      }
      drawDrop(dropX, dropY, 3, 1.5 + p * 2, 0.9);

    } else if (ph < IMPACT_END) {
      var frac = (ph - FALL_END) / (IMPACT_END - FALL_END);
      var a = 1 - frac, r = 8 + 10 * frac;
      ctx.save();
      ctx.globalCompositeOperation = 'screen';
      var grd = ctx.createRadialGradient(fex, fey, 0, fex, fey, r);
      grd.addColorStop(0,   'rgba(255,245,160,' + (0.9 * a) + ')');
      grd.addColorStop(0.4, 'rgba(255,200,80,'  + (0.6 * a) + ')');
      grd.addColorStop(1,   'rgba(255,150,30,0)');
      ctx.fillStyle = grd;
      ctx.beginPath();
      ctx.arc(fex, fey, r, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }
  }

  /* ── Fire glow: brightens the existing photo fire on every flicker + ghee impact ── */
  function drawFire(t) {
    var b  = CFG.fireBox;
    var fx = (b.x + b.w * 0.45) * W;
    var fy = (b.y + b.h * 0.38) * H;
    var fw = b.w * W, fh = b.h * H;
    var flick  = 0.55 + 0.52 * Math.abs(noise(t * 4.2 + 1.3));
    var alpha  = FIRE_BASE_A * flick + getImpact(t) * FIRE_IMP_A;

    ctx.save();
    ctx.globalCompositeOperation = 'screen';
    var gr = ctx.createRadialGradient(fx, fy, 0, fx, fy, fw * 0.9);
    gr.addColorStop(0,    'rgba(255,220,100,' + alpha + ')');
    gr.addColorStop(0.35, 'rgba(255,170,50,'  + (alpha * 0.65) + ')');
    gr.addColorStop(0.70, 'rgba(255,120,20,'  + (alpha * 0.30) + ')');
    gr.addColorStop(1,    'rgba(255,80,0,0)');
    ctx.fillStyle = gr;
    ctx.beginPath();
    ctx.ellipse(fx, fy, fw * 0.9, fh * 0.72, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }

  /* ── Diya glow: brightens + sways the existing diya lamp in the photo ── */
  function drawDiya(t) {
    var dx = CFG.diyaFlame.x * W, dy = CFG.diyaFlame.y * H;
    var gx = dx + W * 0.004 * noise(t * 2.6 + 3.1);
    var gy = dy + H * 0.003 * noise(t * 3.4 + 7.8);
    var flick = 0.50 + 0.58 * Math.abs(noise(t * 5.8 + 8.7));
    var alpha = DIYA_BASE_A * flick;
    var r = W * 0.018;

    ctx.save();
    ctx.globalCompositeOperation = 'screen';
    var gr = ctx.createRadialGradient(gx, gy, 0, gx, gy, r);
    gr.addColorStop(0,    'rgba(255,230,110,' + alpha + ')');
    gr.addColorStop(0.45, 'rgba(255,170,50,'  + (alpha * 0.55) + ')');
    gr.addColorStop(1,    'rgba(255,110,15,0)');
    ctx.fillStyle = gr;
    ctx.beginPath();
    ctx.arc(gx, gy, r, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }

  function render(ts) {
    if (!startTs) startTs = ts;
    var t = (ts - startTs) / 1000;
    ctx.clearRect(0, 0, W, H);
    drawGheeDrop(t);
    drawFire(t);
    drawDiya(t);
    raf = requestAnimationFrame(render);
  }

  function start() {
    if (raf) return;
    startTs = null;
    raf = requestAnimationFrame(render);
  }

  function stop() {
    if (raf) { cancelAnimationFrame(raf); raf = 0; }
  }

  function resize() {
    if (!cv || !havanImg) return;
    W = cv.width  = havanImg.clientWidth;
    H = cv.height = havanImg.clientHeight;
  }

  function init() {
    var hero = document.querySelector('.hero--slider');
    if (!hero) return;

    cv = document.getElementById('havan-cv');
    if (!cv) return;
    ctx = cv.getContext('2d');

    /* Find the havan slide image whether built from HTML or admin content */
    [].slice.call(hero.querySelectorAll('.hero__img')).forEach(function (img) {
      var s = img.src || img.getAttribute('data-src') || '';
      if (s.indexOf('slide-2-havan') !== -1) havanImg = img;
    });
    if (!havanImg) return;

    resize();
    new ResizeObserver(resize).observe(havanImg);

    /* Start/stop animation as slide becomes active/inactive */
    new MutationObserver(function () {
      if (havanImg.classList.contains('is-active')) {
        cv.classList.add('is-active');
        start();
      } else {
        cv.classList.remove('is-active');
        stop();
      }
    }).observe(havanImg, { attributes: true, attributeFilter: ['class'] });

    /* Pause when tab is hidden */
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop();
      else if (havanImg.classList.contains('is-active')) start();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/* ============================================================
   PACK & CARRY — site interactions
   1. Navbar scroll state
   2. Scroll reveal
   3. Hero slider
   4. Image fallback
   ============================================================ */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --------------------------------------------------------
       1. NAVBAR SCROLL STATE
       -------------------------------------------------------- */
    var nav = document.getElementById('pcNav');
    if (nav) {
        var onScroll = function () {
            nav.classList.toggle('is-scrolled', window.scrollY > 24);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* --------------------------------------------------------
       2. SCROLL REVEAL
       -------------------------------------------------------- */
    var revealEls = document.querySelectorAll('.pc-reveal, .pc-reveal-img');
    if (revealEls.length) {
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealEls.forEach(function (el) { el.classList.add('is-visible'); });
        } else {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' });
            revealEls.forEach(function (el) { io.observe(el); });

            // Failsafe: never leave content hidden if the observer misses
            // (e.g. very fast scroll, print, or a rendering quirk).
            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    revealEls.forEach(function (el) {
                        var rect = el.getBoundingClientRect();
                        if (rect.top < window.innerHeight * 1.15) {
                            el.classList.add('is-visible');
                        }
                    });
                }, 300);
            });
        }
    }

    /* --------------------------------------------------------
       3. HERO SLIDER
       -------------------------------------------------------- */
    var hero = document.querySelector('[data-hero-slider]');
    if (hero) {
        var slides = Array.prototype.slice.call(hero.querySelectorAll('.pc-slide'));
        var bars = Array.prototype.slice.call(hero.querySelectorAll('.pc-hero__bar'));
        var prevBtn = hero.querySelector('[data-hero-prev]');
        var nextBtn = hero.querySelector('[data-hero-next]');
        var curEl = hero.querySelector('[data-hero-current]');
        var totalEl = hero.querySelector('[data-hero-total]');
        var DURATION = parseInt(hero.getAttribute('data-hero-duration'), 10) || 6000;
        var index = 0;
        var timer = null;
        var isPaused = false;

        if (totalEl) { totalEl.textContent = String(slides.length).padStart(2, '0'); }

        function setBars(active) {
            bars.forEach(function (bar, i) {
                bar.classList.toggle('is-active', i === active && !reduceMotion && !isPaused);
                bar.classList.toggle('is-done', i < active);
                var span = bar.querySelector('span');
                if (span) {
                    // restart the CSS animation
                    span.style.animation = 'none';
                    void span.offsetWidth;
                    span.style.animation = '';
                }
            });
        }

        function go(next, opts) {
            opts = opts || {};
            index = (next + slides.length) % slides.length;
            slides.forEach(function (s, i) {
                s.classList.toggle('is-active', i === index);
            });
            if (curEl) { curEl.textContent = String(index + 1).padStart(2, '0'); }
            setBars(index);
            if (!opts.silent) { restart(); }
        }

        function restart() {
            window.clearTimeout(timer);
            if (reduceMotion || isPaused) { return; }
            timer = window.setTimeout(function () { go(index + 1); }, DURATION);
        }

        if (prevBtn) { prevBtn.addEventListener('click', function () { go(index - 1); }); }
        if (nextBtn) { nextBtn.addEventListener('click', function () { go(index + 1); }); }
        bars.forEach(function (bar, i) {
            bar.addEventListener('click', function () { go(i); });
        });

        var pause = function () { isPaused = true; window.clearTimeout(timer); setBars(-1); };
        var resume = function () { if (isPaused) { isPaused = false; setBars(index); restart(); } };
        hero.addEventListener('mouseenter', pause);
        hero.addEventListener('mouseleave', resume);
        hero.addEventListener('focusin', pause);
        hero.addEventListener('focusout', resume);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { window.clearTimeout(timer); }
            else { restart(); }
        });

        // keyboard support
        hero.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { go(index - 1); }
            if (e.key === 'ArrowRight') { go(index + 1); }
        });

        go(0, { silent: true });
        restart();
    }

    /* --------------------------------------------------------
       4. IMAGE FALLBACK — keep layout intentional if a
          remote image fails to load
       -------------------------------------------------------- */
    document.querySelectorAll('img[data-fallback]').forEach(function (img) {
        img.addEventListener('error', function () {
            var host = img.closest('[data-media]') || img.parentElement;
            if (host) { host.classList.add('pc-media-failed'); }
            img.style.visibility = 'hidden';
        });
    });
})();

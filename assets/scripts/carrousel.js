(function () {
    'use strict';

    var reduitMouvement = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.carrousel').forEach(function (carrousel) {
        var slides = Array.prototype.slice.call(carrousel.querySelectorAll('.carrousel-slide'));
        if (slides.length < 2) return;

        var dots      = Array.prototype.slice.call(carrousel.querySelectorAll('.carrousel-dot'));
        var btnPrec   = carrousel.querySelector('.carrousel-precedent');
        var btnSuiv   = carrousel.querySelector('.carrousel-suivant');
        var delai     = parseInt(carrousel.dataset.autoplay, 10) || 5000;
        var actif     = slides.findIndex(function (s) { return s.classList.contains('actif'); });
        if (actif < 0) actif = 0;
        var minuteur  = null;
        var visible   = true;

        function allerA(index) {
            index = (index + slides.length) % slides.length;
            if (index === actif) return;
            slides[actif].classList.remove('actif');
            if (dots[actif]) {
                dots[actif].classList.remove('actif');
                dots[actif].setAttribute('aria-selected', 'false');
            }
            actif = index;
            slides[actif].classList.add('actif');
            if (dots[actif]) {
                dots[actif].classList.add('actif');
                dots[actif].setAttribute('aria-selected', 'true');
            }
        }

        function suivant() { allerA(actif + 1); }
        function precedent() { allerA(actif - 1); }

        function demarrer() {
            if (reduitMouvement || minuteur || !visible) return;
            minuteur = setInterval(suivant, delai);
        }
        function arreter() {
            clearInterval(minuteur);
            minuteur = null;
        }

        if (btnSuiv) btnSuiv.addEventListener('click', function () { suivant(); arreter(); demarrer(); });
        if (btnPrec) btnPrec.addEventListener('click', function () { precedent(); arreter(); demarrer(); });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { allerA(i); arreter(); demarrer(); });
        });

        carrousel.addEventListener('mouseenter', arreter);
        carrousel.addEventListener('mouseleave', demarrer);
        carrousel.addEventListener('focusin', arreter);
        carrousel.addEventListener('focusout', demarrer);

        carrousel.setAttribute('tabindex', '0');
        carrousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { precedent(); arreter(); demarrer(); }
            if (e.key === 'ArrowRight') { suivant(); arreter(); demarrer(); }
        });

        /* Glissement tactile basique */
        var xDepart = null;
        carrousel.addEventListener('touchstart', function (e) {
            xDepart = e.touches[0].clientX;
        }, { passive: true });
        carrousel.addEventListener('touchend', function (e) {
            if (xDepart === null) return;
            var delta = e.changedTouches[0].clientX - xDepart;
            if (Math.abs(delta) > 40) {
                if (delta < 0) suivant(); else precedent();
                arreter();
                demarrer();
            }
            xDepart = null;
        });

        /* Pause hors du champ de vision : pas d'intérêt à faire tourner
           un carrousel que personne ne regarde. */
        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function (entrees) {
                entrees.forEach(function (entree) {
                    visible = entree.isIntersecting;
                    if (visible) demarrer(); else arreter();
                });
            }, { threshold: 0.2 });
            obs.observe(carrousel);
        } else {
            demarrer();
        }
    });
})();

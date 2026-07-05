(function () {
    'use strict';

    var form = document.getElementById('pm-formulaire');
    if (!form) return;

    var totalEl   = document.getElementById('pm-total-montant');
    var barreEl   = document.getElementById('pm-barre-total');
    var msgSucces = document.getElementById('pm-msg-succes');
    var msgErreur = document.getElementById('pm-msg-erreur');
    var btnSubmit = document.getElementById('pm-btn-soumettre');

    /* -------------------------------------------------------
       Calcul du total
    ------------------------------------------------------- */
    function calculerTotal() {
        var total = 0;
        document.querySelectorAll('.pam-produit-item:not(.pam-clone) .pam-qty-input').forEach(function (input) {
            var item = input.closest('.pam-produit-item');
            var prix = parseFloat(item.dataset.prix) || 0;
            var qty  = parseInt(input.value, 10)     || 0;
            total   += prix * qty;
        });
        totalEl.textContent = total.toFixed(2).replace('.', ',') + ' $';
        barreEl.classList.toggle('pam-barre-total--active', total > 0);
    }

    /* -------------------------------------------------------
       Carousels : easing + boucle infinie par clonage
    ------------------------------------------------------- */
    var DELAI_AUTOPLAY = 15000;
    var carouselTimers = [];

    function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }

    function scrollSmooth(track, cible, duree) {
        if (track._animId) { cancelAnimationFrame(track._animId); track._animId = null; }
        var debut     = track.scrollLeft;
        var distance  = cible - debut;
        var startTime = null;

        function step(ts) {
            if (!startTime) startTime = ts;
            var progress     = Math.min((ts - startTime) / duree, 1);
            track.scrollLeft = debut + distance * easeInOutCubic(progress);
            if (progress < 1) {
                track._animId = requestAnimationFrame(step);
            } else {
                track._animId = null;
            }
        }
        track._animId = requestAnimationFrame(step);
    }

    function largeurCarte(track) {
        var carte = track.querySelector('.pam-produit-item:not(.pam-clone)');
        return carte ? carte.offsetWidth + 16 : 300;
    }

    function setupClones(track) {
        track.querySelectorAll('.pam-clone').forEach(function (c) { c.remove(); });

        var cartes = Array.from(track.querySelectorAll('.pam-produit-item'));
        if (cartes.length < 2) return 0;

        cartes.forEach(function (carte) {
            var clone = carte.cloneNode(true);
            clone.classList.add('pam-clone');
            clone.setAttribute('aria-hidden', 'true');
            clone.querySelectorAll('input, button').forEach(function (el) {
                el.disabled = true;
                el.setAttribute('tabindex', '-1');
            });
            track.appendChild(clone);
        });

        return track.scrollWidth / 2;
    }

    function initCarousel(carousel) {
        var track = carousel.querySelector('.pam-produits-grille');
        if (!track) return null;

        if (track._loopListener) track.removeEventListener('scroll', track._loopListener);
        if (track._animId) { cancelAnimationFrame(track._animId); track._animId = null; }
        track.scrollLeft = 0;

        var largeurOri = setupClones(track);
        var timer  = null;
        var pause  = false;
        var drag   = false;
        var dragX  = 0;
        var dragSL = 0;

        track._animTarget = null;

        track._loopListener = function () {
            if (largeurOri > 0 && track.scrollLeft >= largeurOri) {
                if (track._animId) { cancelAnimationFrame(track._animId); track._animId = null; }
                track.scrollLeft -= largeurOri;
                if (track._animTarget != null) track._animTarget -= largeurOri;
            }
        };
        track.addEventListener('scroll', track._loopListener);

        function avancer() {
            if (pause) return;
            scrollSmooth(track, track.scrollLeft + largeurCarte(track), 900);
        }

        function demarrer() {
            arreter();
            timer = setInterval(avancer, DELAI_AUTOPLAY);
        }

        function arreter() {
            clearInterval(timer);
            timer = null;
        }

        carousel.addEventListener('mouseenter', function () { if (!drag) { pause = true; arreter(); } });
        carousel.addEventListener('mouseleave', function () { if (!drag) { pause = false; demarrer(); } });

        /* Drag-to-scroll */
        track.addEventListener('mousedown', function (e) {
            if (track._animId) { cancelAnimationFrame(track._animId); track._animId = null; }
            drag   = true;
            dragX  = e.pageX;
            dragSL = track.scrollLeft;
            track.classList.add('pam-drag-actif');
            pause  = true;
            arreter();
        });
        document.addEventListener('mousemove', function (e) {
            if (!drag) return;
            track.scrollLeft = dragSL - (e.pageX - dragX);
        });
        document.addEventListener('mouseup', function () {
            if (!drag) return;
            drag = false;
            track.classList.remove('pam-drag-actif');
            setTimeout(function () { pause = false; demarrer(); }, 800);
        });

        /* Touch */
        track.addEventListener('touchstart', function () { pause = true; arreter(); }, { passive: true });
        track.addEventListener('touchend', function () {
            setTimeout(function () { pause = false; demarrer(); }, 1500);
        }, { passive: true });

        /* Flèches desktop */
        var btnPrev = carousel.querySelector('.pam-fleche-prev');
        var btnNext = carousel.querySelector('.pam-fleche-next');
        if (btnPrev) {
            btnPrev.addEventListener('click', function () {
                arreter();
                scrollSmooth(track, track.scrollLeft - largeurCarte(track), 380);
                setTimeout(function () { pause = false; demarrer(); }, 1200);
            });
        }
        if (btnNext) {
            btnNext.addEventListener('click', function () {
                arreter();
                scrollSmooth(track, track.scrollLeft + largeurCarte(track), 380);
                setTimeout(function () { pause = false; demarrer(); }, 1200);
            });
        }

        return { demarrer: demarrer, arreter: arreter, track: track };
    }

    function initCarousels() {
        carouselTimers.forEach(function (c) { c.arreter(); });
        carouselTimers = [];

        document.querySelectorAll('.pam-carousel').forEach(function (carousel) {
            var cat = carousel.closest('.pam-categorie');
            if (cat && cat.hidden) return;
            var ctrl = initCarousel(carousel);
            if (ctrl) {
                carouselTimers.push(ctrl);
                ctrl.demarrer();
            }
        });
    }
    initCarousels();

    /* -------------------------------------------------------
       Onglets catégorie
    ------------------------------------------------------- */
    var categorieActive = '';

    document.querySelectorAll('.pam-cat-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            categorieActive = btn.dataset.cat;

            document.querySelectorAll('.pam-categorie').forEach(function (cat) {
                cat.hidden = cat.dataset.cat !== categorieActive;
            });
            document.querySelectorAll('.pam-cat-tab').forEach(function (tab) {
                tab.classList.toggle('pam-cat-tab--active', tab.dataset.cat === categorieActive);
            });

            carouselTimers.forEach(function (c) { c.track.scrollLeft = 0; });
            initCarousels();
        });
    });

    /* Premier onglet actif par défaut */
    var premierTab = document.querySelector('.pam-cat-tab');
    if (premierTab) premierTab.click();

    /* -------------------------------------------------------
       Boutons +/-
    ------------------------------------------------------- */
    form.addEventListener('click', function (e) {
        var btn = e.target.closest('.pam-qty-moins, .pam-qty-plus');
        if (!btn) return;
        var controle = btn.closest('.pam-qty-controle');
        var input    = controle ? controle.querySelector('.pam-qty-input') : null;
        if (!input) return;
        var val = parseInt(input.value, 10) || 0;
        val = btn.classList.contains('pam-qty-moins') ? Math.max(0, val - 1) : Math.min(20, val + 1);
        input.value = val;
        calculerTotal();
    });

    form.addEventListener('input', function (e) {
        if (e.target.classList.contains('pam-qty-input')) calculerTotal();
        if (e.target.closest('.pam-champ')) {
            e.target.classList.remove('pam-invalide');
            var champ = e.target.closest('.pam-champ');
            champ.classList.remove('pam-champ--erreur');
            var hint = champ.querySelector('.pam-hint-erreur');
            if (hint) hint.remove();
        }
    });

    /* -------------------------------------------------------
       Validation côté client
    ------------------------------------------------------- */
    function marquerInvalide(input, message) {
        input.classList.add('pam-invalide');
        var champ = input.closest('.pam-champ');
        if (champ) {
            champ.classList.add('pam-champ--erreur');
            if (!champ.querySelector('.pam-hint-erreur')) {
                var hint = document.createElement('span');
                hint.className = 'pam-hint-erreur';
                hint.textContent = message;
                champ.appendChild(hint);
            }
        }
    }

    function validerFormulaire() {
        var erreurs = false;
        var premier = null;

        var regles = [
            { name: 'prenom', msg: 'Veuillez entrer votre prénom.' },
            { name: 'nom',    msg: 'Veuillez entrer votre nom.' },
            { name: 'email',  msg: 'Veuillez entrer votre adresse courriel.' },
        ];

        regles.forEach(function (regle) {
            var input = form.querySelector('[name="' + regle.name + '"]');
            if (!input) return;
            input.classList.remove('pam-invalide');
            var champ = input.closest('.pam-champ');
            if (champ) {
                champ.classList.remove('pam-champ--erreur');
                var hint = champ.querySelector('.pam-hint-erreur');
                if (hint) hint.remove();
            }
            if (!input.value.trim()) {
                marquerInvalide(input, regle.msg);
                if (!premier) premier = input;
                erreurs = true;
            }
        });

        var emailInput = form.querySelector('[name="email"]');
        if (emailInput && emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            marquerInvalide(emailInput, 'Adresse courriel invalide.');
            if (!premier) premier = emailInput;
            erreurs = true;
        }

        if (erreurs && premier) {
            var section = document.querySelector('.pam-infos-client');
            if (section) {
                var top = section.getBoundingClientRect().top + window.pageYOffset - 120;
                window.scrollTo({ top: top, behavior: 'smooth' });
                setTimeout(function () { premier.focus(); }, 400);
            }
            return false;
        }
        return true;
    }

    /* -------------------------------------------------------
       Soumission AJAX
    ------------------------------------------------------- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validerFormulaire()) return;

        msgSucces.hidden = true;
        msgErreur.hidden = true;
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Envoi en cours…';

        var data = new FormData();
        data.append('action',      'pm_soumettre');
        data.append('nonce',       PM.nonce);
        data.append('prenom',      form.querySelector('[name="prenom"]').value.trim());
        data.append('nom',         form.querySelector('[name="nom"]').value.trim());
        data.append('telephone',   form.querySelector('[name="telephone"]').value.trim());
        data.append('email',       form.querySelector('[name="email"]').value.trim());
        data.append('commentaire', form.querySelector('[name="commentaire"]').value.trim());

        document.querySelectorAll('.pam-produit-item:not(.pam-clone) .pam-qty-input').forEach(function (input) {
            var qty = parseInt(input.value, 10) || 0;
            if (qty > 0) {
                var item = input.closest('.pam-produit-item');
                if (item) data.append('produits[' + item.dataset.id + ']', qty);
            }
        });

        fetch(PM.ajax, { method: 'POST', body: data })
            .then(function (resp) { return resp.json(); })
            .then(function (json) {
                if (json.success) {
                    msgSucces.textContent = json.data.message;
                    msgSucces.hidden = false;
                    form.reset();
                    calculerTotal();
                    msgSucces.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    msgErreur.textContent = json.data.message;
                    msgErreur.hidden = false;
                }
            })
            .catch(function () {
                msgErreur.textContent = 'Une erreur de réseau est survenue. Veuillez réessayer.';
                msgErreur.hidden = false;
            })
            .finally(function () {
                btnSubmit.disabled    = false;
                btnSubmit.textContent = 'Envoyer le bon de commande';
            });
    });

    calculerTotal();
})();

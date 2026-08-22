(function () {
    'use strict';

    var form = document.getElementById('pam-formulaire');
    if (!form) return;

    var totalEl   = document.getElementById('pam-total-montant');
    var barreEl   = document.getElementById('pam-barre-total');
    var msgSucces = document.getElementById('pam-msg-succes');
    var msgErreur = document.getElementById('pam-msg-erreur');
    var btnSubmit = document.getElementById('pam-btn-soumettre');

    var recapEl     = document.getElementById('pam-recap');
    var recapListe  = document.getElementById('pam-recap-liste');
    var recapToggle = document.getElementById('pam-recap-toggle');
    var recapCount  = document.getElementById('pam-recap-count');


    /* -------------------------------------------------------
       Filtrage par jour + catégorie (2 niveaux : catégorie
       principale + sous-catégorie optionnelle).

       Important : seul le filtre de JOUR masque un produit via
       l'attribut `hidden` (et remet sa quantité à 0, puisqu'il
       n'est vraiment pas disponible ce jour-là). Le filtre de
       sous-catégorie est purement visuel (classe CSS) : il ne
       doit ni vider la quantité déjà choisie, ni fausser le total
       ou le récapitulatif, qui ne comptent que via `:not([hidden])`.
    ------------------------------------------------------- */
    var categorieActive = '';
    var sousCategorieActive = '';

    /* 1er sous-onglet par défaut quand on entre dans une catégorie.
       Pour les catégories > 5 sous-catégories, le 1er onglet est
       "Tout voir" (data-souscat "__tout__") ; pour les autres, c'est
       la 1re vraie sous-catégorie. '' = pas de barre (ex: Sushis). */
    function premierSouscatDe(catSlug) {
        var premier = document.querySelector('.pam-categorie[data-cat="' + catSlug + '"] .pam-souscat-tab');
        return premier ? premier.dataset.souscat : '';
    }

    function filtrerParJour(jour) {
        /* 1. Masquer les produits qui ne correspondent pas au jour */
        document.querySelectorAll('.pam-produit-item').forEach(function (item) {
            var jours   = item.dataset.jours ? item.dataset.jours.split(' ') : [];
            var visible = jours.indexOf(jour) !== -1;
            item.hidden = !visible;
            if (!visible) {
                var input = item.querySelector('.pam-qty-input');
                if (input) input.value = 0;
            }
        });

        /* 2. Identifier les catégories qui ont encore des produits visibles */
        var catsDispos = [];
        document.querySelectorAll('.pam-categorie').forEach(function (cat) {
            var nb = cat.querySelectorAll('.pam-produit-item:not([hidden])').length;
            if (nb > 0) catsDispos.push(cat.dataset.cat);
        });

        /* 3. Si la catégorie active n'a plus de produits, prendre la première dispo */
        if (!categorieActive || catsDispos.indexOf(categorieActive) === -1) {
            categorieActive = catsDispos[0] || '';
            sousCategorieActive = categorieActive ? premierSouscatDe(categorieActive) : '';
        }

        /* 4. Mettre à jour les onglets et afficher la bonne catégorie */
        majTabsCategorie(catsDispos);
        appliquerFiltreCategorie();
        majSousCategorieTabs(true);
        appliquerFiltreSousCategorie();
        calculerTotal();
    }

    function majTabsCategorie(catsDispos) {
        document.querySelectorAll('.pam-cat-tab').forEach(function (tab) {
            tab.hidden = catsDispos.indexOf(tab.dataset.cat) === -1;
        });
        var barre = document.querySelector('.pam-filtre-cats');
        if (barre) barre.hidden = catsDispos.length < 2;
    }

    function appliquerFiltreCategorie() {
        document.querySelectorAll('.pam-categorie').forEach(function (cat) {
            var aProduits = cat.querySelectorAll('.pam-produit-item:not([hidden])').length > 0;
            var correspond = !categorieActive || cat.dataset.cat === categorieActive;
            cat.hidden = !aProduits || !correspond;
        });

        /* Mettre à jour le style de l'onglet actif */
        document.querySelectorAll('.pam-cat-tab').forEach(function (tab) {
            tab.classList.toggle('pam-cat-tab--active', tab.dataset.cat === categorieActive);
        });
    }

    /* Affiche la barre de sous-onglets de la catégorie active (si elle en a une)
       et resynchronise l'onglet visuellement actif avec sousCategorieActive.
       Sans ça, changer de catégorie principale gardait le surlignage sur le
       dernier sous-onglet cliqué manuellement (ex: Tarte) même si les produits
       affichés étaient ceux d'une autre sous-catégorie (ex: Amaretti). */
    function majSousCategorieTabs(autoSelect) {
        document.querySelectorAll('.pam-filtre-souscats').forEach(function (barre) {
            var cat = barre.closest('.pam-categorie');
            var estActive = !!cat && cat.dataset.cat === categorieActive;
            barre.hidden = !estActive;
            /* Auto-sélectionner la 1re sous-catégorie seulement à l'init ou
               au changement de jour — PAS au clic sur un onglet principal,
               afin que le client voie tous les produits de la catégorie. */
            if (estActive && !sousCategorieActive && autoSelect) {
                var premier = barre.querySelector('.pam-souscat-tab');
                if (premier) sousCategorieActive = premier.dataset.souscat;
            }
            barre.querySelectorAll('.pam-souscat-tab').forEach(function (b) {
                b.classList.toggle('pam-souscat-tab--actif', estActive && b.dataset.souscat === sousCategorieActive);
            });
        });
    }

    /* Masque/affiche visuellement (sans toucher `hidden`) les produits de la
       catégorie active selon la sous-catégorie choisie. */
    function appliquerFiltreSousCategorie() {
        document.querySelectorAll('.pam-categorie').forEach(function (cat) {
            var filtre = (cat.dataset.cat === categorieActive) ? sousCategorieActive : '';
            cat.querySelectorAll('.pam-produit-item').forEach(function (item) {
                var correspond = !filtre || filtre === '__tout__' || item.dataset.souscat === filtre;
                item.classList.toggle('pam-produit-item--filtre-masque', !correspond);
            });
        });
    }

    /* -------------------------------------------------------
       Calcul du total (TPS 5% + TVQ 9,975 %, non composées —
       taux standard du Québec, appliqué seulement aux produits
       marqués "+ taxes" dans WP)
    ------------------------------------------------------- */
    var TAUX_TAXES = 0.14975;

    function prixUnitaireAvecTaxes(item) {
        var prix = parseFloat(item.dataset.prix) || 0;
        return item.dataset.taxable === '1' ? prix * (1 + TAUX_TAXES) : prix;
    }

    function calculerTotal() {
        var total = 0;
        document.querySelectorAll('.pam-produit-item:not([hidden]) .pam-qty-input').forEach(function (input) {
            var item = input.closest('.pam-produit-item');
            var qty  = parseInt(input.value, 10) || 0;
            total   += prixUnitaireAvecTaxes(item) * qty;
        });
        totalEl.textContent = formatMontant(total);
        barreEl.classList.toggle('pam-barre-total--active', total > 0);
        majRecap();
    }

    function formatMontant(n) {
        return n.toFixed(2).replace('.', ',') + ' $';
    }

    /* -------------------------------------------------------
       Toast de suggestion : s'ouvre en bas à droite (bas-centre
       sur mobile) quand un produit passe de qté 0→1, avec photo +
       nom + prix + bouton Ajouter pour le produit suggéré.
       Se ferme seul après SUGG_DUREE ms, ou avant sur clic (X,
       Ajouter, nom) ou nouvelle suggestion.
    ------------------------------------------------------- */
    var SUGG_DUREE = 30000;
    var _suggTimer = null;

    function fermerPanneaux() {
        if (_suggTimer) { clearTimeout(_suggTimer); _suggTimer = null; }
        document.querySelectorAll('.pam-sugg-toast:not(.pam-sugg-toast--sortir)').forEach(function (t) {
            t.classList.add('pam-sugg-toast--sortir');
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 240);
        });
    }

    function ouvrirSuggestions(item) {
        fermerPanneaux();
        var sugg = null;

        /* 1. Suggestions spécifiques configurées par Marie sur ce produit */
        if (item.dataset.suggestions) {
            var donnees;
            try { donnees = JSON.parse(item.dataset.suggestions); } catch (e) { donnees = []; }
            if (Array.isArray(donnees)) {
                for (var i = 0; i < donnees.length; i++) {
                    var cibleItem = document.querySelector('.pam-produit-item[data-id="' + donnees[i].id + '"]');
                    if (!cibleItem || cibleItem.hidden) continue;
                    var inp0 = cibleItem.querySelector('.pam-qty-input');
                    if (inp0 && (parseInt(inp0.value, 10) || 0) > 0) continue;
                    sugg = donnees[i];
                    break;
                }
            }
        }

        /* 2. Repli automatique : paires de catégories (Level 1) */
        if (!sugg && window.pamReglesCategories && window.pamProduitsParCategorie) {
            var catGrp = item.closest('.pam-categorie');
            var catPrincipale = catGrp ? catGrp.dataset.cat : '';
            var sousCat = item.dataset.souscat || '';
            var idProduit = parseInt(item.dataset.id, 10) || 0;

            /* Fusionner les règles de la sous-catégorie et de la catégorie principale */
            var catsCibles = [];
            if (sousCat && window.pamReglesCategories[sousCat]) {
                catsCibles = catsCibles.concat(window.pamReglesCategories[sousCat]);
            }
            if (catPrincipale && window.pamReglesCategories[catPrincipale]) {
                window.pamReglesCategories[catPrincipale].forEach(function (c) {
                    if (catsCibles.indexOf(c) === -1) catsCibles.push(c);
                });
            }

            for (var ci = 0; ci < catsCibles.length && !sugg; ci++) {
                var pool = (window.pamProduitsParCategorie[catsCibles[ci]] || []).slice();
                if (!pool.length) continue;
                /* Mélange aléatoire (Fisher-Yates) pour varier les suggestions */
                for (var k = pool.length - 1; k > 0; k--) {
                    var j = Math.floor(Math.random() * (k + 1));
                    var tmp = pool[k]; pool[k] = pool[j]; pool[j] = tmp;
                }
                for (var pi = 0; pi < pool.length; pi++) {
                    var cand = pool[pi];
                    if (cand.id === idProduit) continue;
                    var candEl = document.querySelector('.pam-produit-item[data-id="' + cand.id + '"]');
                    if (!candEl || candEl.hidden) continue;
                    var candInp = candEl.querySelector('.pam-qty-input');
                    if (candInp && (parseInt(candInp.value, 10) || 0) > 0) continue;
                    sugg = cand;
                    break;
                }
            }
        }

        if (!sugg) return;

        var toast = document.createElement('div');
        toast.className = 'pam-sugg-toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');

        /* En-tete : eyebrow Professor + bouton fermer */
        var entete = document.createElement('div');
        entete.className = 'pam-sugg-entete';

        var eyebrow = document.createElement('p');
        eyebrow.className = 'pam-sugg-eyebrow';
        eyebrow.textContent = 'Parfait avec ça';
        entete.appendChild(eyebrow);

        var fermer = document.createElement('button');
        fermer.type = 'button';
        fermer.className = 'pam-sugg-fermer';
        fermer.setAttribute('aria-label', 'Fermer la suggestion');
        fermer.textContent = '×';
        fermer.addEventListener('click', fermerPanneaux);
        entete.appendChild(fermer);

        toast.appendChild(entete);

        /* Corps : photo + infos (nom, prix, bouton Ajouter) */
        var carte = document.createElement('div');
        carte.className = 'pam-sugg-carte';

        var photoEl = document.createElement(sugg.photo ? 'img' : 'div');
        photoEl.className = 'pam-sugg-photo' + (sugg.photo ? '' : ' pam-sugg-photo--vide');
        if (sugg.photo) {
            photoEl.src = sugg.photo;
            photoEl.alt = sugg.titre;
            photoEl.loading = 'lazy';
        } else {
            photoEl.setAttribute('aria-hidden', 'true');
        }
        carte.appendChild(photoEl);

        var infos = document.createElement('div');
        infos.className = 'pam-sugg-infos';

        var nom = document.createElement('button');
        nom.type = 'button';
        nom.className = 'pam-sugg-nom';
        nom.textContent = sugg.titre;
        nom.setAttribute('aria-label', 'Voir ' + sugg.titre);
        nom.addEventListener('click', (function (id) {
            return function () { allerAuProduit(String(id)); fermerPanneaux(); };
        })(sugg.id));
        infos.appendChild(nom);

        var prixEl = document.createElement('span');
        prixEl.className = 'pam-sugg-prix';
        prixEl.textContent = formatMontant(sugg.prix) + (sugg.taxable ? ' + tx' : '');
        infos.appendChild(prixEl);

        var btnAjouter = document.createElement('button');
        btnAjouter.type = 'button';
        btnAjouter.className = 'pam-sugg-btn';
        btnAjouter.textContent = 'Ajouter';
        btnAjouter.setAttribute('aria-label', 'Ajouter ' + sugg.titre + ' au panier');
        btnAjouter.addEventListener('click', (function (id) {
            return function () {
                var ci = document.querySelector('.pam-produit-item[data-id="' + id + '"]');
                if (ci) {
                    var inp = ci.querySelector('.pam-qty-input');
                    if (inp) {
                        inp.value = (parseInt(inp.value, 10) || 0) + 1;
                        calculerTotal();
                        majRecap();
                        majOptionChaud(ci);
                    }
                }
                fermerPanneaux();
            };
        })(sugg.id));
        infos.appendChild(btnAjouter);

        carte.appendChild(infos);
        toast.appendChild(carte);

        /* Barre de progression au bas du toast, durée pilotée par SUGG_DUREE
           (pas de valeur dupliquée en dur dans le CSS) */
        var barre = document.createElement('div');
        barre.className = 'pam-sugg-barre';
        barre.style.animationDuration = SUGG_DUREE + 'ms';
        toast.appendChild(barre);

        document.body.appendChild(toast);
        _suggTimer = setTimeout(fermerPanneaux, SUGG_DUREE);
    }


    /* -------------------------------------------------------
       Récapitulatif de la sélection (dans la barre de total)
    ------------------------------------------------------- */
    function ouvrirRecap() {
        recapEl.hidden = false;
        recapToggle.setAttribute('aria-expanded', 'true');
        barreEl.classList.add('pam-barre-total--ouverte');
    }

    function fermerRecap() {
        if (!recapEl || recapEl.hidden) return;
        recapEl.hidden = true;
        recapToggle.setAttribute('aria-expanded', 'false');
        barreEl.classList.remove('pam-barre-total--ouverte');
    }

    function retirerProduit(id) {
        var input = document.querySelector('.pam-produit-item[data-id="' + id + '"] .pam-qty-input');
        if (input) input.value = 0;
        calculerTotal();
    }

    function allerAuProduit(id) {
        var item = document.querySelector('.pam-produit-item[data-id="' + id + '"]');
        if (!item) return;
        /* Si le produit est dans une catégorie repliée, activer son onglet */
        var cat = item.closest('.pam-categorie');
        if (cat && cat.hidden) {
            var tab = document.querySelector('.pam-cat-tab[data-cat="' + cat.dataset.cat + '"]');
            if (tab) tab.click();
        }
        /* Si un filtre de sous-catégorie le masque encore, activer SA sous-catégorie */
        if (item.classList.contains('pam-produit-item--filtre-masque')) {
            var barreSous = cat ? cat.querySelector('.pam-filtre-souscats') : null;
            var tabSous = barreSous ? barreSous.querySelector('.pam-souscat-tab[data-souscat="' + item.dataset.souscat + '"]') : null;
            if (tabSous) tabSous.click();
        }
        fermerRecap();
        fermerPanneaux();
        item.scrollIntoView({ behavior: 'smooth', block: 'center' });
        item.classList.add('pam-produit-item--focus');
        clearTimeout(item._focusTimer);
        item._focusTimer = setTimeout(function () {
            item.classList.remove('pam-produit-item--focus');
        }, 1600);
    }

    function majRecap() {
        if (!recapEl) return;

        var items = [];
        document.querySelectorAll('.pam-produit-item:not([hidden])').forEach(function (item) {
            var input = item.querySelector('.pam-qty-input');
            var qty   = input ? parseInt(input.value, 10) || 0 : 0;
            if (qty < 1) return;
            var nomEl = item.querySelector('.pam-produit-nom');
            items.push({
                id:   item.dataset.id,
                nom:  nomEl ? nomEl.textContent.trim() : 'Produit',
                qty:  qty,
                sous: qty * prixUnitaireAvecTaxes(item),
            });
        });

        var nb = items.reduce(function (s, it) { return s + it.qty; }, 0);
        recapCount.textContent = nb + (nb > 1 ? ' articles' : ' article');
        recapToggle.hidden = nb === 0;
        if (nb === 0) fermerRecap();

        recapListe.innerHTML = '';
        items.forEach(function (it) {
            var li = document.createElement('li');

            var nom = document.createElement('button');
            nom.type = 'button';
            nom.className = 'pam-recap-nom';
            nom.textContent = it.qty + ' × ' + it.nom;
            nom.setAttribute('aria-label', 'Voir ' + it.nom + ' dans la liste');
            nom.addEventListener('click', function () { allerAuProduit(it.id); });

            var detail = document.createElement('span');
            detail.className = 'pam-recap-detail';
            detail.textContent = formatMontant(it.sous);

            var suppr = document.createElement('button');
            suppr.type = 'button';
            suppr.className = 'pam-recap-suppr';
            suppr.setAttribute('aria-label', 'Retirer ' + it.nom);
            suppr.textContent = '×';
            suppr.addEventListener('click', function () { retirerProduit(it.id); });

            li.appendChild(nom);
            li.appendChild(detail);
            li.appendChild(suppr);
            recapListe.appendChild(li);
        });
    }

    if (recapToggle) {
        recapToggle.addEventListener('click', function () {
            if (recapEl.hidden) { ouvrirRecap(); } else { fermerRecap(); }
        });
    }

    /* -------------------------------------------------------
       Message informatif par jour et/ou par catégorie
       (un message s'affiche si son jour OU sa catégorie associée
       correspond à la sélection actuelle du client)
    ------------------------------------------------------- */
    function majMessagesJour() {
        var jourChecked = form.querySelector('input[name="jour"]:checked');
        var jour = jourChecked ? jourChecked.value : '';
        document.querySelectorAll('.pam-msg-jour').forEach(function (el) {
            /* Un message peut être associé à plusieurs jours (ex: un produit
               offert mercredi, jeudi ET vendredi) : data-jour contient les
               slugs séparés par des espaces, même patron que data-jours
               sur les produits. */
            var joursMsg  = el.dataset.jour ? el.dataset.jour.split(' ') : [];
            var hasJour  = joursMsg.length > 0;
            var hasCat   = !!el.dataset.categorie;
            var matchJour = hasJour && joursMsg.indexOf(jour) !== -1;
            /* La catégorie d'un message peut être une catégorie principale
               (ex: "sushis") OU une sous-catégorie (ex: "focaccias" sous
               Pâtisseries) : on vérifie les deux niveaux de sélection. */
            var matchCat = hasCat && (
                el.dataset.categorie === categorieActive ||
                el.dataset.categorie === sousCategorieActive
            );
            /* Si le message a DEUX déclencheurs (jour + catégorie), les deux
               doivent correspondre, sinon un "Spécial jeudi" reste affiché
               en naviguant vers une catégorie sans rapport. */
            el.hidden = !(hasJour && hasCat ? matchJour && matchCat : matchJour || matchCat);
            /* Sur desktop : ouvre automatiquement le <details> quand visible */
            if (el.tagName === 'DETAILS' && !el.hidden && window.matchMedia('(min-width: 641px)').matches) {
                el.setAttribute('open', '');
            }
        });
    }

    /* -------------------------------------------------------
       Radios filtre jour
    ------------------------------------------------------- */
    form.querySelectorAll('input[name="jour"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            filtrerParJour(radio.value);
            majMessagesJour();
        });
    });

    /* -------------------------------------------------------
       Onglets catégorie (principale) et sous-catégorie
    ------------------------------------------------------- */
    function syncCatSelect() {
        var sel = document.querySelector('.pam-cat-select');
        if (sel) sel.value = categorieActive;
    }

    document.querySelectorAll('.pam-cat-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            categorieActive = btn.dataset.cat;
            sousCategorieActive = '';
            appliquerFiltreCategorie();
            majSousCategorieTabs();
            appliquerFiltreSousCategorie();
            majMessagesJour();
            syncCatSelect();
        });
    });

    var catSelect = document.querySelector('.pam-cat-select');
    if (catSelect) {
        catSelect.addEventListener('change', function () {
            categorieActive = catSelect.value;
            sousCategorieActive = '';
            appliquerFiltreCategorie();
            majSousCategorieTabs();
            appliquerFiltreSousCategorie();
            majMessagesJour();
            var colProduits = document.querySelector('.pam-produits-col');
            if (colProduits) setTimeout(function () { colProduits.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 80);
        });
    }

    document.querySelectorAll('.pam-souscat-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sousCategorieActive = btn.dataset.souscat;
            var barre = btn.closest('.pam-filtre-souscats');
            if (barre) {
                barre.querySelectorAll('.pam-souscat-tab').forEach(function (b) {
                    b.classList.toggle('pam-souscat-tab--actif', b === btn);
                });
            }
            appliquerFiltreSousCategorie();
            majMessagesJour();
        });
    });

    /* -------------------------------------------------------
       Boutons +/-
    ------------------------------------------------------- */
    function majOptionChaud(item) {
        var opt = item ? item.querySelector('.pam-option-chaud') : null;
        if (!opt) return;
        var qty = parseInt((item.querySelector('.pam-qty-input') || {}).value, 10) || 0;
        if (qty === 0) {
            opt.hidden = true;
            var cb = opt.querySelector('.pam-chaud-input');
            if (cb) cb.checked = false;
        } else {
            opt.hidden = false;
        }
        majRechauffageInfo();
    }

    function majRechauffageInfo() {
        var infoEl  = document.getElementById('pam-rechauffage-info');
        var listeEl = document.getElementById('pam-rechauffage-liste');
        if (!infoEl || !listeEl) return;

        var noms = [];
        document.querySelectorAll('.pam-chaud-input:checked').forEach(function (cb) {
            var item = cb.closest('.pam-produit-item');
            if (!item) return;
            var qty = parseInt((item.querySelector('.pam-qty-input') || {}).value, 10) || 0;
            if (qty === 0) return;
            var nomEl = item.querySelector('.pam-produit-nom');
            if (nomEl) noms.push(nomEl.textContent.trim());
        });

        if (noms.length) {
            listeEl.textContent = noms.join(', ');
            infoEl.hidden = false;
        } else {
            infoEl.hidden = true;
        }
    }

    form.addEventListener('change', function (e) {
        if (e.target.classList.contains('pam-chaud-input')) {
            majRechauffageInfo();
        }
    });

    form.addEventListener('click', function (e) {
        var btn = e.target.closest('.pam-qty-moins, .pam-qty-plus');
        if (!btn) return;
        var controle = btn.closest('.pam-qty-controle');
        var input    = controle ? controle.querySelector('.pam-qty-input') : null;
        if (!input) return;
        var valAvant = parseInt(input.value, 10) || 0;
        var val = btn.classList.contains('pam-qty-moins') ? Math.max(0, valAvant - 1) : Math.min(20, valAvant + 1);
        input.value = val;
        calculerTotal();
        var item = controle.closest('.pam-produit-item');
        majOptionChaud(item);
        if (valAvant === 0 && val === 1) {
            if (item) ouvrirSuggestions(item);
        }
    });

    function nettoyerErreurChamp(e) {
        if (!e.target.closest('.pam-champ')) return;
        e.target.classList.remove('pam-invalide');
        var champ = e.target.closest('.pam-champ');
        champ.classList.remove('pam-champ--erreur');
        var hint = champ.querySelector('.pam-hint-erreur');
        if (hint) hint.remove();
    }

    form.addEventListener('input', function (e) {
        if (e.target.classList.contains('pam-qty-input')) {
            calculerTotal();
            majOptionChaud(e.target.closest('.pam-produit-item'));
        }
        nettoyerErreurChamp(e);
    });

    /* Les select et champs date déclenchent "change", pas toujours "input" */
    form.addEventListener('change', nettoyerErreurChamp);

    /* -------------------------------------------------------
       Validation côté client
    ------------------------------------------------------- */
    function scrollVersCoordonnees(premierChampInvalide) {
        var section = document.querySelector('.pam-infos-client');
        if (!section) return;
        var offset  = 120; /* header fixe + respiration */
        var top     = section.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
        setTimeout(function () { premierChampInvalide.focus(); }, 400);
    }

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

        /* Vérifier qu'au moins un produit visible a une quantité > 0 */
        var totalQty = 0;
        document.querySelectorAll('.pam-produit-item:not([hidden]) .pam-qty-input').forEach(function (inp) {
            totalQty += parseInt(inp.value, 10) || 0;
        });
        var msgAucun = document.getElementById('pam-msg-aucun-produit');
        if (totalQty === 0) {
            if (!msgAucun) {
                msgAucun = document.createElement('p');
                msgAucun.id = 'pam-msg-aucun-produit';
                msgAucun.className = 'pam-hint-erreur pam-msg-aucun-produit--global';
                msgAucun.textContent = 'Veuillez choisir au moins un produit avant d\'envoyer.';
                var colProduits = document.querySelector('.pam-produits-col');
                if (colProduits) colProduits.appendChild(msgAucun);
            }
            msgAucun.hidden = false;
            var col = document.querySelector('.pam-produits-col');
            if (col) col.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return false;
        }
        if (msgAucun) msgAucun.hidden = true;

        var regles = [
            { name: 'prenom',             msg: 'Veuillez entrer votre prénom.' },
            { name: 'nom',                msg: 'Veuillez entrer votre nom.' },
            { name: 'email',              msg: 'Veuillez entrer votre adresse courriel.' },
            { name: 'date_recuperation',  msg: 'Veuillez choisir une date de récupération.' },
            { name: 'heure_recuperation', msg: "Veuillez choisir une heure de récupération." },
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

        /* Validation basique du format courriel */
        var emailInput = form.querySelector('[name="email"]');
        if (emailInput && emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            marquerInvalide(emailInput, 'Adresse courriel invalide.');
            if (!premier) premier = emailInput;
            erreurs = true;
        }

        if (erreurs) {
            scrollVersCoordonnees(premier);
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
        data.append('action',      'pam_soumettre');
        data.append('nonce',       PAM.nonce);
        data.append('prenom',      form.querySelector('[name="prenom"]').value.trim());
        data.append('nom',         form.querySelector('[name="nom"]').value.trim());
        data.append('telephone',   form.querySelector('[name="telephone"]').value.trim());
        data.append('email',       form.querySelector('[name="email"]').value.trim());
        data.append('commentaire', form.querySelector('[name="commentaire"]').value.trim());
        data.append('date_recuperation',  form.querySelector('[name="date_recuperation"]').value);
        data.append('heure_recuperation', form.querySelector('[name="heure_recuperation"]').value);

        var jourChecked = form.querySelector('input[name="jour"]:checked');
        data.append('jour', jourChecked ? jourChecked.value : 'tous_les_jours');

        document.querySelectorAll('.pam-produit-item:not([hidden]) .pam-qty-input').forEach(function (input) {
            var qty = parseInt(input.value, 10) || 0;
            if (qty > 0) {
                var item = input.closest('.pam-produit-item');
                if (item) data.append('produits[' + item.dataset.id + ']', qty);
            }
        });

        document.querySelectorAll('.pam-chaud-input:checked').forEach(function (cb) {
            var item = cb.closest('.pam-produit-item');
            if (item) data.append('chaud[' + item.dataset.id + ']', '1');
        });

        fetch(PAM.ajax, { method: 'POST', body: data })
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

    /* Initialisation */
    var jourInit = form.querySelector('input[name="jour"]:checked');
    if (jourInit) {
        filtrerParJour(jourInit.value);
        majMessagesJour();
    }
    syncCatSelect();

    /* Catégorie demandée dans l'URL (?cat=slug&souscat=slug), ex : lien
       "Commander" depuis une famille de la page Produits Maison */
    var paramsUrl = new URLSearchParams(window.location.search);
    var catDemandee = paramsUrl.get('cat');
    if (catDemandee) {
        catDemandee = catDemandee.replace(/[^a-z0-9_-]/gi, '');
        var tabCat = document.querySelector('.pam-cat-tab[data-cat="' + catDemandee + '"]:not([hidden])');
        if (tabCat) {
            tabCat.click();
            var sousCatDemandee = paramsUrl.get('souscat');
            if (sousCatDemandee) {
                sousCatDemandee = sousCatDemandee.replace(/[^a-z0-9_-]/gi, '');
                var blocCat = document.querySelector('.pam-categorie[data-cat="' + catDemandee + '"]');
                var tabSous = blocCat ? blocCat.querySelector('.pam-souscat-tab[data-souscat="' + sousCatDemandee + '"]') : null;
                if (tabSous) tabSous.click();
            }
            var colProduits = document.querySelector('.pam-produits-col');
            if (colProduits) colProduits.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
})();

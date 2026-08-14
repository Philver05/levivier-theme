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

    var suggestionEl      = document.getElementById('pam-suggestion');
    var suggestionNomEl   = document.getElementById('pam-suggestion-nom');
    var suggestionPrixEl  = document.getElementById('pam-suggestion-prix');
    var suggestionAjouter = document.getElementById('pam-suggestion-ajouter');
    var suggestionFermer  = document.getElementById('pam-suggestion-fermer');
    var suggestionNomEl2   = document.getElementById('pam-suggestion-nom-2');
    var suggestionPrixEl2  = document.getElementById('pam-suggestion-prix-2');
    var suggestionAjouter2 = document.getElementById('pam-suggestion-ajouter-2');
    var suggestionItem2    = document.getElementById('pam-sug-item-2');
    var suggestionDivider  = document.getElementById('pam-sug-divider');
    var suggestionTimer   = null;

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

    /* Plus de sous-onglet "Tout voir" (Marie n'aime pas) : quand on arrive
       sur une catégorie qui a des sous-catégories, la 1re sous-catégorie
       devient active par défaut plutôt que "tout afficher". Une catégorie
       sans enfants (ex: Sushis) n'a pas de barre de sous-onglets : '' dans
       ce cas, rien à filtrer. */
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
        majSousCategorieTabs();
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
    function majSousCategorieTabs() {
        document.querySelectorAll('.pam-filtre-souscats').forEach(function (barre) {
            var cat = barre.closest('.pam-categorie');
            var estActive = !!cat && cat.dataset.cat === categorieActive;
            barre.hidden = !estActive;
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
                var correspond = !filtre || item.dataset.souscat === filtre;
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
       Suggestion automatique (accompagnement) : quand un produit
       est ajouté pour la première fois (quantité 0 -> 1), propose
       le premier produit de sa liste "pam_suggestions" qui n'est
       ni déjà ajouté ni masqué (filtre du jour).
    ------------------------------------------------------- */
    function masquerSuggestion() {
        if (!suggestionEl) return;
        clearTimeout(suggestionTimer);
        suggestionEl.hidden = true;
        suggestionAjouter.onclick = null;
        suggestionNomEl.onclick = null;
        if (suggestionAjouter2) suggestionAjouter2.onclick = null;
        if (suggestionNomEl2)   suggestionNomEl2.onclick = null;
        if (suggestionItem2)    suggestionItem2.hidden = true;
        if (suggestionDivider)  suggestionDivider.hidden = true;
    }

    function suggererApres(item) {
        if (!suggestionEl || !item.dataset.suggestions) return;
        var ids = item.dataset.suggestions.split(' ').filter(Boolean);
        var cibles = [];
        for (var i = 0; i < ids.length && cibles.length < 2; i++) {
            var candidat = form.querySelector('.pam-produit-item[data-id="' + ids[i] + '"]');
            if (!candidat || candidat.hidden) continue;
            var inp = candidat.querySelector('.pam-qty-input');
            if (inp && (parseInt(inp.value, 10) || 0) > 0) continue;
            cibles.push(candidat);
        }
        if (!cibles.length) return;

        /* Suggestion 1 */
        var cible = cibles[0];
        var nom = cible.querySelector('.pam-produit-nom');
        suggestionNomEl.textContent  = nom ? nom.textContent.trim() : '';
        suggestionPrixEl.textContent = formatMontant(parseFloat(cible.dataset.prix) || 0);
        suggestionAjouter.onclick = (function (c) { return function () {
            var input = c.querySelector('.pam-qty-input');
            if (input) { input.value = (parseInt(input.value, 10) || 0) + 1; calculerTotal(); }
            masquerSuggestion();
        }; })(cible);
        /* Cliquer le nom fait défiler jusqu'à la fiche produit (photo,
           description) sans l'ajouter, pour que le client voie ce qu'il
           s'apprête à commander. */
        suggestionNomEl.onclick = (function (c) { return function () { allerAuProduit(c.dataset.id); }; })(cible);

        /* Suggestion 2 (breuvage ou 2e accompagnement, si disponible) */
        if (cibles[1] && suggestionItem2 && suggestionNomEl2 && suggestionPrixEl2 && suggestionAjouter2) {
            var cible2 = cibles[1];
            var nom2 = cible2.querySelector('.pam-produit-nom');
            suggestionNomEl2.textContent  = nom2 ? nom2.textContent.trim() : '';
            suggestionPrixEl2.textContent = formatMontant(parseFloat(cible2.dataset.prix) || 0);
            suggestionAjouter2.onclick = (function (c) { return function () {
                var input = c.querySelector('.pam-qty-input');
                if (input) { input.value = (parseInt(input.value, 10) || 0) + 1; calculerTotal(); }
                masquerSuggestion();
            }; })(cible2);
            suggestionNomEl2.onclick = (function (c) { return function () { allerAuProduit(c.dataset.id); }; })(cible2);
            suggestionItem2.hidden = false;
            suggestionDivider.hidden = false;
        } else {
            if (suggestionItem2)   suggestionItem2.hidden = true;
            if (suggestionDivider) suggestionDivider.hidden = true;
        }

        suggestionEl.hidden = false;
        clearTimeout(suggestionTimer);
        suggestionTimer = setTimeout(masquerSuggestion, 7000);
    }

    if (suggestionFermer) suggestionFermer.addEventListener('click', masquerSuggestion);

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
        /* Si un filtre de sous-catégorie le masque encore, activer SA sous-catégorie
           (plus de pilule "Tout voir" à cliquer, retirée le 22 juillet) */
        if (item.classList.contains('pam-produit-item--filtre-masque')) {
            var barreSous = cat ? cat.querySelector('.pam-filtre-souscats') : null;
            var tabSous = barreSous ? barreSous.querySelector('.pam-souscat-tab[data-souscat="' + item.dataset.souscat + '"]') : null;
            if (tabSous) tabSous.click();
        }
        fermerRecap();
        masquerSuggestion();
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
            var matchJour = joursMsg.indexOf(jour) !== -1;
            /* La catégorie d'un message peut être une catégorie principale
               (ex: "sushis") OU une sous-catégorie (ex: "focaccias" sous
               Pâtisseries) : on vérifie les deux niveaux de sélection. */
            var matchCat = !!el.dataset.categorie && (
                el.dataset.categorie === categorieActive ||
                el.dataset.categorie === sousCategorieActive
            );
            el.hidden = !(matchJour || matchCat);
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
            sousCategorieActive = ''; /* tout afficher, les sous-onglets filtrent si cliqués */
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
            opt.style.display = 'none';
            var cb = opt.querySelector('.pam-chaud-input');
            if (cb) cb.checked = false;
        } else {
            opt.style.display = '';
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
            if (item) suggererApres(item);
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

        document.querySelectorAll('.pam-produit-item .pam-qty-input').forEach(function (input) {
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

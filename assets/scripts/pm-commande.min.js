(function () {
    'use strict';

    var form = document.getElementById('pm-formulaire');
    if (!form) return;

    var totalEl   = document.getElementById('pm-total-montant');
    var barreEl   = document.getElementById('pm-barre-total');
    var msgSucces = document.getElementById('pm-msg-succes');
    var msgErreur = document.getElementById('pm-msg-erreur');
    var btnSubmit = document.getElementById('pm-btn-soumettre');

    var recapEl     = document.getElementById('pm-recap');
    var recapListe  = document.getElementById('pm-recap-liste');
    var recapToggle = document.getElementById('pm-recap-toggle');
    var recapCount  = document.getElementById('pm-recap-count');

    /* -------------------------------------------------------
       Calcul du total
    ------------------------------------------------------- */
    function calculerTotal() {
        var total = 0;
        document.querySelectorAll('.pam-produit-item .pam-qty-input').forEach(function (input) {
            var item = input.closest('.pam-produit-item');
            var prix = parseFloat(item.dataset.prix) || 0;
            var qty  = parseInt(input.value, 10)     || 0;
            total   += prix * qty;
        });
        totalEl.textContent = formatMontant(total);
        barreEl.classList.toggle('pam-barre-total--active', total > 0);
        majRecap();
    }

    function formatMontant(n) {
        return n.toFixed(2).replace('.', ',') + ' $';
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
        fermerRecap();
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
                sous: qty * (parseFloat(item.dataset.prix) || 0),
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
        });
    });

    /* Onglet initial : catégorie demandée dans l'URL (?cat=slug), sinon le premier */
    var catDemandee = new URLSearchParams(window.location.search).get('cat');
    var tabCible = null;
    if (catDemandee) {
        catDemandee = catDemandee.replace(/[^a-z0-9_-]/gi, '');
        tabCible = document.querySelector('.pam-cat-tab[data-cat="' + catDemandee + '"]');
    }
    var premierTab = tabCible || document.querySelector('.pam-cat-tab');
    if (premierTab) premierTab.click();
    if (tabCible) {
        var colProduits = document.querySelector('.pam-produits-col');
        if (colProduits) colProduits.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

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

        document.querySelectorAll('.pam-produit-item .pam-qty-input').forEach(function (input) {
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

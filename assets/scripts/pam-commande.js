(function () {
    'use strict';

    var form = document.getElementById('pam-formulaire');
    if (!form) return;

    var totalEl   = document.getElementById('pam-total-montant');
    var barreEl   = document.getElementById('pam-barre-total');
    var msgSucces = document.getElementById('pam-msg-succes');
    var msgErreur = document.getElementById('pam-msg-erreur');
    var btnSubmit = document.getElementById('pam-btn-soumettre');

    /* -------------------------------------------------------
       Filtrage par jour + catégorie
    ------------------------------------------------------- */
    var categorieActive = '';

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
        }

        /* 4. Mettre à jour les onglets et afficher la bonne catégorie */
        majTabsCategorie(catsDispos);
        appliquerFiltreCategorie();
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

    /* -------------------------------------------------------
       Calcul du total
    ------------------------------------------------------- */
    function calculerTotal() {
        var total = 0;
        document.querySelectorAll('.pam-produit-item:not([hidden]) .pam-qty-input').forEach(function (input) {
            var item = input.closest('.pam-produit-item');
            var prix = parseFloat(item.dataset.prix) || 0;
            var qty  = parseInt(input.value, 10)     || 0;
            total   += prix * qty;
        });
        totalEl.textContent = formatMontant(total);
        barreEl.classList.toggle('pam-barre-total--active', total > 0);
    }

    function formatMontant(n) {
        return n.toFixed(2).replace('.', ',') + ' $';
    }

    /* -------------------------------------------------------
       Message informatif par jour
    ------------------------------------------------------- */
    function afficherMessageJour(jour) {
        document.querySelectorAll('.pam-msg-jour').forEach(function (el) {
            el.hidden = el.dataset.jour !== jour;
        });
    }

    /* -------------------------------------------------------
       Radios filtre jour
    ------------------------------------------------------- */
    form.querySelectorAll('input[name="jour"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            filtrerParJour(radio.value);
            afficherMessageJour(radio.value);
        });
    });

    /* -------------------------------------------------------
       Onglets catégorie
    ------------------------------------------------------- */
    document.querySelectorAll('.pam-cat-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            categorieActive = btn.dataset.cat;
            appliquerFiltreCategorie();
        });
    });

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
        /* Retire l'état invalide dès que l'utilisateur corrige le champ */
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

        var jourChecked = form.querySelector('input[name="jour"]:checked');
        data.append('jour', jourChecked ? jourChecked.value : 'tous_les_jours');

        document.querySelectorAll('.pam-produit-item .pam-qty-input').forEach(function (input) {
            var qty = parseInt(input.value, 10) || 0;
            if (qty > 0) {
                var item = input.closest('.pam-produit-item');
                if (item) data.append('produits[' + item.dataset.id + ']', qty);
            }
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
        afficherMessageJour(jourInit.value);
    }
})();

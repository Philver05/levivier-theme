(function () {
    'use strict';

    var form      = document.getElementById('bx-formulaire');
    if (!form) return;

    var barreEl   = document.getElementById('bx-barre');
    var totalBtEl = document.getElementById('bx-total-boites');
    var totalPxEl = document.getElementById('bx-total-prix');
    var btnSubmit = document.getElementById('bx-btn-soumettre');
    var msgSucces = document.getElementById('bx-msg-succes');
    var msgErreur = document.getElementById('bx-msg-erreur');

    /* -------------------------------------------------------
       Contrôles de quantité
    ------------------------------------------------------- */
    form.addEventListener('click', function (e) {
        var btn = e.target.closest('.bx-qty-moins, .bx-qty-plus');
        if (!btn) return;
        var idx   = btn.dataset.index;
        var input = form.querySelector('.bx-qty-input[data-index="' + idx + '"]');
        if (!input) return;
        var val = parseInt(input.value, 10) || 0;
        if (btn.classList.contains('bx-qty-plus')) {
            val++;
        } else {
            val = Math.max(0, val - 1);
        }
        input.value = val;
        calculerTotal();
    });

    /* -------------------------------------------------------
       Calcul du total
    ------------------------------------------------------- */
    function calculerTotal() {
        var boites = 0;
        var prix   = 0;
        form.querySelectorAll('.bx-qty-input').forEach(function (inp) {
            var q = parseInt(inp.value, 10) || 0;
            var p = parseFloat(inp.dataset.prix) || 0;
            boites += q;
            prix   += q * p;
        });
        if (totalBtEl) totalBtEl.textContent = boites + ' boîte' + (boites !== 1 ? 's' : '');
        if (totalPxEl) totalPxEl.textContent  = formatMontant(prix);
        if (barreEl)   barreEl.hidden = (boites === 0);
    }

    function formatMontant(v) {
        return v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' $';
    }

    /* -------------------------------------------------------
       Date minimum (délai ACF)
    ------------------------------------------------------- */
    var dateInput = form.querySelector('[name="date_recuperation"]');
    if (dateInput && typeof BX !== 'undefined' && BX.date_min) {
        dateInput.min = BX.date_min;
    }

    /* -------------------------------------------------------
       Soumission AJAX
    ------------------------------------------------------- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        masquerMessages();

        /* Validation formules */
        var totalBoites = 0;
        form.querySelectorAll('.bx-qty-input').forEach(function (inp) {
            totalBoites += parseInt(inp.value, 10) || 0;
        });
        var minQte = (typeof BX !== 'undefined') ? (parseInt(BX.min_qte, 10) || 1) : 1;
        if (totalBoites < minQte) {
            afficherErreur('Veuillez sélectionner au moins ' + minQte + ' boîte' + (minQte > 1 ? 's' : '') + '.');
            return;
        }

        /* Validation champs */
        var prenom    = (form.querySelector('[name="prenom"]')    || {}).value || '';
        var nom       = (form.querySelector('[name="nom"]')       || {}).value || '';
        var email     = (form.querySelector('[name="email"]')     || {}).value || '';
        var telephone = (form.querySelector('[name="telephone"]') || {}).value || '';
        var date      = (form.querySelector('[name="date_recuperation"]')  || {}).value || '';
        var heure     = (form.querySelector('[name="heure_recuperation"]') || {}).value || '';

        if (!prenom.trim() || !nom.trim() || !email.trim() || !telephone.trim()) {
            afficherErreur('Veuillez remplir tous les champs obligatoires (prénom, nom, courriel, téléphone).');
            return;
        }
        if (!date || !heure) {
            afficherErreur('Veuillez choisir une date et une heure de récupération.');
            return;
        }

        /* Construction FormData */
        var data = new FormData();
        data.append('action',    'bx_soumettre');
        data.append('nonce',     (typeof BX !== 'undefined') ? BX.nonce : '');
        data.append('prenom',    prenom.trim());
        data.append('nom',       nom.trim());
        data.append('entreprise', ((form.querySelector('[name="entreprise"]') || {}).value || '').trim());
        data.append('email',     email.trim());
        data.append('telephone', telephone.trim());
        data.append('date_recuperation',  date);
        data.append('heure_recuperation', heure);
        data.append('restrictions', ((form.querySelector('[name="restrictions"]') || {}).value || '').trim());
        data.append('commentaire',  ((form.querySelector('[name="commentaire"]')  || {}).value || '').trim());
        data.append('site_web',     ((form.querySelector('[name="site_web"]')     || {}).value || ''));

        var idx = 0;
        form.querySelectorAll('.bx-qty-input').forEach(function (inp) {
            var q = parseInt(inp.value, 10) || 0;
            if (q > 0) {
                data.append('formules[' + idx + '][nom]',   inp.dataset.nom   || '');
                data.append('formules[' + idx + '][prix]',  inp.dataset.prix  || '0');
                data.append('formules[' + idx + '][qty]',   q);
                data.append('formules[' + idx + '][inclus]', inp.dataset.inclus || '');
                idx++;
            }
        });

        if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Envoi en cours…'; }

        var ajaxUrl = (typeof BX !== 'undefined') ? BX.ajax : '/wp-admin/admin-ajax.php';
        fetch(ajaxUrl, { method: 'POST', body: data })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (r.success) {
                    afficherSucces(r.data.message || 'Commande envoyée !');
                    form.querySelectorAll('.bx-qty-input').forEach(function (inp) { inp.value = 0; });
                    form.reset();
                    calculerTotal();
                } else {
                    afficherErreur(r.data.message || 'Une erreur est survenue.');
                }
            })
            .catch(function () {
                afficherErreur('Erreur réseau. Veuillez nous appeler au (418) 562-5230.');
            })
            .finally(function () {
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Envoyer ma commande'; }
            });
    });

    function afficherSucces(msg) {
        if (!msgSucces) return;
        msgSucces.textContent = msg;
        msgSucces.hidden = false;
        msgSucces.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function afficherErreur(msg) {
        if (!msgErreur) return;
        msgErreur.textContent = msg;
        msgErreur.hidden = false;
        msgErreur.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function masquerMessages() {
        if (msgSucces) msgSucces.hidden = true;
        if (msgErreur) msgErreur.hidden = true;
    }

    calculerTotal();
})();

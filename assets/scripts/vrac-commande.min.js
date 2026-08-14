(function () {
    'use strict';

    const form = document.getElementById('vrac-formulaire');
    if (!form) return;

    const sousTotal  = document.getElementById('vrac-sous-total');
    const escompteInfo = document.getElementById('vrac-escompte-info');
    const totalFinal = document.getElementById('vrac-total-final');
    const barreEl    = document.getElementById('vrac-barre-total');
    const msgSucces  = document.getElementById('vrac-msg-succes');
    const msgErreur  = document.getElementById('vrac-msg-erreur');
    const btnSubmit  = document.getElementById('vrac-btn-soumettre');

    var escomptes = (typeof VRAC !== 'undefined' && VRAC.escomptes) ? VRAC.escomptes : [];

    /* -------------------------------------------------------
       Sélection produit : affiche/masque le tableau de formats
    ------------------------------------------------------- */
    document.querySelectorAll('.vrac-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var item    = cb.closest('.vrac-produit-item');
            var tableau = item ? item.querySelector('.vrac-formats-tableau') : null;
            if (!tableau) return;

            if (cb.checked) {
                tableau.hidden = false;
                item.classList.add('selectionne');
            } else {
                tableau.hidden = true;
                item.classList.remove('selectionne');
                tableau.querySelectorAll('.vrac-qty-input').forEach(function (i) { i.value = 0; });
            }
            calculerTotal();
        });
    });

    /* -------------------------------------------------------
       Calcul du total avec escompte
    ------------------------------------------------------- */
    function calculerTotal() {
        var brut = 0;
        form.querySelectorAll('.vrac-qty-input').forEach(function (input) {
            var qty  = parseInt(input.value, 10) || 0;
            var prix = parseFloat(input.dataset.prix) || 0;
            brut    += qty * prix;
        });

        var pct    = trouverEscompte(brut);
        var rabais = pct > 0 ? Math.round(brut * pct) / 100 : 0;
        var total  = brut - rabais;

        sousTotal.textContent = formatMontant(brut);

        if (pct > 0) {
            escompteInfo.hidden = false;
            escompteInfo.innerHTML =
                'Escompte&nbsp;' + pct + '&nbsp;%&nbsp;&mdash;&nbsp;-' + formatMontant(rabais);
        } else {
            escompteInfo.hidden = true;
        }

        totalFinal.textContent = formatMontant(total);
        barreEl.classList.toggle('vrac-barre-total--active', brut > 0);
    }

    function trouverEscompte(montant) {
        var pct = 0;
        escomptes.forEach(function (palier) {
            if (montant >= parseFloat(palier.min)) pct = parseInt(palier.pct, 10);
        });
        return pct;
    }

    function formatMontant(n) {
        return n.toFixed(2).replace('.', ',') + ' $';
    }

    /* -------------------------------------------------------
       Mise à jour sur saisie de quantité
    ------------------------------------------------------- */
    form.addEventListener('input', function (e) {
        if (e.target.classList.contains('vrac-qty-input')) {
            var input = e.target;
            var prix  = parseFloat(input.dataset.prix) || 0;
            var qty   = parseInt(input.value, 10) || 0;
            var cellTotal = input.closest('tr') ? input.closest('tr').querySelector('.vrac-ligne-total') : null;
            if (cellTotal) cellTotal.textContent = formatMontant(prix * qty);
            calculerTotal();
        }
    });

    /* -------------------------------------------------------
       Soumission AJAX
    ------------------------------------------------------- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        msgSucces.hidden = true;
        msgErreur.hidden = true;

        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Envoi en cours…';

        var data = new FormData();
        data.append('action',      'vrac_soumettre');
        data.append('nonce',       VRAC.nonce);
        data.append('prenom',      form.querySelector('[name="prenom"]').value.trim());
        data.append('nom',         form.querySelector('[name="nom"]').value.trim());
        data.append('telephone',   form.querySelector('[name="telephone"]').value.trim());
        data.append('email',       form.querySelector('[name="email"]').value.trim());
        data.append('commentaire', form.querySelector('[name="commentaire"]').value.trim());
        data.append('escompte_pct', trouverEscompte(
            (function () {
                var brut = 0;
                form.querySelectorAll('.vrac-qty-input').forEach(function (i) {
                    brut += (parseInt(i.value, 10) || 0) * (parseFloat(i.dataset.prix) || 0);
                });
                return brut;
            })()
        ));

        form.querySelectorAll('.vrac-produit-item.selectionne').forEach(function (item) {
            var prodId = item.dataset.id;
            item.querySelectorAll('.vrac-qty-input').forEach(function (input, idx) {
                var qty = parseInt(input.value, 10) || 0;
                if (qty > 0) {
                    data.append('produits[' + prodId + '][' + idx + '][label]', input.dataset.label);
                    data.append('produits[' + prodId + '][' + idx + '][qty]',   qty);
                    data.append('produits[' + prodId + '][' + idx + '][prix]',  input.dataset.prix);
                }
            });
        });

        fetch(VRAC.ajax, { method: 'POST', body: data })
            .then(function (resp) { return resp.json(); })
            .then(function (json) {
                if (json.success) {
                    msgSucces.textContent = json.data.message;
                    msgSucces.hidden = false;
                    form.reset();
                    document.querySelectorAll('.vrac-produit-item').forEach(function (item) {
                        item.classList.remove('selectionne');
                        var t = item.querySelector('.vrac-formats-tableau');
                        if (t) t.hidden = true;
                    });
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
                btnSubmit.textContent = 'Envoyer la commande';
            });
    });

    calculerTotal();
})();

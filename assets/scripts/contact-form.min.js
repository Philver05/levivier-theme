/* Formulaire Contactez-nous : envoi AJAX (même mécanique que les bons PAM/PM) */
document.addEventListener("DOMContentLoaded", function () {

  var form = document.getElementById("ct-formulaire");
  if (!form || typeof CT === "undefined") return;

  var retour = document.getElementById("ct-retour");
  var bouton = form.querySelector('button[type="submit"]');

  function afficherRetour(texte, succes) {
    if (!retour) return;
    retour.textContent = texte;
    retour.classList.remove("ct-retour--succes", "ct-retour--erreur");
    retour.classList.add(succes ? "ct-retour--succes" : "ct-retour--erreur");
    retour.hidden = false;
    retour.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    /* Validation native : marque les champs manquants */
    if (!form.checkValidity()) {
      form.querySelectorAll(":invalid").forEach(function (champ) {
        champ.classList.add("pam-invalide");
      });
      var premier = form.querySelector(":invalid");
      if (premier) premier.focus();
      afficherRetour("Veuillez remplir les champs obligatoires.", false);
      return;
    }

    var donnees = new FormData(form);
    donnees.append("action", "ct_soumettre");
    donnees.append("nonce", CT.nonce);

    bouton.disabled = true;
    var texteBouton = bouton.textContent;
    bouton.textContent = "Envoi en cours...";

    fetch(CT.ajax, { method: "POST", body: donnees })
      .then(function (r) { return r.json(); })
      .then(function (rep) {
        var ok = rep && rep.success;
        var msg = (rep && rep.data && rep.data.message) ||
          (ok ? "Votre message a bien été envoyé. Merci !"
              : "Une erreur est survenue. Réessayez ou appelez-nous.");
        afficherRetour(msg, ok);
        if (ok) form.reset();
      })
      .catch(function () {
        afficherRetour("Impossible de joindre le serveur. Vérifiez votre connexion et réessayez.", false);
      })
      .finally(function () {
        bouton.disabled = false;
        bouton.textContent = texteBouton;
      });
  });

  /* Retire le marquage d'erreur dès que l'usager corrige */
  form.addEventListener("input", function (e) {
    if (e.target.classList) e.target.classList.remove("pam-invalide");
  });

});

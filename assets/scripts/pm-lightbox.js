/* Zoom photo produit (bon de commande Produits Maison) : clic/Entrée sur une
   photo → grand format en superposition. Vanilla JS, aucune dépendance. */
document.addEventListener("DOMContentLoaded", function () {

  var lightbox = document.getElementById("pm-lightbox");
  if (!lightbox) return;

  var img = document.getElementById("pm-lightbox-img");
  var btnFermer = document.getElementById("pm-lightbox-fermer");
  var declencheur = null;

  var ouvrir = function (source) {
    declencheur = document.activeElement;
    img.src = source.currentSrc || source.src;
    img.alt = source.alt || "";
    lightbox.hidden = false;
    document.body.classList.add("pm-lightbox-ouvert");
    btnFermer.focus();
  };

  var fermer = function () {
    lightbox.hidden = true;
    img.src = "";
    document.body.classList.remove("pm-lightbox-ouvert");
    if (declencheur) declencheur.focus();
  };

  document.querySelectorAll(".pm-lightbox-trigger").forEach(function (el) {
    el.addEventListener("click", function () { ouvrir(el); });
    el.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        ouvrir(el);
      }
    });
  });

  btnFermer.addEventListener("click", fermer);
  lightbox.addEventListener("click", function (e) {
    if (e.target === lightbox) fermer();
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !lightbox.hidden) fermer();
  });
});

/* ==========================================================================
   LE VIVIER — Header + Footer partagés (injectés sur chaque page)
   ========================================================================== */
(function () {
  var pages = [
    { href: "epicerie.html",    label: "Épicerie" },
    { href: "boutique.html",    label: "Boutique" },
    { href: "producteurs.html", label: "Producteurs" },
    { href: "lofts.html",       label: "Lofts" },
    { href: "a-propos.html",    label: "À propos" }
  ];
  var courant = document.body.getAttribute("data-page") || "";

  var liens = pages.map(function (p) {
    var actif = (p.href === courant) ? ' class="actif"' : "";
    return '<a href="' + p.href + '"' + actif + ">" + p.label + "</a>";
  }).join("");

  var header =
    '<header class="entete">' +
      '<div class="conteneur entete-barre">' +
        '<a href="index.html" class="logo">Le Vivier</a>' +
        '<nav class="nav-principale" aria-label="Menu principal">' +
          liens +
          '<a href="commander.html" class="btn btn-primaire">Commander</a>' +
        "</nav>" +
        '<button class="burger" aria-label="Ouvrir le menu" aria-expanded="false"><span></span><span></span><span></span></button>' +
      "</div>" +
    "</header>";

  var footer =
    '<footer class="pied">' +
      '<div class="conteneur pied-grille">' +
        '<div class="pied-marque">' +
          '<div class="logo">Le Vivier</div>' +
          "<p>Épicerie fine locale à Matane. Produits locaux, frais et durables.</p>" +
          "<p>14 Avenue D'Amours<br>Matane, QC G4W 2X4</p>" +
          '<p><a href="tel:+14185625230">(418) 562-5230</a><br><a href="mailto:epicerie@levivier.net">epicerie@levivier.net</a></p>' +
        "</div>" +
        "<div><h4>Navigation</h4><ul>" +
          '<li><a href="epicerie.html">Épicerie</a></li>' +
          '<li><a href="boutique.html">Boutique</a></li>' +
          '<li><a href="producteurs.html">Producteurs</a></li>' +
          '<li><a href="lofts.html">Lofts</a></li>' +
          '<li><a href="a-propos.html">À propos</a></li>' +
          '<li><a href="contact.html">Nous joindre</a></li>' +
        "</ul></div>" +
        '<div><h4>Horaires</h4><ul>' +
          "<li>Lun - Ven : 8 h 30 - 18 h</li><li>Samedi : 9 h - 17 h</li><li>Dimanche : 10 h - 17 h</li>" +
        "</ul><h4 style=\"margin-top:1.4rem\">Suivez-nous</h4><ul><li><a href=\"https://facebook.com/epicerielevivier/\" target=\"_blank\" rel=\"noopener\">Facebook</a></li></ul></div>" +
      "</div>" +
      '<div class="pied-bas">© 2026 Le Vivier · Épicerie fine locale, Matane, Québec</div>' +
    "</footer>";

  var sh = document.getElementById("site-header");
  if (sh) sh.outerHTML = header;
  var sf = document.getElementById("site-footer");
  if (sf) sf.outerHTML = footer;
})();

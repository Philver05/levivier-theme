/* ==========================================================================
   LE VIVIER — Interactions du thème (refonte 2026)
   ========================================================================== */
document.addEventListener("DOMContentLoaded", function () {

  /* ----- Header collant au scroll ----- */
  var entete = document.querySelector(".entete");
  if (entete) {
    var majEntete = function () {
      if (window.scrollY > 40) entete.classList.add("colle");
      else entete.classList.remove("colle");
    };
    window.addEventListener("scroll", majEntete, { passive: true });
    majEntete();
  }

  /* ----- Menu burger mobile (panneau à droite, overlay, accessibilité) ----- */
  var burger = document.querySelector(".burger");
  var nav = document.querySelector(".nav-principale");
  var overlay = document.querySelector(".nav-overlay");
  var fermerBtn = document.querySelector(".nav-fermer");

  if (burger && nav) {
    var ouvrirMenu = function () {
      nav.classList.add("ouvert");
      burger.classList.add("actif");
      burger.setAttribute("aria-expanded", "true");
      if (overlay) overlay.classList.add("actif");
      document.body.classList.add("menu-ouvert");
      var premier = nav.querySelector("a, button");
      if (premier) premier.focus();
    };

    var fermerMenu = function (rendreFocus) {
      nav.classList.remove("ouvert");
      burger.classList.remove("actif");
      burger.setAttribute("aria-expanded", "false");
      if (overlay) overlay.classList.remove("actif");
      document.body.classList.remove("menu-ouvert");
      if (rendreFocus) burger.focus();
    };

    burger.addEventListener("click", function () {
      if (nav.classList.contains("ouvert")) fermerMenu(true);
      else ouvrirMenu();
    });

    if (fermerBtn) fermerBtn.addEventListener("click", function () { fermerMenu(true); });
    if (overlay) overlay.addEventListener("click", function () { fermerMenu(true); });

    nav.querySelectorAll("a").forEach(function (lien) {
      lien.addEventListener("click", function () { fermerMenu(false); });
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && nav.classList.contains("ouvert")) fermerMenu(true);
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 900 && nav.classList.contains("ouvert")) fermerMenu(false);
    });
  }

  /* ----- Recherche déroulante + suggestions en direct ----- */
  var rechToggle = document.querySelector(".recherche-toggle");
  var rechPanneau = document.querySelector(".recherche-panneau");
  if (rechToggle && rechPanneau) {
    var champRech = rechPanneau.querySelector('input[type="search"]');
    var rechFermer = rechPanneau.querySelector(".recherche-fermer");
    var suggestions = rechPanneau.querySelector(".recherche-suggestions");
    var minuteur = null;
    var actif = -1;

    var viderSuggestions = function () {
      if (!suggestions) return;
      suggestions.innerHTML = "";
      suggestions.hidden = true;
      actif = -1;
    };

    var fermerRech = function () {
      rechPanneau.classList.remove("ouvert");
      rechToggle.setAttribute("aria-expanded", "false");
      viderSuggestions();
    };

    var afficherSuggestions = function (liste) {
      if (!suggestions) return;
      if (!liste.length) { viderSuggestions(); return; }
      suggestions.innerHTML = liste.map(function (item) {
        return '<li role="option"><a href="' + item.url + '">' +
               '<span class="sugg-titre"></span>' +
               (item.type ? '<span class="sugg-type"></span>' : '') +
               '</a></li>';
      }).join("");
      // Injecter le texte en toute sécurité (pas via innerHTML)
      var lis = suggestions.querySelectorAll("li");
      liste.forEach(function (item, i) {
        lis[i].querySelector(".sugg-titre").textContent = item.titre;
        var t = lis[i].querySelector(".sugg-type");
        if (t) t.textContent = item.type;
      });
      suggestions.hidden = false;
      actif = -1;
    };

    var chercher = function (terme) {
      if (typeof LV_RECHERCHE === "undefined") return;
      fetch(LV_RECHERCHE.ajax + "?action=lv_suggestions&q=" + encodeURIComponent(terme))
        .then(function (r) { return r.json(); })
        .then(afficherSuggestions)
        .catch(function () {});
    };

    if (champRech) {
      champRech.addEventListener("input", function () {
        var val = champRech.value.trim();
        clearTimeout(minuteur);
        if (val.length < 2) { viderSuggestions(); return; }
        minuteur = setTimeout(function () { chercher(val); }, 220);
      });

      champRech.addEventListener("keydown", function (e) {
        if (suggestions.hidden) return;
        var options = suggestions.querySelectorAll("li");
        if (!options.length) return;
        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
          e.preventDefault();
          actif = e.key === "ArrowDown"
            ? (actif + 1) % options.length
            : (actif - 1 + options.length) % options.length;
          options.forEach(function (o, i) { o.classList.toggle("actif", i === actif); });
        } else if (e.key === "Enter" && actif > -1) {
          e.preventDefault();
          var lien = options[actif].querySelector("a");
          if (lien) window.location.href = lien.href;
        }
      });
    }

    rechToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      var ouvert = rechPanneau.classList.toggle("ouvert");
      rechToggle.setAttribute("aria-expanded", ouvert ? "true" : "false");
      if (ouvert) {
        if (typeof fermerMenu === "function") fermerMenu(false);
        if (champRech) champRech.focus();
      } else {
        viderSuggestions();
      }
    });

    if (rechFermer) rechFermer.addEventListener("click", function () {
      fermerRech();
      rechToggle.focus();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && rechPanneau.classList.contains("ouvert")) {
        fermerRech();
        rechToggle.focus();
      }
    });

    document.addEventListener("click", function (e) {
      if (rechPanneau.classList.contains("ouvert") &&
          !rechPanneau.contains(e.target) && e.target !== rechToggle) {
        fermerRech();
      }
    });
  }

  /* ----- Révélation au scroll ----- */
  var aReveler = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window && aReveler.length) {
    var obs = new IntersectionObserver(function (entrees) {
      entrees.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add("visible");
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.04, rootMargin: "0px 0px 140px 0px" });
    aReveler.forEach(function (el) { obs.observe(el); });
    requestAnimationFrame(function () {
      aReveler.forEach(function (el) {
        if (el.getBoundingClientRect().top < window.innerHeight * 0.95) {
          el.classList.add("visible");
          obs.unobserve(el);
        }
      });
    });
  } else {
    aReveler.forEach(function (el) { el.classList.add("visible"); });
  }

  /* ----- Filtres de grilles (épicerie, boutique, producteurs) ----- */
  function initFiltreGrille(grilleId) {
    var grille = document.getElementById(grilleId);
    if (!grille) return;
    var section = grille.closest("section");
    var filtres = section ? section.querySelectorAll(".filtre-lien[data-cat]") : null;
    if (!filtres || filtres.length === 0) filtres = document.querySelectorAll(".filtre-lien[data-cat]");
    if (!filtres || filtres.length === 0) return;

    filtres.forEach(function (filtre) {
      filtre.addEventListener("click", function (evenement) {
        evenement.preventDefault();
        var categorie = this.getAttribute("data-cat");
        filtres.forEach(function (f) { f.classList.remove("actif"); });
        this.classList.add("actif");

        var cartes = grille.querySelectorAll("[data-cat]");
        var indexVisible = 0;
        cartes.forEach(function (carte) {
          var cats = (carte.getAttribute("data-cat") || "").split(" ");
          var correspond = (categorie === "tout" || cats.indexOf(categorie) !== -1);
          if (correspond) {
            carte.style.display = "";
            carte.style.opacity = "0";
            carte.style.transform = "translateY(12px) scale(0.97)";
            var delai = indexVisible * 55;
            indexVisible++;
            setTimeout(function (el) {
              el.style.transition = "opacity .32s ease, transform .32s ease, box-shadow .3s ease";
              el.style.opacity = "1";
              el.style.transform = "";
            }, delai, carte);
          } else {
            carte.style.transition = "opacity .18s ease, transform .18s ease";
            carte.style.opacity = "0";
            carte.style.transform = "scale(0.96)";
            setTimeout(function (el) { el.style.display = "none"; }, 180, carte);
          }
        });
      });
    });
  }
  initFiltreGrille("grille-produits");
  initFiltreGrille("grille-boutique");
  initFiltreGrille("grille-producteurs");
  initFiltreGrille("grille-maison");

  /* ----- Parallaxe du bouquet du hero ----- */
  var fleur = document.querySelector(".hero-fleur");
  if (fleur) {
    var par = fleur.querySelector(".fleur-parallax");
    var hero = fleur.closest(".hero");
    if (par && hero && window.matchMedia("(pointer:fine)").matches) {
      hero.addEventListener("mousemove", function (ev) {
        var r = hero.getBoundingClientRect();
        var dx = (ev.clientX - (r.left + r.width / 2)) / r.width;
        var dy = (ev.clientY - (r.top + r.height / 2)) / r.height;
        par.style.transform = "translate(" + (dx * 38).toFixed(1) + "px," + (dy * 30).toFixed(1) + "px)";
      });
      hero.addEventListener("mouseleave", function () { par.style.transform = ""; });
    }
  }

});

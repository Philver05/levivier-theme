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

  /* ----- Menu mobile ----- */
  var burger = document.querySelector(".burger");
  var nav = document.querySelector(".nav-principale");
  if (burger && nav) {
    burger.addEventListener("click", function () {
      var ouvert = nav.classList.toggle("ouvert");
      burger.classList.toggle("actif", ouvert);
      burger.setAttribute("aria-expanded", ouvert ? "true" : "false");
    });
    nav.querySelectorAll("a").forEach(function (lien) {
      lien.addEventListener("click", function () {
        nav.classList.remove("ouvert");
        burger.classList.remove("actif");
        burger.setAttribute("aria-expanded", "false");
      });
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

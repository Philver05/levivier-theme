/* ==========================================================================
   LE VIVIER — Prototype : interactions
   ========================================================================== */
document.addEventListener("DOMContentLoaded", function () {

  /* ----- Header collant au scroll ----- */
  var entete = document.querySelector(".entete");
  function majEntete() {
    if (window.scrollY > 40) entete.classList.add("colle");
    else entete.classList.remove("colle");
  }
  window.addEventListener("scroll", majEntete, { passive: true });
  majEntete();

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
    /* Garantie : tout ce qui est déjà visible au chargement se révèle immédiatement */
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

  /* ----- Fleur : préparer le tracé animé ----- */
  var fleur = document.querySelector(".hero-fleur");
  if (fleur) {
    fleur.querySelectorAll(".draw").forEach(function (p) {
      var len = p.getTotalLength ? p.getTotalLength() : 1200;
      p.style.setProperty("--len", Math.ceil(len));
    });
    fleur.classList.add("fleur-animee");

    /* Parallaxe : la fleur dérive avec le curseur sur toute la zone du hero */
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

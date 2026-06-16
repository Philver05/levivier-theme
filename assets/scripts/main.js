document.addEventListener("DOMContentLoaded", function () {

    // ===== HAMBURGER MENU =====

    var hamburger = document.querySelector(".hamburger");
    var nav = document.querySelector(".menu-principal");

    if (hamburger && nav) {
        hamburger.addEventListener("click", function () {
            var estOuvert = nav.classList.contains("ouvert");

            if (estOuvert) {
                nav.classList.remove("ouvert");
                hamburger.setAttribute("aria-expanded", "false");
                hamburger.setAttribute("aria-label", "Ouvrir le menu");
            } else {
                nav.classList.add("ouvert");
                hamburger.setAttribute("aria-expanded", "true");
                hamburger.setAttribute("aria-label", "Fermer le menu");
            }
        });

        // Fermer si clic en dehors
        document.addEventListener("click", function (evenement) {
            if (!nav.contains(evenement.target) && !hamburger.contains(evenement.target)) {
                nav.classList.remove("ouvert");
                hamburger.setAttribute("aria-expanded", "false");
                hamburger.setAttribute("aria-label", "Ouvrir le menu");
            }
        });

        // Fermer au redimensionnement bureau
        window.addEventListener("resize", function () {
            if (window.innerWidth > 768) {
                nav.classList.remove("ouvert");
                hamburger.setAttribute("aria-expanded", "false");
            }
        });
    }

    // ===== FILTRE PRODUITS — générique (épicerie + boutique) =====

    function initFiltreGrille(grilleId) {
        var grille = document.getElementById(grilleId);
        if (!grille) return;

        var filtres = grille.closest("section") &&
                      grille.closest("section").querySelectorAll(".filtre-lien[data-cat]");
        if (!filtres || filtres.length === 0) {
            // fallback : cherche dans le conteneur parent
            filtres = document.querySelectorAll(".filtre-lien[data-cat]");
        }
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
                    var cartesCats = (carte.getAttribute("data-cat") || "").split(" ");
                    var correspond = (categorie === "tout" || cartesCats.indexOf(categorie) !== -1);

                    if (correspond) {
                        carte.style.display = "";
                        carte.style.opacity  = "0";
                        carte.style.transform = "translateY(12px) scale(0.97)";

                        var delai = indexVisible * 55;
                        indexVisible++;

                        setTimeout(function (el) {
                            el.style.transition = "opacity 0.32s ease, transform 0.32s ease, box-shadow 0.3s ease";
                            el.style.opacity    = "1";
                            el.style.transform  = "";
                        }, delai, carte);

                    } else {
                        carte.style.transition = "opacity 0.18s ease, transform 0.18s ease";
                        carte.style.opacity    = "0";
                        carte.style.transform  = "scale(0.96)";
                        setTimeout(function (el) { el.style.display = "none"; }, 180, carte);
                    }
                });
            });
        });
    }

    initFiltreGrille("grille-produits");
    initFiltreGrille("grille-boutique");
    initFiltreGrille("grille-producteurs");

    // ===== NETTOYAGE : paragraphes vides (espace insécable) dans l'accroche épicerie =====

    document.querySelectorAll(".hero-direct-accroche p, .section-hero .epicerie-intro p").forEach(function (p) {
        if (p.textContent.replace(/ /g, "").trim() === "") {
            p.remove();
        }
    });

    // ===== NAV RÉVÉLÉE AU SCROLL (accueil — héros plein écran) =====

    if (document.body.classList.contains("home")) {
        var seuilNav = Math.min(window.innerHeight * 0.6, 600);
        var navTicking = false;

        function majNav() {
            if (window.scrollY > seuilNav) {
                document.body.classList.add("nav-visible");
            } else {
                document.body.classList.remove("nav-visible");
            }
            navTicking = false;
        }

        window.addEventListener("scroll", function () {
            if (!navTicking) {
                window.requestAnimationFrame(majNav);
                navTicking = true;
            }
        }, { passive: true });

        window.addEventListener("resize", function () {
            seuilNav = Math.min(window.innerHeight * 0.6, 600);
        });

        majNav();
    }

});

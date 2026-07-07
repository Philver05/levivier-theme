# Le Vivier — Thème WordPress

## Chemin
Path: `C:\Users\phili\OneDrive\Desktop\levivier\levivier-theme\`

## Stack & technologies
Thème WP classique (PHP, pas de FSE), ACF/SCF pour les champs, site de dev dans Local (PHP 8.2). Design system "bouquet" (sauge/terracotta/crème, Montserrat + Professor pour la sous-marque Lofts).

## Objectif
Refonte du site de l'épicerie Le Vivier (Matane). Tout contenu visible éditable dans WP/ACF, jamais hardcodé.

## État actuel (7 juillet 2026)
- Refonte design globale terminée (page-entete partout, footer 4-col).
- Page Produits Maison : CPT `famille_maison` + taxonomie `categorie_famille` (Marie gère les catégories : focaccias, amaretti...). Un article éditorial image/texte alterné par famille.
- Le "style brisé" de cette page était une fatale PHP (`lv_opt()` non défini) — corrigé par la page d'options ACF "Réglages du site" + `lv_opt()`/`lv_opt_tel_lien()` dans functions.php.
- Bouton Commander de chaque famille : nouveau champ ACF `famille_cta_categorie` (taxonomy `categorie_produit`) → lien vers le bon Produits Maison (`template-bon-pm.php`) avec `?cat=slug` ; `pm-commande.js` ouvre l'onglet correspondant et scrolle vers les produits.
- Accueil allégé (7 juil.): sections Producteurs et Lofts retirées, newsletter avec fond dégradé sable→vert footer (la carte blanche chevauche la limite).
- Décors retirés partout sauf hero accueil + footer: cercles arche-mini des en-têtes internes, filigranes botaniques (::after/::before des sections), fleurs de remplissage des cartes sans photo (blocs sauge pâle à la place).

## Next step
Philippe teste dans Local : accueil (newsletter/footer), page Produits Maison, bouton d'une famille avec catégorie choisie → bon PM ouvert sur le bon onglet.

## Blockers
[aucun]

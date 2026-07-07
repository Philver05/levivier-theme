# Le Vivier — Thème WordPress

## Chemin
Path: `C:\Users\phili\OneDrive\Desktop\levivier\levivier-theme\`

## Stack & technologies
Thème WP classique (PHP, pas de FSE), ACF/SCF pour les champs, site de dev dans Local (PHP 8.2). Design system "bouquet" (sauge/terracotta/crème, Montserrat + Professor pour la sous-marque Lofts).

## Objectif
Refonte du site de l'épicerie Le Vivier (Matane). Tout contenu visible éditable dans WP/ACF, jamais hardcodé.

## État actuel (7 juillet 2026, après-midi)
- Bande CTA finale Lofts (/lofts/ et fiches loft individuelles, style.css .lofts-cta-finale) : fleur agrandie (clamp 260-460px → 340-600px, mobile 200-320px → 260-420px) ; copyright/liseré du bas repoussé plus bas (.lofts-cta-bas margin-top clamp 2-3rem → 3.5-5rem).
- Page /lofts/ : cartes élargies sur toute la largeur du conteneur (.lofts-grille minmax(300px, 440px) → minmax(300px, 1fr) dans style.css ; avec 2 lofts le plafond 440px laissait ~40% de vide). Idem page Commander : max-width 880px retiré de .cmd-grille (cap mobile 460px conservé).
- Épicerie Africaine : gros espace vide entre l'en-tête et "Nos spécialités". Cause réelle (1er correctif insuffisant) : `get_the_content()` renvoyait une chaîne non-vide (ex: `<p></p>` laissé par l'éditeur WP) même sans texte visible, donc la condition `if (get_the_content() || $pres_image)` affichait quand même la section Présentation (invisible mais toujours paddée par `.section`, padding-block jusqu'à 7rem), ET empêchait le padding réduit de s'appliquer à "Nos spécialités". Fix : `$pres_texte = trim(wp_strip_all_tags(get_the_content()))` pour tester du contenu réellement visible, utilisé partout dans template-epicerie-africaine.php à la place du test brut sur get_the_content(). 2e resserrage (Philippe trouvait le vide encore trop grand, fond ivoire continu = paddings additionnés perçus comme un seul trou) : padding-bottom du page-entete réduit à clamp(1.25rem→2rem) via body.univers-africaine (style.css) + padding-top inline des sections suivantes réduit à clamp(1rem→1.5rem) dans le template.
- Fiches loft individuelles (single-loft.php) : ajout d'une bande CTA finale identique à celle de /lofts/ (même design sarcelle + fleur), titre/texte partagés avec la page Lofts (1 seul endroit à éditer dans WP) mais bouton/lien propres à CHAQUE loft (reprend $resa_principal : Reservit > Airbnb > téléphone), pas un lien partagé. Barre du bas simplifiée : juste le copyright (lien "Tous les lofts" retiré, déjà présent en haut de fiche). Nouveau champ ACF `lofts_cta_lien` (page Lofts) pour le bouton de la page hub /lofts/ uniquement.
- Bons de commande PAM + Produits Maison refondus (Marie n'aimait pas le carrousel ni la disposition) : carrousel supprimé (autoplay/clones/drag/flèches retirés des 2 templates, des 2 JS et du CSS), remplacé par une grille statique multi-rangées (auto-fill 215px, 150px mobile). Nouvelle disposition en 1 colonne : étape 1 "Votre commande" pleine largeur, étape 2 "Vos coordonnées" en carte crème (pastilles numérotées .pam-etape). Barre total sticky : PM aligné sur le modèle PAM (barre fixe bas avec bouton Envoyer, form="pm-formulaire"). `.pam-grille-form` redéfini en grille de champs 2 col ≥580px (utilisé aussi par le vrac, vérifié compatible). Ajustement "exploite l'espace" (Philippe trouvait cartes + formulaire trop compacts) : cartes produits élargies (minmax 215→320px après 2e demande de Philippe, gap 1.75rem, corps 1.15rem, nom/prix 1.05rem ; 3/rangée écran standard, 4 sur très grand), carte coordonnées pleine largeur (max-width 820px retiré) avec les 4 champs sur une rangée ≥1000px (scopé .pam-bloc-coordonnees, le vrac garde 2 col). Fichiers à déployer ENSEMBLE : style.css, templates/template-bon-pam.php, templates/template-bon-pm.php, assets/scripts/pam-commande.js, assets/scripts/pm-commande.js.
- Épicerie Africaine : en-tête rouille remplacé par le page-entete standard comme Produits Maison, puis page entièrement dévertie (demande de Philippe : aucun vert, rester dans la palette épices) : titre cacao + surtitre rouille + arc or dans l'en-tête, section-titre/page-prose/apropos en cacao-rouille, .produits transparent (fond ivoire), cartes spécialités numérotées via compteur CSS (01/02/03, filet or), photo 4/3 fond or pâle, carte centrale décalée (margin-top, ≥961px seulement — pas transform, conflit .reveal), cadre or décalé derrière la photo de présentation (≥861px). Tout en CSS (style.css, bloc univers-africaine). Var ajoutée : --afr-texte #6b5443. Bande CTA finale rouille ("Venez découvrir nos saveurs") retirée du template-epicerie-africaine.php (+ variables afr_cta_* nettoyées) ; les champs ACF afr_cta_* restent dans le groupe mais ne sont plus affichés.
- FIX header Produits Maison : le h1 affichait "Nos amaretti" au lieu du titre de la page. Cause : `foreach ($familles->posts as $post)` (collecte des filtres) écrasait le post global. Renommé en `$famille` dans template-produits-maison.php. Aucune autre occurrence du piège dans le thème.
- Footer : fleurs décoratives recadrées dans la boîte (bottom -24% / top -18% → -2%), plus de coupe en pleine tige (style.css .pied::before/::after).
- page-entete : paragraphe d'intro élargi de 56ch → 85% (style.css, demande de Philippe).
- Infolettre accueil : haut du dégradé passé de sable → crème (le sable jurait depuis le retrait de la section Producteurs) ; .produits simplifié en fond crème uni (la rampe vers sable ne servait plus).
- tasks/lessons.md créé (leçon : chercher les réassignations de $post avant de blâmer contenu/cache).
- Philippe teste sur levivier.net (site en ligne), pas seulement Local. Contenu page Produits Maison rempli (intro + surtitre).
- Refonte design globale terminée (page-entete partout, footer 4-col).
- Page Produits Maison : CPT `famille_maison` + taxonomie `categorie_famille` (Marie gère les catégories : focaccias, amaretti...). Un article éditorial image/texte alterné par famille.
- Le "style brisé" de cette page était une fatale PHP (`lv_opt()` non défini) — corrigé par la page d'options ACF "Réglages du site" + `lv_opt()`/`lv_opt_tel_lien()` dans functions.php.
- Bouton Commander de chaque famille : nouveau champ ACF `famille_cta_categorie` (taxonomy `categorie_produit`) → lien vers le bon Produits Maison (`template-bon-pm.php`) avec `?cat=slug` ; `pm-commande.js` ouvre l'onglet correspondant et scrolle vers les produits.
- Accueil allégé (7 juil.): sections Producteurs et Lofts retirées, newsletter avec fond dégradé sable→vert footer (la carte blanche chevauche la limite).
- Décors retirés partout sauf hero accueil + footer: cercles arche-mini des en-têtes internes, filigranes botaniques (::after/::before des sections), fleurs de remplissage des cartes sans photo (blocs sauge pâle à la place).

- Accueil 100% éditable (7 juil.): groupe ACF "Contenu de la page d'accueil" (onglets Hero/Intro/Engagements/Produits/Infolettre), textes actuels en défauts. Formulaire infolettre toujours décoratif (à brancher plus tard, JotForm?).

## Next step
Philippe déploie ensemble les 5 fichiers de la refonte des bons (style.css, template-bon-pam.php, template-bon-pm.php, pam-commande.js, pm-commande.js) et vérifie la page Prêt à manger avec Marie (grille sans carrousel, coordonnées en bas, envoi d'un bon test). Restent aussi à vérifier : h1 Produits Maison (fix $famille), footer (fleurs entières), largeur intro, en-tête + espacement Épicerie Africaine, fiches loft (bande CTA finale, lien par loft), single-loft.php pas encore uploadé sur levivier.net. Ensuite : commit de tout le lot non commité.

## Blockers
[aucun]

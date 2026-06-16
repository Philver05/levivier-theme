# Le Vivier — Thème WordPress

Épicerie fine locale, Matane QC. Thème PHP classique, pas de FSE/blocks.

## Infos client
- Fondatrice: Marie Fortin
- Adresse: 14 Avenue D'Amours, Matane, QC G4W 2X4
- Tél: (418) 562-5230 | Email: epicerie@levivier.net
- Horaires: Lun-Ven 8h30-18h / Sam 9h-17h / Dim 10h-17h
- Slogan: "Cultivons un avenir durable, un achat à la fois!"
- Facebook: facebook.com/epicerielevivier/
- Commandes: JotForm (thés + vrac)

## Palette & typo
```
--sauge:            #4d6040
--sauge-moyen:      #5c6e4f
--sauge-pale:       #dde8d9
--terracotta:       #b85c50
--terracotta-texte: #a04738
--terracotta-pale:  #f5ddd5
--creme:            #f9f5f0
--texte:            #1f2937
--texte-moyen:      #4b5563
--bordure:          #d1d5db
--blanc:            #ffffff

--police-accent: professor, sans-serif   ← accents décoratifs + gros titres Lofts
--police-titre:  "Montserrat"
--police-corps:  "Montserrat"
```
Sous-marque Lofts: gros titres en Professor (script), reste en Montserrat. Playfair Display n'est plus utilisé.

## Structure des fichiers
```
front-page.php          — page d'accueil
header.php / footer.php
page.php / index.php / 404.php
single.php / search.php / searchform.php

templates/
  template-epicerie.php
  template-epicerie-africaine.php
  template-commander.php
  template-boutique.php
  template-promotions.php
  template-apropos.php
  template-lofts.php

parts/
  produit-card.php
  producteur-card.php
  boutique-card.php
  promotion-card.php
  commander-card.php
  loft-card.php
  temoignage-item.php

single-produit.php
single-producteur.php
single-loft.php
archive-produit.php
archive-producteur.php

includes/
  post-types.php   — CPTs: produit, producteur, temoignage, loft
  seed-data.php

functions.php    — enqueue, theme support, menus
style.css        — CSS global + variables (source de vérité)
```

## CPTs enregistrés
| Slug | Archive | Public |
|------|---------|--------|
| `produit` | `/produits/` | oui |
| `producteur` | `/producteurs/` | oui |
| `temoignage` | non | non (show_ui seulement) |
| `loft` | `/lofts/` | oui |

## Règles absolues
- Tout contenu visible vient de l'éditeur WP ou ACF — jamais hardcodé dans le PHP.
- Ne pas utiliser de tiret cadratin (—) dans le code ni les réponses.
- Ne pas modifier `style.css` pour changer les variables — modifier directement les valeurs.
- Les polices Professor (woff2 + otf) sont dans `assets/fonts/`.

## Sous-marque Lofts de la Rivière
Identité visuelle distincte: couleur sarcelle + Professor pour les gros titres (script, comme les affiches), Montserrat pour le reste. Fichiers: `template-lofts.php`, `single-loft.php`, `parts/loft-card.php`. Ne pas mélanger la charte sarcelle avec la charte principale.

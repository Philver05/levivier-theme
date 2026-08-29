# Le Vivier

Thème WordPress développé from scratch pour Le Vivier,
une plateforme qui met en valeur des producteurs, des produits
et des lofts artisanaux locaux.

---

## Ce que j'ai fait

J'ai tout construit sans page builder, sans thème parent.
Chaque fichier PHP est écrit à la main en respectant
la hiérarchie de templates WordPress.

Le projet avait besoin de trois types de contenu sur mesure :
producteur, produit et loft. J'ai créé les custom post types correspondants
avec leurs archives et leurs pages individuelles dédiées.
La logique est divisée par responsabilité dans `functions.php`
et les fragments réutilisables vivent dans `parts/`.

---

## Stack

| Outil | Rôle |
|---|---|
| PHP 8 | Logique du thème, hooks WordPress |
| WordPress | Gestion du contenu |
| CSS | Styles sur mesure |
| JavaScript | Interactions |

---

## Structure

```
levivier-theme/
  header.php / footer.php
  front-page.php
  archive-producteur.php    single-producteur.php
  archive-produit.php       single-produit.php
  single-loft.php
  functions.php
  includes/                 Fonctions par responsabilité
  parts/                    Fragments réutilisables
  templates/                Mises en page spécifiques
  assets/
```

---

## Installer en local

Copier le dossier dans `wp-content/themes/levivier-theme` et activer
le thème depuis WordPress Admin. Les custom post types s'enregistrent
automatiquement au chargement.

Développé par Philippe Verlain

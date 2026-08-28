# Le Vivier — Thème WordPress sur mesure

Thème WordPress développé from scratch pour **Le Vivier**, plateforme de mise en valeur de producteurs, produits et lofts artisanaux locaux.

---

## Ce que j'ai construit

Thème WordPress complet sans page builder, sans thème parent. Chaque template est écrit à la main en PHP, avec une hiérarchie de fichiers respectant les standards WordPress.

**Décisions clés :**
- Trois custom post types sur mesure : `producteur`, `produit`, `loft` — avec leurs archives et singles dédiés
- Architecture modulaire : `includes/` pour les fonctions, `parts/` pour les fragments réutilisables, `templates/` pour les mises en page spécifiques
- `functions.php` structuré par responsabilité (enqueue, CPT, menus, supports)
- CSS et JS maintenus séparément, chargés via `wp_enqueue_scripts`

---

## Stack

| Outil | Rôle |
|---|---|
| PHP 8 | Logique du thème, hooks WordPress |
| WordPress | CMS et gestion du contenu |
| CSS | Styles sur mesure |
| JavaScript | Interactions côté client |

---

## Structure

```
levivier-theme/
  header.php / footer.php   En-tête et pied de page
  front-page.php            Page d'accueil
  archive-producteur.php    Liste des producteurs
  single-producteur.php     Fiche producteur
  archive-produit.php       Liste des produits
  single-produit.php        Fiche produit
  single-loft.php           Fiche loft
  functions.php             Enregistrement CPT, menus, scripts
  includes/                 Fonctions modulaires
  parts/                    Fragments réutilisables
  templates/                Mises en page spécifiques
  assets/                   CSS, JS, images
```

---

## Installer en local

1. Copier le dossier dans `wp-content/themes/levivier-theme`
2. Activer le thème dans WordPress Admin → Apparence → Thèmes
3. Créer les types de contenu (CPT enregistrés automatiquement au chargement)

---

Développé par Philippe Verlain — Matane, Québec

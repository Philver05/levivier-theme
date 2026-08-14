<?php
/**
 * Script de démarrage — Le Vivier
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed=1
 * Nécessite d'être connecté en tant qu'administrateur.
 */

add_action('admin_init', function () {

    if (!isset($_GET['lv_seed']) || $_GET['lv_seed'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_done')) {
        wp_die('✅ Le script a déjà été exécuté. Les données existent déjà dans la base.');
    }

    $log = [];

    /* ================================================================
       1. TERMES — type_producteur
    ================================================================ */
    $types_producteur = [
        'Maraîchers',
        'Fromageries',
        'Producteurs bovin et autres',
        'Transformateurs',
    ];

    $type_ids = [];
    foreach ($types_producteur as $nom) {
        $result = wp_insert_term($nom, 'type_producteur');
        if (is_wp_error($result)) {
            // Terme déjà existant → récupérer son ID
            $existing = get_term_by('name', $nom, 'type_producteur');
            $type_ids[$nom] = $existing ? $existing->term_id : null;
            $log[] = "type_producteur « $nom » déjà existant (ID {$type_ids[$nom]})";
        } else {
            $type_ids[$nom] = $result['term_id'];
            $log[] = "✔ type_producteur « $nom » créé (ID {$type_ids[$nom]})";
        }
    }

    /* ================================================================
       2. TERMES — categorie_produit
    ================================================================ */
    $categories_produit = [
        'Produits frais',
        'Produits en vrac',
        'Produits transformés',
        'Produit Maison',
        'Fromages',
        'Thés & Infusions',
        // 'Épicerie Africaine' → a sa propre page sur le site
    ];

    foreach ($categories_produit as $nom) {
        $result = wp_insert_term($nom, 'categorie_produit');
        if (is_wp_error($result)) {
            $log[] = "categorie_produit « $nom » déjà existante";
        } else {
            $log[] = "✔ categorie_produit « $nom » créée (ID {$result['term_id']})";
        }
    }

    /* ================================================================
       3. PRODUCTEURS
       Format : ['Nom', 'Région / Ville', 'Type (clé de $type_ids)']
    ================================================================ */
    $producteurs = [
        // Maraîchers
        ['Mycobio',                      'St-Luc-de-Matane',  'Maraîchers'],
        ['Les jardins de l\'Orme',        'Matane',            'Maraîchers'],
        ['Les Jardins Hallé',             'Saint-Moïse',       'Maraîchers'],
        ['Les Serres René-Santerre',      'Baie-des-Sables',   'Maraîchers'],
        ['La Vallée de Framboise',        'Val-Brillant',      'Maraîchers'],

        // Fromageries
        ['Fromagerie du Littoral',        'St-Ulric',          'Fromageries'],
        ['Le Chant des fromages',         'St-Luce',           'Fromageries'],

        // Bovin et autres
        ['La Ferme des Érables',          'St-Luc-de-Matane',  'Producteurs bovin et autres'],
        ['Les Productions Quatre Vents',  'St-Ulric',          'Producteurs bovin et autres'],
        ['La Fumerie de l\'Est',          'Rimouski',          'Producteurs bovin et autres'],
        ['Sunset Wagyu',                  'Baie-des-Sables',   'Producteurs bovin et autres'],

        // Transformateurs
        ['Le Jardin des Corneilles',      '',                  'Transformateurs'],
        ['Le Plato Bistro Traiteur',      'Matane',            'Transformateurs'],
    ];

    foreach ($producteurs as [$nom, $region, $type_nom]) {

        // Créer le post
        $post_id = wp_insert_post([
            'post_type'   => 'producteur',
            'post_title'  => $nom,
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            $log[] = "❌ Erreur création producteur « $nom » : " . $post_id->get_error_message();
            continue;
        }

        // Champ ACF — région
        if ($region && function_exists('update_field')) {
            update_field('producteur_region', $region, $post_id);
        }

        // Taxonomie — type
        if (isset($type_ids[$type_nom]) && $type_ids[$type_nom]) {
            wp_set_object_terms($post_id, (int) $type_ids[$type_nom], 'type_producteur');
        }

        $log[] = "✔ Producteur « $nom » ($region) créé (ID $post_id, type: $type_nom)";
    }

    /* ================================================================
       4. Marquer comme exécuté
    ================================================================ */
    update_option('lv_seed_done', true);

    /* ================================================================
       5. Rapport
    ================================================================ */
    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🌱 Le Vivier — Script de démarrage</h1>';
    echo '<p><strong>✅ Terminé !</strong> Voici ce qui a été créé :</p>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Seed Produits Maison : trois familles d'exemple (Focaccias, Amaretti,
 * Confitures) avec contenu rédigé, catégories et lien du bouton Commander.
 * Tout est ensuite modifiable dans WP (menu Produits Maison).
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed_maison=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_maison']) || $_GET['lv_seed_maison'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_maison_done')) {
        wp_die('✅ Le script Produits Maison a déjà été exécuté. Modifiez les articles dans le menu « Produits Maison ».');
    }

    $log = [];

    /* Parent « Produit Maison » dans categorie_produit (pour le bon de commande) */
    $parent_maison = get_term_by('slug', 'produit-maison', 'categorie_produit');
    if (!$parent_maison) {
        $res = wp_insert_term('Produit Maison', 'categorie_produit');
        if (!is_wp_error($res)) {
            $parent_maison = get_term($res['term_id'], 'categorie_produit');
            $log[] = '✔ categorie_produit « Produit Maison » créée';
        }
    }

    $familles = [
        [
            'titre'    => 'Nos focaccias',
            'cat'      => 'Focaccias',
            'contenu'  => "Chaque matin, la pâte de nos focaccias est pétrie sur place, au Vivier. On lui laisse le temps qu'il faut : une longue fermentation lente qui donne cette mie aérée et ce goût légèrement acidulé qu'aucun raccourci ne peut imiter.\n\nGénéreusement arrosée d'huile d'olive, parsemée de romarin frais, de tomates confites ou d'olives selon l'humeur du jour, elle sort du four en fin de matinée. Il n'est pas rare qu'elle soit encore tiède quand vous passez la porte.\n\nParfaite pour accompagner un repas, garnir un lunch ou simplement se faire plaisir. Commandez la vôtre à l'avance pour être certain qu'il en reste!",
        ],
        [
            'titre'    => 'Nos amaretti',
            'cat'      => 'Amaretti',
            'contenu'  => "L'amaretti, ce petit biscuit italien aux amandes, croquant dehors et fondant dedans, a trouvé une deuxième maison à Matane. Notre recette artisanale ne contient que l'essentiel : amandes moulues, sucre, blancs d'oeufs et une pointe d'amaretto.\n\nNaturellement sans farine de blé, ils sont préparés en petites fournées pour garder tout leur moelleux. Chaque biscuit est façonné à la main, roulé dans le sucre glace et cuit doucement jusqu'à ce que la surface craquelle joliment.\n\nAvec un espresso, un thé de notre section vrac ou en fin de repas, c'est la douceur parfaite. Ils se conservent très bien... si vous réussissez à ne pas tout manger le premier soir.",
        ],
        [
            'titre'    => 'Nos confitures',
            'cat'      => 'Confitures',
            'contenu'  => "Quand les petits fruits du Bas-Saint-Laurent arrivent à maturité, nos chaudrons se mettent au travail. Fraises, framboises de la région, bleuets : on cuisine nos confitures en petites cuvées, avec beaucoup de fruits et juste ce qu'il faut de sucre.\n\nPas d'agents de conservation ni d'arômes ajoutés. Le goût du fruit d'abord, celui de la saison où il a été cueilli. C'est pour cela que nos saveurs changent au fil de l'année : chaque pot raconte un moment de la région.\n\nSur vos rôties du matin, dans un yogourt ou avec un fromage d'ici, elles font le bonheur des déjeuners. Les saveurs disponibles varient selon les récoltes : commandez tôt vos préférées!",
        ],
    ];

    foreach ($familles as $ordre => $f) {

        /* Catégorie d'affichage (filtres de la page Produits Maison) */
        $terme_famille = term_exists($f['cat'], 'categorie_famille');
        if (!$terme_famille) {
            $terme_famille = wp_insert_term($f['cat'], 'categorie_famille');
        }
        $terme_famille_id = is_array($terme_famille) ? (int) $terme_famille['term_id'] : (int) $terme_famille;

        /* Sous-catégorie du bon de commande (sous Produit Maison) */
        $terme_bon_id = 0;
        if ($parent_maison && !is_wp_error($parent_maison)) {
            $terme_bon = term_exists($f['cat'], 'categorie_produit', $parent_maison->term_id);
            if (!$terme_bon) {
                $terme_bon = wp_insert_term($f['cat'], 'categorie_produit', ['parent' => $parent_maison->term_id]);
            }
            if (!is_wp_error($terme_bon)) {
                $terme_bon_id = is_array($terme_bon) ? (int) $terme_bon['term_id'] : (int) $terme_bon;
            }
        }

        /* Article de la famille */
        $post_id = wp_insert_post([
            'post_type'    => 'famille_maison',
            'post_title'   => $f['titre'],
            'post_content' => $f['contenu'],
            'post_status'  => 'publish',
            'menu_order'   => $ordre,
        ]);

        if (is_wp_error($post_id)) {
            $log[] = "❌ Erreur création « {$f['titre']} » : " . $post_id->get_error_message();
            continue;
        }

        wp_set_object_terms($post_id, $terme_famille_id, 'categorie_famille');

        if (function_exists('update_field')) {
            update_field('field_famille_cta_label', 'Commander nos ' . strtolower($f['cat']), $post_id);
            if ($terme_bon_id) {
                update_field('field_famille_cta_categorie', $terme_bon_id, $post_id);
            }
        }

        $log[] = "✔ Famille « {$f['titre']} » créée (ID $post_id, catégorie {$f['cat']})";
    }

    update_option('lv_seed_maison_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🏡 Le Vivier — Seed Produits Maison</h1>';
    echo '<p><strong>✅ Terminé !</strong> Marie peut maintenant modifier ces articles dans le menu <strong>Produits Maison</strong> :</p>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p>N\'oubliez pas d\'ajouter une <strong>image mise en avant</strong> à chaque article (photo du produit).</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Seed Prêt à manger : 5 catégories + 18 produits (prix TEMPORAIRES plausibles,
 * à corriger dans WP) + 2 produits amaretti pour le bon Produits Maison.
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed_pam=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_pam']) || $_GET['lv_seed_pam'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_pam_done')) {
        wp_die('✅ Le script Prêt à manger a déjà été exécuté. Modifiez les produits dans le menu « Prêt à manger ».');
    }

    $log = [];

    /* Un post du même titre existe-t-il déjà ? (Philippe a du contenu en ligne) */
    $titre_existe = function ($titre, $post_type) {
        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'title'          => $titre,
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ]);
        return !empty($q->posts);
    };

    /* ================================================================
       1. Catégories + produits PAM (prix temporaires plausibles)
    ================================================================ */
    $catalogue = [
        'Pâtisseries' => [
            ['Tartelettes',       4.50],
            ['Cake citron',      12.00],
            ['Cake carottes',    12.00],
            ['Biscuits déjeuner', 5.50],
        ],
        'Sandwichs' => [
            ['Sandwich au jambon', 7.50],
            ['Burger végé',        8.50],
            ['Sandwich aux oeufs', 6.95],
        ],
        'Pâtés' => [
            ['Pâté au saumon',               8.95],
            ['Pâté saumon-crevettes',        9.95],
            ['Pâté mexicain',                8.50],
            ['Pâté mexicain extra jalapenos', 8.95],
            ['Pâté mexicain végé',           8.50],
        ],
        'Salades' => [
            ['Salade de carottes', 5.95],
            ['Salade de patates',  5.95],
            ['Salade de goberge',  7.50],
        ],
        'Sauces' => [
            ['Sauce au saumon-crevettes', 9.50],
            ['Chili à la viande',        10.50],
            ['Chili végé',                9.50],
        ],
    ];

    foreach ($catalogue as $cat_nom => $produits) {

        $terme = term_exists($cat_nom, 'pam_categorie');
        if (!$terme) {
            $terme = wp_insert_term($cat_nom, 'pam_categorie');
            if (is_wp_error($terme)) {
                $log[] = "❌ Erreur catégorie « $cat_nom » : " . $terme->get_error_message();
                continue;
            }
            $log[] = "✔ pam_categorie « $cat_nom » créée";
        } else {
            $log[] = "pam_categorie « $cat_nom » déjà existante";
        }
        $terme_id = is_array($terme) ? (int) $terme['term_id'] : (int) $terme;

        foreach ($produits as [$titre, $prix]) {
            if ($titre_existe($titre, 'pam_produit')) {
                $log[] = "« $titre » existe déjà, sauté";
                continue;
            }
            $post_id = wp_insert_post([
                'post_type'   => 'pam_produit',
                'post_title'  => $titre,
                'post_status' => 'publish',
            ]);
            if (is_wp_error($post_id)) {
                $log[] = "❌ Erreur produit « $titre » : " . $post_id->get_error_message();
                continue;
            }
            wp_set_object_terms($post_id, $terme_id, 'pam_categorie');
            if (function_exists('update_field')) {
                update_field('field_pam_prix', $prix, $post_id);
                update_field('field_pam_jours', ['tous_les_jours'], $post_id);
            }
            $log[] = "✔ Produit PAM « $titre » créé ($cat_nom, " . number_format($prix, 2, ',', ' ') . " $)";
        }
    }

    /* ================================================================
       2. Amaretti du bon Produits Maison : mini paquet de 6 + boîte
          mixte de 24, placés en FIN de liste via menu_order.
          Ordre voulu : individuels (0) → boîtes de 6 par saveur
          (menu_order 10-89, saisies par Philippe) → paquet de 6 minis
          (90) → boîte mixte de 24 (95).
    ================================================================ */
    $parent_maison = get_term_by('slug', 'produit-maison', 'categorie_produit');
    $terme_amaretti_id = 0;
    if ($parent_maison && !is_wp_error($parent_maison)) {
        $terme_amaretti = term_exists('Amaretti', 'categorie_produit', $parent_maison->term_id);
        if (!$terme_amaretti) {
            $terme_amaretti = wp_insert_term('Amaretti', 'categorie_produit', ['parent' => $parent_maison->term_id]);
        }
        if (!is_wp_error($terme_amaretti)) {
            $terme_amaretti_id = is_array($terme_amaretti) ? (int) $terme_amaretti['term_id'] : (int) $terme_amaretti;
        }
    }

    if ($terme_amaretti_id) {
        $amaretti = [
            ['Mini amaretti — paquet de 6', 9.00, 90],
            ['Boîte mixte de 24',          28.00, 95],
        ];
        foreach ($amaretti as [$titre, $prix, $ordre]) {
            if ($titre_existe($titre, 'produit')) {
                $log[] = "« $titre » existe déjà, sauté";
                continue;
            }
            $post_id = wp_insert_post([
                'post_type'   => 'produit',
                'post_title'  => $titre,
                'post_status' => 'publish',
                'menu_order'  => $ordre,
            ]);
            if (is_wp_error($post_id)) {
                $log[] = "❌ Erreur produit « $titre » : " . $post_id->get_error_message();
                continue;
            }
            wp_set_object_terms($post_id, $terme_amaretti_id, 'categorie_produit');
            if (function_exists('update_field')) {
                update_field('field_pm_commandable', 1, $post_id);
                update_field('field_pm_prix', $prix, $post_id);
            }
            $log[] = "✔ Produit amaretti « $titre » créé (menu_order $ordre, " . number_format($prix, 2, ',', ' ') . " $)";
        }
    } else {
        $log[] = "❌ Catégorie parent « produit-maison » introuvable : produits amaretti non créés (lancer d'abord ?lv_seed_maison=1).";
    }

    update_option('lv_seed_pam_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥗 Le Vivier — Seed Prêt à manger</h1>';
    echo '<p><strong>✅ Terminé !</strong></p>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p><strong>⚠ Les prix sont des exemples plausibles</strong> : Marie doit les corriger dans le menu « Prêt à manger ».</p>';
    echo '<p>Ordre des amaretti dans le bon Produits Maison : individuels (ordre 0) → boîtes de 6 par saveur (mettre « Ordre » entre 10 et 89 dans l\'attribut de page) → paquet de 6 minis (90) → boîte mixte de 24 (95).</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Migration des catégories Prêt à manger vers une structure à deux niveaux.
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_migrer_pam_categories=1
 *
 * Cible :
 *   Pâtisseries      > Amaretti, Biscuits, Cake, Tarte
 *   Pains            > Focaccias
 *   Prêt-à-manger    > Pizzas, Sandwichs, Salades
 *   Pâtés et Quiches (autonome)
 *   Sushis           (autonome)
 *   Divers prêt-à-manger > Sauces
 *
 * Ne crée/renomme QUE des termes retrouvés par nom exact (issus des scripts
 * de seed) : ne devine pas les catégories ajoutées manuellement dans WP
 * depuis (ex: si le vrai nom en ligne diffère légèrement, comme « Focaccia
 * Maison » plutôt que « Focaccias »). Ne déplace AUCUN produit individuel
 * entre sous-catégories : Philippe doit vérifier/réassigner dans WP après
 * coup si des produits doivent changer de sous-catégorie précise.
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_migrer_pam_categories']) || $_GET['lv_migrer_pam_categories'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_migrer_pam_categories_done')) {
        wp_die('✅ La migration des catégories PAM a déjà été exécutée. Ajustez la hiérarchie dans WP → Prêt à manger → Catégories si besoin.');
    }

    $log = [];

    /* Retrouve un terme pam_categorie existant par nom exact, sinon le crée.
       Si un parent est précisé et diffère du parent actuel, re-parente. */
    $terme_id = function ($nom, $parent_id = 0) use (&$log) {
        $existant = term_exists($nom, 'pam_categorie');
        if ($existant) {
            $id = is_array($existant) ? (int) $existant['term_id'] : (int) $existant;
            $terme = get_term($id, 'pam_categorie');
            if ($parent_id && (int) $terme->parent !== $parent_id) {
                wp_update_term($id, 'pam_categorie', ['parent' => $parent_id]);
                $log[] = "↳ « $nom » déplacée sous son nouveau parent";
            } else {
                $log[] = "« $nom » déjà existante, inchangée";
            }
            return $id;
        }
        $res = wp_insert_term($nom, 'pam_categorie', $parent_id ? ['parent' => $parent_id] : []);
        if (is_wp_error($res)) {
            $log[] = "❌ Erreur catégorie « $nom » : " . $res->get_error_message();
            return 0;
        }
        $log[] = "✔ « $nom » créée" . ($parent_id ? ' (sous-catégorie)' : ' (catégorie principale)');
        return (int) $res['term_id'];
    };

    /* Catégories principales */
    $id_patisseries   = $terme_id('Pâtisseries');
    $id_pains         = $terme_id('Pains');
    $id_pretamanger   = $terme_id('Prêt-à-manger');
    $id_pates_quiches = $terme_id('Pâtés et Quiches');
    $terme_id('Sushis');
    $id_divers        = $terme_id('Divers prêt-à-manger');

    /* Renommer l'ancienne catégorie « Pâtés » en « Pâtés et Quiches » si elle
       existe encore séparément (évite un doublon) */
    $ancien_pates = term_exists('Pâtés', 'pam_categorie');
    if ($ancien_pates) {
        $id_ancien = is_array($ancien_pates) ? (int) $ancien_pates['term_id'] : (int) $ancien_pates;
        if ($id_ancien !== $id_pates_quiches) {
            wp_update_term($id_ancien, 'pam_categorie', ['name' => 'Pâtés et Quiches']);
            $log[] = "✔ « Pâtés » renommée en « Pâtés et Quiches »";
        }
    }

    /* Sous-catégories, rattachées à leur parent ci-dessus */
    $terme_id('Amaretti', $id_patisseries);
    $terme_id('Biscuits', $id_patisseries);
    $terme_id('Cake', $id_patisseries);
    $terme_id('Tarte', $id_patisseries);

    $terme_id('Focaccias', $id_pains);

    $terme_id('Pizzas', $id_pretamanger);
    $terme_id('Sandwichs', $id_pretamanger);
    $terme_id('Salades', $id_pretamanger);

    $terme_id('Sauces', $id_divers);

    update_option('lv_migrer_pam_categories_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥗 Le Vivier — Migration catégories PAM (2 niveaux)</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p><strong>⚠ Cette migration ne déplace aucun produit</strong>, elle crée seulement la structure de catégories. Va dans WP → Prêt à manger → Catégories pour vérifier la hiérarchie (au cas où un nom en ligne diffère légèrement, ex: « Focaccia Maison » créerait un doublon à fusionner avec « Focaccias »), puis réassigne chaque produit à la bonne sous-catégorie si besoin.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Ajustements catégories PAM :
 *  - Nouvelle catégorie principale « Mets préparé »
 *  - Fusion de l'ancienne catégorie « Amarettis » (à plat) vers la
 *    sous-catégorie « Amaretti » (sous Pâtisseries) : déplace les produits,
 *    ne supprime PAS le terme « Amarettis » (vérifier puis supprimer à la
 *    main dans WP une fois confirmé vide).
 * Déclencher UNE SEULE FOIS : /wp-admin/?lv_ajuster_pam_categories=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_ajuster_pam_categories']) || $_GET['lv_ajuster_pam_categories'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_ajuster_pam_categories_done')) {
        wp_die('✅ Cet ajustement a déjà été exécuté.');
    }

    $log = [];

    /* 1. Nouvelle catégorie principale « Mets préparé » */
    $existant = term_exists('Mets préparé', 'pam_categorie');
    if (!$existant) {
        $res = wp_insert_term('Mets préparé', 'pam_categorie');
        if (is_wp_error($res)) {
            $log[] = "❌ Erreur catégorie « Mets préparé » : " . $res->get_error_message();
        } else {
            $log[] = "✔ « Mets préparé » créée";
        }
    } else {
        $log[] = "« Mets préparé » déjà existante, inchangée";
    }

    /* 2. Fusionner « Amarettis » (ancienne catégorie à plat) dans
       « Amaretti » (sous-catégorie de Pâtisseries) */
    $ancien  = term_exists('Amarettis', 'pam_categorie');
    $nouveau = term_exists('Amaretti', 'pam_categorie');
    if ($ancien && $nouveau) {
        $ancien_id  = is_array($ancien)  ? (int) $ancien['term_id']  : (int) $ancien;
        $nouveau_id = is_array($nouveau) ? (int) $nouveau['term_id'] : (int) $nouveau;

        $produits = get_posts([
            'post_type'      => 'pam_produit',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'tax_query'      => [['taxonomy' => 'pam_categorie', 'field' => 'term_id', 'terms' => $ancien_id]],
        ]);

        if ($produits) {
            foreach ($produits as $pid) {
                wp_set_object_terms($pid, $nouveau_id, 'pam_categorie', true);
                wp_remove_object_terms($pid, $ancien_id, 'pam_categorie');
                $log[] = "↳ Produit #$pid déplacé de « Amarettis » vers « Amaretti »";
            }
        } else {
            $log[] = "Aucun produit trouvé dans « Amarettis » (peut-être déjà vide, ou le vrai nom en ligne diffère)";
        }
        $log[] = "⚠ Le terme « Amarettis » n'est pas supprimé automatiquement (par prudence). Une fois vérifié qu'il est vide, supprime-le dans WP → Prêt à manger → Catégories.";
    } else {
        $log[] = "❌ Terme « Amarettis » ou « Amaretti » introuvable : rien fait, vérifie les noms exacts dans WP → Prêt à manger → Catégories.";
    }

    update_option('lv_ajuster_pam_categories_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥗 Le Vivier — Ajustements catégories PAM</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Ajustements catégories Épicerie : renomme « Produits frais » en
 * « Produits locaux » (garde les produits déjà tagués) et crée les
 * nouvelles catégories pour l'ordre d'affichage. Ces catégories sont
 * visibles même vides (hide_empty=false) : elles restent des onglets
 * sans produit tant que Marie n'y aura pas tagué d'articles.
 * Déclencher UNE SEULE FOIS : /wp-admin/?lv_ajuster_epicerie_categories=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_ajuster_epicerie_categories']) || $_GET['lv_ajuster_epicerie_categories'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_ajuster_epicerie_categories_done')) {
        wp_die('✅ Cet ajustement a déjà été exécuté.');
    }

    $log = [];

    /* Renommer « Produits frais » en « Produits locaux » (même terme, juste le nom) */
    $frais = term_exists('Produits frais', 'categorie_produit');
    if ($frais) {
        $frais_id = is_array($frais) ? (int) $frais['term_id'] : (int) $frais;
        wp_update_term($frais_id, 'categorie_produit', ['name' => 'Produits locaux']);
        $log[] = "✔ « Produits frais » renommée en « Produits locaux »";
    } elseif (term_exists('Produits locaux', 'categorie_produit')) {
        $log[] = "« Produits locaux » déjà existante, inchangée";
    } else {
        wp_insert_term('Produits locaux', 'categorie_produit');
        $log[] = "✔ « Produits locaux » créée (aucune ancienne « Produits frais » trouvée)";
    }

    /* Nouvelles catégories principales */
    $nouvelles = ['Produits fins', 'Café', 'Bières, vins et spiritueux', 'Sirop Monin', 'Confiseries', 'Grignotines'];
    foreach ($nouvelles as $nom) {
        if (term_exists($nom, 'categorie_produit')) {
            $log[] = "« $nom » déjà existante, inchangée";
            continue;
        }
        $res = wp_insert_term($nom, 'categorie_produit');
        if (is_wp_error($res)) {
            $log[] = "❌ Erreur catégorie « $nom » : " . $res->get_error_message();
        } else {
            $log[] = "✔ « $nom » créée";
        }
    }

    update_option('lv_ajuster_epicerie_categories_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#4d6040;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥬 Le Vivier — Ajustements catégories Épicerie</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p><strong>Note :</strong> les nouvelles catégories apparaissent tout de suite comme onglets sur la page Épicerie, même sans produit dedans (elles resteront vides tant que des produits n\'y seront pas tagués).</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#4d6040;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * PAM : renomme « Prêt-à-manger » en « Mets préparés », ajoute les
 * sous-catégories Pizzas et Mets cuisinés (garde Sandwichs/Salades déjà
 * présentes), et fusionne l'ancienne catégorie autonome « Mets préparé »
 * (singulier) dedans pour éviter deux noms presque identiques.
 * Déclencher UNE SEULE FOIS : /wp-admin/?lv_ajuster_pam_mets_prepares=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_ajuster_pam_mets_prepares']) || $_GET['lv_ajuster_pam_mets_prepares'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_ajuster_pam_mets_prepares_done')) {
        wp_die('✅ Cet ajustement a déjà été exécuté.');
    }

    $log = [];

    /* 1. Renommer « Prêt-à-manger » en « Mets préparés » */
    $pam_terme = term_exists('Prêt-à-manger', 'pam_categorie');
    $mets_id = 0;
    if ($pam_terme) {
        $mets_id = is_array($pam_terme) ? (int) $pam_terme['term_id'] : (int) $pam_terme;
        wp_update_term($mets_id, 'pam_categorie', ['name' => 'Mets préparés']);
        $log[] = "✔ « Prêt-à-manger » renommée en « Mets préparés »";
    } else {
        $log[] = "❌ Catégorie « Prêt-à-manger » introuvable, rien renommé.";
    }

    /* 2. Ajouter Pizzas + Mets cuisinés comme enfants (Sandwichs/Salades restent) */
    if ($mets_id) {
        foreach (['Pizzas', 'Mets cuisinés'] as $nom) {
            $existant = term_exists($nom, 'pam_categorie');
            if ($existant) {
                $id = is_array($existant) ? (int) $existant['term_id'] : (int) $existant;
                $terme = get_term($id, 'pam_categorie');
                if ((int) $terme->parent !== $mets_id) {
                    wp_update_term($id, 'pam_categorie', ['parent' => $mets_id]);
                    $log[] = "↳ « $nom » déplacée sous « Mets préparés »";
                } else {
                    $log[] = "« $nom » déjà sous « Mets préparés »";
                }
            } else {
                $res = wp_insert_term($nom, 'pam_categorie', ['parent' => $mets_id]);
                if (is_wp_error($res)) {
                    $log[] = "❌ Erreur sous-catégorie « $nom » : " . $res->get_error_message();
                } else {
                    $log[] = "✔ « $nom » créée sous « Mets préparés »";
                }
            }
        }
    }

    /* 3. Fusionner l'ancienne catégorie autonome « Mets préparé » (singulier) dans « Mets préparés » */
    $ancien = term_exists('Mets préparé', 'pam_categorie');
    if ($ancien && $mets_id) {
        $ancien_id = is_array($ancien) ? (int) $ancien['term_id'] : (int) $ancien;
        if ($ancien_id !== $mets_id) {
            $produits = get_posts([
                'post_type'      => 'pam_produit',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
                'tax_query'      => [['taxonomy' => 'pam_categorie', 'field' => 'term_id', 'terms' => $ancien_id]],
            ]);
            foreach ($produits as $pid) {
                wp_set_object_terms($pid, $mets_id, 'pam_categorie', true);
                wp_remove_object_terms($pid, $ancien_id, 'pam_categorie');
                $log[] = "↳ Produit #$pid déplacé de « Mets préparé » vers « Mets préparés »";
            }
            if (!$produits) $log[] = "Aucun produit trouvé dans l'ancienne « Mets préparé » (probablement vide)";
            $log[] = "⚠ L'ancien terme « Mets préparé » (singulier) n'est pas supprimé automatiquement : vérifie qu'il est vide puis supprime-le dans WP → Prêt à manger → Catégories.";
        }
    } elseif (!$ancien) {
        $log[] = "Pas d'ancienne catégorie « Mets préparé » autonome trouvée (rien à fusionner).";
    }

    update_option('lv_ajuster_pam_mets_prepares_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🍽️ Le Vivier — Mets préparés (fusion + sous-catégories)</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = (str_contains($ligne, '❌') || str_contains($ligne, '⚠')) ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * PAM : crée la sous-catégorie « Pizzas » (sous Mets préparés) si elle
 * n'existe pas déjà, puis 5 pizzas avec des prix TEMPORAIRES PLAUSIBLES
 * (Philippe complète le reste manuellement dans WP → Prêt à manger).
 * Autonome : fonctionne que ?lv_ajuster_pam_mets_prepares=1 ait déjà été
 * visité ou non (cherche « Mets préparés », sinon « Prêt-à-manger », sinon
 * crée « Mets préparés » directement).
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed_pam_pizzas=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_pam_pizzas']) || $_GET['lv_seed_pam_pizzas'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_pam_pizzas_done')) {
        wp_die('✅ Les pizzas ont déjà été créées. Modifiez-les dans le menu « Prêt à manger ».');
    }

    $log = [];

    /* 1. Catégorie parente : Mets préparés (ou Prêt-à-manger si pas encore
       renommée, ou création directe si aucune des deux n'existe). */
    $parent_id = 0;
    foreach (['Mets préparés', 'Prêt-à-manger'] as $nom_parent) {
        $existant = term_exists($nom_parent, 'pam_categorie');
        if ($existant) {
            $parent_id = is_array($existant) ? (int) $existant['term_id'] : (int) $existant;
            $log[] = "Catégorie parente trouvée : « $nom_parent »";
            break;
        }
    }
    if (!$parent_id) {
        $cree = wp_insert_term('Mets préparés', 'pam_categorie');
        if (is_wp_error($cree)) {
            wp_die('❌ Impossible de créer « Mets préparés » : ' . $cree->get_error_message());
        }
        $parent_id = (int) $cree['term_id'];
        $log[] = "✔ pam_categorie « Mets préparés » créée (aucune catégorie parente trouvée)";
    }

    /* 2. Sous-catégorie Pizzas */
    $pizzas_terme = term_exists('Pizzas', 'pam_categorie', $parent_id);
    if (!$pizzas_terme) {
        $pizzas_terme = wp_insert_term('Pizzas', 'pam_categorie', ['parent' => $parent_id]);
        if (is_wp_error($pizzas_terme)) {
            wp_die('❌ Impossible de créer « Pizzas » : ' . $pizzas_terme->get_error_message());
        }
        $log[] = "✔ Sous-catégorie « Pizzas » créée";
    } else {
        $log[] = "Sous-catégorie « Pizzas » déjà existante";
    }
    $pizzas_id = is_array($pizzas_terme) ? (int) $pizzas_terme['term_id'] : (int) $pizzas_terme;

    /* 3. Les 5 pizzas (prix temporaires plausibles, Philippe corrige) */
    $titre_existe = function ($titre) {
        $q = new WP_Query([
            'post_type'      => 'pam_produit',
            'post_status'    => 'any',
            'title'          => $titre,
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ]);
        return !empty($q->posts);
    };

    $pizzas = [
        ['Pizza au saumon et crevettes',                        12.95],
        ['Pizza Tex-Mex au chili',                               11.95],
        ['Pizza Grecque (végé)',                                 10.95],
        ['Pizza sauce pomodoro et fromage italien',               9.95],
        ['Pizza sauce pomodoro, salami fin et fromage italien',  11.50],
    ];

    foreach ($pizzas as [$titre, $prix]) {
        if ($titre_existe($titre)) {
            $log[] = "« $titre » existe déjà, sauté";
            continue;
        }
        $post_id = wp_insert_post([
            'post_type'   => 'pam_produit',
            'post_title'  => $titre,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id)) {
            $log[] = "❌ Erreur produit « $titre » : " . $post_id->get_error_message();
            continue;
        }
        wp_set_object_terms($post_id, $pizzas_id, 'pam_categorie');
        if (function_exists('update_field')) {
            update_field('field_pam_prix', $prix, $post_id);
            update_field('field_pam_jours', ['tous_les_jours'], $post_id);
        }
        $log[] = "✔ Pizza « $titre » créée (" . number_format($prix, 2, ',', ' ') . " $, prix temporaire à ajuster)";
    }

    update_option('lv_seed_pam_pizzas_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🍕 Le Vivier — Pizzas Prêt à manger</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = (str_contains($ligne, '❌')) ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p>Prix temporaires plausibles : ajustez-les dans <strong>Prêt à manger</strong>, ainsi que description/ingrédients/photo pour chaque pizza.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * PAM : ajoute un message spécial (brouillon) pour chaque sous-catégorie
 * qui n'en a pas encore. N'AJOUTE QUE des lignes manquantes
 * au repeater `pam_messages_jours` existant — ne touche jamais aux
 * messages déjà saisis (Sushis, Focaccias, Amaretti), et peut être relancé
 * sans risque (ex : après avoir créé la sous-catégorie Pizzas) puisque
 * chaque catégorie n'est ajoutée qu'une fois.
 * Déclencher en visitant : /wp-admin/?lv_ajouter_messages_pam=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_ajouter_messages_pam']) || $_GET['lv_ajouter_messages_pam'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (!function_exists('get_field') || !function_exists('update_field')) {
        wp_die('❌ ACF n\'est pas actif.');
    }

    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'templates/template-bon-pam.php',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);
    if (!$pages) {
        wp_die('❌ Page « Bon de commande — Prêt à manger » introuvable ou non publiée.');
    }
    $page_id = $pages[0];

    $log = [];

    $trouver_terme = function ($nom, $nom_parent) use (&$log) {
        $parent_id = 0;
        if ($nom_parent) {
            $parent = get_term_by('name', $nom_parent, 'pam_categorie');
            if (!$parent) {
                $log[] = "❌ Catégorie parente « $nom_parent » introuvable (pour « $nom »)";
                return 0;
            }
            $parent_id = (int) $parent->term_id;
        }
        $terme = get_term_by('name', $nom, 'pam_categorie');
        if (!$terme) return 0;
        if ($nom_parent && (int) $terme->parent !== $parent_id) return 0;
        return (int) $terme->term_id;
    };

    /* [nom de la sous-catégorie, parent (ou null si autonome), titre, description, encart d'infos pratiques] */
    $messages = [
        ['Biscuit',           'Pâtisseries',           'Nos biscuits maison',            "Croustillants à l'extérieur, moelleux à l'intérieur, nos biscuits sont préparés en petites fournées pour une fraîcheur garantie. Parfaits avec un café ou en collation.", 'Commandez avant 10h la veille et récupérez-les frais le lendemain dès midi.'],
        ['Cake',              'Pâtisseries',           'Nos cakes maison',                'Moelleux et généreux, nos cakes sont cuisinés avec des ingrédients simples et de qualité. Idéals pour le déjeuner ou une pause gourmande.', 'Commandez avant 10h la veille et récupérez votre cake frais le lendemain dès midi.'],
        ['Tarte',             'Pâtisseries',           'Nos tartes maison',               'Une croûte dorée et une garniture généreuse : nos tartes sont préparées sur place, sucrées ou salées selon la saison.', 'Commandez avant 10h la veille et récupérez votre tarte fraîche le lendemain dès midi.'],
        ['Pâtés et Quiches',  null,                    'Nos pâtés et quiches maison',      'Une croûte dorée, une garniture généreuse : nos pâtés et quiches sont cuisinés sur place pour un repas réconfortant en un rien de temps.', 'Commandez avant 10h la veille et récupérez-le frais le lendemain dès midi.'],
        ['Accompagnement',    'Mets préparés',         'Nos accompagnements maison',       'Pour compléter votre repas sans effort, nos accompagnements sont préparés avec des ingrédients frais et prêts à réchauffer.', 'Commandez avant 10h la veille et récupérez-le frais le lendemain dès midi.'],
        ['Mets cuisinés',     'Mets préparés',         'Nos mets cuisinés maison',         'Des plats complets, mijotés ou cuisinés sur place, pour un repas réconfortant sans avoir à cuisiner.', 'Commandez avant 10h la veille et récupérez votre plat frais le lendemain dès midi.'],
        ['Salades',           'Mets préparés',         'Nos salades fraîcheur',            'Des salades composées avec des légumes de saison, idéales en accompagnement ou en repas léger.', 'Commandez avant 10h la veille et récupérez votre salade fraîche le lendemain.'],
        ['Sandwichs',         'Mets préparés',         'Nos sandwichs maison',             'Préparés avec des ingrédients frais, nos sandwichs sont généreux et parfaits pour un dîner rapide ou une envie du midi.', 'Commandez avant 10h la veille et récupérez votre sandwich frais le lendemain.'],
        ['Pizzas',            'Mets préparés',         'Nos pizzas maison',                'Pâte maison et garnitures généreuses, nos pizzas sont prêtes à réchauffer pour un repas simple et savoureux.', 'Commandez avant 10h la veille et récupérez-la fraîche le lendemain dès midi.'],
        ['Sauces',            'Divers prêt-à-manger',  'Nos sauces maison',                'Prêtes à réchauffer, nos sauces sont cuisinées en petites quantités pour garder toute leur saveur.', 'Commandez avant 10h la veille et récupérez le lendemain dès midi.'],
    ];

    $existants = get_field('pam_messages_jours', $page_id);
    if (!is_array($existants)) $existants = [];

    $ids_existants = [];
    foreach ($existants as $m) {
        if (!empty($m['msg_categorie'])) $ids_existants[(int) $m['msg_categorie']] = true;
    }

    $ajoutes = 0;
    foreach ($messages as [$nom, $parent, $titre, $description, $cta]) {
        $terme_id = $trouver_terme($nom, $parent);
        if (!$terme_id) {
            $log[] = "❌ Catégorie « $nom » introuvable, message sauté (créez-la d'abord, ex : lancer ?lv_seed_pam_pizzas=1 pour Pizzas)";
            continue;
        }
        if (isset($ids_existants[$terme_id])) {
            $log[] = "« $nom » a déjà un message, sauté";
            continue;
        }
        $existants[] = [
            'msg_jour'        => '',
            'msg_categorie'   => $terme_id,
            'msg_titre'       => $titre,
            'msg_image'       => null,
            'msg_description' => $description,
            'msg_cta'         => $cta,
            'msg_note_titre'  => '',
            'msg_note_texte'  => '',
        ];
        $ids_existants[$terme_id] = true;
        $ajoutes++;
        $log[] = "✔ Message brouillon ajouté pour « $nom »";
    }

    if ($ajoutes > 0) {
        update_field('pam_messages_jours', $existants, $page_id);
    }

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#4d6040;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>📋 Le Vivier — Messages spéciaux Prêt à manger</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = (str_contains($ligne, '❌')) ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p><strong>' . $ajoutes . '</strong> message(s) brouillon ajouté(s). Ce sont des textes provisoires (temps de préparation, allergènes non vérifiés) : relisez et ajustez chacun dans <strong>Prêt à manger → Modifier → Messages spéciaux</strong>. Aucun message existant (Sushis, Focaccias, Amaretti) n\'a été touché.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#4d6040;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * PAM : catégorie Boissons + 5 breuvages d'exemple (prix TEMPORAIRES).
 * Idempotent (ignore les titres déjà présents, relançable sans risque).
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed_pam_boissons=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_pam_boissons']) || $_GET['lv_seed_pam_boissons'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_pam_boissons_done')) {
        wp_die('✅ Les boissons ont déjà été créées. Modifiez-les dans le menu « Prêt à manger ».');
    }

    $log = [];

    /* 1. Catégorie principale Boissons */
    $terme = term_exists('Boissons', 'pam_categorie');
    if (!$terme) {
        $terme = wp_insert_term('Boissons', 'pam_categorie');
        if (is_wp_error($terme)) {
            wp_die('❌ Impossible de créer « Boissons » : ' . $terme->get_error_message());
        }
        $log[] = "✔ pam_categorie « Boissons » créée";
    } else {
        $log[] = "pam_categorie « Boissons » déjà existante";
    }
    $cat_id = is_array($terme) ? (int) $terme['term_id'] : (int) $terme;

    /* 2. Breuvages (prix TEMPORAIRES — Marie ajuste dans WP) */
    $boissons = [
        ['Eau pétillante',      2.25],
        ['Jus de pomme local',  3.75],
        ['Kombucha maison',     4.25],
        ['Café filtre',         2.50],
        ['Limonade artisanale', 3.50],
    ];

    foreach ($boissons as [$titre, $prix]) {
        $existant = get_posts([
            'post_type'      => 'pam_produit',
            'post_status'    => 'any',
            'title'          => $titre,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);
        if ($existant) {
            $log[] = "« $titre » déjà présente, ignorée";
            continue;
        }
        $post_id = wp_insert_post([
            'post_type'   => 'pam_produit',
            'post_title'  => $titre,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id)) {
            $log[] = "❌ Erreur « $titre » : " . $post_id->get_error_message();
            continue;
        }
        wp_set_object_terms($post_id, $cat_id, 'pam_categorie');
        if (function_exists('update_field')) {
            update_field('field_pam_prix',  $prix,               $post_id);
            update_field('field_pam_jours', ['tous_les_jours'],  $post_id);
        }
        $log[] = "✔ « $titre » créée (" . number_format($prix, 2, ',', ' ') . " $, prix temporaire à ajuster)";
    }

    update_option('lv_seed_pam_boissons_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#4d6040;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥤 Le Vivier — Boissons Prêt à manger</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = (str_contains($ligne, '❌')) ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p>Prix temporaires plausibles : ajustez-les dans <strong>Prêt à manger</strong>, ainsi que description/photo pour chaque boisson.</p>';
    echo '<p>Ensuite, pour activer les suggestions : ouvrez chaque plat principal → champ <strong>« Suggestions automatiques »</strong> → ajoutez l\'accompagnement <strong>puis</strong> la boisson souhaitée.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#4d6040;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * Boutique : 7 rayons (blocs photo+texte en alternance, sur le modèle des
 * familles Produits Maison). Purement descriptif, aucun bouton.
 * Idempotent (ignore les titres déjà présents, relançable sans risque).
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_seed_rayons_boutique=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_rayons_boutique']) || $_GET['lv_seed_rayons_boutique'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    if (get_option('lv_seed_rayons_boutique_done')) {
        wp_die('✅ Le script Rayons boutique a déjà été exécuté. Modifiez les rayons dans le menu « Rayons boutique ».');
    }

    $log = [];

    $rayons = [
        ['titre' => 'Nos boîtes cadeaux',        'contenu' => "Besoin d'un cadeau qui sort de l'ordinaire ? On compose pour vous une boîte cadeau sur mesure avec des produits d'ici : thés, confitures, chocolats, savons... Dites-nous l'occasion et le budget, on s'occupe du reste, joliment emballé."],
        ['titre' => 'Les produits zéro déchets', 'contenu' => "Brosses à dents en bambou, pains de savon solides, sacs à vrac réutilisables : nos produits zéro déchet vous aident à réduire ce qui finit à la poubelle, sans sacrifier la qualité."],
        ['titre' => "L'Art de la Table",         'contenu' => "Vaisselle, linge de table, planches de bois et petits accessoires : de quoi dresser une table qui a du caractère, entre pièces artisanales et classiques indémodables."],
        ['titre' => 'Les artisans',              'contenu' => "On aime mettre en valeur le travail des mains : poterie, bijoux, savons et autres créations d'artisans d'ici trouvent une place de choix sur nos tablettes."],
        ['titre' => 'Le coin des plantes',       'contenu' => "Petites plantes, cache-pots et accessoires pour les pouces verts : notre coin des plantes est parfait pour verdir un intérieur ou offrir un brin de nature."],
        ['titre' => 'Les beaux objets',          'contenu' => "Des trouvailles qui n'ont pas besoin d'être utiles pour être aimées : objets décoratifs, curiosités et jolies pièces qui ont attiré notre oeil."],
        ['titre' => 'Les chandelles et cie',     'contenu' => "Chandelles de cire naturelle, brume d'oreiller, encens : de quoi parfumer la maison et se créer une ambiance douce, à petit prix ou en cadeau."],
    ];

    foreach ($rayons as $ordre => $r) {
        $existe = new WP_Query([
            'post_type'      => 'rayon_boutique',
            'post_status'    => 'any',
            'title'          => $r['titre'],
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ]);
        if (!empty($existe->posts)) {
            $log[] = "« {$r['titre']} » existe déjà, sauté";
            continue;
        }

        $post_id = wp_insert_post([
            'post_type'    => 'rayon_boutique',
            'post_title'   => $r['titre'],
            'post_content' => $r['contenu'],
            'post_status'  => 'publish',
            'menu_order'   => $ordre,
        ]);

        if (is_wp_error($post_id)) {
            $log[] = "❌ Erreur création « {$r['titre']} » : " . $post_id->get_error_message();
            continue;
        }

        $log[] = "✔ Rayon « {$r['titre']} » créé (ID $post_id)";
    }

    update_option('lv_seed_rayons_boutique_done', true);

    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🎁 Le Vivier : Seed Rayons boutique</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p>Textes provisoires : à relire par Marie. N\'oubliez pas d\'ajouter une <strong>image mise en avant</strong> à chaque rayon dans le menu <strong>Rayons boutique</strong>.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url() . '" style="color:#b85c50;">← Retour au tableau de bord</a></p>';
    exit;
});

/**
 * PAM : met à jour le catalogue selon le PDF de Marie (août 2026).
 * - Renomme + corrige les 5 pizzas (vrais noms, descriptions, prix)
 * - Renomme Patates sarroises → Patates sarladaises + description
 * - Crée Salade Grecque et Salade Racines (taxables)
 * - Dépublie "Sauce saumon et crevettes" (Divers)
 * - Active pam_chaud sur les 5 pizzas + le shish taouk
 * Relançable sans risque (idempotent).
 * Déclencher en visitant : /wp-admin/?lv_seed_pam_contenu_pdf=1
 */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_pam_contenu_pdf']) || $_GET['lv_seed_pam_contenu_pdf'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    $log = [];

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */

    /* Trouve l'ID d'un pam_produit par son titre exact */
    $trouver_produit = function (string $titre): int {
        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'pam_produit'
               AND post_status IN ('publish','draft')
               AND post_title = %s
             LIMIT 1",
            $titre
        ));
        return $id ? (int) $id : 0;
    };

    /* Trouve l'ID d'un terme pam_categorie par son nom */
    $trouver_cat = function (string $nom): int {
        $t = get_term_by('name', $nom, 'pam_categorie');
        return $t ? (int) $t->term_id : 0;
    };

    /* Enregistre tous les champs ACF d'un produit via update_field() */
    $acf = function (int $id, array $champs): void {
        foreach ($champs as $cle => $val) {
            update_field($cle, $val, $id);
        }
    };

    /* ------------------------------------------------------------------ */
    /* 1. Pizzas : renommer + vrais prix + descriptions + champs ACF      */
    /* ------------------------------------------------------------------ */
    $pizzas_cat_id = $trouver_cat('Pizzas');
    if (!$pizzas_cat_id) {
        $log[] = '❌ Sous-catégorie « Pizzas » introuvable — exécutez d\'abord ?lv_seed_pam_pizzas=1';
    }

    $pizzas = [
        /* ancien_titre => [titre, description, prix] */
        'Pizza sauce pomodoro et fromage italien' => [
            'titre' => 'Pizza mozzarella (végé)',
            'desc'  => 'Une pizza des plus classiques, revisitée sur notre généreuse pâte à focaccia maison. Garnie de notre sauce pomodoro maison et de mozzarella fondante, elle mise sur la simplicité... et le plaisir.',
            'prix'  => 9.95,
        ],
        'Pizza sauce pomodoro, salami fin et fromage italien' => [
            'titre' => 'Pizza Italienne à la viande',
            'desc'  => "Pâte à focaccia maison garnie de notre sauce pomodoro maison, d'un savoureux mélange de viandes fines italiennes et de mozzarella fondante. Une pizza généreuse aux saveurs italiennes réconfortantes.",
            'prix'  => 13.95,
        ],
        'Pizza Grecque (végé)' => [
            'titre' => 'Pizza Grecque (végé)',
            'desc'  => 'Pâte à focaccia maison, sauce pomodoro maison, fromage féta, olives noires, oignons rouges et poivrons grillés.',
            'prix'  => 15.95,
        ],
        'Pizza Tex-Mex au chili' => [
            'titre' => 'Pizza Tex-Mex boeuf',
            'desc'  => "Notre fameux chili maison au boeuf, mijoté avec soin et recouvert de fromage Tex-Mex fondant, transforme notre pâte à focaccia maison en une pizza Olé!",
            'prix'  => 15.95,
        ],
        'Pizza au saumon et crevettes' => [
            'titre' => 'Pizza Gaspésienne',
            'desc'  => "Un duo de saumon frais et de saumon fumé, accompagné de crevettes nordiques, se retrouve sur cette pizza garnie de mozzarella, le tout sur notre pâte à focaccia maison.",
            'prix'  => 18.95,
        ],
    ];

    foreach ($pizzas as $ancien_titre => $info) {
        $id = $trouver_produit($ancien_titre) ?: $trouver_produit($info['titre']);
        if (!$id) {
            $id = (int) wp_insert_post([
                'post_type'    => 'pam_produit',
                'post_status'  => 'publish',
                'post_title'   => $info['titre'],
                'post_content' => $info['desc'],
            ]);
            $log[] = "✔ Pizza « {$info['titre']} » créée";
        } else {
            wp_update_post(['ID' => $id, 'post_title' => $info['titre'], 'post_content' => $info['desc']]);
            $log[] = "✔ Pizza « {$info['titre']} » mise à jour (titre + description)";
        }
        $acf($id, [
            'field_pam_prix'   => $info['prix'],
            'field_pam_chaud'  => true,
            'field_pam_jours'  => ['tous_les_jours'],
        ]);
        if ($pizzas_cat_id) wp_set_object_terms($id, $pizzas_cat_id, 'pam_categorie');
    }

    /* ------------------------------------------------------------------ */
    /* 2. Patates sarladaises (renommer + description + jours)            */
    /* ------------------------------------------------------------------ */
    $acc_cat_id    = $trouver_cat('Accompagnement');
    $desc_patates  = "Un accompagnement parfait de notre shish taouk : de petites pommes de terre grelots cuites au gras de canard, enrobées d'herbes salées du Bas-du-Fleuve, servies avec une sauce crémeuse à l'ail maison.";
    $id_patates    = $trouver_produit('Patates sarroises') ?: $trouver_produit('Patates sarladaises');

    if ($id_patates) {
        wp_update_post(['ID' => $id_patates, 'post_title' => 'Patates sarladaises', 'post_content' => $desc_patates]);
        $acf($id_patates, ['field_pam_jours' => ['tous_les_jours']]);
        $log[] = '✔ Patates sarladaises : renommées + description + jours mis à jour';
    } else {
        $id_patates = (int) wp_insert_post([
            'post_type'    => 'pam_produit',
            'post_status'  => 'publish',
            'post_title'   => 'Patates sarladaises',
            'post_content' => $desc_patates,
        ]);
        $acf($id_patates, ['field_pam_prix' => 5.95, 'field_pam_jours' => ['tous_les_jours']]);
        if ($acc_cat_id) wp_set_object_terms($id_patates, $acc_cat_id, 'pam_categorie');
        $log[] = '✔ Patates sarladaises créées (introuvables sous l\'ancien titre)';
    }

    /* ------------------------------------------------------------------ */
    /* 3. Salade Grecque + Salade Racines                                 */
    /* ------------------------------------------------------------------ */
    $salades_cat_id  = $trouver_cat('Salades');
    $nouvelles_salades = [
        'Salade Grecque' => [
            'desc' => "Laitue croquante, feta, olives Kalamata, tomates, concombre, poivrons et oignons rouges, accompagnés d'une vinaigrette grecque maison.",
            'prix' => 11.95,
        ],
        'Salade Racines' => [
            'desc' => "Laitue croquante garnie d'un mélange de légumes racines, de croûtons au fromage de chèvre, accompagnée d'une vinaigrette maison à la menthe.",
            'prix' => 12.95,
        ],
    ];
    foreach ($nouvelles_salades as $titre => $info) {
        $id_s = $trouver_produit($titre);
        if ($id_s) {
            wp_update_post(['ID' => $id_s, 'post_content' => $info['desc']]);
            $acf($id_s, ['field_pam_prix' => $info['prix'], 'field_pam_taxable' => true, 'field_pam_jours' => ['tous_les_jours']]);
            $log[] = "✔ $titre : description + prix + jours mis à jour";
        } else {
            $id_s = (int) wp_insert_post([
                'post_type'    => 'pam_produit',
                'post_status'  => 'publish',
                'post_title'   => $titre,
                'post_content' => $info['desc'],
            ]);
            $acf($id_s, ['field_pam_prix' => $info['prix'], 'field_pam_taxable' => true, 'field_pam_jours' => ['tous_les_jours']]);
            if ($salades_cat_id) wp_set_object_terms($id_s, $salades_cat_id, 'pam_categorie');
            $log[] = "✔ $titre créée ({$info['prix']}\$ + tx, tous les jours)";
        }
    }

    /* ------------------------------------------------------------------ */
    /* 4. Sauce saumon et crevettes → dépublier                           */
    /* ------------------------------------------------------------------ */
    $id_sauce = $trouver_produit('Sauce saumon et crevettes');
    if ($id_sauce) {
        wp_update_post(['ID' => $id_sauce, 'post_status' => 'draft']);
        $log[] = '✔ « Sauce saumon et crevettes » mise en brouillon';
    } else {
        $log[] = '— « Sauce saumon et crevettes » introuvable (peut-être déjà supprimée)';
    }

    /* ------------------------------------------------------------------ */
    /* 5. pam_chaud sur le shish taouk                                    */
    /* ------------------------------------------------------------------ */
    $shish = get_posts([
        'post_type'   => 'pam_produit',
        'post_status' => 'publish',
        's'           => 'shish',
        'numberposts' => 5,
    ]);
    foreach ($shish as $s) {
        $acf($s->ID, ['field_pam_chaud' => true]);
        $log[] = '✔ Option repas chaud activée sur « ' . $s->post_title . ' »';
    }
    if (empty($shish)) $log[] = '— Shish taouk introuvable (titre contenant « shish ») — activez pam_chaud manuellement';

    /* ------------------------------------------------------------------ */
    /* Rapport                                                              */
    /* ------------------------------------------------------------------ */
    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;} h1{color:#b85c50;} li{margin:.3rem 0;} .ok{color:#4d6040;} .warn{color:#b85c50;}</style>';
    echo '<h1>Le Vivier — Mise à jour catalogue PAM (PDF Marie)</h1>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'warn' : (str_contains($ligne, '—') ? 'warn' : 'ok');
        echo '<li class="' . $class . '">' . esc_html($ligne) . '</li>';
    }
    echo '</ul>';
    echo '<p><strong>Reste à faire manuellement :</strong> ajouter une photo à chaque pizza + shish taouk dans <em>Prêt à manger</em>.</p>';
    echo '<p style="margin-top:2rem;"><a href="' . admin_url('edit.php?post_type=pam_produit') . '" style="color:#b85c50;">→ Voir tous les produits PAM</a></p>';
    exit;
});


/* =========================================================================
 * lv_seed_pam_chaud  —  Active l'option "Je le veux réchauffé" sur tous les
 * produits PAM des catégories qui peuvent être chauffées. Les catégories
 * froides (Pâtisseries, Sushis, Salades, Sauces, Boissons) sont ignorées.
 *
 * Idempotent : peut être relancé sans risque quand de nouveaux produits
 * sont ajoutés (les produits déjà activés sont signalés, pas modifiés).
 * Déclencher : /wp-admin/?lv_seed_pam_chaud=1
 * ========================================================================= */
add_action('admin_init', function () {

    if (!isset($_GET['lv_seed_pam_chaud']) || $_GET['lv_seed_pam_chaud'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    /* Catégories (nom exact dans WP) qui bénéficient du réchauffage.
       Pains/Focaccias exclus : servis tels quels, pas réchauffés. */
    $noms_chauds = [
        'Pâtés et Quiches', 'Pâtés', 'Pâté',
        'Mets préparés', 'Mets préparé',
        'Pizzas', 'Mets cuisinés',
        'Accompagnement', 'Accompagnements',
        'Sandwichs', 'Sandwich',
    ];

    /* Résolution des term_id + leurs sous-catégories */
    $term_ids = [];
    foreach ($noms_chauds as $nom) {
        $t = get_term_by('name', $nom, 'pam_categorie');
        if (!$t) $t = get_term_by('slug', sanitize_title($nom), 'pam_categorie');
        if ($t) {
            $term_ids[] = $t->term_id;
            $enfants = get_term_children($t->term_id, 'pam_categorie');
            if (!is_wp_error($enfants)) $term_ids = array_merge($term_ids, $enfants);
        }
    }
    $term_ids = array_unique($term_ids);

    if (empty($term_ids)) {
        wp_die('<p style="font-family:monospace;padding:2rem;">Aucune catégorie "chaude" trouvée dans WP — vérifiez les noms dans <em>Prêt à manger > Catégories</em>.</p>');
    }

    /* Produits dans ces catégories (publiés et brouillons) */
    $posts = get_posts([
        'post_type'      => 'pam_produit',
        'posts_per_page' => -1,
        'post_status'    => ['publish', 'draft'],
        'tax_query'      => [[
            'taxonomy' => 'pam_categorie',
            'field'    => 'term_id',
            'terms'    => $term_ids,
        ]],
        'fields'         => 'ids',
    ]);

    $actives  = [];
    $deja     = [];

    foreach ($posts as $pid) {
        if (get_field('pam_chaud', $pid)) {
            $deja[] = get_the_title($pid);
        } else {
            update_field('field_pam_chaud', true, $pid);
            $actives[] = get_the_title($pid);
        }
    }

    echo '<style>body{font-family:monospace;padding:2rem 3rem;background:#f9f5f0;line-height:1.7;}
        h2{color:#4d6040;} .ok{color:#4d6040;} .deja{color:#888;} a{color:#b85c50;}</style>';
    echo '<h2>Option réchauffage — résultat</h2>';

    if ($actives) {
        echo '<p class="ok"><strong>' . count($actives) . ' produit(s) activé(s) :</strong></p>';
        echo '<ul class="ok"><li>' . implode('</li><li>', array_map('esc_html', $actives)) . '</li></ul>';
    } else {
        echo '<p class="deja">Aucun nouveau produit à activer.</p>';
    }

    if ($deja) {
        echo '<p class="deja"><strong>' . count($deja) . ' produit(s) déjà actif(s) :</strong> ' . implode(', ', array_map('esc_html', $deja)) . '</p>';
    }

    echo '<p>Les catégories <strong>Pâtisseries, Sushis, Salades, Sauces, Boissons, Pains</strong> sont inchangées.</p>';
    echo '<p style="margin-top:1.5rem;"><a href="' . admin_url('edit.php?post_type=pam_produit') . '">→ Voir tous les produits PAM</a></p>';
    exit;
});


/* =========================================================================
 * lv_unset_pam_chaud_seed  —  Annule tout ce que lv_seed_pam_chaud a activé.
 * Protège Shish taouk au poulet et Patates sarladaises (réglés manuellement).
 * Idempotent. Déclencher : /wp-admin/?lv_unset_pam_chaud_seed=1
 * ========================================================================= */
add_action('admin_init', function () {

    if (!isset($_GET['lv_unset_pam_chaud_seed']) || $_GET['lv_unset_pam_chaud_seed'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    /* Tous les titres à NE PAS toucher (réglés manuellement) */
    $proteger = ['Shish taouk au poulet', 'Patates sarladaises'];

    /* Toutes les catégories que la seed avait activées */
    $noms_seed = [
        'Pains', 'Focaccias', 'Focaccia Maison',
        'Pâtés et Quiches', 'Pâtés', 'Pâté',
        'Mets préparés', 'Mets préparé',
        'Pizzas', 'Mets cuisinés',
        'Accompagnement', 'Accompagnements',
        'Sandwichs', 'Sandwich',
    ];

    $term_ids = [];
    foreach ($noms_seed as $nom) {
        $t = get_term_by('name', $nom, 'pam_categorie');
        if (!$t) $t = get_term_by('slug', sanitize_title($nom), 'pam_categorie');
        if ($t) {
            $term_ids[] = $t->term_id;
            $enfants = get_term_children($t->term_id, 'pam_categorie');
            if (!is_wp_error($enfants)) $term_ids = array_merge($term_ids, $enfants);
        }
    }
    $term_ids = array_unique($term_ids);

    $posts = get_posts(['post_type' => 'pam_produit', 'posts_per_page' => -1,
                        'post_status' => ['publish', 'draft'],
                        'tax_query' => [['taxonomy' => 'pam_categorie', 'field' => 'term_id', 'terms' => $term_ids]],
                        'fields' => 'ids']);

    $retires   = [];
    $proteges  = [];
    $inchanges = [];

    foreach ($posts as $pid) {
        $titre = get_the_title($pid);
        if (in_array($titre, $proteger, true)) {
            $proteges[] = $titre;
            continue;
        }
        if (get_field('pam_chaud', $pid)) {
            update_field('field_pam_chaud', false, $pid);
            $retires[] = $titre;
        } else {
            $inchanges[] = $titre;
        }
    }

    echo '<style>body{font-family:monospace;padding:2rem 3rem;background:#f9f5f0;line-height:1.7;}
        h2{color:#b85c50;} .retire{color:#b85c50;} .protege{color:#4d6040;} .gris{color:#888;} a{color:#b85c50;}</style>';
    echo '<h2>Annulation seed pam_chaud</h2>';
    if ($retires) {
        echo '<p class="retire"><strong>Désactivés (' . count($retires) . ') :</strong> ' . implode(', ', array_map('esc_html', $retires)) . '</p>';
    }
    if ($proteges) {
        echo '<p class="protege"><strong>Protégés (réglage manuel conservé) :</strong> ' . implode(', ', array_map('esc_html', $proteges)) . '</p>';
    }
    if ($inchanges) {
        echo '<p class="gris">Déjà inactifs : ' . implode(', ', array_map('esc_html', $inchanges)) . '</p>';
    }
    echo '<p style="margin-top:1.5rem;"><a href="' . admin_url('edit.php?post_type=pam_produit') . '">→ Voir tous les produits PAM</a></p>';
    exit;
});

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
 * Migration des catégories Prêt à manger vers une structure à deux niveaux
 * (demande de Marie, juillet 2026).
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
 * Ajustements catégories PAM (demande de Philippe, 17 juillet) :
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
 * Ajustements catégories Épicerie (demande de Philippe/Marie, priorités
 * du 17 juillet) : renomme « Produits frais » en « Produits locaux »
 * (garde les produits déjà tagués) et crée les nouvelles catégories
 * pour l'ordre d'affichage demandé. Ces catégories sont visibles sur la
 * page Épicerie même vides (hide_empty=false) : elles resteront des
 * onglets sans produit tant que Marie n'y aura pas tagué d'articles.
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

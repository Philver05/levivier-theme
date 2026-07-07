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

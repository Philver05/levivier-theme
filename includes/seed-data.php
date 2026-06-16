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

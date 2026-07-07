<?php

require_once 'includes/post-types.php';
require_once 'includes/seed-data.php';
require_once 'includes/import-producteurs.php';
require_once 'includes/image-optim.php';
require_once 'includes/pam-ajax.php';
require_once 'includes/vrac-ajax.php';
require_once 'includes/pm-ajax.php';

/* Avertit dans l'admin si ACF n'est pas actif (sinon aucun champ éditable n'apparaît) */
add_action('admin_notices', function () {
    if (!function_exists('get_field')) {
        echo '<div class="notice notice-error"><p><strong>Le Vivier :</strong> l\'extension <strong>Advanced Custom Fields (ACF)</strong> n\'est pas activée. '
            . 'Les contenus éditables (lofts, page Commander, promotions, producteurs, etc.) n\'apparaissent pas dans l\'éditeur tant qu\'elle n\'est pas installée et activée. '
            . '<a href="' . esc_url(admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term')) . '">Installer ACF</a>.</p></div>';
    }
});

/* Chargement des styles et scripts */
function lv_enqueue_scripts_styles()
{
    // Polices Google : chargées en parallèle (WP ajoute le preconnect vers fonts.gstatic),
    // au lieu d'un @import en cascade qui bloquait le rendu.
    wp_enqueue_style(
        'lv-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    // Versions basées sur la date de modification : cache navigateur long ET
    // rafraîchissement automatique à chaque mise à jour de fichier.
    $css_path = get_stylesheet_directory() . '/style.css';
    $js_path  = get_stylesheet_directory() . '/assets/scripts/main.js';
    $css_ver  = file_exists($css_path) ? filemtime($css_path) : null;
    $js_ver   = file_exists($js_path)  ? filemtime($js_path)  : null;

    wp_enqueue_style('main', get_stylesheet_uri(), ['lv-google-fonts'], $css_ver);

    wp_enqueue_script(
        'main',
        get_stylesheet_directory_uri() . '/assets/scripts/main.js',
        [],
        $js_ver,
        ['strategy' => 'defer', 'in_footer' => true]
    );

    wp_localize_script('main', 'LV_RECHERCHE', [
        'ajax' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'lv_enqueue_scripts_styles');

/* Scripts spécifiques PAM et Vrac — chargés uniquement sur leur template */
add_action('wp_enqueue_scripts', function () {

    if (is_page_template('templates/template-bon-pam.php')) {
        $path = get_stylesheet_directory() . '/assets/scripts/pam-commande.js';
        wp_enqueue_script(
            'pam-commande',
            get_stylesheet_directory_uri() . '/assets/scripts/pam-commande.js',
            [],
            file_exists($path) ? filemtime($path) : null,
            ['strategy' => 'defer', 'in_footer' => true]
        );
        wp_localize_script('pam-commande', 'PAM', [
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pam_commande'),
        ]);
    }

    if (is_page_template('templates/template-bon-pm.php')) {
        $path = get_stylesheet_directory() . '/assets/scripts/pm-commande.js';
        wp_enqueue_script(
            'pm-commande',
            get_stylesheet_directory_uri() . '/assets/scripts/pm-commande.js',
            [],
            file_exists($path) ? filemtime($path) : null,
            ['strategy' => 'defer', 'in_footer' => true]
        );
        wp_localize_script('pm-commande', 'PM', [
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pm_commande'),
        ]);
    }

    if (is_page_template('templates/template-bon-vrac.php')) {
        $path = get_stylesheet_directory() . '/assets/scripts/vrac-commande.js';
        wp_enqueue_script(
            'vrac-commande',
            get_stylesheet_directory_uri() . '/assets/scripts/vrac-commande.js',
            [],
            file_exists($path) ? filemtime($path) : null,
            ['strategy' => 'defer', 'in_footer' => true]
        );
        wp_localize_script('vrac-commande', 'VRAC', [
            'ajax'      => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('vrac_commande'),
            'escomptes' => lv_vrac_escomptes_globaux(),
        ]);
    }
});

/* Suggestions de recherche en direct (AJAX) */
function lv_recherche_suggestions()
{
    $terme = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    if (mb_strlen($terme) < 2) {
        wp_send_json([]);
    }

    $q = new WP_Query([
        's'              => $terme,
        'post_type'      => ['produit', 'producteur', 'article_boutique', 'loft', 'post', 'page'],
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'no_found_rows'  => true,
        'ignore_sticky_posts' => true,
    ]);

    $labels = [
        'produit'         => 'Produit',
        'producteur'      => 'Producteur',
        'article_boutique' => 'Boutique',
        'loft'            => 'Loft',
        'post'            => 'Article',
        'page'            => 'Page',
    ];

    $resultats = [];
    while ($q->have_posts()) {
        $q->the_post();
        $type = get_post_type();
        $resultats[] = [
            'titre' => html_entity_decode(get_the_title()),
            'url'   => get_permalink(),
            'type'  => $labels[$type] ?? '',
        ];
    }
    wp_reset_postdata();

    wp_send_json($resultats);
}
add_action('wp_ajax_lv_suggestions', 'lv_recherche_suggestions');
add_action('wp_ajax_nopriv_lv_suggestions', 'lv_recherche_suggestions');

/* Configuration du thème */
function lv_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Bannière personnalisée
    add_theme_support('custom-header', [
        'width'       => 1920,
        'height'      => 600,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    // Logo personnalisé
    add_theme_support('custom-logo', [
        'width'                => 512,
        'height'               => 512,
        'flex-width'           => true,
        'flex-height'          => true,
        'unlink-homepage-logo' => false,
    ]);

    // Menu principal
    register_nav_menu('principal', 'Menu principal');
}
add_action('after_setup_theme', 'lv_theme_setup');

/* Le menu "principal" est construit à la main dans Apparence > Menus : un
   lien vers une page reste dans le menu même si Marie repasse ensuite cette
   page en brouillon ou en privé, ce qui donnerait un lien mort (404) côté
   client. On retire ici, à l'affichage, tout élément de menu qui pointe
   vers une page/publication qui n'est pas "publish". */
add_filter('wp_nav_menu_objects', function ($items) {
    if (is_admin()) {
        return $items;
    }

    return array_values(array_filter($items, function ($item) {
        if ($item->type !== 'post_type') {
            return true; // lien personnalisé, catégorie, etc. : non concerné
        }
        return get_post_status($item->object_id) === 'publish';
    }));
});

/* Header = menu court (menu principal, 5 pages, SANS Producteurs).
   Footer = navigation complete auto-generee (toutes les pages publiees
   + archives Producteurs/Produits), voir footer.php. */

/* Recherche : inclure les contenus du Vivier (produits, producteurs, boutique, lofts)
   en plus des articles et pages. */
add_action('pre_get_posts', function ($q) {
    if (!is_admin() && $q->is_main_query() && $q->is_search()) {
        $q->set('post_type', ['post', 'page', 'produit', 'producteur', 'article_boutique', 'loft']);
    }
});

/* Produits : n'afficher dans les listes que ceux avec une photo mise en avant.
   La fiche produit seule (acces direct) reste consultable pendant que Marie
   ajoute la photo, donc on exclut les requetes "singular". */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || $q->get('post_type') !== 'produit' || $q->is_singular()) {
        return;
    }
    $meta_query   = $q->get('meta_query') ?: [];
    $meta_query[] = ['key' => '_thumbnail_id', 'compare' => 'EXISTS'];
    $q->set('meta_query', $meta_query);
});

/* Logo : chargement immédiat et prioritaire (il est tout en haut de page),
   au lieu du « lazy » par défaut qui le faisait apparaître en retard. */
add_filter('get_custom_logo_image_attributes', function ($attr) {
    $attr['loading']       = 'eager';
    $attr['fetchpriority']  = 'high';
    $attr['decoding']       = 'async';
    return $attr;
});

/* Univers visuels (classe sur le body pour recolorer header/footer/menu) */
add_filter('body_class', function ($classes) {
    if (is_singular('loft') || is_page_template('templates/template-lofts.php')) {
        $classes[] = 'univers-lofts';
    }
    if (is_page_template('templates/template-epicerie-africaine.php')) {
        $classes[] = 'univers-africaine';
    }
    if (is_page_template('templates/template-boutique.php')) {
        $classes[] = 'page-boutique';
    }
    if (is_page_template('templates/template-epicerie.php')) {
        $classes[] = 'page-epicerie';
    }
    if (is_page_template('templates/template-produits-maison.php')) {
        $classes[] = 'page-produits-maison';
    }
    return $classes;
});

/* ======================================================
   PERSONNALISATEUR — Contenu des pages d'archive
   (Apparence → Personnaliser → Page Producteurs / Page Produits)
====================================================== */
function lv_prod_intro_defaut()
{
    return "Chaque produit est choisi avec soin pour soutenir les producteurs d'ici et encourager une consommation consciente et respectueuse de l'environnement. Découvrez celles et ceux qui cultivent, récoltent et transforment pour vous, à deux pas d'ici.";
}

function lv_produits_intro_defaut()
{
    return "Découvrez l'ensemble de notre sélection : produits locaux, biologiques, en vrac et zéro déchet, choisis avec soin pour allier goût, santé et respect de l'environnement.";
}

add_action('customize_register', function ($wp_customize) {

    $sections = [
        'lv_producteurs' => [
            'titre'       => 'Page Producteurs',
            'description' => 'Textes affichés en haut de la page /producteurs/.',
            'champs'      => [
                'lv_prod_surtitre' => ['label' => 'Surtitre', 'default' => 'Nos partenaires · Le Vivier', 'type' => 'text'],
                'lv_prod_titre'    => ['label' => 'Titre', 'default' => 'Nos Producteurs & Transformateurs', 'type' => 'text'],
                'lv_prod_intro'    => ['label' => 'Texte d\'introduction', 'default' => lv_prod_intro_defaut(), 'type' => 'textarea'],
            ],
        ],
        'lv_produits' => [
            'titre'       => 'Page Produits',
            'description' => 'Textes affichés en haut de la page /produits/.',
            'champs'      => [
                'lv_produits_surtitre' => ['label' => 'Surtitre', 'default' => 'Épicerie boutique · Le Vivier', 'type' => 'text'],
                'lv_produits_titre'    => ['label' => 'Titre', 'default' => 'Tous nos produits', 'type' => 'text'],
                'lv_produits_intro'    => ['label' => 'Texte d\'introduction', 'default' => lv_produits_intro_defaut(), 'type' => 'textarea'],
            ],
        ],
    ];

    $priorite = 160;

    foreach ($sections as $section_id => $section) {
        $wp_customize->add_section($section_id, [
            'title'       => $section['titre'],
            'description' => $section['description'],
            'priority'    => $priorite++,
        ]);

        foreach ($section['champs'] as $id => $cfg) {
            $wp_customize->add_setting($id, [
                'default'           => $cfg['default'],
                'sanitize_callback' => $cfg['type'] === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control($id, [
                'label'   => $cfg['label'],
                'section' => $section_id,
                'type'    => $cfg['type'],
            ]);
        }
    }
});

/* Catégories de la galerie des lofts (slug => nom affiché) */
function lv_loft_categories()
{
    return [
        'salon'   => 'Salon',
        'cuisine' => 'Cuisine',
        'chambre' => 'Chambre',
        'bain'    => 'Salle de bain',
        'ext'     => 'Extérieur & vue',
        'autre'   => 'Autres',
    ];
}

/* Champs ACF de la galerie : 4 photos par catégorie */
function lv_loft_galerie_fields()
{
    $fields = [[
        'key'     => 'field_loft_msg_galerie',
        'label'   => '📸 Galerie par pièce',
        'type'    => 'message',
        'message' => 'L\'<strong>image mise en avant</strong> (colonne de droite) sert de grande photo de couverture. Ajoutez ensuite les photos par pièce : dans la visite « Afficher toutes les photos », une catégorie n\'apparaît que si elle contient au moins une photo.',
    ]];

    foreach (lv_loft_categories() as $slug => $nom) {
        $fields[] = ['key' => "field_loft_cat_{$slug}_msg", 'label' => $nom, 'type' => 'message', 'message' => ''];
        for ($n = 1; $n <= 4; $n++) {
            $fields[] = [
                'key'           => "field_loft_cat_{$slug}_{$n}",
                'name'          => "loft_cat_{$slug}_{$n}",
                'label'         => "{$nom} — Photo {$n}",
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ];
        }
    }

    return $fields;
}

/* Collecte les paliers d'escompte vrac depuis tous les produits (max % par palier) */
function lv_vrac_escomptes_globaux()
{
    if (!function_exists('get_field')) return [];

    $q = new WP_Query([
        'post_type'      => 'vrac_produit',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'fields'         => 'ids',
    ]);

    $paliers = [];
    foreach ($q->posts as $id) {
        $rows = get_field('vrac_escomptes', $id);
        if (!$rows || !is_array($rows)) continue;
        foreach ($rows as $row) {
            $min = (int) ($row['palier_min']   ?? 0);
            $pct = (int) ($row['pourcentage']  ?? 0);
            if ($min > 0 && $pct > 0) {
                if (!isset($paliers[$min]) || $paliers[$min] < $pct) {
                    $paliers[$min] = $pct;
                }
            }
        }
    }

    ksort($paliers);
    $result = [];
    foreach ($paliers as $min => $pct) {
        $result[] = ['min' => $min, 'pct' => $pct];
    }
    return $result;
}

/* ======================================================
   CHAMPS ACF — enregistrement programmatique
====================================================== */
add_action('acf/init', function () {

    if (!function_exists('acf_add_local_field_group')) return;

    /* Bon de commande (type d'article) */
    acf_add_local_field_group([
        'key'    => 'group_bon_commande',
        'title'  => 'Détails du bon de commande',
        'fields' => [
            ['key' => 'field_bon_image', 'name' => 'bon_image', 'label' => 'Image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium', 'instructions' => 'Photo affichée en haut de la carte (format paysage 16:9 idéal).'],
            ['key' => 'field_bon_description', 'name' => 'bon_description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_bon_liste', 'name' => 'bon_liste', 'label' => 'Liste (un élément par ligne)', 'type' => 'textarea', 'rows' => 5, 'instructions' => 'Ce que contient ce bon de commande, une ligne par catégorie.'],
            ['key' => 'field_bon_url', 'name' => 'bon_url', 'label' => 'Lien du formulaire (JotForm)', 'type' => 'url', 'instructions' => 'URL du bon de commande en ligne.'],
            ['key' => 'field_bon_cta', 'name' => 'bon_cta', 'label' => 'Texte du bouton', 'type' => 'text', 'default_value' => 'Remplir ce bon de commande'],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'bon_commande']]],
        'menu_order' => 0,
    ]);

    /* Produit : prix + badge */
    acf_add_local_field_group([
        'key'    => 'group_produit',
        'title'  => 'Informations produit',
        'fields' => [
            [
                'key'          => 'field_produit_prix',
                'name'         => 'produit_prix',
                'label'        => 'Prix',
                'type'         => 'text',
                'instructions' => 'Ex: 4,50 $ / 100g',
            ],
            [
                'key'     => 'field_produit_badge',
                'name'    => 'produit_badge',
                'label'   => 'Badge',
                'type'    => 'select',
                'choices' => [
                    ''       => '— Aucun —',
                    'Bio'    => 'Bio',
                    'Local'  => 'Local',
                    'Maison' => 'Maison',
                    'Frais'  => 'Frais',
                    'Vrac'   => 'Vrac',
                ],
                'allow_null' => 1,
            ],
            [
                'key'          => 'field_produit_bon_url',
                'name'         => 'produit_bon_url',
                'label'        => 'Lien du bon de commande (réservation)',
                'type'         => 'url',
                'instructions' => 'Optionnel. Lien JotForm pour réserver ce produit (utile pour les produits maison). Laissez vide pour ne pas afficher de bouton.',
            ],
            [
                'key'           => 'field_produit_bon_label',
                'name'          => 'produit_bon_label',
                'label'         => 'Texte du bouton de réservation',
                'type'          => 'text',
                'default_value' => 'Réserver ce produit',
                'instructions'  => 'Affiché seulement si un lien de bon de commande est renseigné.',
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'produit']]],
        'menu_order' => 0,
    ]);

    /* Produit — champs spécifiques au bon de commande Produits Maison */
    acf_add_local_field_group([
        'key'    => 'group_produit_pm',
        'title'  => 'Bon de commande Produits Maison',
        'fields' => [
            [
                'key'           => 'field_pm_commandable',
                'name'          => 'pm_commandable',
                'label'         => 'Disponible à la commande (bon Produits Maison)',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'instructions'  => 'Activez pour que ce produit apparaisse dans le bon de commande Produits Maison.',
            ],
            [
                'key'               => 'field_pm_prix',
                'name'              => 'pm_prix',
                'label'             => 'Prix (bon PM)',
                'type'              => 'number',
                'default_value'     => '',
                'min'               => 0,
                'step'              => 0.01,
                'instructions'      => 'Prix affiché sur le bon de commande Produits Maison (peut différer du prix rayons).',
                'conditional_logic' => [[['field' => 'field_pm_commandable', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key'               => 'field_pm_description',
                'name'              => 'pm_description',
                'label'             => 'Description courte (bon PM)',
                'type'              => 'textarea',
                'rows'              => 2,
                'instructions'      => 'Texte affiché sous le nom sur le bon de commande.',
                'conditional_logic' => [[['field' => 'field_pm_commandable', 'operator' => '==', 'value' => '1']]],
            ],
        ],
        'location'   => [[['param' => 'post_type', 'operator' => '==', 'value' => 'produit']]],
        'menu_order' => 10,
    ]);

    /* Famille Maison (CPT famille_maison) */
    acf_add_local_field_group([
        'key'    => 'group_famille_maison',
        'title'  => 'Options Produit Maison',
        'fields' => [
            [
                'key'           => 'field_famille_cta_label',
                'name'          => 'famille_cta_label',
                'label'         => 'Texte du bouton Commander',
                'type'          => 'text',
                'default_value' => 'Commander',
                'instructions'  => 'Texte du bouton qui redirige vers le bon de commande.',
            ],
            [
                'key'           => 'field_famille_cta_categorie',
                'name'          => 'famille_cta_categorie',
                'label'         => 'Catégorie du bon de commande',
                'type'          => 'taxonomy',
                'taxonomy'      => 'categorie_produit',
                'field_type'    => 'select',
                'allow_null'    => 1,
                'return_format' => 'object',
                'save_terms'    => 0,
                'load_terms'    => 0,
                'add_term'      => 0,
                'instructions'  => 'Le bouton ouvre le bon de commande Produits Maison directement sur cette catégorie (ex : Amaretti). Laissez vide pour ouvrir le bon sans catégorie présélectionnée.',
            ],
        ],
        'location'   => [[['param' => 'post_type', 'operator' => '==', 'value' => 'famille_maison']]],
        'menu_order' => 0,
    ]);

    /* Page d'accueil : tout le contenu visible éditable */
    acf_add_local_field_group([
        'key'    => 'group_page_accueil',
        'title'  => 'Contenu de la page d\'accueil',
        'fields' => [
            ['key' => 'field_acc_tab_hero', 'type' => 'tab', 'label' => 'Hero'],
            [
                'key'           => 'field_acc_hero_texte',
                'name'          => 'acc_hero_texte',
                'label'         => 'Texte sous le titre',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Produits locaux, vrac et zéro déchet, choisis avec soin auprès de nos producteurs du Bas-Saint-Laurent.',
                'instructions'  => 'Le grand titre est le slogan du site (Réglages > Général).',
            ],
            [
                'key'           => 'field_acc_hero_btn1',
                'name'          => 'acc_hero_btn1',
                'label'         => 'Bouton principal',
                'type'          => 'text',
                'default_value' => 'Découvrir l\'épicerie',
            ],
            [
                'key'           => 'field_acc_hero_btn2',
                'name'          => 'acc_hero_btn2',
                'label'         => 'Bouton secondaire',
                'type'          => 'text',
                'default_value' => 'Nos producteurs',
            ],
            [
                'key'           => 'field_acc_hero_scroll',
                'name'          => 'acc_hero_scroll',
                'label'         => 'Mot de l\'indicateur de défilement',
                'type'          => 'text',
                'default_value' => 'Découvrir',
            ],
            ['key' => 'field_acc_tab_intro', 'type' => 'tab', 'label' => 'Intro'],
            [
                'key'           => 'field_acc_intro_puce1',
                'name'          => 'acc_intro_puce1',
                'label'         => 'Puce 1',
                'type'          => 'text',
                'default_value' => 'Producteurs locaux',
                'instructions'  => 'Le texte d\'intro se rédige dans l\'éditeur principal de la page, l\'image est l\'image mise en avant.',
            ],
            [
                'key'           => 'field_acc_intro_puce2',
                'name'          => 'acc_intro_puce2',
                'label'         => 'Puce 2',
                'type'          => 'text',
                'default_value' => 'Bio & naturel',
            ],
            [
                'key'           => 'field_acc_intro_puce3',
                'name'          => 'acc_intro_puce3',
                'label'         => 'Puce 3',
                'type'          => 'text',
                'default_value' => 'Vrac & zéro déchet',
            ],
            [
                'key'           => 'field_acc_intro_badge',
                'name'          => 'acc_intro_badge',
                'label'         => 'Badge sur l\'image',
                'type'          => 'text',
                'default_value' => '100 % local',
            ],
            [
                'key'           => 'field_acc_intro_btn',
                'name'          => 'acc_intro_btn',
                'label'         => 'Bouton',
                'type'          => 'text',
                'default_value' => 'Voir la boutique',
            ],
            ['key' => 'field_acc_tab_eng', 'type' => 'tab', 'label' => 'Engagements'],
            [
                'key'           => 'field_acc_eng_surtitre',
                'name'          => 'acc_eng_surtitre',
                'label'         => 'Surtitre',
                'type'          => 'text',
                'default_value' => 'Nos engagements',
            ],
            [
                'key'           => 'field_acc_eng_titre',
                'name'          => 'acc_eng_titre',
                'label'         => 'Titre',
                'type'          => 'text',
                'default_value' => 'Le bon goût, en conscience',
            ],
            [
                'key'           => 'field_acc_eng1_titre',
                'name'          => 'acc_eng1_titre',
                'label'         => 'Carte 1 : titre',
                'type'          => 'text',
                'default_value' => 'Local',
            ],
            [
                'key'           => 'field_acc_eng1_texte',
                'name'          => 'acc_eng1_texte',
                'label'         => 'Carte 1 : texte',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => '13 producteurs de la région de Matane, Bas-Saint-Laurent.',
            ],
            [
                'key'           => 'field_acc_eng2_titre',
                'name'          => 'acc_eng2_titre',
                'label'         => 'Carte 2 : titre',
                'type'          => 'text',
                'default_value' => 'Biologique',
            ],
            [
                'key'           => 'field_acc_eng2_texte',
                'name'          => 'acc_eng2_texte',
                'label'         => 'Carte 2 : texte',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Une sélection naturelle, choisie pour sa qualité et son impact positif.',
            ],
            [
                'key'           => 'field_acc_eng3_titre',
                'name'          => 'acc_eng3_titre',
                'label'         => 'Carte 3 : titre',
                'type'          => 'text',
                'default_value' => 'En vrac',
            ],
            [
                'key'           => 'field_acc_eng3_texte',
                'name'          => 'acc_eng3_texte',
                'label'         => 'Carte 3 : texte',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Achetez ce dont vous avez besoin, réduisez vos emballages.',
            ],
            [
                'key'           => 'field_acc_eng4_titre',
                'name'          => 'acc_eng4_titre',
                'label'         => 'Carte 4 : titre',
                'type'          => 'text',
                'default_value' => 'Communauté',
            ],
            [
                'key'           => 'field_acc_eng4_texte',
                'name'          => 'acc_eng4_texte',
                'label'         => 'Carte 4 : texte',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Soutenir l\'économie locale, un achat à la fois.',
            ],
            ['key' => 'field_acc_tab_prod', 'type' => 'tab', 'label' => 'Produits du moment'],
            [
                'key'           => 'field_acc_prod_surtitre',
                'name'          => 'acc_prod_surtitre',
                'label'         => 'Surtitre',
                'type'          => 'text',
                'default_value' => 'La boutique',
            ],
            [
                'key'           => 'field_acc_prod_titre',
                'name'          => 'acc_prod_titre',
                'label'         => 'Titre',
                'type'          => 'text',
                'default_value' => 'Nos produits du moment',
            ],
            [
                'key'           => 'field_acc_prod_texte',
                'name'          => 'acc_prod_texte',
                'label'         => 'Sous-titre',
                'type'          => 'text',
                'default_value' => 'Une sélection fraîche de nos rayons.',
            ],
            [
                'key'           => 'field_acc_prod_btn',
                'name'          => 'acc_prod_btn',
                'label'         => 'Bouton',
                'type'          => 'text',
                'default_value' => 'Voir tous les produits',
            ],
            ['key' => 'field_acc_tab_news', 'type' => 'tab', 'label' => 'Infolettre'],
            [
                'key'           => 'field_acc_news_surtitre',
                'name'          => 'acc_news_surtitre',
                'label'         => 'Surtitre',
                'type'          => 'text',
                'default_value' => 'Infolettre',
            ],
            [
                'key'           => 'field_acc_news_titre',
                'name'          => 'acc_news_titre',
                'label'         => 'Titre',
                'type'          => 'text',
                'default_value' => 'Restez dans la boucle',
            ],
            [
                'key'           => 'field_acc_news_texte',
                'name'          => 'acc_news_texte',
                'label'         => 'Texte',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Promotions, arrivages et nouvelles de nos producteurs, directement dans votre boîte de courriel.',
            ],
            [
                'key'           => 'field_acc_news_btn',
                'name'          => 'acc_news_btn',
                'label'         => 'Bouton',
                'type'          => 'text',
                'default_value' => 'S\'abonner',
            ],
        ],
        'location'   => [[['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']]],
        'menu_order' => 0,
    ]);

    /* Page Commander : contenu éditable */
    acf_add_local_field_group([
        'key'    => 'group_page_commander',
        'title'  => 'Contenu de la page Commander',
        'fields' => [

            /* ---- HÉROS ----
               La description s'écrit dans l'éditeur principal de la page.
               Les bons de commande sont gérés par « Bons de commande ». */
            [
                'key'   => 'field_cmd_surtitre',
                'name'  => 'cmd_surtitre',
                'label' => '① Héros — Surtitre',
                'type'  => 'text',
                'instructions' => 'Petit texte au-dessus du titre. Ex: Commandes · Le Vivier. La description se rédige dans l\'éditeur principal de la page.',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-commander.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Page Promotions : intro éditable */
    acf_add_local_field_group([
        'key'    => 'group_page_promotions',
        'title'  => 'Contenu de la page Promotions',
        'fields' => [
            [
                'key'          => 'field_promo_page_intro',
                'name'         => 'promo_page_intro',
                'label'        => 'Texte d\'introduction',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Paragraphe d\'accroche en haut de la page.',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-promotions.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Page Les Lofts de la Rivière — présentation de la marque */
    acf_add_local_field_group([
        'key'    => 'group_page_lofts',
        'title'  => 'Contenu — Page Lofts (présentation)',
        'fields' => [
            ['key' => 'field_lofts_surtitre', 'name' => 'lofts_surtitre', 'label' => 'Surtitre', 'type' => 'text'],
            ['key' => 'field_lofts_intro', 'name' => 'lofts_intro', 'label' => 'Introduction (ou utilisez l\'éditeur principal)', 'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_lofts_tagline', 'name' => 'lofts_tagline', 'label' => 'Slogan (ex: L\'art de vivre en ville)', 'type' => 'text'],

            /* ---- SECTION « POURQUOI VOUS ALLEZ ADORER » ---- */
            ['key' => 'field_lofts_msg_atouts', 'label' => '', 'type' => 'message', 'message' => '<strong>💚 Section « Pourquoi vous allez adorer »</strong>'],
            ['key' => 'field_lofts_atouts_titre', 'name' => 'lofts_atouts_titre', 'label' => 'Titre de la section', 'type' => 'text', 'default_value' => 'Pourquoi vous allez adorer'],
            [
                'key'           => 'field_lofts_atouts',
                'name'          => 'lofts_atouts',
                'label'         => 'Atouts',
                'type'          => 'textarea',
                'rows'          => 6,
                'instructions'  => 'Une ligne par atout, au format : <code>Titre | Description</code>. L\'icône est choisie automatiquement selon le titre.',
                'default_value' => "Tout à distance de marche | Restos, cafés, commerces et bord de mer à quelques minutes. Stationnez gratuitement une fois, puis oubliez la voiture.\nRien à apporter | Cuisinette complète, Wi-Fi rapide, téléviseur 65 pouces et literie soignée. Vous n'avez qu'à vous installer.\nUne épicerie sous vos pieds | Le Vivier vous attend au rez-de-chaussée : produits frais, cafés et prêt-à-manger des artisans d'ici. Le déjeuner commence dans l'escalier.\nRéservez l'esprit tranquille | Hébergement enregistré (CITQ 323422). Échanges directs, conditions claires, aucune surprise à l'arrivée.",
            ],

            /* ---- CTA FINALE (fait aussi office de pied de page sur cette page) ---- */
            ['key' => 'field_lofts_msg_cta', 'label' => '', 'type' => 'message', 'message' => '<strong>📣 Bande finale</strong> (cette bande remplace le pied de page habituel sur /lofts/)'],
            ['key' => 'field_lofts_cta_titre', 'name' => 'lofts_cta_titre', 'label' => 'Titre', 'type' => 'text', 'default_value' => 'Vos dates partent vite'],
            ['key' => 'field_lofts_cta_texte', 'name' => 'lofts_cta_texte', 'label' => 'Texte', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'Deux lofts seulement, un calendrier qui se remplit. Réservez les vôtres pendant qu\'ils sont libres.'],
            ['key' => 'field_lofts_cta_bouton', 'name' => 'lofts_cta_bouton', 'label' => 'Texte du bouton', 'type' => 'text', 'default_value' => 'Voir les lofts et réserver'],
            [
                'key'          => 'field_lofts_cta_lien',
                'name'         => 'lofts_cta_lien',
                'label'        => 'Lien du bouton',
                'type'         => 'url',
                'instructions' => 'Ex : votre lien Reservit ou Airbnb. Laissez vide pour faire défiler jusqu\'à la liste des lofts. Ce bouton apparaît ici et en bas de chaque fiche loft individuelle.',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-lofts.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Loft individuel (type d'article) */
    acf_add_local_field_group([
        'key'    => 'group_loft',
        'title'  => 'Détails du loft',
        'fields' => [

            /* ---- DESCRIPTION ---- */
            ['key' => 'field_loft_msg_desc', 'label' => '', 'type' => 'message', 'message' => '<strong>📝 Description :</strong> écrivez la description complète du loft dans l\'éditeur principal ci-dessus (le grand cadre de texte). Elle s\'affiche avec un bouton « Afficher plus ».'],

            /* ---- CAPACITÉ ---- */
            ['key' => 'field_loft_msg_capacite', 'label' => '🛏️ Capacité', 'type' => 'message', 'message' => 'La ligne affichée sous le titre, ex : « 2 voyageurs · 1 chambre · 1 lit · 1 salle de bain ».'],
            ['key' => 'field_loft_type', 'name' => 'loft_type', 'label' => 'Type de logement', 'type' => 'text', 'default_value' => 'Logement de location en entier'],
            ['key' => 'field_loft_voyageurs', 'name' => 'loft_voyageurs', 'label' => 'Nombre de voyageurs', 'type' => 'number', 'default_value' => 2],
            ['key' => 'field_loft_chambres', 'name' => 'loft_chambres', 'label' => 'Nombre de chambres', 'type' => 'number', 'default_value' => 1],
            ['key' => 'field_loft_lits', 'name' => 'loft_lits', 'label' => 'Nombre de lits', 'type' => 'number', 'default_value' => 1],
            ['key' => 'field_loft_sdb', 'name' => 'loft_sdb', 'label' => 'Nombre de salles de bain', 'type' => 'number', 'default_value' => 1],

            /* ---- PRIX ---- */
            ['key' => 'field_loft_msg_prix', 'label' => '💲 Prix', 'type' => 'message', 'message' => ''],
            ['key' => 'field_loft_prix', 'name' => 'loft_prix', 'label' => 'Prix par nuit (ex: 145 $)', 'type' => 'text'],
            ['key' => 'field_loft_prix_sub', 'name' => 'loft_prix_sub', 'label' => 'Sous-texte prix', 'type' => 'text', 'default_value' => 'Tout inclus'],
            ['key' => 'field_loft_prix_label', 'name' => 'loft_prix_label', 'label' => 'Étiquette prix (cartes liste, ex: À partir de)', 'type' => 'text', 'default_value' => 'À partir de'],
            ['key' => 'field_loft_badge', 'name' => 'loft_badge', 'label' => 'Badge (ex: Disponible / Nouveauté)', 'type' => 'text'],

            /* ---- POINTS FORTS ---- */
            ['key' => 'field_loft_msg_highlights', 'label' => '⭐ Points forts', 'type' => 'message', 'message' => 'Les 3 atouts mis en avant (style Airbnb). <strong>Une ligne par point fort</strong>, au format : <code>Titre | Description</code>. L\'icône est choisie automatiquement selon le titre (stationnement, café, arrivée autonome, etc.).'],
            ['key' => 'field_loft_highlights', 'name' => 'loft_highlights', 'label' => 'Points forts', 'type' => 'textarea', 'rows' => 4, 'default_value' => "Arrivée autonome | Entrez à votre rythme grâce à la serrure intelligente.\nStationnement gratuit | Un des rares logements de la région avec stationnement gratuit.\nCafé maison | Commencez la journée du bon pied avec la cafetière filtre."],

            /* ---- POUR VOTRE CONFORT ---- */
            ['key' => 'field_loft_msg_features', 'label' => '🧺 Pour votre confort', 'type' => 'message', 'message' => 'La liste des commodités, affichée en grille avec des icônes automatiques. <strong>Une commodité par ligne.</strong>'],
            ['key' => 'field_loft_features', 'name' => 'loft_features', 'label' => 'Commodités (une par ligne)', 'type' => 'textarea', 'rows' => 8, 'default_value' => "Cuisinette équipée\nWi-Fi gratuit\nTéléviseur intelligent 65\"\nSalle de bain privée\nStationnement gratuit\nLiterie de qualité\nCafetière filtre\nArrivée autonome"],

            /* ---- GALERIE PAR PIÈCE (générée) ---- */
            ...lv_loft_galerie_fields(),

            /* ---- RÉSERVATION ---- */
            ['key' => 'field_loft_msg_resa', 'label' => '🔗 Réservation', 'type' => 'message', 'message' => 'Collez les liens de réservation. Reservit s\'affiche en bouton principal, Airbnb en second. Si les deux sont vides, le bouton appelle par téléphone.'],
            ['key' => 'field_loft_reservit_url', 'name' => 'loft_reservit_url', 'label' => 'Lien Reservit', 'type' => 'url', 'instructions' => 'URL de réservation directe Reservit.'],
            ['key' => 'field_loft_airbnb_url', 'name' => 'loft_airbnb_url', 'label' => 'Lien Airbnb', 'type' => 'url', 'instructions' => 'Ex: https://fr.airbnb.ca/rooms/1678583818314456534'],
            ['key' => 'field_loft_cta_label', 'name' => 'loft_cta_label', 'label' => 'Texte du bouton sur les cartes (liste)', 'type' => 'text', 'default_value' => 'Réserver'],
            ['key' => 'field_loft_telephone', 'name' => 'loft_telephone', 'label' => 'Téléphone', 'type' => 'text', 'default_value' => '418 562-5230'],

            /* ---- LOCALISATION ---- */
            ['key' => 'field_loft_msg_loc', 'label' => '📍 Localisation', 'type' => 'message', 'message' => 'La carte Google Maps se génère automatiquement depuis l\'adresse. Pour un placement exact, collez une intégration personnalisée ci-dessous.'],
            ['key' => 'field_loft_adresse', 'name' => 'loft_adresse', 'label' => 'Adresse', 'type' => 'text', 'default_value' => '14, avenue D\'Amours'],
            ['key' => 'field_loft_ville', 'name' => 'loft_ville', 'label' => 'Ville / secteur', 'type' => 'text', 'default_value' => 'Matane, Québec · Centre-ville'],
            ['key' => 'field_loft_map_embed', 'name' => 'loft_map_embed', 'label' => 'Carte Google Maps (code d\'intégration — optionnel)', 'type' => 'textarea', 'rows' => 4, 'instructions' => 'Optionnel. Sur Google Maps : Partager → Intégrer une carte → « COPIER LE HTML », puis collez ici tout le code (il commence par &lt;iframe et finit par &lt;/iframe&gt;). Laissez vide pour générer automatiquement la carte depuis l\'adresse.'],
            ['key' => 'field_loft_facebook', 'name' => 'loft_facebook', 'label' => 'Lien Facebook', 'type' => 'url'],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'loft']]],
        'menu_order' => 0,
    ]);

    /* Page Épicerie Africaine : vitrine éditable */
    acf_add_local_field_group([
        'key'    => 'group_page_africaine',
        'title'  => 'Contenu — Épicerie Africaine',
        'fields' => [
            /* Héros */
            [
                'key'   => 'field_afr_surtitre',
                'name'  => 'afr_surtitre',
                'label' => '① Héros — Surtitre',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_intro',
                'name'  => 'afr_intro',
                'label' => '① Héros — Texte d\'introduction',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            /* Présentation */
            [
                'key'           => 'field_afr_presentation_image',
                'name'          => 'afr_presentation_image',
                'label'         => '② Présentation — Image (le texte = éditeur principal ci-dessus)',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            /* Spécialité 1 */
            [
                'key'   => 'field_afr_spec1_titre',
                'name'  => 'afr_spec1_titre',
                'label' => '③ Spécialité 1 — Titre',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_spec1_texte',
                'name'  => 'afr_spec1_texte',
                'label' => '③ Spécialité 1 — Description',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'           => 'field_afr_spec1_image',
                'name'          => 'afr_spec1_image',
                'label'         => '③ Spécialité 1 — Image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            /* Spécialité 2 */
            [
                'key'   => 'field_afr_spec2_titre',
                'name'  => 'afr_spec2_titre',
                'label' => '④ Spécialité 2 — Titre',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_spec2_texte',
                'name'  => 'afr_spec2_texte',
                'label' => '④ Spécialité 2 — Description',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'           => 'field_afr_spec2_image',
                'name'          => 'afr_spec2_image',
                'label'         => '④ Spécialité 2 — Image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            /* Spécialité 3 */
            [
                'key'   => 'field_afr_spec3_titre',
                'name'  => 'afr_spec3_titre',
                'label' => '⑤ Spécialité 3 — Titre',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_spec3_texte',
                'name'  => 'afr_spec3_texte',
                'label' => '⑤ Spécialité 3 — Description',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'           => 'field_afr_spec3_image',
                'name'          => 'afr_spec3_image',
                'label'         => '⑤ Spécialité 3 — Image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            /* Bande finale */
            [
                'key'   => 'field_afr_cta_titre',
                'name'  => 'afr_cta_titre',
                'label' => '⑥ Bande finale — Titre',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_cta_texte',
                'name'  => 'afr_cta_texte',
                'label' => '⑥ Bande finale — Texte',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'   => 'field_afr_cta_label',
                'name'  => 'afr_cta_label',
                'label' => '⑥ Bande finale — Texte du bouton',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_afr_cta_lien',
                'name'  => 'afr_cta_lien',
                'label' => '⑥ Bande finale — Lien du bouton',
                'type'  => 'url',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-epicerie-africaine.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Page Produits Maison : héros + présentation + bon de commande */
    acf_add_local_field_group([
        'key'    => 'group_page_maison',
        'title'  => 'Contenu — Produits Maison',
        'fields' => [
            /* Héros */
            [
                'key'   => 'field_pm_surtitre',
                'name'  => 'pm_surtitre',
                'label' => '① Héros — Surtitre',
                'type'  => 'text',
            ],
            [
                'key'          => 'field_pm_intro',
                'name'         => 'pm_intro',
                'label'        => '① Héros — Texte d\'introduction',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => 'Gardez 2 à 3 lignes courtes pour la lisibilité.',
            ],
            /* Présentation */
            [
                'key'           => 'field_pm_presentation_image',
                'name'          => 'pm_presentation_image',
                'label'         => '② Présentation — Image (le texte = éditeur principal ci-dessus)',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            /* Bon de commande / réservation */
            [
                'key'          => 'field_pm_bon_url',
                'name'         => 'pm_bon_url',
                'label'        => '③ Bon de commande — Lien du formulaire (JotForm)',
                'type'         => 'url',
                'instructions' => 'URL du bon de commande en ligne. Laissez vide pour masquer le bloc de réservation.',
            ],
            [
                'key'   => 'field_pm_bon_label',
                'name'  => 'pm_bon_label',
                'label' => '③ Bon de commande — Texte du bouton',
                'type'  => 'text',
            ],
            [
                'key'          => 'field_pm_bon_note',
                'name'         => 'pm_bon_note',
                'label'        => '③ Bon de commande — Texte d\'accompagnement',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => 'Gardez 2 à 3 lignes courtes.',
            ],
            /* Bande finale */
            [
                'key'   => 'field_pm_cta_titre',
                'name'  => 'pm_cta_titre',
                'label' => '④ Bande finale — Titre',
                'type'  => 'text',
            ],
            [
                'key'          => 'field_pm_cta_texte',
                'name'         => 'pm_cta_texte',
                'label'        => '④ Bande finale — Texte',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => 'Gardez 2 à 3 lignes courtes.',
            ],
            [
                'key'   => 'field_pm_cta_label',
                'name'  => 'pm_cta_label',
                'label' => '④ Bande finale — Texte du bouton',
                'type'  => 'text',
            ],
            [
                'key'          => 'field_pm_cta_lien',
                'name'         => 'pm_cta_lien',
                'label'        => '④ Bande finale — Lien du bouton',
                'type'         => 'url',
                'instructions' => 'Laissez vide pour masquer le bouton.',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-produits-maison.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Promotion : image + prix + dates de validité */
    acf_add_local_field_group([
        'key'    => 'group_promotion',
        'title'  => 'Détails de la promotion',
        'fields' => [
            [
                'key'           => 'field_promo_image',
                'name'          => 'promo_image',
                'label'         => 'Image du produit',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Photo du produit en promotion. Si vide, l\'image mise en avant est utilisée.',
            ],
            [
                'key'          => 'field_promo_prix_regulier',
                'name'         => 'promo_prix_regulier',
                'label'        => 'Prix régulier',
                'type'         => 'text',
                'instructions' => 'Ex: 12,99 $ — affiché barré',
            ],
            [
                'key'          => 'field_promo_prix_promo',
                'name'         => 'promo_prix_promo',
                'label'        => 'Prix promotionnel',
                'type'         => 'text',
                'instructions' => 'Ex: 9,99 $ — le nouveau prix',
            ],
            [
                'key'          => 'field_promo_rabais',
                'name'         => 'promo_rabais',
                'label'        => 'Étiquette de rabais (optionnel)',
                'type'         => 'text',
                'instructions' => 'Ex: -20 % ou 2 pour 1. Si vide, calculé automatiquement.',
            ],
            [
                'key'            => 'field_promo_date_debut',
                'name'           => 'promo_date_debut',
                'label'          => 'Date de début',
                'type'           => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format'  => 'Ymd',
                'first_day'      => 1,
                'instructions'   => 'La promo apparaît automatiquement à partir de cette date.',
                'required'       => 1,
            ],
            [
                'key'            => 'field_promo_date_fin',
                'name'           => 'promo_date_fin',
                'label'          => 'Date de fin',
                'type'           => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format'  => 'Ymd',
                'first_day'      => 1,
                'instructions'   => 'La promo disparaît automatiquement après cette date (incluse).',
                'required'       => 1,
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'promotion']]],
        'menu_order' => 0,
    ]);

    /* Article boutique : prix + marque + badge */
    acf_add_local_field_group([
        'key'    => 'group_boutique',
        'title'  => 'Informations article boutique',
        'fields' => [
            [
                'key'          => 'field_boutique_prix',
                'name'         => 'boutique_prix',
                'label'        => 'Prix',
                'type'         => 'text',
                'instructions' => 'Ex: 12,95 $',
            ],
            [
                'key'          => 'field_boutique_marque',
                'name'         => 'boutique_marque',
                'label'        => 'Marque / Fabricant',
                'type'         => 'text',
                'instructions' => "Ex: Abeego, Bee's Wrap…",
            ],
            [
                'key'     => 'field_boutique_badge',
                'name'    => 'boutique_badge',
                'label'   => 'Badge',
                'type'    => 'select',
                'choices' => [
                    ''               => '— Aucun —',
                    'Éco'            => 'Éco',
                    'Zéro déchet'    => 'Zéro déchet',
                    'Fait au Québec' => 'Fait au Québec',
                    'Biologique'     => 'Biologique',
                    'Nouveau'        => 'Nouveau',
                ],
                'allow_null' => 1,
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'article_boutique']]],
        'menu_order' => 0,
    ]);

    /* Produit Prêt à manger */
    acf_add_local_field_group([
        'key'    => 'group_pam_produit',
        'title'  => 'Informations PAM',
        'fields' => [
            [
                'key'          => 'field_pam_prix',
                'name'         => 'pam_prix',
                'label'        => 'Prix',
                'type'         => 'number',
                'required'     => 1,
                'prepend'      => '$',
                'step'         => '0.01',
                'min'          => 0,
                'instructions' => 'Ex: 6.50',
            ],
            [
                'key'     => 'field_pam_jours',
                'name'    => 'pam_jours',
                'label'   => 'Disponibilité',
                'type'    => 'checkbox',
                'choices' => [
                    'tous_les_jours' => 'Disponibles tous les jours',
                    'lundi'          => 'Spécial lundi',
                    'mardi'          => 'Spécial mardi',
                    'mercredi'       => 'Spécial mercredi',
                    'jeudi'          => 'Spécial jeudi',
                    'vendredi'       => 'Spécial vendredi',
                    'samedi'         => 'Spécial samedi',
                    'dimanche'       => 'Spécial dimanche',
                ],
                'layout'       => 'vertical',
                'instructions' => 'Cochez les jours où ce produit est disponible.',
            ],
            [
                'key'   => 'field_pam_description',
                'name'  => 'pam_description',
                'label' => 'Description courte',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'pam_produit']]],
        'menu_order' => 0,
    ]);

    /* Page bon PAM */
    acf_add_local_field_group([
        'key'    => 'group_page_bon_pam',
        'title'  => 'Contenu — Bon de commande PAM',
        'fields' => [
            [
                'key'          => 'field_pam_surtitre',
                'name'         => 'pam_surtitre',
                'label'        => 'Surtitre',
                'type'         => 'text',
                'instructions' => 'Petit texte au-dessus du titre, ex : Prêt à manger · Le Vivier',
            ],
            [
                'key'          => 'field_pam_messages_jours',
                'name'         => 'pam_messages_jours',
                'label'        => 'Messages par jour spécial',
                'type'         => 'repeater',
                'instructions' => 'Ajoutez un message visible par les clients quand ils sélectionnent un jour. Ex: "Cette semaine : focaccia aux tomates séchées et basilic !"',
                'button_label' => 'Ajouter un message',
                'layout'       => 'block',
                'sub_fields'   => [
                    [
                        'key'          => 'field_pam_msg_jour',
                        'name'         => 'msg_jour',
                        'label'        => 'Jour',
                        'type'         => 'select',
                        'allow_null'   => 1,
                        'instructions' => 'Choisissez le jour auquel ce message doit s\'afficher.',
                        'choices'      => [
                            'lundi'    => 'Lundi',
                            'mardi'    => 'Mardi',
                            'mercredi' => 'Mercredi',
                            'jeudi'    => 'Jeudi',
                            'vendredi' => 'Vendredi',
                            'samedi'   => 'Samedi',
                            'dimanche' => 'Dimanche',
                        ],
                        'wrapper' => ['width' => '100'],
                    ],
                    [
                        'key'           => 'field_pam_msg_image',
                        'name'          => 'msg_image',
                        'label'         => 'Logo ou photo (optionnel)',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'instructions'  => 'Ex : le logo du traiteur partenaire. Laissez vide pour un message texte seul.',
                        'wrapper'       => ['width' => '100'],
                    ],
                    [
                        'key'          => 'field_pam_msg_titre',
                        'name'         => 'msg_titre',
                        'label'        => 'Titre',
                        'type'         => 'text',
                        'placeholder'  => 'Ex : Les jeudis sushis au Vivier',
                        'instructions' => 'Titre accrocheur affiché en gras.',
                        'wrapper'      => ['width' => '100'],
                    ],
                    [
                        'key'          => 'field_pam_msg_description',
                        'name'         => 'msg_description',
                        'label'        => 'Description',
                        'type'         => 'textarea',
                        'rows'         => 3,
                        'placeholder'  => 'Ex : Le Vivier accueille chaque jeudi Le P\'tit Béret, un traiteur passionné qui vous propose de généreux sushis maison.',
                        'instructions' => 'Courte description du spécial du jour.',
                        'wrapper'      => ['width' => '100'],
                    ],
                    [
                        'key'          => 'field_pam_msg_cta',
                        'name'         => 'msg_cta',
                        'label'        => 'Infos pratiques',
                        'type'         => 'textarea',
                        'rows'         => 2,
                        'placeholder'  => "Commandez avant 10h le mercredi\nRécupérez au Vivier à partir de 12h le jeudi",
                        'instructions' => 'Délais de commande, heure de récupération, conditions, etc. Chaque ligne = une ligne affichée.',
                        'wrapper'      => ['width' => '100'],
                    ],
                ],
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-bon-pam.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Produit Vrac */
    acf_add_local_field_group([
        'key'    => 'group_vrac_produit',
        'title'  => 'Informations Vrac',
        'fields' => [
            [
                'key'          => 'field_vrac_formats',
                'name'         => 'vrac_formats',
                'label'        => 'Formats disponibles',
                'type'         => 'repeater',
                'instructions' => 'Ajoutez un format par ligne. Ex: 25 gr = 6,63 $',
                'min'          => 1,
                'button_label' => 'Ajouter un format',
                'sub_fields'   => [
                    [
                        'key'          => 'field_vrac_format_label',
                        'name'         => 'format_label',
                        'label'        => 'Format',
                        'type'         => 'text',
                        'instructions' => 'Ex: 25 gr, 100 gr, 250 gr',
                        'required'     => 1,
                        'wrapper'      => ['width' => '50'],
                    ],
                    [
                        'key'      => 'field_vrac_format_prix',
                        'name'     => 'format_prix',
                        'label'    => 'Prix',
                        'type'     => 'number',
                        'prepend'  => '$',
                        'step'     => '0.01',
                        'min'      => 0,
                        'required' => 1,
                        'wrapper'  => ['width' => '50'],
                    ],
                ],
            ],
            [
                'key'          => 'field_vrac_escomptes',
                'name'         => 'vrac_escomptes',
                'label'        => 'Paliers d\'escompte',
                'type'         => 'repeater',
                'instructions' => 'Montant minimum de commande (en $) pour déclencher l\'escompte. Ex : 30$ = 5%.',
                'button_label' => 'Ajouter un palier',
                'sub_fields'   => [
                    [
                        'key'          => 'field_vrac_palier_min',
                        'name'         => 'palier_min',
                        'label'        => 'Montant minimum ($)',
                        'type'         => 'number',
                        'instructions' => 'Ex: 30 pour déclencher à partir de 30 $ de commande',
                        'min'          => 0,
                        'wrapper'      => ['width' => '50'],
                    ],
                    [
                        'key'     => 'field_vrac_pourcentage',
                        'name'    => 'pourcentage',
                        'label'   => 'Escompte (%)',
                        'type'    => 'number',
                        'min'     => 1,
                        'max'     => 100,
                        'wrapper' => ['width' => '50'],
                    ],
                ],
            ],
            [
                'key'   => 'field_vrac_description',
                'name'  => 'vrac_description',
                'label' => 'Description',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            [
                'key'          => 'field_vrac_code',
                'name'         => 'vrac_code',
                'label'        => 'Code produit (optionnel)',
                'type'         => 'text',
                'instructions' => 'Code interne, ex: 9870',
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'vrac_produit']]],
        'menu_order' => 0,
    ]);

    /* Page bon Vrac */
    acf_add_local_field_group([
        'key'    => 'group_page_bon_vrac',
        'title'  => 'Contenu — Bon de commande Vrac',
        'fields' => [
            [
                'key'          => 'field_vrac_surtitre',
                'name'         => 'vrac_surtitre',
                'label'        => 'Surtitre',
                'type'         => 'text',
                'instructions' => 'Petit texte au-dessus du titre, ex : Commande en vrac · Le Vivier',
            ],
            [
                'key'          => 'field_vrac_intro',
                'name'         => 'vrac_intro',
                'label'        => 'Introduction',
                'type'         => 'wysiwyg',
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'instructions' => 'Paragraphe d\'accroche sous le titre.',
            ],
            [
                'key'          => 'field_vrac_bandeau_escompte',
                'name'         => 'vrac_bandeau_escompte',
                'label'        => 'Bandeau escompte',
                'type'         => 'wysiwyg',
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'instructions' => 'Bloc d\'information sur les escomptes vrac (ex: Opte pour le vrac écono & écolo).',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-bon-vrac.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Bon de commande Produits Maison — page */
    acf_add_local_field_group([
        'key'    => 'group_page_bon_pm',
        'title'  => 'Contenu — Bon de commande Produits Maison',
        'fields' => [
            [
                'key'           => 'field_pm_bon_surtitre',
                'name'          => 'pm_bon_surtitre',
                'label'         => 'Surtitre',
                'type'          => 'text',
                'default_value' => 'Produits Maison · Le Vivier',
                'instructions'  => 'Petit texte au-dessus du titre, ex : Produits Maison · Le Vivier',
            ],
        ],
        'location' => [[[
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'templates/template-bon-pm.php',
        ]]],
        'menu_order' => 0,
    ]);

    /* Producteur : tous les champs */
    acf_add_local_field_group([
        'key'    => 'group_producteur',
        'title'  => 'Informations producteur',
        'fields' => [
            [
                'key'           => 'field_producteur_logo',
                'name'          => 'producteur_logo',
                'label'         => 'Logo / Image du producteur',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
                'instructions'  => 'Logo ou photo représentant le producteur. Préférer un format carré ou paysage.',
            ],
            [
                'key'          => 'field_producteur_region',
                'name'         => 'producteur_region',
                'label'        => 'Région / Ville',
                'type'         => 'text',
                'instructions' => 'Ex: St-Luc-de-Matane',
            ],
            [
                'key'          => 'field_producteur_slogan',
                'name'         => 'producteur_slogan',
                'label'        => 'Slogan / Accroche',
                'type'         => 'text',
                'instructions' => 'Courte phrase qui décrit le producteur',
            ],
            [
                'key'          => 'field_producteur_telephone',
                'name'         => 'producteur_telephone',
                'label'        => 'Téléphone',
                'type'         => 'text',
                'instructions' => 'Ex: (418) 562-0000',
            ],
            [
                'key'          => 'field_producteur_email',
                'name'         => 'producteur_email',
                'label'        => 'Courriel',
                'type'         => 'email',
            ],
            [
                'key'          => 'field_producteur_adresse',
                'name'         => 'producteur_adresse',
                'label'        => 'Adresse',
                'type'         => 'text',
                'instructions' => 'Adresse complète',
            ],
            [
                'key'          => 'field_producteur_site_web',
                'name'         => 'producteur_site_web',
                'label'        => 'Site web',
                'type'         => 'url',
                'instructions' => 'URL complète avec https://',
            ],
            [
                'key'          => 'field_producteur_facebook',
                'name'         => 'producteur_facebook',
                'label'        => 'Page Facebook',
                'type'         => 'url',
            ],
            [
                'key'          => 'field_producteur_instagram',
                'name'         => 'producteur_instagram',
                'label'        => 'Instagram',
                'type'         => 'url',
            ],
            [
                'key'          => 'field_producteur_produits',
                'name'         => 'producteur_produits',
                'label'        => 'Produits disponibles au Vivier',
                'type'         => 'textarea',
                'rows'         => 8,
                'new_lines'    => '',
                'instructions' => 'Un produit par ligne. Affiché en liste sur la fiche du producteur (section « Disponibles au Vivier »). Laisser vide pour masquer la section.',
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'producteur']]],
        'menu_order' => 0,
    ]);
});

/* ======================================================
   RÉGLAGES DU SITE — page d'options (téléphone, etc.)
   lv_opt() / lv_opt_tel_lien() sont appelés dans plusieurs templates
   (single-loft.php, template-produits-maison.php) mais n'avaient pas
   d'implémentation : ça provoquait une erreur PHP fatale ("Call to
   undefined function") qui coupait le rendu de la page en plein milieu
   (d'où l'impression de CSS cassé — ce n'était pas le CSS, la page
   s'arrêtait avant même d'atteindre la suite du gabarit).
====================================================== */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => 'Réglages du site',
        'menu_title' => 'Réglages du site',
        'menu_slug'  => 'lv-reglages',
        'capability' => 'manage_options',
        'redirect'   => false,
        'icon_url'   => 'dashicons-admin-generic',
    ]);
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'    => 'group_reglages_site',
        'title'  => 'Réglages du site',
        'fields' => [
            [
                'key'           => 'field_opt_telephone',
                'name'          => 'opt_telephone',
                'label'         => 'Téléphone',
                'type'          => 'text',
                'default_value' => '(418) 562-5230',
                'instructions'  => 'Numéro affiché sur le site (boutons d\'appel, bandes CTA...).',
            ],
        ],
        'location' => [[[
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'lv-reglages',
        ]]],
    ]);
});

/* Valeur d'un réglage du site (page d'options ACF/SCF), avec repli si vide/absent. */
function lv_opt($cle, $defaut = '')
{
    if (!function_exists('get_field')) return $defaut;
    $valeur = get_field($cle, 'option');
    return $valeur ?: $defaut;
}

/* Lien tel: à partir du téléphone des réglages du site (même format que les
   autres liens tel: dynamiques du thème : chiffres seulement, sans indicatif). */
function lv_opt_tel_lien()
{
    return 'tel:' . preg_replace('/\D/', '', lv_opt('opt_telephone', '(418) 562-5230'));
}

/* Icône SVG (style ligne, couleur via currentColor) choisie selon un libellé.
   Déplacée depuis single-loft.php pour être réutilisable sur toutes les
   pages Lofts (liste ET fiche détail) sans dupliquer la fonction. */
if (!function_exists('lv_loft_icone')) {
    function lv_loft_icone($label) {
        $l = ' ' . mb_strtolower($label) . ' ';
        $mots = [
            'wifi'      => ['wi-fi', 'wifi', 'internet'],
            'sechoir'   => ['cheveux', 'sèche-cheveux'],
            'cuisine'   => ['cuisin', 'vaisselle'],
            'tele'      => ['télé', 'tele', ' tv ', 'téléviseur'],
            'parking'   => ['stationnement', 'parking'],
            'douche'    => ['bain', 'douche'],
            'lit'       => ['lit', 'chambre', 'literie', 'couchage'],
            'cafe'      => ['café', 'cafe', 'cafetière'],
            'buanderie' => ['laveuse', 'sécheuse', 'buanderie', 'lavage', 'laverie'],
            'camera'    => ['caméra', 'surveillance', 'sécurité', 'vidéo'],
            'volume'    => ['volume', 'bruit', 'silence', 'tapage'],
            'poubelle'  => ['poubelle', 'déchet', 'ordure', 'recyclage', 'tri'],
            'interdit'  => ['réservé', 'personnel', 'interdit', 'défense'],
            'porte'     => ['entrée', 'porte', 'accès'],
            'cle'       => ['arrivée', 'serrure', 'autonome', 'clé'],
            'eau'       => ['eau', 'rivière', 'fleuve', 'mer', 'vue'],
            'air'       => ['climatis', 'chauffage', 'ventil'],
            'produits'  => ['corporel', 'produit', 'savon', 'toilette'],
        ];
        $key = 'check';
        foreach ($mots as $k => $liste) {
            foreach ($liste as $m) {
                if (strpos($l, $m) !== false) { $key = $k; break 2; }
            }
        }
        $paths = [
            'wifi'      => '<path d="M4 11a12 12 0 0 1 16 0"/><path d="M7.5 14.5a7 7 0 0 1 9 0"/><path d="M10.5 18a3 3 0 0 1 3 0"/>',
            'cuisine'   => '<path d="M8 3v18"/><path d="M6 3v5a2 2 0 0 0 4 0V3"/><path d="M16 3v18"/><path d="M16 3c-2 0-3 2-3 4s1 3 3 3"/>',
            'tele'      => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8"/><path d="M12 16v4"/>',
            'parking'   => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 16V8h3a2.5 2.5 0 0 1 0 5H9"/>',
            'douche'    => '<path d="M12 3s6 6.5 6 10a6 6 0 0 1-12 0c0-3.5 6-10 6-10Z"/>',
            'lit'       => '<path d="M2 17h20"/><path d="M4 17v-4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M4 21v-4"/><path d="M20 21v-4"/><path d="M7 11V9a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2"/>',
            'cafe'      => '<path d="M5 8h11v5a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8Z"/><path d="M16 9h2a2 2 0 0 1 0 4h-2"/><path d="M8 3v2"/><path d="M11 3v2"/>',
            'buanderie' => '<rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="13" r="4"/><path d="M7 6h.01"/><path d="M10 6h.01"/>',
            'cle'       => '<circle cx="8" cy="8" r="4"/><path d="M11 11l9 9"/><path d="M20 17l-2 2"/><path d="M17 14l-2 2"/>',
            'eau'       => '<path d="M2 9q3-3 6 0t6 0 6 0"/><path d="M2 14q3-3 6 0t6 0 6 0"/><path d="M2 19q3-3 6 0t6 0 6 0"/>',
            'air'       => '<path d="M4 8h11a3 3 0 1 0-3-3"/><path d="M2 12h15a3 3 0 1 1-3 3"/><path d="M4 16h8a2.5 2.5 0 1 1-2.5 2.5"/>',
            'produits'  => '<path d="M10 3h4v3l1 2v11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V8l1-2Z"/><path d="M9 12h6"/>',
            'sechoir'   => '<path d="M3 8h9a4 4 0 0 1 0 8h-2"/><path d="M10 16l-1 4a1.5 1.5 0 0 1-3 0v-4"/><path d="M3 8v8h3"/><circle cx="8.5" cy="12" r="1.2"/>',
            'camera'    => '<path d="M2 8l15-4 1.2 4.6L3.2 12.6z"/><path d="M4.2 12.4 5 16a2 2 0 0 0 2 1.5h2.5"/><path d="M17.5 7.2 22 6"/><circle cx="9" cy="20" r="1.4"/>',
            'volume'    => '<path d="M4 9v6h4l5 4V5L8 9H4Z"/><path d="M17 9a4 4 0 0 1 0 6"/><path d="M19.5 7a7 7 0 0 1 0 10"/>',
            'porte'     => '<path d="M5 21V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v17"/><path d="M3 21h16"/><circle cx="13" cy="12" r="0.9" fill="currentColor" stroke="none"/>',
            'interdit'  => '<circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/>',
            'poubelle'  => '<path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            'check'     => '<path d="M4 12l5 5L20 6"/>',
        ];
        $inner = $paths[$key] ?? $paths['check'];
        return '<svg class="lv-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }
}

/* Icône des 4 « atouts » de la page Lofts (section « Pourquoi vous allez
   adorer ») : registre de mots-clés distinct de lv_loft_icone() (pensée
   pour les commodités d'une fiche) car ces titres marketing ("Tout à
   distance de marche", "Rien à apporter"...) ne matchent aucun mot-clé
   d'amenité et retomberaient tous sur la même icône générique. */
if (!function_exists('lv_lofts_atout_icone')) {
    function lv_lofts_atout_icone($label) {
        $l = ' ' . mb_strtolower($label) . ' ';
        $mots = [
            'marche'    => ['marche', 'distance', 'pied', 'centre-ville', 'proche'],
            'confort'   => ['apporter', 'cuisinette', 'wifi', 'confort', 'literie', 'équipé'],
            'epicerie'  => ['épicerie', 'epicerie', 'vivier', 'produits', 'déjeuner'],
            'securite'  => ['tranquille', 'réserv', 'enregistr', 'citq', 'sécur'],
        ];
        $key = 'coeur';
        foreach ($mots as $k => $liste) {
            foreach ($liste as $m) {
                if (strpos($l, $m) !== false) { $key = $k; break 2; }
            }
        }
        $paths = [
            'marche'   => '<path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
            'confort'  => '<rect x="4" y="8" width="16" height="11" rx="2"/><path d="M9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M9 11v5M15 11v5"/>',
            'epicerie' => '<path d="M5 8h14l-1.4 11.1a1 1 0 0 1-1 .9H7.4a1 1 0 0 1-1-.9L5 8Z"/><path d="M9 8 12 3l3 5"/>',
            'securite' => '<path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
            'coeur'    => '<path d="M20 8.6c0-2.6-2-4.6-4.5-4.6-1.4 0-2.7.7-3.5 1.8-.8-1.1-2.1-1.8-3.5-1.8C6 4 4 6 4 8.6 4 13.5 12 20 12 20s8-6.5 8-11.4Z"/>',
        ];
        $inner = $paths[$key];
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }
}

/* ======================================================
   PROMOTIONS — récupération des promos actives
   Une promo est active si : date_debut <= aujourd'hui <= date_fin
====================================================== */
function lv_get_promotions_actives($limite = -1)
{
    $aujourdhui = date('Ymd');

    return new WP_Query([
        'post_type'      => 'promotion',
        'post_status'    => 'publish',
        'posts_per_page' => $limite,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'promo_date_debut',
                'value'   => $aujourdhui,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ],
            [
                'key'     => 'promo_date_fin',
                'value'   => $aujourdhui,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ],
        ],
        'meta_key' => 'promo_date_fin',
        'orderby'  => 'meta_value_num',
        'order'    => 'ASC',
    ]);
}

/* Calcule un pourcentage de rabais à partir de deux prix texte ("12,99 $") */
function lv_calc_rabais($prix_regulier, $prix_promo)
{
    $reg = (float) str_replace(',', '.', preg_replace('/[^\d,.]/', '', $prix_regulier));
    $pro = (float) str_replace(',', '.', preg_replace('/[^\d,.]/', '', $prix_promo));

    if ($reg > 0 && $pro > 0 && $pro < $reg) {
        return '-' . round(($reg - $pro) / $reg * 100) . ' %';
    }
    return '';
}

/* IDs de la categorie « Produit Maison » + ses sous-categories (enfants).
   Sert a regrouper les produits maison sur leur page et a les exclure de l'Epicerie. */
function lv_maison_term_ids()
{
    $parent = get_term_by('slug', 'produit-maison', 'categorie_produit');
    if (!$parent || is_wp_error($parent)) {
        return [];
    }

    $ids = [(int) $parent->term_id];

    $enfants = get_terms([
        'taxonomy'   => 'categorie_produit',
        'child_of'   => $parent->term_id,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]);
    if ($enfants && !is_wp_error($enfants)) {
        $ids = array_merge($ids, array_map('intval', $enfants));
    }

    return $ids;
}

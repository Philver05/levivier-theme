<?php

require_once 'includes/post-types.php';
require_once 'includes/seed-data.php';

/* Chargement des styles et scripts */
function lv_enqueue_scripts_styles()
{
    wp_enqueue_style('main', get_stylesheet_uri());

    wp_enqueue_script(
        'main',
        get_stylesheet_directory_uri() . '/assets/scripts/main.js',
        [],
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'lv_enqueue_scripts_styles');

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

/* Liens d'archives ajoutés aux menus :
   - « Producteurs » : en-tête + pied de page (menu principal)
   - « Produits »    : pied de page uniquement (arg lv_footer) */
add_filter('wp_nav_menu_items', function ($items, $args) {

    if (($args->menu ?? '') === 'principal') {
        $url = get_post_type_archive_link('producteur');
        if ($url && strpos($items, $url) === false) {
            $items .= '<li class="menu-item"><a href="' . esc_url($url) . '">Producteurs</a></li>';
        }
    }

    if (!empty($args->lv_footer)) {
        $url = get_post_type_archive_link('produit');
        if ($url && strpos($items, $url) === false) {
            $items .= '<li class="menu-item"><a href="' . esc_url($url) . '">Produits</a></li>';
        }
    }

    return $items;
}, 10, 2);

/* Univers visuel « Lofts » : classe sur le body pour recolorer header/footer/menu */
add_filter('body_class', function ($classes) {
    if (is_singular('loft') || is_page_template('templates/template-lofts.php')) {
        $classes[] = 'univers-lofts';
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
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'produit']]],
        'menu_order' => 0,
    ]);

    /* Page Commander : contenu éditable */
    acf_add_local_field_group([
        'key'    => 'group_page_commander',
        'title'  => 'Contenu de la page Commander',
        'fields' => [

            /* ---- HÉROS ---- */
            [
                'key'   => 'field_cmd_surtitre',
                'name'  => 'cmd_surtitre',
                'label' => '① Héros — Surtitre',
                'type'  => 'text',
                'instructions' => 'Petit texte au-dessus du titre. Ex: Commandes · Le Vivier',
            ],
            [
                'key'          => 'field_cmd_intro',
                'name'         => 'cmd_intro',
                'label'        => '① Héros — Texte d\'introduction',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Court paragraphe d\'accroche sous le titre.',
            ],

            /* ---- CARTE THÉS ---- */
            [
                'key'           => 'field_cmd_the_image',
                'name'          => 'cmd_the_image',
                'label'         => '② Carte Thés — Image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Image d\'en-tête de la carte. Si vide, une icône 🍵 est utilisée.',
            ],
            [
                'key'   => 'field_cmd_the_titre',
                'name'  => 'cmd_the_titre',
                'label' => '② Carte Thés — Titre',
                'type'  => 'text',
            ],
            [
                'key'       => 'field_cmd_the_texte',
                'name'      => 'cmd_the_texte',
                'label'     => '② Carte Thés — Description',
                'type'      => 'textarea',
                'rows'      => 3,
            ],
            [
                'key'          => 'field_cmd_the_liste',
                'name'         => 'cmd_the_liste',
                'label'        => '② Carte Thés — Liste (un élément par ligne)',
                'type'         => 'textarea',
                'rows'         => 5,
                'instructions' => 'Une catégorie par ligne. Ex: Thés noirs, verts et matcha',
            ],
            [
                'key'   => 'field_cmd_the_url',
                'name'  => 'cmd_the_url',
                'label' => '② Carte Thés — Lien du formulaire JotForm',
                'type'  => 'url',
            ],

            /* ---- CARTE VRAC ---- */
            [
                'key'           => 'field_cmd_vrac_image',
                'name'          => 'cmd_vrac_image',
                'label'         => '③ Carte Vrac — Image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Image d\'en-tête de la carte. Si vide, une icône 🌾 est utilisée.',
            ],
            [
                'key'   => 'field_cmd_vrac_titre',
                'name'  => 'cmd_vrac_titre',
                'label' => '③ Carte Vrac — Titre',
                'type'  => 'text',
            ],
            [
                'key'       => 'field_cmd_vrac_texte',
                'name'      => 'cmd_vrac_texte',
                'label'     => '③ Carte Vrac — Description',
                'type'      => 'textarea',
                'rows'      => 3,
            ],
            [
                'key'          => 'field_cmd_vrac_liste',
                'name'         => 'cmd_vrac_liste',
                'label'        => '③ Carte Vrac — Liste (un élément par ligne)',
                'type'         => 'textarea',
                'rows'         => 5,
            ],
            [
                'key'   => 'field_cmd_vrac_url',
                'name'  => 'cmd_vrac_url',
                'label' => '③ Carte Vrac — Lien du formulaire JotForm',
                'type'  => 'url',
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
            ['key' => 'field_loft_msg_highlights', 'label' => '⭐ Points forts', 'type' => 'message', 'message' => 'Les 3 atouts mis en avant (style Airbnb). <strong>Une ligne par point fort</strong>, au format : <code>emoji | Titre | Description</code>'],
            ['key' => 'field_loft_highlights', 'name' => 'loft_highlights', 'label' => 'Points forts', 'type' => 'textarea', 'rows' => 4, 'default_value' => "🚪 | Arrivée autonome | Entrez à votre rythme grâce à la serrure intelligente.\n🅿️ | Stationnement gratuit | Un des rares logements de la région avec stationnement gratuit.\n☕ | Café maison | Commencez la journée du bon pied avec la cafetière filtre."],

            /* ---- POUR VOTRE CONFORT ---- */
            ['key' => 'field_loft_msg_features', 'label' => '🧺 Pour votre confort', 'type' => 'message', 'message' => 'La liste des commodités, affichée en grille avec des icônes automatiques. <strong>Une commodité par ligne.</strong>'],
            ['key' => 'field_loft_features', 'name' => 'loft_features', 'label' => 'Commodités (une par ligne)', 'type' => 'textarea', 'rows' => 8, 'default_value' => "Cuisinette équipée\nWi-Fi gratuit\nTéléviseur intelligent 65\"\nSalle de bain privée\nStationnement gratuit\nLiterie de qualité\nCafetière filtre\nArrivée autonome"],

            /* ---- GALERIE ---- */
            ['key' => 'field_loft_msg_galerie', 'label' => '📸 Galerie photos', 'type' => 'message', 'message' => 'L\'image mise en avant (encadré « Image mise en avant », colonne de droite) sert de grande photo principale. Ajoutez jusqu\'à 6 photos supplémentaires ci-dessous.'],
            ['key' => 'field_loft_img1', 'name' => 'loft_img1', 'label' => 'Galerie — Photo 1', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_loft_img2', 'name' => 'loft_img2', 'label' => 'Galerie — Photo 2', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_loft_img3', 'name' => 'loft_img3', 'label' => 'Galerie — Photo 3', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_loft_img4', 'name' => 'loft_img4', 'label' => 'Galerie — Photo 4', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_loft_img5', 'name' => 'loft_img5', 'label' => 'Galerie — Photo 5', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_loft_img6', 'name' => 'loft_img6', 'label' => 'Galerie — Photo 6', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],

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
            ['key' => 'field_loft_map_embed', 'name' => 'loft_map_embed', 'label' => 'Carte Google Maps (intégration — optionnel)', 'type' => 'url', 'instructions' => 'Optionnel. Google Maps → Partager → Intégrer une carte → copiez seulement le lien entre guillemets après src=. Laissez vide pour générer la carte depuis l\'adresse.'],
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
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'producteur']]],
        'menu_order' => 0,
    ]);
});

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

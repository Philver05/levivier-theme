<?php

/* CPT : Produit */
function lv_register_produit()
{
    $slug = 'produit';

    $labels = [
        'name'                  => 'Produits',
        'singular_name'         => 'Produit',
        'add_new'               => 'Ajouter un produit',
        'add_new_item'          => 'Ajouter un nouveau produit',
        'edit_item'             => 'Modifier le produit',
        'new_item'              => 'Nouveau produit',
        'view_item'             => 'Afficher le produit',
        'view_items'            => 'Afficher les produits',
        'search_items'          => 'Rechercher un produit',
        'not_found'             => 'Aucun produit trouvé',
        'not_found_in_trash'    => 'Aucun produit dans la corbeille',
        'all_items'             => 'Tous les produits',
        'archives'              => 'Archives des produits',
        'menu_name'             => 'Produits',
    ];

    $args = [
        'label'         => 'Produits',
        'labels'        => $labels,
        'description'   => 'Produits de l\'épicerie Le Vivier',
        'public'        => true,
        'has_archive'   => true,
        'show_in_rest'  => false,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-cart',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'       => ['slug' => 'produits'],
    ];

    register_post_type($slug, $args);
}

/* CPT : Producteur */
function lv_register_producteur()
{
    $slug = 'producteur';

    $labels = [
        'name'                  => 'Producteurs',
        'singular_name'         => 'Producteur',
        'add_new'               => 'Ajouter un producteur',
        'add_new_item'          => 'Ajouter un nouveau producteur',
        'edit_item'             => 'Modifier le producteur',
        'new_item'              => 'Nouveau producteur',
        'view_item'             => 'Afficher le producteur',
        'view_items'            => 'Afficher les producteurs',
        'search_items'          => 'Rechercher un producteur',
        'not_found'             => 'Aucun producteur trouvé',
        'not_found_in_trash'    => 'Aucun producteur dans la corbeille',
        'all_items'             => 'Tous les producteurs',
        'archives'              => 'Archives des producteurs',
        'menu_name'             => 'Producteurs',
    ];

    $args = [
        'label'         => 'Producteurs',
        'labels'        => $labels,
        'description'   => 'Producteurs locaux partenaires du Vivier',
        'public'        => true,
        'has_archive'   => true,
        'show_in_rest'  => false,
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-store',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'       => ['slug' => 'producteurs'],
    ];

    register_post_type($slug, $args);
}

/* CPT : Témoignage */
function lv_register_temoignage()
{
    $slug = 'temoignage';

    $labels = [
        'name'                  => 'Témoignages',
        'singular_name'         => 'Témoignage',
        'add_new'               => 'Ajouter un témoignage',
        'add_new_item'          => 'Ajouter un nouveau témoignage',
        'edit_item'             => 'Modifier le témoignage',
        'new_item'              => 'Nouveau témoignage',
        'view_item'             => 'Afficher le témoignage',
        'search_items'          => 'Rechercher un témoignage',
        'not_found'             => 'Aucun témoignage trouvé',
        'not_found_in_trash'    => 'Aucun témoignage dans la corbeille',
        'all_items'             => 'Tous les témoignages',
        'menu_name'             => 'Témoignages',
    ];

    $args = [
        'label'         => 'Témoignages',
        'labels'        => $labels,
        'description'   => 'Témoignages clients du Vivier',
        'public'        => false,
        'show_ui'       => true,
        'show_in_rest'  => false,
        'menu_position' => 7,
        'menu_icon'     => 'dashicons-format-quote',
        'supports'      => ['title', 'editor'],
        'rewrite'       => false,
    ];

    register_post_type($slug, $args);
}

/* CPT : Loft */
function lv_register_loft()
{
    $labels = [
        'name'               => 'Lofts',
        'singular_name'      => 'Loft',
        'add_new'            => 'Ajouter un loft',
        'add_new_item'       => 'Ajouter un nouveau loft',
        'edit_item'          => 'Modifier le loft',
        'new_item'           => 'Nouveau loft',
        'view_item'          => 'Afficher le loft',
        'view_items'         => 'Afficher les lofts',
        'search_items'       => 'Rechercher un loft',
        'not_found'          => 'Aucun loft trouvé',
        'not_found_in_trash' => 'Aucun loft dans la corbeille',
        'all_items'          => 'Tous les lofts',
        'menu_name'          => 'Lofts',
    ];

    register_post_type('loft', [
        'label'         => 'Lofts',
        'labels'        => $labels,
        'description'   => 'Les Lofts de la Rivière — locations',
        'public'        => true,
        'has_archive'   => false,
        'show_in_rest'  => false,
        'menu_position' => 8,
        'menu_icon'     => 'dashicons-admin-home',
        'supports'      => ['title', 'editor', 'thumbnail'],
        'rewrite'       => ['slug' => 'loft'],
    ]);
}

/* CPT : Bon de commande */
function lv_register_bon_commande()
{
    $labels = [
        'name'               => 'Bons de commande',
        'singular_name'      => 'Bon de commande',
        'add_new'            => 'Ajouter un bon',
        'add_new_item'       => 'Ajouter un nouveau bon de commande',
        'edit_item'          => 'Modifier le bon de commande',
        'new_item'           => 'Nouveau bon de commande',
        'search_items'       => 'Rechercher un bon de commande',
        'not_found'          => 'Aucun bon de commande trouvé',
        'not_found_in_trash' => 'Aucun bon de commande dans la corbeille',
        'all_items'          => 'Tous les bons de commande',
        'menu_name'          => 'Bons de commande',
    ];

    register_post_type('bon_commande', [
        'label'         => 'Bons de commande',
        'labels'        => $labels,
        'description'   => 'Bons de commande affichés sur la page Commander',
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-clipboard',
        'supports'      => ['title', 'page-attributes'],
    ]);
}

/* CPT : Promotion */
function lv_register_promotion()
{
    $labels = [
        'name'               => 'Promotions',
        'singular_name'      => 'Promotion',
        'add_new'            => 'Ajouter une promotion',
        'add_new_item'       => 'Ajouter une nouvelle promotion',
        'edit_item'          => 'Modifier la promotion',
        'new_item'           => 'Nouvelle promotion',
        'view_item'          => 'Afficher la promotion',
        'view_items'         => 'Afficher les promotions',
        'search_items'       => 'Rechercher une promotion',
        'not_found'          => 'Aucune promotion trouvée',
        'not_found_in_trash' => 'Aucune promotion dans la corbeille',
        'all_items'          => 'Toutes les promotions',
        'menu_name'          => 'Promotions',
    ];

    register_post_type('promotion', [
        'label'         => 'Promotions',
        'labels'        => $labels,
        'description'   => 'Promotions hebdomadaires du Vivier',
        'public'        => true,
        'has_archive'   => false,
        'show_in_rest'  => false,
        'menu_position' => 4,
        'menu_icon'     => 'dashicons-megaphone',
        'supports'      => ['title', 'editor', 'thumbnail'],
        'rewrite'       => ['slug' => 'promotion'],
    ]);
}

/* CPT : Article Boutique */
function lv_register_article_boutique()
{
    $labels = [
        'name'               => 'Boutique',
        'singular_name'      => 'Article boutique',
        'add_new'            => 'Ajouter un article',
        'add_new_item'       => 'Ajouter un nouvel article',
        'edit_item'          => 'Modifier l\'article',
        'new_item'           => 'Nouvel article',
        'view_item'          => 'Afficher l\'article',
        'view_items'         => 'Afficher les articles',
        'search_items'       => 'Rechercher un article',
        'not_found'          => 'Aucun article trouvé',
        'not_found_in_trash' => 'Aucun article dans la corbeille',
        'all_items'          => 'Tous les articles',
        'menu_name'          => 'Boutique',
    ];

    register_post_type('article_boutique', [
        'label'         => 'Boutique',
        'labels'        => $labels,
        'description'   => 'Articles non-alimentaires de la boutique Le Vivier',
        'public'        => true,
        // Pas d'archive : la Page "Boutique" (template-boutique.php) sert de listing.
        'has_archive'   => false,
        'show_in_rest'  => false,
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-tag',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        // Slug 'article-boutique' (et NON 'boutique') : sinon le CPT capture l'URL
        // /boutique/ et empeche la Page "Boutique" de s'afficher (collision de slug).
        'rewrite'       => ['slug' => 'article-boutique'],
    ]);
}

/* Taxonomie : Catégorie boutique */
function lv_register_categorie_boutique()
{
    register_taxonomy('categorie_boutique', 'article_boutique', [
        'labels' => [
            'name'          => 'Catégories boutique',
            'singular_name' => 'Catégorie boutique',
            'add_new_item'  => 'Ajouter une catégorie',
            'edit_item'     => 'Modifier la catégorie',
            'search_items'  => 'Rechercher une catégorie',
            'all_items'     => 'Toutes les catégories',
            'menu_name'     => 'Catégories',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'categorie-boutique'],
    ]);
}

/* Taxonomie : Catégorie de produit */
function lv_register_categorie_produit()
{
    register_taxonomy('categorie_produit', 'produit', [
        'labels' => [
            'name'              => 'Catégories',
            'singular_name'     => 'Catégorie',
            'add_new_item'      => 'Ajouter une catégorie',
            'edit_item'         => 'Modifier la catégorie',
            'search_items'      => 'Rechercher une catégorie',
            'all_items'         => 'Toutes les catégories',
            'menu_name'         => 'Catégories',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'categorie-produit'],
    ]);
}

/* Taxonomie : Type de producteur */
function lv_register_type_producteur()
{
    register_taxonomy('type_producteur', 'producteur', [
        'labels' => [
            'name'              => 'Types',
            'singular_name'     => 'Type',
            'add_new_item'      => 'Ajouter un type',
            'edit_item'         => 'Modifier le type',
            'search_items'      => 'Rechercher un type',
            'all_items'         => 'Tous les types',
            'menu_name'         => 'Types',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'type-producteur'],
    ]);
}

/* CPT : Produit Prêt à manger */
function lv_register_pam_produit()
{
    register_post_type('pam_produit', [
        'label'         => 'Prêt à manger',
        'labels'        => [
            'name'               => 'Prêt à manger',
            'singular_name'      => 'Produit PAM',
            'add_new'            => 'Ajouter un produit',
            'add_new_item'       => 'Ajouter un produit PAM',
            'edit_item'          => 'Modifier le produit PAM',
            'new_item'           => 'Nouveau produit PAM',
            'search_items'       => 'Rechercher un produit PAM',
            'not_found'          => 'Aucun produit PAM trouvé',
            'not_found_in_trash' => 'Aucun produit PAM dans la corbeille',
            'all_items'          => 'Tous les produits PAM',
            'menu_name'          => 'Prêt à manger',
        ],
        'description'   => 'Produits du bon de commande Prêt à manger',
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'show_in_rest'  => false,
        'menu_position' => 9,
        'menu_icon'     => 'dashicons-food',
        'supports'      => ['title', 'thumbnail', 'excerpt', 'page-attributes'],
        'rewrite'       => false,
    ]);
}

/* Taxonomie : Catégorie PAM */
function lv_register_pam_categorie()
{
    register_taxonomy('pam_categorie', 'pam_produit', [
        'labels' => [
            'name'          => 'Catégories PAM',
            'singular_name' => 'Catégorie PAM',
            'add_new_item'  => 'Ajouter une catégorie',
            'edit_item'     => 'Modifier la catégorie',
            'search_items'  => 'Rechercher une catégorie',
            'all_items'     => 'Toutes les catégories',
            'menu_name'     => 'Catégories',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => false,
    ]);
}

/* Seed des termes PAM — une seule fois au premier flush */
function lv_pam_seeder_termes()
{
    if (get_option('lv_pam_termes_seeded')) return;

    $termes = [
        'Focaccias', 'Pizzas', 'Sandwichs', 'Salades',
        'Pâtés', 'Sushis', 'Sauces', 'Divers prêt-à-manger',
    ];
    foreach ($termes as $terme) {
        if (!term_exists($terme, 'pam_categorie')) {
            wp_insert_term($terme, 'pam_categorie');
        }
    }
    update_option('lv_pam_termes_seeded', true);
}

/* CPT : Produit Vrac */
function lv_register_vrac_produit()
{
    register_post_type('vrac_produit', [
        'label'         => 'Produits vrac',
        'labels'        => [
            'name'               => 'Produits vrac',
            'singular_name'      => 'Produit vrac',
            'add_new'            => 'Ajouter un produit',
            'add_new_item'       => 'Ajouter un produit vrac',
            'edit_item'          => 'Modifier le produit vrac',
            'new_item'           => 'Nouveau produit vrac',
            'search_items'       => 'Rechercher un produit vrac',
            'not_found'          => 'Aucun produit vrac trouvé',
            'not_found_in_trash' => 'Aucun produit vrac dans la corbeille',
            'all_items'          => 'Tous les produits vrac',
            'menu_name'          => 'Vrac',
        ],
        'description'   => 'Produits du bon de commande en vrac (thés, tisanes, noix, grains…)',
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'show_in_rest'  => false,
        'menu_position' => 10,
        'menu_icon'     => 'dashicons-carrot',
        'supports'      => ['title', 'thumbnail', 'excerpt', 'page-attributes'],
        'rewrite'       => false,
    ]);
}

/* Taxonomie : Catégorie vrac */
function lv_register_vrac_categorie()
{
    register_taxonomy('vrac_categorie', 'vrac_produit', [
        'labels' => [
            'name'          => 'Catégories vrac',
            'singular_name' => 'Catégorie vrac',
            'add_new_item'  => 'Ajouter une catégorie',
            'edit_item'     => 'Modifier la catégorie',
            'search_items'  => 'Rechercher une catégorie',
            'all_items'     => 'Toutes les catégories',
            'menu_name'     => 'Catégories',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => false,
    ]);
}

function lv_register_famille_maison()
{
    register_post_type('famille_maison', [
        'labels' => [
            'name'               => 'Produits Maison',
            'singular_name'      => 'Produit Maison',
            'add_new_item'       => 'Ajouter un produit maison',
            'edit_item'          => 'Modifier le produit maison',
            'new_item'           => 'Nouveau produit maison',
            'view_item'          => 'Voir le produit maison',
            'search_items'       => 'Rechercher',
            'not_found'          => 'Aucun produit maison trouvé',
            'not_found_in_trash' => 'Aucun produit maison dans la corbeille',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'menu_icon'         => 'dashicons-heart',
        'menu_position'     => 6,
        'supports'          => ['title', 'editor', 'thumbnail'],
        'has_archive'       => false,
        'show_in_rest'      => true,
    ]);

    register_taxonomy('categorie_famille', ['famille_maison'], [
        'labels' => [
            'name'          => 'Catégories',
            'singular_name' => 'Catégorie',
            'add_new_item'  => 'Ajouter une catégorie',
            'edit_item'     => 'Modifier la catégorie',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ]);
}

function lv_register_rayon_boutique()
{
    register_post_type('rayon_boutique', [
        'labels' => [
            'name'               => 'Rayons boutique',
            'singular_name'      => 'Rayon boutique',
            'add_new_item'       => 'Ajouter un rayon',
            'edit_item'          => 'Modifier le rayon',
            'new_item'           => 'Nouveau rayon',
            'view_item'          => 'Voir le rayon',
            'search_items'       => 'Rechercher',
            'not_found'          => 'Aucun rayon trouvé',
            'not_found_in_trash' => 'Aucun rayon dans la corbeille',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'menu_icon'         => 'dashicons-art',
        'menu_position'     => 6,
        'supports'          => ['title', 'editor', 'thumbnail'],
        'has_archive'       => false,
        'show_in_rest'      => true,
    ]);
}

function lv_register_post_types()
{
    lv_register_produit();
    lv_register_producteur();
    lv_register_temoignage();
    lv_register_loft();
    lv_register_bon_commande();
    lv_register_promotion();
    lv_register_article_boutique();
    lv_register_pam_produit();
    lv_register_vrac_produit();
    lv_register_famille_maison();
    lv_register_rayon_boutique();
    lv_register_categorie_produit();
    lv_register_type_producteur();
    lv_register_categorie_boutique();
    lv_register_pam_categorie();
    lv_register_vrac_categorie();
}
add_action('init', 'lv_register_post_types');
add_action('init', 'lv_pam_seeder_termes', 20);

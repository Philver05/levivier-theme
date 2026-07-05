<?php
/**
 * Import ponctuel — fiches producteurs (d'après les affiches du Vivier)
 * Déclencher UNE SEULE FOIS en visitant : /wp-admin/?lv_import_prod=1
 * Nécessite d'être connecté en tant qu'administrateur.
 *
 * Remplit, pour 4 producteurs : description (éditeur), région, type, et la
 * liste « Disponibles au Vivier » (champ ACF producteur_produits).
 * Ne remplace JAMAIS un contenu déjà saisi : seuls les champs vides sont remplis.
 * Tout reste éditable ensuite dans l'admin WordPress.
 */

add_action('admin_init', function () {

    if (!isset($_GET['lv_import_prod']) || $_GET['lv_import_prod'] !== '1') return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    $log = [];

    /* Normalise un titre pour comparaison (minuscules, sans accents, espaces compactés) */
    $normaliser = function ($txt) {
        $txt = mb_strtolower(trim($txt), 'UTF-8');
        $txt = strtr($txt, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'û' => 'u', 'ù' => 'u', 'ü' => 'u',
            'ç' => 'c', "'" => ' ', "’" => ' ',
        ]);
        return preg_replace('/\s+/', ' ', $txt);
    };

    /* Producteurs existants, indexés par titre normalisé */
    $existants = [];
    foreach (get_posts(['post_type' => 'producteur', 'numberposts' => -1, 'post_status' => 'any']) as $p) {
        $existants[$normaliser($p->post_title)] = $p->ID;
    }

    /* Type « Producteurs bovin et autres » */
    $type_terme = get_term_by('name', 'Producteurs bovin et autres', 'type_producteur');
    $type_id    = $type_terme ? (int) $type_terme->term_id : 0;

    /* ----------------------------------------------------------------
       Données des affiches.
       'titres' : variantes possibles pour retrouver une fiche existante.
    ---------------------------------------------------------------- */
    $data = [
        [
            'titre'   => 'Porc O\'Rye du Kamouraska',
            'titres'  => ['Porc O\'Rye du Kamouraska', 'Porc O\'Rye', 'Porc O Rye'],
            'region'  => 'Saint-Joseph de Kamouraska',
            'desc'    => 'Claude Ouellet et sa famille élèvent des porcs nourris à même la production familiale de seigle. Cette alimentation particulière, l\'élevage sans antibiotique et la génétique unique développée par la famille depuis 45 ans, permettent de produire un porc de haute qualité au goût authentique.',
            'produits' => [
                'Grillades de Valleyfield',
                'Terrine Orange & Argousier',
                'Terrine Pistaches & Olives',
                'Burger Épinards & Fromage',
                'Burger Érable & Poivre',
                'Saucisses du Haut-Pays',
                'Saucisses poireaux et vin blanc',
                'Saucisses La Petite fumée',
                'Saucisses Abricot & miel',
                'Saucisses Whisky, cheddar et bacon',
            ],
            'contact' => [
                'producteur_slogan'    => 'Vous offrir une viande savoureuse, élevée et transformée ici, sans compromis sur la qualité.',
                'producteur_telephone' => '418 866-1573',
                'producteur_email'     => 'claude@oryekamouraska.com',
                'producteur_site_web'  => 'https://porcorye.ca/',
                'producteur_facebook'  => 'https://www.facebook.com/oryedukamouraska/',
                'producteur_instagram' => 'https://www.instagram.com/oryedukamouraska/',
            ],
        ],
        [
            'titre'   => 'Sunset Wagyu',
            'titres'  => ['Sunset Wagyu'],
            'region'  => 'Baie-des-Sables, QC',
            'desc'    => 'La ferme de Maude et Jonathan Fortin élève des bovins de boucherie depuis deux générations. La ferme produit entre autres du bœuf Wagyu, commercialisé sous la marque Sunset Wagyu, qui promet une expérience culinaire digne du ciel.',
            'produits' => [
                'Cube brochette',
                'Saucisse Italienne',
                'Steak de Surlonge',
                'Steak pointe surlonge',
                'Bavette',
                'Bloc à tartare',
                'Steak haché',
                'Filet mignon',
                'Faux filet',
                'Contre Filet',
                'Tournedos',
                'Denver',
            ],
            'contact' => [
                // Sunset Wagyu : seule la page Facebook a été trouvée publiquement.
                'producteur_facebook' => 'https://www.facebook.com/people/Sunset-Wagyu/100092533113886/',
            ],
        ],
        [
            'titre'   => 'La Ferme des Érables',
            'titres'  => ['La Ferme des Érables', 'La ferme des érables'],
            'region'  => 'Saint-Luc de Matane',
            'desc'    => 'La ferme de Martin et Johanne Gauthier se transmet de père en fils depuis quatre générations. En plus de la production bovine, acéricole et porcine, la ferme possède un centre de découpe. Grâce à une telle variété de production, l\'entreprise ne cesse de vous offrir une gamme variée de produits locaux.',
            'produits' => [
                'Pâté à la viande',
                'Cretons sans gluten',
                'Pâtés de campagne',
                'Sauce spaghetti',
                'Porc : côtelettes, filet, steak, rôti, jambon, bacon, etc.',
                'Saucisses : italiennes douces, rassembleuse, érable, etc.',
                'Bœuf : rôti de palette, bavette, filet mignon, steak, etc.',
            ],
            'contact' => [
                'producteur_telephone' => '418 562-1741',
                'producteur_email'     => 'la.boucherie.erable@hotmail.ca', // a reconfirmer (masque sur le site)
                'producteur_adresse'   => '75 rang Pierre-Gauthier, Matane (secteur Saint-Luc), QC G4W 9C2',
                'producteur_site_web'  => 'https://fermedeserables.com/',
            ],
        ],
        [
            'titre'   => 'Les Productions Quatre Vents',
            'titres'  => ['Les Productions Quatre Vents', 'Les Productions aux quatre vents', 'Les Productions aux Quatre Vents'],
            'region'  => 'Saint-Ulric',
            'desc'    => 'Fondée en 2009 par Nancy Lavoie et Steeve Côté, l\'entreprise se spécialise dans les productions ovine et bovine. En 2022, leur fils Pierre-Luc s\'est joint à l\'équipe à temps plein. Grâce à sa formation en boucherie de détail, il assure la découpe et la transformation des viandes.',
            'produits' => [
                'Saucisses d\'agneau',
                'Jerky d\'agneau',
                'Bœuf haché et boulettes de burger assaisonnés',
                'Agneau : haché, cubes et autres coupes sur demande',
                'Bacon d\'agneau',
                'Cretons d\'agneau',
            ],
            'contact' => [
                'producteur_telephone' => '418 737-4422',
                'producteur_email'     => 'pierreluccote8@gmail.com',
                'producteur_adresse'   => '2738, rang 5, Saint-Ulric, QC G0J 3H0',
                'producteur_facebook'  => 'https://www.facebook.com/Les-Productions-aux-quatre-vents-inc-100612392662573/', // a reconfirmer (2 profils existent)
            ],
        ],

        /* ----------------------------------------------------------------
           Producteurs SANS affiche : description + coordonnees trouvees en
           ligne (recherche web). Pas de liste « Disponibles au Vivier »
           (a remplir par Marie selon ce qui est vendu au magasin).
        ---------------------------------------------------------------- */
        [
            'titre'   => 'Mycobio',
            'titres'  => ['Mycobio', 'Les Potagers Mycobio'],
            'region'  => 'St-Luc-de-Matane',
            'desc'    => 'Fondés en 2012 par Marie-Hélène Côté et Denis Morais, Les Potagers Mycobio cultivent à Saint-Luc-de-Matane des légumes, des fruits, des germinations et des champignons certifiés biologiques.',
            'contact' => [
                'producteur_email' => 'lespotagersmycobio@hotmail.com',
            ],
        ],
        [
            'titre'   => 'Les jardins de l\'Orme',
            'titres'  => ['Les jardins de l\'Orme', 'Les Jardins de l\'Orme'],
            'region'  => 'Matane',
            'desc'    => 'Créés en 2000 à Matane par Caroline Durette et David Porlier, Les Jardins de l\'Orme produisent légumes, fines herbes et fleurs comestibles en culture écologique. La ferme prépare aussi confitures, aromates et produits de soin à base de fleurs et d\'herbes.',
            'contact' => [
                'producteur_telephone' => '418 562-1048',
                'producteur_adresse'   => '1584 Grand-Détour, Matane, QC G4W 3M7',
                'producteur_site_web'  => 'https://www.lesjardinsdelorme.com/',
                'producteur_facebook'  => 'https://www.facebook.com/lesjardinsdelorme/',
            ],
        ],
        [
            'titre'   => 'Les Jardins Hallé',
            'titres'  => ['Les Jardins Hallé', 'Les Jardins Halle'],
            'region'  => 'Saint-Moïse',
            'desc'    => 'Entreprise familiale fondée en 2023 à Saint-Moïse, dans la Vallée de la Matapédia. Elle se spécialise dans la production de brocoli, chou-fleur, choux, carottes, navets, oignons et autres légumes cultivés en harmonie avec la nature.',
            // Aucune coordonnee publique fiable trouvee.
        ],
        [
            'titre'   => 'Les Serres René-Santerre',
            'titres'  => ['Les Serres René-Santerre', 'Serres René Santerre', 'Les Serres René Santerre'],
            'region'  => 'Baie-des-Sables',
            'desc'    => 'Entreprise familiale de Baie-des-Sables qui exploite sept serres et produit des tomates rouges et jaunes certifiées biologiques (Québec Vrai). La récolte s\'étend de la fin juin à la mi-octobre.',
            'contact' => [
                'producteur_telephone' => '418 772-6339',
                'producteur_adresse'   => '494, rang 4, Baie-des-Sables, QC G0J 1C0',
                'producteur_facebook'  => 'https://www.facebook.com/profile.php?id=100063841261584',
            ],
        ],
        [
            'titre'   => 'La Vallée de Framboise',
            'titres'  => ['La Vallée de Framboise', 'La Vallée de la Framboise'],
            'region'  => 'Val-Brillant',
            'desc'    => 'Entreprise familiale de Val-Brillant et économusée des liquoristes. Elle cultive framboises, bleuets, fraises, gadelles, groseilles et autres petits fruits, transformés en vins, mistelles, liqueurs, tartinades, gelées et pâtes de fruits.',
            'contact' => [
                'producteur_telephone' => '418 742-3787',
                'producteur_email'     => 'passion@valleedelaframboise.com',
                'producteur_adresse'   => '34, rue Lauzier, Val-Brillant, QC',
                'producteur_site_web'  => 'https://lavalleedelaframboise.com/',
                'producteur_facebook'  => 'https://www.facebook.com/lavalleedelaframboise/',
            ],
        ],
        [
            'titre'   => 'Fromagerie du Littoral',
            'titres'  => ['Fromagerie du Littoral', 'La Fromagerie du Littoral'],
            'region'  => 'St-Ulric',
            'desc'    => 'Fromagerie artisanale de la Matanie qui transforme le lait de vache entier et pasteurisé en fromages fins.',
            'contact' => [
                'producteur_email'    => 'contact@fromageriedulittoral.com',
                'producteur_site_web' => 'https://fromageriedulittoral.com/',
                'producteur_facebook' => 'https://www.facebook.com/fromageriedulittoral/',
                // Telephone et adresse a confirmer : sources divergentes (Grand-Métis / Baie-des-Sables, 418 772-1314 / 418 772-6707).
            ],
        ],
        [
            'titre'   => 'Le Chant des fromages',
            'titres'  => ['Le Chant des fromages', 'Le Chant des Fromages'],
            'region'  => 'St-Luce',
            'desc'    => 'Fromagerie artisanale de Sainte-Luce qui transforme le lait de la Ferme Martin Turbide (Val-Brillant) en fromages. Comptoirs libre-service à Sainte-Luce et à Val-Brillant.',
            'contact' => [
                'producteur_telephone' => '418 713-4419',
                'producteur_email'     => 'lechantdesfromages@gmail.com',
                'producteur_adresse'   => '224, route 132 Ouest, Sainte-Luce, QC G0K 1P0',
                'producteur_site_web'  => 'https://www.lechantdesfromages.ca/',
                'producteur_facebook'  => 'https://www.facebook.com/lechantdesfromages/',
            ],
        ],
        [
            'titre'   => 'La Fumerie de l\'Est',
            'titres'  => ['La Fumerie de l\'Est', 'La Fumerie de l Est'],
            'region'  => 'Rimouski',
            'desc'    => 'Fumoir fondé en 1997 à Rimouski, reconnu pour sa truite fumée marinée et une gamme de poissons fumés artisanaux.',
            'contact' => [
                'producteur_telephone' => '418 721-2621',
                'producteur_email'     => 'info@lafumeriedelest.com',
                'producteur_adresse'   => '371, avenue Léonidas Sud, Rimouski, QC',
                'producteur_site_web'  => 'https://www.lafumeriedelest.com/',
                'producteur_facebook'  => 'https://www.facebook.com/lafumerie/',
            ],
        ],
        [
            'titre'   => 'Le Jardin des Corneilles',
            'titres'  => ['Le Jardin des Corneilles'],
            'region'  => 'La Matanie',
            'desc'    => 'Le Jardin des Corneilles crée des produits gourmands à partir de plantes sauvages cueillies en forêt et sur le littoral de la Matanie : confitures d\'églantier, marinades de pousses d\'épinette et tisanes artisanales.',
            'contact' => [
                'producteur_site_web' => 'https://www.jardindescorneilles.com/',
            ],
        ],
        [
            'titre'   => 'Le Plato Bistro Traiteur',
            'titres'  => ['Le Plato Bistro Traiteur', 'Plato Bistro-Traiteur', 'Le Plato'],
            'region'  => 'Matane',
            'desc'    => 'Bistro-traiteur de Matane proposant des plats originaux et prêts-à-manger : buffets, tapas, tartares et produits maison.',
            'contact' => [
                'producteur_telephone' => '581 232-2179',
                'producteur_email'     => 'leplatobistro@gmail.com',
                'producteur_adresse'   => '616, avenue du Rédempteur, Matane, QC G4W 9H7',
                'producteur_site_web'  => 'https://platobistrotraiteur.com/',
                'producteur_facebook'  => 'https://www.facebook.com/PlatoBistroTraiteur/',
                'producteur_instagram' => 'https://www.instagram.com/platobistro/',
            ],
        ],
    ];

    foreach ($data as $d) {

        /* Retrouver la fiche existante via l'une des variantes de titre */
        $post_id = 0;
        foreach ($d['titres'] as $variante) {
            $cle = $normaliser($variante);
            if (isset($existants[$cle])) {
                $post_id = $existants[$cle];
                break;
            }
        }

        /* Créer si introuvable */
        if (!$post_id) {
            $post_id = wp_insert_post([
                'post_type'   => 'producteur',
                'post_title'  => $d['titre'],
                'post_status' => 'publish',
            ]);
            if (is_wp_error($post_id)) {
                $log[] = "❌ Création « {$d['titre']} » : " . $post_id->get_error_message();
                continue;
            }
            $log[] = "✔ Producteur « {$d['titre']} » CRÉÉ (ID $post_id)";
        } else {
            $log[] = "• Producteur « {$d['titre']} » trouvé (ID $post_id)";
        }

        /* Description — seulement si l'éditeur est vide */
        $post = get_post($post_id);
        if (trim($post->post_content) === '') {
            wp_update_post(['ID' => $post_id, 'post_content' => $d['desc']]);
            $log[] = "&nbsp;&nbsp;↳ description ajoutée";
        } else {
            $log[] = "&nbsp;&nbsp;↳ description déjà présente (non modifiée)";
        }

        /* Région — seulement si vide */
        if (function_exists('update_field')) {
            $region_actuelle = get_field('producteur_region', $post_id);
            if (!$region_actuelle) {
                update_field('producteur_region', $d['region'], $post_id);
                $log[] = "&nbsp;&nbsp;↳ région « {$d['region']} » ajoutée";
            }

            /* Liste produits — seulement pour les producteurs ayant une affiche, et si vide */
            if (!empty($d['produits'])) {
                $produits_actuels = get_field('producteur_produits', $post_id);
                if (!$produits_actuels) {
                    update_field('producteur_produits', implode("\n", $d['produits']), $post_id);
                    $log[] = "&nbsp;&nbsp;↳ " . count($d['produits']) . " produits « Disponibles au Vivier » ajoutés";
                } else {
                    $log[] = "&nbsp;&nbsp;↳ liste produits déjà présente (non modifiée)";
                }
            }

            /* Coordonnées (recherche web) — chaque champ seulement s'il est vide */
            if (!empty($d['contact'])) {
                foreach ($d['contact'] as $champ => $valeur) {
                    if ($valeur === '') continue;
                    if (!get_field($champ, $post_id)) {
                        update_field($champ, $valeur, $post_id);
                        $log[] = "&nbsp;&nbsp;↳ {$champ} ajouté";
                    }
                }
            }
        }

        /* Type — seulement si aucun type assigné */
        if ($type_id) {
            $deja = wp_get_object_terms($post_id, 'type_producteur', ['fields' => 'ids']);
            if (empty($deja) || is_wp_error($deja)) {
                wp_set_object_terms($post_id, $type_id, 'type_producteur');
                $log[] = "&nbsp;&nbsp;↳ type « Producteurs bovin et autres » assigné";
            }
        }
    }

    /* Rapport */
    echo '<style>body{font-family:monospace;padding:2rem;background:#f9f5f0;}
          h1{color:#b85c50;} li{margin:.25rem 0;list-style:none;} .ok{color:#4d6040;} .err{color:#c00;}</style>';
    echo '<h1>🥩 Le Vivier — Import des fiches producteurs</h1>';
    echo '<p><strong>✅ Terminé.</strong> Détail (seuls les champs vides ont été remplis) :</p>';
    echo '<ul>';
    foreach ($log as $ligne) {
        $class = str_contains($ligne, '❌') ? 'err' : 'ok';
        echo '<li class="' . $class . '">' . wp_kses($ligne, ['br' => []]) . '</li>';
    }
    echo '</ul>';
    echo '<p style="margin-top:1.5rem;color:#a04738;"><strong>Tu peux relancer cette URL sans risque :</strong> elle ne touche jamais à un champ déjà rempli.</p>';
    echo '<p style="margin-top:1rem;"><a href="' . admin_url('edit.php?post_type=producteur') . '" style="color:#b85c50;">→ Voir les producteurs</a></p>';
    exit;
});

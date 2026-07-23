<?php
/*
Template Name: L'Épicerie
*/
get_header();

/* Champs ACF avec repli (textes actuels) — tout éditable dans WP */
$ep = function ($cle, $defaut = '') {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};

/* Catégories produit (bande de filtres sous l'en-tête, comme Produits Maison) */
$categories_produit = get_terms([
    'taxonomy'   => 'categorie_produit',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);
// « Produit Maison » + ses sous-categories ont leur propre page → exclus ici
$maison_ids = function_exists('lv_maison_term_ids') ? lv_maison_term_ids() : [];

// Ordre d'affichage demandé par Philippe (au lieu de l'ordre alphabétique)
$ordre_categories_epicerie = [
    'Produits en vrac', 'Produits locaux', 'Produits transformés',
    'Produits végétaliens', 'Produits sans gluten', 'Produits fins',
    'Légumes', 'Viandes', 'Fromages', 'Thés et infusion', 'Café',
    'Bières, vins et spiritueux', 'Sirop Monin', 'Confiseries', 'Grignotines',
];
if ($categories_produit && !is_wp_error($categories_produit)) {
    usort($categories_produit, function ($a, $b) use ($ordre_categories_epicerie) {
        $pos_a = array_search($a->name, $ordre_categories_epicerie, true);
        $pos_b = array_search($b->name, $ordre_categories_epicerie, true);
        if ($pos_a === false) $pos_a = 999;
        if ($pos_b === false) $pos_b = 999;
        return $pos_a <=> $pos_b;
    });
}
?>

<!-- ======================================================
     EN-TÊTE — intro de la page (titre + contenu éditeur WP)
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
            <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
            <?php the_content(); ?>
            <!-- Pastilles de valeurs (déplacées de l'accueil, demande de Philippe) -->
            <ul class="intro-liste intro-liste--centree">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21c0-7 4-12 9-14-1 8-4 13-9 14Z"/><path d="M12 21c0-6-3-10-7-12 1 7 3 11 7 12Z"/></svg> <?php echo esc_html($ep('ep_valeur1_texte', 'Producteurs locaux')); ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21V10"/><path d="M12 13c0-4-3-6-7-6 0 4 3 6 7 6Z"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7Z"/></svg> <?php echo esc_html($ep('ep_valeur2_texte', 'Bio & naturel')); ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 8h10l-1 11a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1Z"/><path d="M9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg> <?php echo esc_html($ep('ep_valeur3_texte', 'Vrac & zéro déchet')); ?></li>
            </ul>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     FILTRE PAR CATÉGORIE (bande sous l'en-tête)
====================================================== -->
<div class="pm-filtres-wrap">
    <div class="conteneur">
        <nav class="pm-filtres" aria-label="Filtrer par catégorie">
            <?php if ($categories_produit && !is_wp_error($categories_produit)):
                $premiere_cat = true;
                foreach ($categories_produit as $cat):
                    if (in_array((int) $cat->term_id, $maison_ids, true)) continue; ?>
                    <a href="#produits" class="pm-filtre filtre-lien<?php echo $premiere_cat ? ' actif' : ''; ?>" data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
            <?php $premiere_cat = false; endforeach; endif; ?>
        </nav>
    </div>
</div>

<!-- ======================================================
     NOS PRODUITS
====================================================== -->
<section class="section" id="produits">
    <div class="conteneur">

        <?php /* Pas de titre de section : l'en-tête de page et la bande de
                 filtres disent déjà où on est (demande de Philippe) */ ?>

        <!-- Grille produits (les produits maison sont présentés sur leur page dédiée) -->
        <?php
        $args_produits = [
            'post_type'      => 'produit',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        if ($maison_ids) {
            $args_produits['tax_query'] = [[
                'taxonomy' => 'categorie_produit',
                'field'    => 'term_id',
                'terms'    => $maison_ids,
                'operator' => 'NOT IN',
            ]];
        }
        $produits = new WP_Query($args_produits);
        ?>
        <div class="grille-cartes" id="grille-produits">
            <?php if ($produits->have_posts()):
                while ($produits->have_posts()): $produits->the_post();
                    get_template_part('parts/produit', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="grille-vide"><?php echo esc_html($ep('ep_vide_texte', 'Les produits arrivent bientôt, revenez nous voir !')); ?></p>
            <?php endif; ?>
        </div>

        <div class="section-cta">
            <a href="<?php echo esc_url(get_post_type_archive_link('produit')); ?>" class="btn btn-fantome"><?php echo esc_html($ep('ep_prod_btn', 'Voir tous nos produits')); ?></a>
        </div>

    </div>
</section>

<!-- ======================================================
     LES DÉPARTEMENTS — trio à icônes, après le contenu et
     avant la bande de clôture (demande de Philippe)
====================================================== -->
<section class="section section-compacte">
    <div class="conteneur">

        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 21c0-7 5-13 14-14-1 9-7 14-14 14Z"/><path d="M5 21c2-5 6-8 10-9"/></svg>
                </span>
                <h3><?php echo esc_html($ep('ep_dept1_titre', 'Produits frais')); ?></h3>
                <p><?php echo esc_html($ep('ep_dept1_texte', 'Fruits et légumes, pains spécialisés, fromages régionaux, viandes, poulet bio et œufs bio.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M7 8h10l-1 11a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2L7 8Z"/><path d="M8 8a4 4 0 0 1 8 0"/></svg>
                </span>
                <h3><?php echo esc_html($ep('ep_dept2_titre', 'Produits en vrac')); ?></h3>
                <p><?php echo esc_html($ep('ep_dept2_texte', 'Aliments secs, noix, légumineuses, farines, huiles, produits ménagers et corporels.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 10h14M6 10l1 9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-9"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
                </span>
                <h3><?php echo esc_html($ep('ep_dept3_titre', 'Produits transformés')); ?></h3>
                <p><?php echo esc_html($ep('ep_dept3_texte', 'Pâtisseries, mets cuisinés, tartinades, sauces, condiments et douceurs faits par des artisans de la région.')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     NOS PRODUCTEURS & TRANSFORMATEURS
====================================================== -->
<section class="section bande-blanche" id="producteurs">
    <div class="conteneur">

        <div class="producteurs-entete">
            <div>
                <span class="script-accent"><?php echo esc_html($ep('ep_part_surtitre', 'Nos partenaires')); ?></span>
                <h2><?php echo esc_html($ep('ep_part_titre', 'Nos Producteurs & Transformateurs')); ?></h2>
            </div>
            <p><?php echo esc_html($ep('ep_part_texte', 'Chaque produit est choisi avec soin pour soutenir les producteurs d\'ici et encourager une consommation consciente et respectueuse de l\'environnement. Ensemble, soutenons les producteurs et transformateurs de la région !')); ?></p>
        </div>

        <?php
        $types_producteur = get_terms([
            'taxonomy'   => 'type_producteur',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        /* Échantillon équilibré plutôt que l'annuaire complet (demande de
           Philippe) : les 3 familles les plus fournies, max 4 noms chacune,
           une rangée de cartes égales. Le bouton « Voir tous nos
           producteurs » donne accès au reste. */
        $groupes_prod = [];
        if ($types_producteur && !is_wp_error($types_producteur)) {
            foreach ($types_producteur as $type) {
                $producteurs_type = new WP_Query([
                    'post_type'      => 'producteur',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'tax_query'      => [[
                        'taxonomy' => 'type_producteur',
                        'field'    => 'term_id',
                        'terms'    => $type->term_id,
                    ]],
                ]);
                if (!$producteurs_type->have_posts()) continue;
                $groupes_prod[] = [
                    'type'  => $type,
                    'query' => $producteurs_type,
                    'total' => (int) $producteurs_type->post_count,
                ];
            }
            usort($groupes_prod, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            $groupes_prod = array_slice($groupes_prod, 0, 3);
        }

        if ($groupes_prod): ?>

        <div class="prod-groupes">
            <?php foreach ($groupes_prod as $groupe): $affiches = 0; ?>
                <div class="prod-groupe reveal">
                    <h3 class="prod-groupe-titre"><?php echo esc_html($groupe['type']->name); ?></h3>
                    <ul class="prod-liste">
                        <?php while ($groupe['query']->have_posts() && $affiches < 4): $groupe['query']->the_post(); $affiches++;
                            $region = get_field('producteur_region');
                        ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="prod-nom">
                                    <?php the_title(); ?>
                                </a>
                                <?php if ($region): ?>
                                    <span class="prod-region"><?php echo esc_html($region); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                    <?php if ($groupe['total'] > 4): $reste = (int) ($groupe['total'] - 4); ?>
                        <a class="prod-groupe-plus" href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>">
                            + <?php echo $reste; ?> <?php echo $reste > 1 ? 'autres' : 'autre'; ?> à découvrir
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>" class="btn btn-fantome"><?php echo esc_html($ep('ep_part_btn', 'Voir tous nos producteurs')); ?></a>
        </div>

        <?php else: ?>
            <p class="grille-vide">Les producteurs partenaires seront présentés ici bientôt.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer();

<?php
/*
Template Name: L'Épicerie
*/
get_header();
?>

<!-- ======================================================
     EN-TÊTE — intro de la page (titre + contenu éditeur WP)
====================================================== -->
<section class="page-entete">
    <span class="arche-mini am-terra"></span>
    <span class="arche-mini am-ocre"></span>
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
            <p class="eyebrow">Épicerie boutique · Le Vivier</p>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     LES DÉPARTEMENTS
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">

        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 21c0-7 5-13 14-14-1 9-7 14-14 14Z"/><path d="M5 21c2-5 6-8 10-9"/></svg>
                </span>
                <h3>Produits frais</h3>
                <p>Fruits et légumes, pains spécialisés, fromages régionaux, viandes, poulet bio et œufs bio.</p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M7 8h10l-1 11a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2L7 8Z"/><path d="M8 8a4 4 0 0 1 8 0"/></svg>
                </span>
                <h3>Produits en vrac</h3>
                <p>Aliments secs, noix, légumineuses, farines, huiles, produits ménagers et corporels.</p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 10h14M6 10l1 9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-9"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
                </span>
                <h3>Produits transformés</h3>
                <p>Pâtisseries, mets cuisinés, tartinades, sauces, condiments et douceurs faits par des artisans de la région.</p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     NOS PRODUITS
====================================================== -->
<section class="section produits" id="produits">
    <div class="conteneur">

        <div class="section-titre">
            <h2>Nos produits</h2>
        </div>

        <!-- Filtres dynamiques depuis la taxonomie categorie_produit -->
        <?php
        $categories_produit = get_terms([
            'taxonomy'   => 'categorie_produit',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtres" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($categories_produit && !is_wp_error($categories_produit)):
                foreach ($categories_produit as $cat): ?>
                    <a href="#" class="filtre filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
            <?php endforeach; endif; ?>
        </nav>

        <!-- Grille produits -->
        <?php
        $produits = new WP_Query([
            'post_type'      => 'produit',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="grille-cartes" id="grille-produits">
            <?php if ($produits->have_posts()):
                while ($produits->have_posts()): $produits->the_post();
                    get_template_part('parts/produit', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="grille-vide">Les produits arrivent bientôt — revenez nous voir !</p>
            <?php endif; ?>
        </div>

        <div class="section-cta">
            <a href="<?php echo esc_url(get_post_type_archive_link('produit')); ?>" class="btn btn-fantome">Voir tous nos produits</a>
        </div>

    </div>
</section>

<!-- ======================================================
     NOS PRODUCTEURS & TRANSFORMATEURS
====================================================== -->
<section class="section producteurs" id="producteurs">
    <div class="conteneur">

        <div class="producteurs-entete">
            <div>
                <span class="script-accent">Nos partenaires</span>
                <h2>Nos Producteurs &amp; Transformateurs</h2>
            </div>
            <p>Chaque produit est choisi avec soin pour soutenir les producteurs d'ici et encourager une consommation consciente et respectueuse de l'environnement. Ensemble, soutenons les producteurs et transformateurs de la région&nbsp;!</p>
        </div>

        <?php
        $types_producteur = get_terms([
            'taxonomy'   => 'type_producteur',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if ($types_producteur && !is_wp_error($types_producteur)): ?>

        <div class="prod-groupes">
            <?php foreach ($types_producteur as $type):

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
            ?>
                <div class="prod-groupe reveal">
                    <h3 class="prod-groupe-titre"><?php echo esc_html($type->name); ?></h3>
                    <ul class="prod-liste">
                        <?php while ($producteurs_type->have_posts()): $producteurs_type->the_post();
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
                </div>

            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>" class="btn btn-fantome">Voir tous nos producteurs</a>
        </div>

        <?php else: ?>
            <p class="grille-vide">Les producteurs partenaires seront présentés ici bientôt.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer();

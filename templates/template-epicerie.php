<?php
/*
Template Name: L'Épicerie
*/
get_header();
?>

<!-- ======================================================
     HÉROS — intro de la page (garder seulement l'intro dans l'éditeur WP)
====================================================== -->
<section class="section-hero-direct section-hero-direct--sauge">
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
        <p class="banniere-surtitre">Épicerie boutique · Le Vivier</p>
        <h1 class="hero-direct-titre"><?php the_title(); ?></h1>
        <div class="hero-direct-accroche"><?php the_content(); ?></div>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     LES DÉPARTEMENTS
====================================================== -->
<section class="section-departements">
    <div class="conteneur">
        <h2 class="titre-section-centre">Les Départements</h2>
        <p class="sous-titre-section">Une offre complète pour tous les modes de vie et besoins alimentaires</p>

        <div class="grille-departements">

            <div class="carte-departement">
                <span class="dept-icone">🥬</span>
                <h3>Produits frais</h3>
                <p>Fruits et légumes, pains spécialisés, fromages régionaux, viandes, poulet bio et œufs bio.</p>
            </div>

            <div class="carte-departement">
                <span class="dept-icone">🌾</span>
                <h3>Produits en vrac</h3>
                <p>Aliments secs, noix, légumineuses, farines, huiles, produits ménagers et corporels.</p>
            </div>

            <div class="carte-departement">
                <span class="dept-icone">🫙</span>
                <h3>Produits transformés</h3>
                <p>Pâtisseries, mets cuisinés, tartinades, sauces, condiments et douceurs faits par des artisans de la région.</p>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================
     NOS PRODUITS
====================================================== -->
<section class="section-produits-epicerie" id="produits">
    <div class="conteneur">

        <h2 class="titre-section">Nos produits</h2>

        <!-- Filtres dynamiques depuis la taxonomie categorie_produit -->
        <?php
        $categories_produit = get_terms([
            'taxonomy'   => 'categorie_produit',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtre-categories" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($categories_produit && !is_wp_error($categories_produit)):
                foreach ($categories_produit as $cat): ?>
                    <a href="#" class="filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
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
        <div class="grille-cartes-4" id="grille-produits">
            <?php if ($produits->have_posts()):
                while ($produits->have_posts()): $produits->the_post();
                    get_template_part('parts/produit', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="epicerie-vide">Les produits arrivent bientôt — revenez nous voir !</p>
            <?php endif; ?>
        </div>

        <div class="section-lien-centre">
            <a href="<?php echo esc_url(get_post_type_archive_link('produit')); ?>" class="bouton-secondaire">Voir tous nos produits</a>
        </div>

    </div>
</section>

<!-- ======================================================
     NOS PRODUCTEURS & TRANSFORMATEURS
====================================================== -->
<section class="section-producteurs-epicerie" id="producteurs">
    <div class="conteneur">

        <div class="producteurs-epicerie-entete">
            <h2 class="titre-section">Nos Producteurs &amp; Transformateurs</h2>
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

        <div class="producteurs-categories-grille">
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
                <div class="producteur-groupe">
                    <h3 class="producteur-groupe-titre"><?php echo esc_html($type->name); ?></h3>
                    <ul class="producteur-liste">
                        <?php while ($producteurs_type->have_posts()): $producteurs_type->the_post();
                            $region = get_field('producteur_region');
                        ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="producteur-nom">
                                    <?php the_title(); ?>
                                </a>
                                <?php if ($region): ?>
                                    <span class="producteur-region"><?php echo esc_html($region); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                </div>

            <?php endforeach; ?>
        </div>

        <div class="section-lien-centre">
            <a href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>" class="bouton-secondaire">Voir tous nos producteurs</a>
        </div>

        <?php else: ?>
            <p class="epicerie-vide">Les producteurs partenaires seront présentés ici bientôt.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer();

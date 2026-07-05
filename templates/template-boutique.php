<?php
/*
Template Name: La Boutique
*/
get_header();
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <span class="arche-mini am-terra"></span>
    <span class="arche-mini am-ocre"></span>
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
            <p class="eyebrow">Boutique · Le Vivier</p>
            <h1><?php the_title(); ?></h1>
            <?php if (get_the_content()): the_content(); else: ?>
                <p>Des objets beaux et utiles pour un quotidien plus durable : zéro déchet, naturels, et fièrement fabriqués au Québec.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     VALEURS — 3 atouts au trait
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">
        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 13-6l2 2M20 12a8 8 0 0 1-13 6l-2-2"/><path d="M19 4v4h-4M5 20v-4h4"/></svg>
                </span>
                <h3>Zéro déchet</h3>
                <p>Des alternatives durables pour réduire l'empreinte au quotidien.</p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 21c0-7 5-13 14-14-1 9-7 14-14 14Z"/><path d="M5 21c2-5 6-8 10-9"/></svg>
                </span>
                <h3>Naturel &amp; Éco</h3>
                <p>Cosmétiques, produits ménagers et corporels respectueux de l'environnement.</p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </span>
                <h3>Fait au Québec</h3>
                <p>Des artisans locaux et des produits fabriqués ici, pour consommer autrement.</p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     ARTICLES BOUTIQUE
====================================================== -->
<section class="section produits" id="articles">
    <div class="conteneur">

        <div class="section-titre">
            <h2>Nos articles</h2>
        </div>

        <!-- Filtres par catégorie boutique -->
        <?php
        $categories_boutique = get_terms([
            'taxonomy'   => 'categorie_boutique',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtres" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($categories_boutique && !is_wp_error($categories_boutique)):
                foreach ($categories_boutique as $cat): ?>
                    <a href="#" class="filtre filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
            <?php endforeach; endif; ?>
        </nav>

        <!-- Grille articles -->
        <?php
        $articles = new WP_Query([
            'post_type'      => 'article_boutique',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="grille-cartes" id="grille-boutique">
            <?php if ($articles->have_posts()):
                while ($articles->have_posts()): $articles->the_post();
                    get_template_part('parts/boutique', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="grille-vide">Les articles arrivent bientôt, revenez nous voir !</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php get_footer(); ?>

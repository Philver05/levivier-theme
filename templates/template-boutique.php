<?php
/*
Template Name: La Boutique
*/
get_header();
?>

<!-- ======================================================
     HÉROS
====================================================== -->
<section class="section-hero-direct section-hero-direct--sauge">
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
        <p class="banniere-surtitre">Boutique · Le Vivier</p>
        <h1 class="hero-direct-titre"><?php the_title(); ?></h1>
        <div class="hero-direct-accroche">
            <?php if (get_the_content()): the_content(); else: ?>
                <p>Des objets beaux et utiles pour un quotidien plus durable : zéro déchet, naturels, et fièrement fabriqués au Québec.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     VALEURS — 3 icônes (fond sauge-pale)
====================================================== -->
<section class="section-boutique-valeurs">
    <div class="conteneur">
        <div class="boutique-valeurs-grille">
            <div class="boutique-valeur-item">
                <span class="boutique-valeur-icone">♻️</span>
                <h3>Zéro déchet</h3>
                <p>Des alternatives durables pour réduire l'empreinte au quotidien.</p>
            </div>
            <div class="boutique-valeur-item">
                <span class="boutique-valeur-icone">🌿</span>
                <h3>Naturel & Éco</h3>
                <p>Cosmétiques, produits ménagers et corporels respectueux de l'environnement.</p>
            </div>
            <div class="boutique-valeur-item">
                <span class="boutique-valeur-icone">
                    <svg class="drapeau-qc" viewBox="0 0 90 60" width="44" height="29" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Drapeau du Québec">
                        <defs>
                            <g id="lv-qc-lis">
                                <path d="M7 .5C5 4 5.6 7 7 8.5 8.4 7 9 4 7 .5Z"/>
                                <path d="M6.6 9C3 8 1 10.5 2.6 12.7 3.6 14 6 13.2 6.7 11.3Z"/>
                                <path d="M7.4 9C11 8 13 10.5 11.4 12.7 10.4 14 8 13.2 7.3 11.3Z"/>
                                <path d="M2.8 11.2C5 10.2 9 10.2 11.2 11.2L11.2 13C9 12 5 12 2.8 13Z"/>
                                <path d="M5.7 12.6L8.3 12.6C8.3 15 9.1 17 10.2 18L3.8 18C4.9 17 5.7 15 5.7 12.6Z"/>
                            </g>
                        </defs>
                        <rect width="90" height="60" rx="5" fill="#003DA5"/>
                        <rect x="39" width="12" height="60" fill="#fff"/>
                        <rect y="24" width="90" height="12" fill="#fff"/>
                        <g fill="#fff">
                            <use href="#lv-qc-lis" x="12" y="2.5"/>
                            <use href="#lv-qc-lis" x="66" y="2.5"/>
                            <use href="#lv-qc-lis" x="12" y="38.5"/>
                            <use href="#lv-qc-lis" x="66" y="38.5"/>
                        </g>
                    </svg>
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
<section class="section-articles-boutique" id="articles">
    <div class="conteneur">

        <h2 class="titre-section">Nos articles</h2>

        <!-- Filtres par catégorie boutique -->
        <?php
        $categories_boutique = get_terms([
            'taxonomy'   => 'categorie_boutique',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtre-categories" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($categories_boutique && !is_wp_error($categories_boutique)):
                foreach ($categories_boutique as $cat): ?>
                    <a href="#" class="filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
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
        <div class="grille-cartes-4" id="grille-boutique">
            <?php if ($articles->have_posts()):
                while ($articles->have_posts()): $articles->the_post();
                    get_template_part('parts/boutique', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="epicerie-vide">Les articles arrivent bientôt — revenez nous voir !</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php get_footer(); ?>

<?php
/*
 * Archive des produits — page « Tous nos produits » (filtrable par catégorie)
 */
get_header();

$pr_surtitre = get_theme_mod('lv_produits_surtitre', 'Épicerie boutique · Le Vivier');
$pr_titre    = get_theme_mod('lv_produits_titre', 'Tous nos produits');
$pr_intro    = get_theme_mod('lv_produits_intro', lv_produits_intro_defaut());
?>

<section class="section-archive-producteurs">
    <div class="conteneur">

        <header class="archive-producteurs-entete">
            <?php if ($pr_surtitre): ?>
                <p class="banniere-surtitre"><?php echo esc_html($pr_surtitre); ?></p>
            <?php endif; ?>
            <?php if ($pr_titre): ?>
                <h1 class="archive-producteurs-titre"><?php echo esc_html($pr_titre); ?></h1>
            <?php endif; ?>
            <?php if ($pr_intro): ?>
                <div class="archive-producteurs-intro"><?php echo wpautop(esc_html($pr_intro)); ?></div>
            <?php endif; ?>
        </header>

        <?php
        $categories = get_terms([
            'taxonomy'   => 'categorie_produit',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtre-categories" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($categories && !is_wp_error($categories)):
                foreach ($categories as $cat): ?>
                    <a href="#" class="filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
            <?php endforeach; endif; ?>
        </nav>

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
                endwhile; wp_reset_postdata();
            else: ?>
                <p class="epicerie-vide">Les produits arrivent bientôt — revenez nous voir&nbsp;!</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php get_footer();

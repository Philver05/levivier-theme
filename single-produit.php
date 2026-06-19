<?php
/*
 * Template pour un produit individuel
 */
get_header();

if (!have_posts()) {
    get_footer();
    exit;
}

the_post();

/* Champs ACF */
$prix  = get_field('produit_prix');
$badge = get_field('produit_badge');

/* Catégorie */
$terms_cat      = get_the_terms(get_the_ID(), 'categorie_produit');
$categorie_nom  = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slug = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->slug : '';

$url_epicerie = get_permalink(get_page_by_path('epicerie'));
$illu = get_stylesheet_directory_uri() . '/assets/images/illustrations/';
?>

<section class="section section-detail">
    <div class="conteneur">

        <p class="fil"><a href="<?php echo esc_url($url_epicerie); ?>#produits">← Retour aux produits</a></p>

        <div class="single-grille" style="margin-top:1.4rem">

            <div class="single-media<?php echo has_post_thumbnail() ? ' single-media--photo' : ''; ?> reveal">
                <?php if (has_post_thumbnail()):
                    the_post_thumbnail('large', ['alt' => get_the_title()]);
                else: ?>
                    <img src="<?php echo esc_url($illu); ?>fleur%2005.svg" alt="">
                <?php endif; ?>
            </div>

            <div class="single-info reveal">
                <?php if ($categorie_nom): ?>
                    <span class="cat"><?php echo esc_html($categorie_nom); ?></span>
                <?php endif; ?>

                <h1><?php the_title(); ?></h1>

                <?php if ($badge): ?>
                    <span class="single-badge"><?php echo esc_html($badge); ?></span>
                <?php endif; ?>

                <?php if ($prix): ?>
                    <p class="single-prix"><?php echo esc_html($prix); ?></p>
                <?php endif; ?>

                <?php if (get_the_content()): ?>
                    <div class="desc"><?php the_content(); ?></div>
                <?php endif; ?>

                <div class="hero-actions" style="margin-top:1.6rem">
                    <a href="<?php echo esc_url($url_epicerie); ?>#produits" class="btn btn-fantome">← Tous les produits</a>
                    <?php if ($categorie_slug): ?>
                    <a href="<?php echo esc_url($url_epicerie); ?>?cat=<?php echo esc_attr($categorie_slug); ?>#produits" class="btn btn-primaire">Voir <?php echo esc_html($categorie_nom); ?></a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php get_footer(); ?>

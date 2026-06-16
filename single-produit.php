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
$terms_cat     = get_the_terms(get_the_ID(), 'categorie_produit');
$categorie_nom  = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slug = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->slug : '';

/* Classe badge */
$classes_badge = 'carte-badge';
if ($badge === 'Bio')    $classes_badge .= ' bio';
if ($badge === 'Frais')  $classes_badge .= ' frais';
if ($badge === 'Maison') $classes_badge .= ' maison';
?>

<!-- ======================================================
     HÉROS PRODUIT
====================================================== -->
<section class="section-hero section-hero-produit">
    <div class="hero-interieur">

        <!-- Image -->
        <div class="hero-image-cadre">
            <div class="hero-image produit-single-image">
                <?php if (has_post_thumbnail()):
                    the_post_thumbnail('large', ['alt' => get_the_title()]);
                else: ?>
                    <div class="produit-placeholder-img"></div>
                <?php endif; ?>

                <?php if ($badge): ?>
                    <span class="<?php echo esc_attr($classes_badge); ?> badge-single"><?php echo esc_html($badge); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Infos -->
        <div class="hero-texte">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('epicerie'))); ?>#produits"
               class="retour-lien">← Retour aux produits</a>

            <?php if ($categorie_nom): ?>
                <p class="banniere-surtitre"><?php echo esc_html($categorie_nom); ?></p>
            <?php endif; ?>

            <h1><?php the_title(); ?></h1>

            <?php if ($prix): ?>
                <p class="produit-single-prix"><?php echo esc_html($prix); ?></p>
            <?php endif; ?>

            <?php if (get_the_content()): ?>
            <div class="produit-single-desc corps-article">
                <?php the_content(); ?>
            </div>
            <?php endif; ?>

            <div class="hero-actions">
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('epicerie'))); ?>#produits"
                   class="bouton-secondaire">← Tous les produits</a>
                <?php if ($categorie_slug): ?>
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('epicerie'))); ?>?cat=<?php echo esc_attr($categorie_slug); ?>#produits"
                   class="bouton-primaire">Voir <?php echo esc_html($categorie_nom); ?></a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php get_footer(); ?>

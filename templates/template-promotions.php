<?php
/*
Template Name: Promotions
*/
get_header();

if (have_posts()) the_post();

$intro = get_field('promo_page_intro') ?: "Chaque semaine, profitez de rabais exclusifs sur une sélection de produits locaux et de saison. Les offres changent régulièrement, revenez souvent&nbsp;!";
?>

<!-- ======================================================
     HÉROS
====================================================== -->
<section class="section-hero-direct section-hero-direct--rose">
    <div class="conteneur">
        <p class="banniere-surtitre">Offres de la semaine</p>
        <h1 class="hero-direct-titre"><?php the_title(); ?></h1>
        <div class="hero-direct-accroche"><?php echo wpautop(esc_html($intro)); ?></div>
    </div>
</section>

<!-- ======================================================
     PROMOS ACTIVES
====================================================== -->
<section class="section-promotions">
    <div class="conteneur">

        <h2 class="titre-section-centre">En promotion cette semaine</h2>
        <p class="sous-titre-section">Des rabais qui changent au fil des arrivages</p>

        <?php $promos = lv_get_promotions_actives(); ?>

        <?php if ($promos->have_posts()): ?>
            <div class="grille-cartes-4 grille-promos">
                <?php while ($promos->have_posts()): $promos->the_post();
                    get_template_part('parts/promotion', 'card');
                endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else: ?>
            <div class="promos-vide">
                <span class="promos-vide-icone">🌱</span>
                <h3>Aucune promotion en cours pour le moment</h3>
                <p>Nos prochaines offres arrivent bientôt. Revenez nous voir ou suivez-nous sur Facebook pour ne rien manquer&nbsp;!</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>

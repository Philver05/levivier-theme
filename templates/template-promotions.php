<?php
/*
Template Name: Promotions
*/
get_header();

if (have_posts()) the_post();

$intro = get_field('promo_page_intro') ?: "Chaque semaine, profitez de rabais exclusifs sur une sélection de produits locaux et de saison. Les offres changent régulièrement, revenez souvent&nbsp;!";
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow">Offres de la semaine</p>
        <h1><?php the_title(); ?></h1>
        <p><?php echo wp_kses_post($intro); ?></p>
    </div>
</section>

<!-- ======================================================
     PROMOS ACTIVES
====================================================== -->
<section class="section produits">
    <div class="conteneur">

        <div class="section-titre">
            <h2>En promotion cette semaine</h2>
            <p>Des rabais qui changent au fil des arrivages</p>
        </div>

        <?php $promos = lv_get_promotions_actives(); ?>

        <?php if ($promos->have_posts()): ?>
            <div class="promo-grille">
                <?php while ($promos->have_posts()): $promos->the_post();
                    get_template_part('parts/promotion', 'card');
                endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else: ?>
            <div class="etat-vide">
                <span class="etat-vide-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 21c0-7 5-13 14-14-1 9-7 14-14 14Z"/><path d="M5 21c2-5 6-8 10-9"/></svg>
                </span>
                <h3>Aucune promotion en cours pour le moment</h3>
                <p>Nos prochaines offres arrivent bientôt. Revenez nous voir ou suivez-nous sur Facebook pour ne rien manquer&nbsp;!</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>

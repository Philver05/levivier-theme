<?php /* Template Name: Politique de confidentialité */
get_header(); ?>

<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow">Informations légales</p>
        <h1><?php the_title(); ?></h1>
    </div>
</section>

<section class="section pol-section">
    <div class="conteneur">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="pol-prose reveal"><?php the_content(); ?></div>
        <?php endwhile; endif; ?>
    </div>
</section>

<?php get_footer();

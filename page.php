<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>

    <section class="page-entete">
        <span class="arche-mini am-terra"></span>
        <span class="arche-mini am-ocre"></span>
        <div class="conteneur">
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <?php if (trim(get_the_content()) !== ''): ?>
    <section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
        <div class="conteneur">
            <div class="page-prose reveal"><?php the_content(); ?></div>
        </div>
    </section>
    <?php endif; ?>

<?php endwhile; endif; ?>

<?php get_footer();

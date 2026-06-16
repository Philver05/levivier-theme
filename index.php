<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="page-contenu">
        <h1 class="titre-page"><?php the_title(); ?></h1>
        <div class="corps-article"><?php the_content(); ?></div>
    </article>
<?php endwhile; endif; ?>

<?php get_footer();

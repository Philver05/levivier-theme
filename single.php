<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>

    <article class="contenu-single">

        <a class="bouton-secondaire" href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>">Retour aux nouvelles</a>

        <h1 class="titre-page"><?php the_title(); ?></h1>

        <p class="single-meta">
            Publié le <strong><?php echo esc_html(get_the_date()); ?></strong>
        </p>

        <?php if (has_post_thumbnail()): ?>
            <div class="image-principale">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>

        <div class="corps-article">
            <?php the_content(); ?>
        </div>

    </article>

<?php endwhile; endif; ?>

<?php get_footer();

<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>

    <section class="page-entete">
        <div class="conteneur">
            <p class="eyebrow">Le journal · Le Vivier</p>
            <h1><?php the_title(); ?></h1>
            <p>Publié le <?php echo esc_html(get_the_date()); ?></p>
        </div>
    </section>

    <section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
        <div class="conteneur">
            <div class="page-prose reveal">
                <?php if (has_post_thumbnail()): ?>
                    <div class="cadre" style="border-radius:var(--rayon-l);overflow:hidden;box-shadow:var(--ombre);margin-bottom:1.8rem">
                        <?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?>
                    </div>
                <?php endif; ?>

                <?php the_content(); ?>

                <p style="margin-top:2rem">
                    <a class="btn btn-fantome" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/')); ?>">Retour aux nouvelles</a>
                </p>
            </div>
        </div>
    </section>

<?php endwhile; endif; ?>

<?php get_footer();

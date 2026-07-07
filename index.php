<?php get_header(); ?>

<section class="page-entete">
    <div class="conteneur">
        <?php if (is_home() && !is_front_page()): ?>
            <p class="eyebrow">Le journal · Le Vivier</p>
            <h1><?php echo esc_html(get_the_title(get_option('page_for_posts'))) ?: 'Nouvelles'; ?></h1>
        <?php else: ?>
            <h1><?php single_post_title(); ?></h1>
        <?php endif; ?>
    </div>
</section>

<section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
    <div class="conteneur">
        <?php if (have_posts()): ?>
            <ul class="recherche-liste">
                <?php while (have_posts()): the_post(); ?>
                    <li class="recherche-item reveal">
                        <a class="recherche-titre" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
                        <?php if (has_excerpt() || get_the_content()): ?>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="grille-vide">Aucun contenu pour le moment — revenez bientôt&nbsp;!</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();

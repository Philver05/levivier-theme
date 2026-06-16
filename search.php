<?php get_header(); ?>

<div class="resultats-recherche">
    <h2>
        <?php if (have_posts()): ?>
            Résultats pour : <em><?php echo esc_html(get_search_query()); ?></em>
        <?php else: ?>
            Aucun résultat pour : <em><?php echo esc_html(get_search_query()); ?></em>
        <?php endif; ?>
    </h2>

    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="resultat-item">
            <a class="resultat-titre" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
            <p><?php the_excerpt(); ?></p>
        </div>
    <?php endwhile; else: ?>
        <p class="aucun-resultat">Essayez d'autres termes ou consultez <a href="<?php echo esc_url(get_post_type_archive_link('produit')); ?>">l'épicerie</a>.</p>
    <?php endif; ?>
</div>

<?php get_footer();

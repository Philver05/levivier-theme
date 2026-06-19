<?php get_header(); ?>

<section class="page-entete">
    <span class="arche-mini am-terra"></span>
    <span class="arche-mini am-ocre"></span>
    <div class="conteneur">
        <p class="eyebrow">Recherche</p>
        <h1>
            <?php if (have_posts()): ?>
                Résultats pour «&nbsp;<?php echo esc_html(get_search_query()); ?>&nbsp;»
            <?php else: ?>
                Aucun résultat pour «&nbsp;<?php echo esc_html(get_search_query()); ?>&nbsp;»
            <?php endif; ?>
        </h1>
    </div>
</section>

<section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
    <div class="conteneur">
        <?php if (have_posts()): ?>
            <ul class="recherche-liste">
                <?php while (have_posts()): the_post(); ?>
                    <li class="recherche-item reveal">
                        <a class="recherche-titre" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
                        <p><?php the_excerpt(); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="grille-vide">Essayez d'autres termes ou consultez <a href="<?php echo esc_url(get_post_type_archive_link('produit')); ?>" style="color:var(--terra-fonce);font-weight:600">l'épicerie</a>.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();

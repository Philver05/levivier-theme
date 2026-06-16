<?php
/*
 * Archive des producteurs — page « Nos producteurs » (filtrable par type)
 */
get_header();

$prod_surtitre = get_theme_mod('lv_prod_surtitre', 'Nos partenaires · Le Vivier');
$prod_titre    = get_theme_mod('lv_prod_titre', 'Nos Producteurs & Transformateurs');
$prod_intro    = get_theme_mod('lv_prod_intro', lv_prod_intro_defaut());
?>

<section class="section-archive-producteurs">
    <div class="conteneur">

        <header class="archive-producteurs-entete">
            <?php if ($prod_surtitre): ?>
                <p class="banniere-surtitre"><?php echo esc_html($prod_surtitre); ?></p>
            <?php endif; ?>
            <?php if ($prod_titre): ?>
                <h1 class="archive-producteurs-titre"><?php echo esc_html($prod_titre); ?></h1>
            <?php endif; ?>
            <?php if ($prod_intro): ?>
                <div class="archive-producteurs-intro"><?php echo wpautop(esc_html($prod_intro)); ?></div>
            <?php endif; ?>
        </header>

        <?php
        $types = get_terms([
            'taxonomy'   => 'type_producteur',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtre-categories" aria-label="Filtrer par type">
            <a href="#" class="filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php if ($types && !is_wp_error($types)):
                foreach ($types as $type): ?>
                    <a href="#" class="filtre-lien" data-cat="<?php echo esc_attr($type->slug); ?>">
                        <?php echo esc_html($type->name); ?>
                    </a>
            <?php endforeach; endif; ?>
        </nav>

        <?php
        $producteurs = new WP_Query([
            'post_type'      => 'producteur',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="grille-cartes" id="grille-producteurs">
            <?php if ($producteurs->have_posts()):
                while ($producteurs->have_posts()): $producteurs->the_post();
                    get_template_part('parts/producteur', 'card');
                endwhile; wp_reset_postdata();
            else: ?>
                <p class="epicerie-vide">Nos producteurs seront présentés ici très bientôt.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php get_footer();

<?php
/*
 * Archive des producteurs — page « Nos producteurs » (filtrable par type)
 */
get_header();

$prod_surtitre = get_theme_mod('lv_prod_surtitre', 'Nos partenaires · Le Vivier');
$prod_titre    = get_theme_mod('lv_prod_titre', 'Nos Producteurs & Transformateurs');
$prod_intro    = get_theme_mod('lv_prod_intro', lv_prod_intro_defaut());
?>

<section class="page-entete">
    <div class="conteneur">
        <?php if ($prod_surtitre): ?><p class="eyebrow"><?php echo esc_html($prod_surtitre); ?></p><?php endif; ?>
        <?php if ($prod_titre): ?><h1><?php echo esc_html($prod_titre); ?></h1><?php endif; ?>
        <?php if ($prod_intro): ?><p><?php echo esc_html($prod_intro); ?></p><?php endif; ?>
    </div>
</section>

<section class="section producteurs" style="padding-top:clamp(2rem,1rem+3vw,3.5rem)">
    <div class="conteneur">

        <?php
        $types = get_terms([
            'taxonomy'   => 'type_producteur',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <nav class="filtres" aria-label="Filtrer par type">
            <?php if ($types && !is_wp_error($types)):
                $premiere_cat = true;
                foreach ($types as $type): ?>
                    <a href="#" class="filtre filtre-lien<?php echo $premiere_cat ? ' actif' : ''; ?>" data-cat="<?php echo esc_attr($type->slug); ?>">
                        <?php echo esc_html($type->name); ?>
                    </a>
            <?php $premiere_cat = false; endforeach; endif; ?>
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
        <div class="grille-prod" id="grille-producteurs">
            <?php if ($producteurs->have_posts()):
                while ($producteurs->have_posts()): $producteurs->the_post();
                    get_template_part('parts/producteur', 'card');
                endwhile; wp_reset_postdata();
            else: ?>
                <p class="grille-vide">Nos producteurs seront présentés ici très bientôt.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php get_footer();

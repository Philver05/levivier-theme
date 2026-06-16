<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header">

        <div class="header-barre">

            <div class="logo-zone">
                <?php
                if (has_custom_logo()):
                    the_custom_logo();
                else: ?>
                    <!-- <a class="nom-site" href="<?php echo esc_url(home_url('/')); ?>">
                        <?php bloginfo('name'); ?>
                    </a> -->
                <?php endif; ?>
            </div>

            <nav class="menu-principal" id="nav-menu" aria-label="Menu principal">
                <div>
                    <?php wp_nav_menu(['menu' => 'principal', 'container' => false]); ?>
                </div>
            </nav>

            <button class="hamburger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-menu">
                <span class="hamburger-barre"></span>
                <span class="hamburger-barre"></span>
                <span class="hamburger-barre"></span>
            </button>

        </div>

    </header>

    <?php
    /* Bande promo dynamique : affiche les promos actives, sinon message par défaut */
    $bandeau_promos = function_exists('lv_get_promotions_actives') ? lv_get_promotions_actives(6) : null;
    if ($bandeau_promos && $bandeau_promos->have_posts()):
        $items = [];
        while ($bandeau_promos->have_posts()): $bandeau_promos->the_post();
            $pp = get_field('promo_prix_promo');
            $items[] = get_the_title() . ($pp ? ' — ' . $pp : '');
        endwhile;
        wp_reset_postdata();
    ?>
    <div class="bandeau-promo">
        <p>🌿 Promotions de la semaine — <?php echo esc_html(implode('  ·  ', $items)); ?> 🌿</p>
    </div>
    <?php endif; ?>

    <main>
        <div class="conteneur">

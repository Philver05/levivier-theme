<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="entete">
        <div class="conteneur entete-barre">

            <a class="logo" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>

            <nav class="nav-principale" id="nav-menu" aria-label="Menu principal">
                <?php
                wp_nav_menu([
                    'menu'        => 'principal',
                    'container'   => false,
                    'fallback_cb' => false,
                ]);
                $page_commande = get_page_by_path('commandez');
                $url_commande  = $page_commande ? get_permalink($page_commande) : home_url('/');
                ?>
                <a href="<?php echo esc_url($url_commande); ?>" class="btn btn-primaire">Commander</a>
            </nav>

            <button class="burger" aria-label="Ouvrir le menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

        </div>
    </header>

    <main>

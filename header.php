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

            <?php if (has_custom_logo()): ?>
                <div class="logo logo-img"><?php the_custom_logo(); ?></div>
            <?php else: ?>
                <a class="logo" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
            <?php endif; ?>

            <div class="entete-droite">
                <nav class="nav-principale" id="nav-menu" aria-label="Menu principal">
                    <button type="button" class="nav-fermer" aria-label="Fermer le menu">
                        <span></span><span></span>
                    </button>
                    <?php
                    wp_nav_menu([
                        'menu'        => 'principal',
                        'container'   => false,
                        'fallback_cb' => false,
                    ]);
                    $page_commande = get_page_by_path('commandez');
                    $url_commande  = $page_commande ? get_permalink($page_commande) : home_url('/');
                    ?>
                    <a href="<?php echo esc_url($url_commande); ?>" class="btn btn-primaire nav-cta">Commander</a>
                </nav>

                <button type="button" class="recherche-toggle" aria-label="Rechercher" aria-expanded="false" aria-controls="recherche-panneau">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </button>

                <button class="burger" aria-label="Ouvrir le menu" aria-controls="nav-menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>

        </div>

        <div class="recherche-panneau" id="recherche-panneau" aria-label="Recherche">
            <button type="button" class="recherche-fermer" aria-label="Fermer la recherche">
                <span></span><span></span>
            </button>
            <div class="conteneur recherche-conteneur">
                <?php get_search_form(); ?>
                <ul class="recherche-suggestions" role="listbox" hidden></ul>
            </div>
        </div>

        <div class="nav-overlay"></div>
    </header>

    <main>

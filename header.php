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

                    /* Contactez-nous : le menu « principal » est géré dans
                       Apparence > Menus; tant que la page n'y est pas remise,
                       on affiche le lien ici. Garde anti-doublon : s'il est
                       un jour ré-ajouté au menu WP, ce repli disparaît seul. */
                    $page_contact = get_page_by_path('contactez-nous');
                    if ($page_contact && $page_contact->post_status === 'publish') {
                        $contact_au_menu = false;
                        $items_menu = wp_get_nav_menu_items('principal');
                        if ($items_menu) {
                            foreach ($items_menu as $item_menu) {
                                if ((int) $item_menu->object_id === (int) $page_contact->ID) {
                                    $contact_au_menu = true;
                                    break;
                                }
                            }
                        }
                        if (!$contact_au_menu) {
                            $actif_contact = is_page($page_contact->ID) ? ' actif' : '';
                            echo '<a href="' . esc_url(get_permalink($page_contact)) . '" class="nav-contact' . $actif_contact . '">'
                                . esc_html(get_the_title($page_contact)) . '</a>';
                        }
                    }

                    $page_commande = get_page_by_path('commandez');
                    $url_commande  = $page_commande ? get_permalink($page_commande) : home_url('/');
                    ?>
                    <a href="<?php echo esc_url($url_commande); ?>" class="btn btn-primaire nav-cta"><?php echo esc_html(lv_opt('opt_nav_cta_texte', 'Commandez')); ?></a>
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

<?php
/*
Template Name: Les Lofts de la Rivière
*/
get_header();

if (have_posts()) the_post();

$surtitre = get_field('lofts_surtitre') ?: 'Location touristique · Centre-ville de Matane';
$intro    = get_field('lofts_intro')    ?: "Deux lofts modernes avec cuisinette, pensés pour qu'il ne vous manque rien. Vous arrivez, vous déposez vos valises, et Matane est déjà à vos pieds.";
?>

<div class="page-lofts">

    <!-- ======================================================
         EN-TÊTE COMPACT — pas de bannière, on va droit au but
    ====================================================== -->
    <section class="lofts-entete">
        <div class="conteneur">
            <p class="lofts-eyebrow lofts-eyebrow-centre"><?php echo esc_html($surtitre); ?></p>
            <h1 class="lofts-entete-titre"><?php the_title(); ?></h1>
            <p class="lofts-entete-lead"><?php echo esc_html($intro); ?></p>
            <p class="lofts-entete-meta">
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    14, av. D'Amours, Matane
                </span>
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    CITQ&nbsp;323422
                </span>
                <a href="tel:+14185625230">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L17 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg>
                    418&nbsp;562-5230
                </a>
            </p>
        </div>
    </section>

    <!-- ======================================================
         LES LOFTS — l'action, tout de suite
    ====================================================== -->
    <section class="lofts-liste-section" id="nos-lofts">
        <div class="conteneur">

            <?php
            $lofts = new WP_Query([
                'post_type'      => 'loft',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            ]);
            ?>

            <?php if ($lofts->have_posts()): ?>
                <div class="lofts-grille">
                    <?php while ($lofts->have_posts()): $lofts->the_post();
                        get_template_part('parts/loft', 'card');
                    endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p class="lofts-vide">Nos deux lofts (Rivière Douce et Rivière Vive) seront présentés ici très bientôt. Réservez dès maintenant au <a href="tel:+14185625230">418 562-5230</a>.</p>
            <?php endif; ?>

        </div>
    </section>

    <!-- ======================================================
         POURQUOI SÉJOURNER ICI
    ====================================================== -->
    <section class="lofts-reassurance">
        <div class="conteneur">
            <h2 class="lofts-section-titre lofts-section-titre-clair">Pourquoi vous allez adorer</h2>
            <div class="lofts-reassurance-grille">
                <div class="lofts-atout reveal">
                    <span class="lofts-atout-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V8l5-3v16M14 21V11l5 2v8"/><path d="M8 9h0M8 12h0M8 15h0"/></svg>
                    </span>
                    <h3>Tout à distance de marche</h3>
                    <p>Restos, cafés, commerces et bord de mer à quelques minutes. Stationnez gratuitement une fois, puis oubliez la voiture.</p>
                </div>
                <div class="lofts-atout reveal reveal-delai-1">
                    <span class="lofts-atout-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="4" y="8" width="16" height="11" rx="2"/><path d="M9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M9 11v5M15 11v5"/></svg>
                    </span>
                    <h3>Rien à apporter</h3>
                    <p>Cuisinette complète, Wi-Fi rapide, téléviseur 65 pouces et literie soignée. Vous n'avez qu'à vous installer.</p>
                </div>
                <div class="lofts-atout reveal reveal-delai-2">
                    <span class="lofts-atout-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 8h14l-1.4 11.1a1 1 0 0 1-1 .9H7.4a1 1 0 0 1-1-.9L5 8Z"/><path d="M9 8 12 3l3 5"/></svg>
                    </span>
                    <h3>Une épicerie sous vos pieds</h3>
                    <p>Le Vivier vous attend au rez-de-chaussée : produits frais, cafés et prêt-à-manger des artisans d'ici. Le déjeuner commence dans l'escalier.</p>
                </div>
                <div class="lofts-atout reveal reveal-delai-3">
                    <span class="lofts-atout-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <h3>Réservez l'esprit tranquille</h3>
                    <p>Hébergement enregistré (CITQ&nbsp;323422). Échanges directs, conditions claires, aucune surprise à l'arrivée.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================
         CTA FINALE
    ====================================================== -->
    <section class="lofts-cta-finale">
        <div class="conteneur">
            <div class="lofts-cta-interieur reveal">
                <h2>Vos dates partent vite</h2>
                <p>Deux lofts seulement, un calendrier qui se remplit. Réservez les vôtres pendant qu'ils sont libres.</p>
                <a href="#nos-lofts" class="lofts-cta">Voir les lofts et réserver</a>
                <p class="lofts-contact-ligne">
                    14, avenue D'Amours, Matane (QC) &nbsp;·&nbsp;
                    <a href="tel:+14185625230">418&nbsp;562-5230</a> &nbsp;·&nbsp; CITQ&nbsp;323422
                </p>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>

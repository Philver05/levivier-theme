<?php
/*
Template Name: Les Lofts de la Rivière
*/
get_header();

if (have_posts()) the_post();

$surtitre = get_field('lofts_surtitre') ?: 'Location touristique · Centre-ville de Matane';
$intro    = get_field('lofts_intro')    ?: "Deux lofts modernes avec cuisinette, pensés pour qu'il ne vous manque rien. Vous arrivez, vous déposez vos valises, et Matane est déjà à vos pieds.";
$tagline  = get_field('lofts_tagline')  ?: "Le confort d'un chez-soi, l'énergie de la ville";

/* Commodités (affiche) */
$amenites = [
    ['🛏️', '1 chambre, lit queen'],
    ['🍳', 'Cuisinette équipée'],
    ['🚿', 'Salle de bain privée'],
    ['📶', 'Wi-Fi gratuit'],
    ['🅿️', 'Stationnement gratuit'],
    ['📺', 'Téléviseur intelligent 65"'],
    ['✨', 'Tout inclus'],
    ['📍', 'Centre-ville de Matane'],
];
?>

<div class="page-lofts">

    <!-- ======================================================
         HÉROS DE MARQUE
    ====================================================== -->
    <section class="lofts-hero lofts-hero-marque">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('full', ['class' => 'lofts-hero-img', 'alt' => get_the_title()]); ?>
        <?php endif; ?>

        <div class="lofts-hero-overlay"></div>

        <div class="lofts-hero-marque-texte">
            <p class="lofts-eyebrow lofts-eyebrow-clair"><?php echo esc_html($surtitre); ?></p>
            <h1 class="lofts-hero-h1"><?php the_title(); ?></h1>
            <?php if ($tagline): ?><p class="lofts-hero-tagline"><?php echo esc_html($tagline); ?></p><?php endif; ?>
            <div class="lofts-hero-actions">
                <a href="#nos-lofts" class="lofts-cta">Réserver votre séjour</a>
            </div>
        </div>
    </section>

    <!-- ======================================================
         COMMODITÉS
    ====================================================== -->
    <section class="lofts-amenites">
        <div class="conteneur">
            <ul class="lofts-amenites-grille">
                <?php foreach ($amenites as $a): ?>
                    <li>
                        <span class="lofts-amenite-icone" aria-hidden="true"><?php echo $a[0]; ?></span>
                        <span><?php echo esc_html($a[1]); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ======================================================
         L'EXPÉRIENCE
    ====================================================== -->
    <section class="lofts-intro-section">
        <div class="conteneur">
            <p class="lofts-eyebrow lofts-eyebrow-centre">L'expérience</p>
            <div class="lofts-rule lofts-rule-centre"></div>
            <div class="lofts-intro-texte">
                <?php if (get_the_content()): the_content(); else: echo wpautop(esc_html($intro)); endif; ?>
            </div>
        </div>
    </section>

    <!-- ======================================================
         LES LOFTS
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
                <h2 class="lofts-section-titre">Choisissez votre loft</h2>
                <p class="lofts-section-sous-titre">Deux ambiances, le même souci du détail. Trouvez celle qui vous ressemble.</p>
                <div class="lofts-grille">
                    <?php while ($lofts->have_posts()): $lofts->the_post();
                        get_template_part('parts/loft', 'card');
                    endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <h2 class="lofts-section-titre">Choisissez votre loft</h2>
                <p class="lofts-vide">Nos deux lofts (Rivière Douce et Rivière Vive) seront présentés ici très bientôt. Contactez-nous au <a href="tel:+14185625230">418 562-5230</a> pour réserver dès maintenant.</p>
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
                <div class="lofts-atout">
                    <span class="lofts-atout-icone" aria-hidden="true">🏙️</span>
                    <h3>Tout à distance de marche</h3>
                    <p>Restos, cafés, commerces et bord de mer à quelques minutes. Stationnez gratuitement une fois, puis oubliez la voiture.</p>
                </div>
                <div class="lofts-atout">
                    <span class="lofts-atout-icone" aria-hidden="true">🧳</span>
                    <h3>Rien à apporter</h3>
                    <p>Cuisinette complète, Wi-Fi rapide, téléviseur 65 pouces et literie soignée. Vous n'avez qu'à vous installer.</p>
                </div>
                <div class="lofts-atout">
                    <span class="lofts-atout-icone" aria-hidden="true">🛒</span>
                    <h3>Une épicerie sous vos pieds</h3>
                    <p>Le Vivier vous attend au rez-de-chaussée : produits frais, cafés et prêt-à-manger des artisans d'ici. Le déjeuner commence dans l'escalier.</p>
                </div>
                <div class="lofts-atout">
                    <span class="lofts-atout-icone" aria-hidden="true">✔️</span>
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
            <div class="lofts-cta-interieur">
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

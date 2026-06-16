<?php
/*
 * Template pour un producteur individuel
 */
get_header();

if (!have_posts()) {
    get_footer();
    exit;
}

the_post();

/* Champs ACF */
$logo      = get_field('producteur_logo');
$region    = get_field('producteur_region');
$slogan    = get_field('producteur_slogan');
$telephone = get_field('producteur_telephone');
$email     = get_field('producteur_email');
$adresse   = get_field('producteur_adresse');
$site_web  = get_field('producteur_site_web');
$facebook  = get_field('producteur_facebook');
$instagram = get_field('producteur_instagram');

/* Taxonomie */
$terms_type = get_the_terms(get_the_ID(), 'type_producteur');
$type       = ($terms_type && !is_wp_error($terms_type)) ? $terms_type[0]->name : '';
?>

<!-- ======================================================
     HÉROS PRODUCTEUR
====================================================== -->
<section class="section-hero section-hero-producteur">
    <div class="hero-interieur">

        <div class="hero-texte">
            <a href="<?php echo esc_url(get_post_type_archive_link('producteur') ?: get_permalink(get_page_by_path('epicerie'))); ?>"
               class="retour-lien">← Tous les producteurs</a>

            <?php if ($type): ?>
                <p class="banniere-surtitre"><?php echo esc_html($type); ?></p>
            <?php endif; ?>

            <h1><?php the_title(); ?></h1>

            <?php if ($slogan): ?>
                <p class="producteur-single-slogan"><?php echo esc_html($slogan); ?></p>
            <?php endif; ?>

            <?php if ($region): ?>
                <p class="producteur-single-region">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                        <circle cx="12" cy="9" r="2.5"/>
                    </svg>
                    <?php echo esc_html($region); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($logo || has_post_thumbnail()): ?>
        <div class="hero-image-cadre">
            <?php if ($logo): ?>
                <!-- Logo ACF prioritaire -->
                <div class="hero-image producteur-logo-cadre">
                    <img src="<?php echo esc_url($logo['url']); ?>"
                         alt="<?php echo esc_attr($logo['alt'] ?: get_the_title()); ?>"
                         width="<?php echo esc_attr($logo['width']); ?>"
                         height="<?php echo esc_attr($logo['height']); ?>">
                </div>
            <?php else: ?>
                <!-- Image mise en avant en fallback -->
                <div class="hero-image">
                    <?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ======================================================
     CORPS — DESCRIPTION + COORDONNÉES
====================================================== -->
<section class="section-producteur-corps">
    <div class="conteneur">
        <div class="producteur-single-grille">

            <!-- Colonne gauche : description WP -->
            <?php if (get_the_content()): ?>
            <div class="producteur-single-description">
                <h2 class="producteur-single-h2">À propos</h2>
                <div class="corps-article">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Colonne droite : coordonnées -->
            <aside class="producteur-single-coordonnees">
                <h2 class="producteur-single-h2">Coordonnées</h2>

                <ul class="coordonnees-liste">

                    <?php if ($adresse): ?>
                    <li class="coordonnee-item">
                        <span class="coordonnee-icone" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                <circle cx="12" cy="9" r="2.5"/>
                            </svg>
                        </span>
                        <span class="coordonnee-texte"><?php echo esc_html($adresse); ?></span>
                    </li>
                    <?php endif; ?>

                    <?php if ($telephone): ?>
                    <li class="coordonnee-item">
                        <span class="coordonnee-icone" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.07 2.18 2 2 0 012.06 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                        </span>
                        <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>"
                           class="coordonnee-lien"><?php echo esc_html($telephone); ?></a>
                    </li>
                    <?php endif; ?>

                    <?php if ($email): ?>
                    <li class="coordonnee-item">
                        <span class="coordonnee-icone" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <a href="mailto:<?php echo esc_attr($email); ?>"
                           class="coordonnee-lien"><?php echo esc_html($email); ?></a>
                    </li>
                    <?php endif; ?>

                    <?php if ($site_web): ?>
                    <li class="coordonnee-item">
                        <span class="coordonnee-icone" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                            </svg>
                        </span>
                        <a href="<?php echo esc_url($site_web); ?>" target="_blank" rel="noopener"
                           class="coordonnee-lien"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($site_web, '/'))); ?></a>
                    </li>
                    <?php endif; ?>

                </ul>

                <!-- Réseaux sociaux -->
                <?php if ($facebook || $instagram): ?>
                <div class="producteur-reseaux">
                    <p class="reseaux-titre">Suivre sur les réseaux</p>
                    <div class="reseaux-liens">
                        <?php if ($facebook): ?>
                        <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener"
                           class="reseau-btn reseau-facebook" aria-label="Page Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                            Facebook
                        </a>
                        <?php endif; ?>
                        <?php if ($instagram): ?>
                        <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener"
                           class="reseau-btn reseau-instagram" aria-label="Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                            Instagram
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </aside>

        </div>
    </div>
</section>

<!-- ======================================================
     CTA — RETOUR
====================================================== -->
<section class="section-retour-epicerie">
    <div class="conteneur">
        <a href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>"
           class="bouton-secondaire">← Tous nos producteurs</a>
    </div>
</section>

<?php get_footer(); ?>

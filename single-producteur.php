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
$produits  = get_field('producteur_produits');

/* Liste « Disponibles au Vivier » : une ligne = un produit */
$produits_liste = [];
if ($produits) {
    foreach (preg_split('/\r\n|\r|\n/', $produits) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne !== '') {
            $produits_liste[] = $ligne;
        }
    }
}

/* Taxonomie */
$terms_type = get_the_terms(get_the_ID(), 'type_producteur');
$type       = ($terms_type && !is_wp_error($terms_type)) ? $terms_type[0]->name : '';

$archive_producteur = get_post_type_archive_link('producteur') ?: get_permalink(get_page_by_path('epicerie'));
?>

<!-- ======================================================
     EN-TÊTE PRODUCTEUR
====================================================== -->
<section class="section section-detail">
    <div class="conteneur">

        <p class="fil"><a href="<?php echo esc_url($archive_producteur); ?>">Tous les producteurs</a></p>

        <div class="prod-hero<?php echo ($logo || has_post_thumbnail()) ? '' : ' prod-hero--solo'; ?>">
            <span class="prod-hero-deco" aria-hidden="true"></span>

            <?php if ($logo): ?>
            <div class="prod-logo reveal">
                <img src="<?php echo esc_url($logo['sizes']['large'] ?? $logo['url']); ?>" alt="<?php echo esc_attr($logo['alt'] ?: get_the_title()); ?>">
            </div>
            <?php elseif (has_post_thumbnail()): ?>
            <div class="prod-photo reveal">
                <?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?>
            </div>
            <?php endif; ?>

            <div class="prod-hero-info reveal">
                <?php if ($type): ?>
                    <span class="lieu"><?php echo esc_html($type); ?></span>
                <?php endif; ?>

                <h1><?php the_title(); ?></h1>

                <?php if ($slogan): ?>
                    <p class="script-accent" style="font-family:var(--f-script);color:var(--terra);font-size:1.6rem;line-height:1.2"><?php echo esc_html($slogan); ?></p>
                <?php endif; ?>

                <?php if ($region): ?>
                    <p class="prod-region-ligne">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        <?php echo esc_html($region); ?>
                    </p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================
     CORPS — DESCRIPTION + COORDONNÉES
====================================================== -->
<section class="section producteurs">
    <div class="conteneur">
        <div class="contact-grille">

            <div class="page-prose reveal" style="margin:0">
                <?php if (get_the_content()): ?>
                    <h2>À propos</h2>
                    <?php the_content(); ?>
                <?php endif; ?>

                <?php if ($produits_liste): ?>
                <div class="prod-dispo">
                    <h2>Disponibles au Vivier</h2>
                    <ul class="prod-dispo-liste">
                        <?php foreach ($produits_liste as $item): ?>
                            <li><?php echo esc_html($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <aside class="reveal">
                <h2 style="font-size:var(--t-h3);color:var(--sauge);margin-bottom:1.2rem">Coordonnées</h2>
                <ul class="contact-info">
                    <?php if ($adresse): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
                        <div><h4>Adresse</h4><p><?php echo esc_html($adresse); ?></p></div>
                    </li>
                    <?php endif; ?>
                    <?php if ($telephone): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L17 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg></span>
                        <div><h4>Téléphone</h4><p><a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>"><?php echo esc_html($telephone); ?></a></p></div>
                    </li>
                    <?php endif; ?>
                    <?php if ($email): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                        <div><h4>Courriel</h4><p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p></div>
                    </li>
                    <?php endif; ?>
                    <?php if ($site_web): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg></span>
                        <div><h4>Site web</h4><p><a href="<?php echo esc_url($site_web); ?>" target="_blank" rel="noopener"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($site_web, '/'))); ?></a></p></div>
                    </li>
                    <?php endif; ?>
                </ul>

                <?php if ($facebook || $instagram): ?>
                <div class="prod-reseaux">
                    <?php if ($facebook): ?>
                        <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener" class="btn btn-fantome">Facebook</a>
                    <?php endif; ?>
                    <?php if ($instagram): ?>
                        <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener" class="btn btn-fantome">Instagram</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </aside>

        </div>
    </div>
</section>

<?php get_footer(); ?>

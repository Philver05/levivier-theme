<?php
/*
 * Template pour un loft individuel
 */
get_header();

if (!have_posts()) { get_footer(); exit; }
the_post();

$badge      = get_field('loft_badge')      ?: 'Disponible';
$prix       = get_field('loft_prix');
$prix_label = get_field('loft_prix_label') ?: 'À partir de';
$prix_sub   = get_field('loft_prix_sub')   ?: 'Tout inclus';
$features   = get_field('loft_features')   ?: "1 chambre, lit queen\nCuisinette équipée\nSalle de bain privée\nWi-Fi gratuit\nStationnement gratuit\nTéléviseur intelligent 65\"\nTout inclus";

$galerie = array_filter([
    get_field('loft_img1'), get_field('loft_img2'), get_field('loft_img3'),
    get_field('loft_img4'), get_field('loft_img5'), get_field('loft_img6'),
]);

$booking   = get_field('loft_booking_url');
$cta_label = get_field('loft_cta_label') ?: 'Réserver ce loft';
$adresse   = get_field('loft_adresse')   ?: '14, avenue D\'Amours';
$ville     = get_field('loft_ville')     ?: 'Matane, Québec · Centre-ville';
$telephone = get_field('loft_telephone') ?: '418 562-5230';
$facebook  = get_field('loft_facebook')  ?: 'https://facebook.com/lesloftsdelariviere';
$carte     = get_field('loft_carte');

$lien_resa = $booking ?: 'tel:' . preg_replace('/\D/', '', $telephone);
$tags = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $features))));
?>

<div class="page-lofts">

    <!-- HÉROS -->
    <section class="lofts-hero">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('full', ['class' => 'lofts-hero-img', 'alt' => get_the_title()]); ?>
        <?php endif; ?>
        <div class="lofts-hero-overlay"></div>

        <a href="<?php echo esc_url(get_permalink(get_page_by_path('location-loft'))); ?>" class="lofts-retour">← Tous les lofts</a>

        <?php if ($badge): ?><span class="lofts-badge"><?php echo esc_html($badge); ?></span><?php endif; ?>

        <div class="lofts-hero-prix">
            <?php if ($prix_label): ?><div class="lofts-prix-label"><?php echo esc_html($prix_label); ?></div><?php endif; ?>
            <div class="lofts-prix-montant"><?php echo esc_html($prix); ?><span> / nuit</span></div>
            <?php if ($prix_sub): ?><div class="lofts-prix-sub"><?php echo esc_html($prix_sub); ?></div><?php endif; ?>
        </div>
    </section>

    <!-- INTRO + CARACTÉRISTIQUES -->
    <section class="lofts-intro-section">
        <div class="conteneur">
            <p class="lofts-eyebrow">Les Lofts de la Rivière</p>
            <h1 class="lofts-titre"><?php the_title(); ?></h1>
            <div class="lofts-rule"></div>

            <div class="lofts-intro-texte">
                <?php if (get_the_content()): the_content(); endif; ?>
            </div>

            <?php if ($tags): ?>
            <div class="lofts-features">
                <?php foreach ($tags as $i => $tag): ?>
                    <span class="lofts-tag<?php echo $i === 0 ? ' filled' : ''; ?>"><?php echo esc_html($tag); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="lofts-resa-zone">
                <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $booking ? ' target="_blank" rel="noopener"' : ''; ?> class="lofts-cta"><?php echo esc_html($cta_label); ?></a>
            </div>
        </div>
    </section>

    <!-- GALERIE -->
    <?php if ($galerie): ?>
    <section class="lofts-galerie-section">
        <div class="conteneur">
            <h2 class="lofts-section-titre">L'intérieur en images</h2>
            <div class="lofts-galerie">
                <?php foreach ($galerie as $img): ?>
                    <a href="<?php echo esc_url($img['url']); ?>" class="lofts-galerie-item" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
                             alt="<?php echo esc_attr($img['alt'] ?: get_the_title()); ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- LOCALISATION + CONTACT -->
    <section class="lofts-localisation">
        <div class="conteneur">
            <div class="lofts-loc-grille">

                <div class="lofts-loc-infos">
                    <p class="lofts-loc-label">Localisation</p>
                    <p class="lofts-loc-adresse"><?php echo esc_html($adresse); ?></p>
                    <p class="lofts-loc-ville"><?php echo esc_html($ville); ?></p>

                    <div class="lofts-loc-contact">
                        <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>" class="lofts-phone"><?php echo esc_html($telephone); ?></a>
                        <?php if ($facebook): ?><a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener" class="lofts-fb-lien">Facebook</a><?php endif; ?>
                    </div>

                    <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $booking ? ' target="_blank" rel="noopener"' : ''; ?> class="lofts-cta"><?php echo esc_html($cta_label); ?></a>
                </div>

                <?php if ($carte): ?>
                <div class="lofts-loc-carte">
                    <img src="<?php echo esc_url($carte['sizes']['large'] ?? $carte['url']); ?>" alt="Carte — <?php echo esc_attr($adresse); ?>">
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>

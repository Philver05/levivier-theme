<?php
$badge      = get_field('loft_badge');
$prix       = get_field('loft_prix');
$reservit   = get_field('loft_reservit_url');
$airbnb     = get_field('loft_airbnb_url') ?: get_field('loft_booking_url');
$telephone  = get_field('loft_telephone') ?: '418 562-5230';
$cta_label  = get_field('loft_cta_label') ?: 'Réserver';

$voyageurs = get_field('loft_voyageurs') ?: '2';
$chambres  = get_field('loft_chambres')  ?: '1';
$lits      = get_field('loft_lits')      ?: '1';

/* Description courte : une phrase coupée avec … */
$desc = trim(wp_strip_all_tags(get_the_excerpt()));
if ($desc !== '') {
    $desc = wp_trim_words($desc, 20, '…');
}

/* Lien réservation : Reservit, sinon Airbnb, sinon téléphone */
$booking   = $reservit ?: $airbnb;
$lien_resa = $booking ?: 'tel:' . preg_replace('/\D/', '', $telephone);
?>

<article class="loft-carte reveal">
    <a href="<?php echo esc_url(get_permalink()); ?>" class="loft-carte-image">
        <?php if (has_post_thumbnail()):
            the_post_thumbnail('large', ['alt' => get_the_title()]);
        else: ?>
            <div class="loft-carte-placeholder">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11 12 4l8 7"/><path d="M6 10v9h12v-9"/><path d="M10 19v-5h4v5"/></svg>
            </div>
        <?php endif; ?>
        <?php if ($badge): ?>
            <span class="loft-carte-badge"><?php echo esc_html($badge); ?></span>
        <?php endif; ?>
    </a>

    <div class="loft-carte-corps">
        <h3 class="loft-carte-titre">
            <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
        </h3>

        <p class="loft-carte-meta"><?php echo esc_html($voyageurs); ?>&nbsp;voyageurs · <?php echo esc_html($chambres); ?>&nbsp;chambre · <?php echo esc_html($lits); ?>&nbsp;lit</p>

        <?php if ($desc): ?>
            <p class="loft-carte-desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>

        <div class="loft-carte-bas">
            <?php if ($prix): ?>
            <div class="loft-carte-prix">
                <span class="loft-carte-prix-montant"><?php echo esc_html($prix); ?></span>
                <span class="loft-carte-prix-nuit">/ nuit</span>
            </div>
            <?php endif; ?>

            <div class="loft-carte-actions">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="loft-btn-secondaire">Voir le loft</a>
                <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $booking ? ' target="_blank" rel="noopener"' : ''; ?> class="loft-btn-primaire"><?php echo esc_html($cta_label); ?></a>
            </div>
        </div>
    </div>
</article>

<?php
$badge      = get_field('loft_badge');
$prix       = get_field('loft_prix');
$prix_label = get_field('loft_prix_label') ?: 'À partir de';
$features   = get_field('loft_features');
$booking    = get_field('loft_booking_url');
$telephone  = get_field('loft_telephone') ?: '418 562-5230';
$cta_label  = get_field('loft_cta_label') ?: 'Réserver';

/* 3 premières caractéristiques pour l'aperçu */
$tags = array_filter(array_map('trim', explode("\n", str_replace("\r", '', (string) $features))));
$tags = array_slice($tags, 0, 4);

/* Lien réservation : URL si fournie, sinon téléphone */
$lien_resa = $booking ?: 'tel:' . preg_replace('/\D/', '', $telephone);
?>

<article class="loft-carte">
    <a href="<?php echo esc_url(get_permalink()); ?>" class="loft-carte-image">
        <?php if (has_post_thumbnail()):
            the_post_thumbnail('large', ['alt' => get_the_title()]);
        else: ?>
            <div class="loft-carte-placeholder">🏠</div>
        <?php endif; ?>
        <?php if ($badge): ?>
            <span class="loft-carte-badge"><?php echo esc_html($badge); ?></span>
        <?php endif; ?>
    </a>

    <div class="loft-carte-corps">
        <h3 class="loft-carte-titre">
            <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ($prix): ?>
        <div class="loft-carte-prix">
            <span class="loft-carte-prix-label"><?php echo esc_html($prix_label); ?></span>
            <span class="loft-carte-prix-montant"><?php echo esc_html($prix); ?><span> / nuit</span></span>
        </div>
        <?php endif; ?>

        <?php if ($tags): ?>
        <div class="loft-carte-tags">
            <?php foreach ($tags as $tag): ?>
                <span class="loft-tag"><?php echo esc_html($tag); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="loft-carte-actions">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="loft-btn-secondaire">Voir le loft</a>
            <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $booking ? ' target="_blank" rel="noopener"' : ''; ?> class="loft-btn-primaire"><?php echo esc_html($cta_label); ?></a>
        </div>
    </div>
</article>

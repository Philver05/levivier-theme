<?php
/* Champs ACF */
$image     = get_field('promo_image');
$prix_reg  = get_field('promo_prix_regulier');
$prix_pro  = get_field('promo_prix_promo');
$rabais    = get_field('promo_rabais');
$date_fin  = get_field('promo_date_fin'); // format Ymd

/* Rabais auto si non renseigné */
if (!$rabais && $prix_reg && $prix_pro) {
    $rabais = lv_calc_rabais($prix_reg, $prix_pro);
}

/* Date de fin lisible */
$fin_lisible = '';
if ($date_fin) {
    $dt = DateTime::createFromFormat('Ymd', $date_fin);
    if ($dt) {
        $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $fin_lisible = $dt->format('j') . ' ' . $mois[(int) $dt->format('n')];
    }
}

$illu = get_stylesheet_directory_uri() . '/assets/images/illustrations/';
?>

<article class="promo-carte reveal">
    <?php if ($rabais): ?>
        <span class="promo-rabais"><?php echo esc_html($rabais); ?></span>
    <?php endif; ?>

    <?php if ($image): ?>
        <div class="promo-img promo-img--photo">
            <img src="<?php echo esc_url($image['sizes']['medium'] ?? $image['url']); ?>"
                 alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
        </div>
    <?php elseif (has_post_thumbnail()): ?>
        <div class="promo-img promo-img--photo">
            <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
        </div>
    <?php else: ?>
        <div class="promo-img"><img src="<?php echo esc_url($illu); ?>petits%20fruits%2001.svg" alt=""></div>
    <?php endif; ?>

    <div class="promo-corps">
        <h3><?php the_title(); ?></h3>

        <?php if (get_the_content()): ?>
            <p class="promo-desc"><?php echo esc_html(wp_trim_words(get_the_content(), 18)); ?></p>
        <?php endif; ?>

        <div class="promo-prix">
            <?php if ($prix_reg): ?>
                <span class="ancien"><?php echo esc_html($prix_reg); ?></span>
            <?php endif; ?>
            <?php if ($prix_pro): ?>
                <span class="neuf"><?php echo esc_html($prix_pro); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($fin_lisible): ?>
            <p class="promo-validite">Valable jusqu'au <?php echo esc_html($fin_lisible); ?></p>
        <?php endif; ?>
    </div>
</article>

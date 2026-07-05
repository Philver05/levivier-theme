<?php
/* Type depuis la taxonomie + champs ACF */
$terms_type = get_the_terms(get_the_ID(), 'type_producteur');
$type       = ($terms_type && !is_wp_error($terms_type)) ? $terms_type[0]->name : '';
$type_slugs = ($terms_type && !is_wp_error($terms_type)) ? implode(' ', wp_list_pluck($terms_type, 'slug')) : '';
$region = get_field('producteur_region');
$logo   = get_field('producteur_logo');
?>

<a class="carte-prod reveal" href="<?php echo esc_url(get_permalink()); ?>" data-cat="<?php echo esc_attr($type_slugs); ?>">
    <div class="photo<?php echo $logo ? ' photo-logo' : ''; ?>">
        <?php if ($logo): ?>
            <img src="<?php echo esc_url($logo['sizes']['medium_large'] ?? $logo['url']); ?>"
                 alt="<?php echo esc_attr($logo['alt'] ?: get_the_title()); ?>">
        <?php elseif (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('medium_large', ['alt' => get_the_title()]); ?>
        <?php else: ?>
            <div class="carte-vide" style="height:100%">
                <span style="font-family:var(--f-script);font-size:2.6rem;color:var(--sauge)"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <div class="corps">
        <?php if ($region): ?>
            <span class="lieu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <?php echo esc_html($region); ?>
            </span>
        <?php endif; ?>
        <h3><?php the_title(); ?></h3>
        <?php
        $extrait = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 16, '…');
        if ($extrait): ?>
            <p class="carte-prod-desc"><?php echo esc_html($extrait); ?></p>
        <?php endif; ?>
        <span class="carte-prod-cta">Voir la fiche</span>
    </div>
</a>

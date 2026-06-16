<?php
/* Type depuis la taxonomie */
$terms_type = get_the_terms(get_the_ID(), 'type_producteur');
$type       = ($terms_type && !is_wp_error($terms_type)) ? $terms_type[0]->name : '';
$type_slugs = ($terms_type && !is_wp_error($terms_type)) ? implode(' ', wp_list_pluck($terms_type, 'slug')) : '';

/* Champs ACF */
$region = get_field('producteur_region');
$logo   = get_field('producteur_logo');
?>

<article class="carte-producteur" data-cat="<?php echo esc_attr($type_slugs); ?>">
    <div class="carte-image">
        <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
            <?php if ($logo): ?>
                <img src="<?php echo esc_url($logo['sizes']['medium_large'] ?? $logo['url']); ?>"
                     alt="<?php echo esc_attr($logo['alt'] ?: get_the_title()); ?>">
            <?php elseif (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('medium_large', ['alt' => get_the_title()]); ?>
            <?php else: ?>
                <div class="carte-image-placeholder">
                    <span><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                </div>
            <?php endif; ?>
        </a>
    </div>
    <div class="carte-contenu">
        <?php if ($type): ?>
            <span class="carte-etiquette"><?php echo esc_html($type); ?></span>
        <?php endif; ?>
        <h3>
            <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
        </h3>
        <?php if ($region): ?>
            <p class="carte-region"><?php echo esc_html($region); ?></p>
        <?php endif; ?>
    </div>
</article>

<?php
/* Catégorie depuis la taxonomie + champs ACF */
$terms_cat       = get_the_terms(get_the_ID(), 'categorie_produit');
$categorie_nom   = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slugs = ($terms_cat && !is_wp_error($terms_cat)) ? implode(' ', wp_list_pluck($terms_cat, 'slug')) : '';
$prix  = get_field('produit_prix');
$badge = get_field('produit_badge');
$etiquette = $categorie_nom ?: $badge;
?>

<a class="carte reveal" href="<?php echo esc_url(get_permalink()); ?>" data-cat="<?php echo esc_attr($categorie_slugs); ?>">
    <div class="carte-img">
        <?php if ($etiquette): ?>
            <span class="carte-cat"><?php echo esc_html($etiquette); ?></span>
        <?php endif; ?>
        <?php if (has_post_thumbnail()):
            the_post_thumbnail('medium', ['alt' => get_the_title()]);
        else: ?>
            <div class="carte-vide" aria-hidden="true"></div>
        <?php endif; ?>
    </div>
    <div class="carte-corps">
        <h3><?php the_title(); ?></h3>
        <?php if ($prix): ?>
            <span class="carte-prix"><?php echo esc_html($prix); ?></span>
        <?php endif; ?>
    </div>
</a>

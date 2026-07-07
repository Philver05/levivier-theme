<?php
/* Catégorie depuis la taxonomie */
$terms_cat      = get_the_terms(get_the_ID(), 'categorie_boutique');
$categorie_nom  = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slug = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->slug : '';

/* Champs ACF */
$prix   = get_field('boutique_prix');
$marque = get_field('boutique_marque');
$badge  = get_field('boutique_badge');

$etiquette = $badge ?: $categorie_nom;
?>

<a class="carte reveal" href="<?php echo esc_url(get_permalink()); ?>" data-cat="<?php echo esc_attr($categorie_slug); ?>">
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
        <?php if ($marque): ?>
            <p class="carte-marque"><?php echo esc_html($marque); ?></p>
        <?php endif; ?>
        <?php if ($prix): ?>
            <span class="carte-prix"><?php echo esc_html($prix); ?></span>
        <?php endif; ?>
    </div>
</a>

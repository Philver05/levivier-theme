<?php
/* Catégorie depuis la taxonomie */
$terms_cat       = get_the_terms(get_the_ID(), 'categorie_produit');
$categorie_nom   = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slugs = ($terms_cat && !is_wp_error($terms_cat)) ? implode(' ', wp_list_pluck($terms_cat, 'slug')) : '';

/* Champs ACF */
$prix  = get_field('produit_prix');
$badge = get_field('produit_badge');

$classes_badge = 'carte-badge';
if ($badge === 'Bio')    $classes_badge .= ' bio';
if ($badge === 'Frais')  $classes_badge .= ' frais';
if ($badge === 'Maison') $classes_badge .= ' maison';
?>

<article class="carte-produit" data-cat="<?php echo esc_attr($categorie_slugs); ?>">
    <div class="carte-image">
        <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
            <?php if (has_post_thumbnail()):
                the_post_thumbnail('medium', ['alt' => get_the_title()]);
            else: ?>
                <span class="carte-image-vide" aria-hidden="true">🌿</span>
            <?php endif; ?>
        </a>
        <?php if ($badge): ?>
            <span class="<?php echo esc_attr($classes_badge); ?>"><?php echo esc_html($badge); ?></span>
        <?php endif; ?>
    </div>
    <div class="carte-contenu">
        <?php if ($categorie_nom): ?>
            <span class="carte-categorie"><?php echo esc_html($categorie_nom); ?></span>
        <?php endif; ?>
        <h3>
            <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
        </h3>
        <?php if ($prix): ?>
            <p class="carte-prix"><?php echo esc_html($prix); ?></p>
        <?php endif; ?>
    </div>
</article>

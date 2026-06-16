<?php
/* Catégorie depuis la taxonomie */
$terms_cat      = get_the_terms(get_the_ID(), 'categorie_boutique');
$categorie_nom  = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->name : '';
$categorie_slug = ($terms_cat && !is_wp_error($terms_cat)) ? $terms_cat[0]->slug : '';

/* Champs ACF */
$prix   = get_field('boutique_prix');
$marque = get_field('boutique_marque');
$badge  = get_field('boutique_badge');

$classes_badge = 'carte-badge';
if ($badge === 'Éco' || $badge === 'Biologique') $classes_badge .= ' bio';
if ($badge === 'Zéro déchet')                    $classes_badge .= ' frais';
if ($badge === 'Fait au Québec')                 $classes_badge .= ' maison';
?>

<article class="carte-produit carte-boutique" data-cat="<?php echo esc_attr($categorie_slug); ?>">
    <div class="carte-image">
        <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
            <?php if (has_post_thumbnail()):
                the_post_thumbnail('medium', ['alt' => get_the_title()]);
            else: ?>
                <div class="carte-image-placeholder">
                    <span><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                </div>
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
        <?php if ($marque): ?>
            <p class="carte-marque"><?php echo esc_html($marque); ?></p>
        <?php endif; ?>
        <?php if ($prix): ?>
            <p class="carte-prix"><?php echo esc_html($prix); ?></p>
        <?php endif; ?>
    </div>
</article>

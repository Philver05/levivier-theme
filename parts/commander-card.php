<?php
/*
 * Carte d'un bon de commande (type d'article « bon_commande »)
 * Utilisée dans la boucle de la page Commander.
 */
$image       = get_field('bon_image');
$description = get_field('bon_description');
$liste       = get_field('bon_liste');
$url         = get_field('bon_url');
$cta         = get_field('bon_cta')         ?: 'Remplir ce bon de commande';

if (!function_exists('lv_lignes')) {
    function lv_lignes($texte) {
        return array_filter(array_map('trim', explode("\n", str_replace("\r", '', (string) $texte))));
    }
}
?>

<div class="commander-carte<?php echo $url ? '' : ' commander-carte--bientot'; ?>">
    <div class="commander-carte-image<?php echo $image ? '' : ' commander-carte-image-vide'; ?>">
        <?php if ($image): ?>
            <img src="<?php echo esc_url($image['sizes']['medium_large'] ?? $image['url']); ?>"
                 alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
        <?php else: ?>
            <span aria-hidden="true">🛒</span>
        <?php endif; ?>
        <?php if (!$url): ?>
            <span class="commander-carte-badge-bientot">Bientôt</span>
        <?php endif; ?>
    </div>
    <div class="commander-carte-corps">
        <h3><?php the_title(); ?></h3>
        <?php if ($description): ?>
            <p><?php echo esc_html($description); ?></p>
        <?php endif; ?>

        <?php $items = lv_lignes($liste); if ($items): ?>
        <ul class="commander-produits-liste">
            <?php foreach ($items as $item): ?>
                <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($url): ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="bouton-primaire commander-carte-cta"><?php echo esc_html($cta); ?> →</a>
        <?php else: ?>
            <span class="commander-carte-cta commander-carte-cta--bientot">Bientôt disponible</span>
        <?php endif; ?>
    </div>
</div>

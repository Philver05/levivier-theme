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

<div class="cmd-carte reveal<?php echo $url ? '' : ' cmd-carte--bientot'; ?>">
    <div class="cmd-carte-haut">
        <?php if ($image): ?>
            <img src="<?php echo esc_url($image['sizes']['medium_large'] ?? $image['url']); ?>"
                 alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
        <?php else: ?>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h16l-1.5 9a2 2 0 0 1-2 1.6H8.5a2 2 0 0 1-2-1.6L4 4H2"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
        <?php endif; ?>
        <h3 class="cmd-carte-titre-photo"><?php the_title(); ?></h3>
        <?php if (!$url): ?>
            <span class="cmd-badge-bientot">Bientôt</span>
        <?php endif; ?>
    </div>
    <div class="cmd-carte-corps">
        <?php if ($description): ?>
            <p><?php echo esc_html($description); ?></p>
        <?php endif; ?>

        <?php $items = lv_lignes($liste); if ($items): ?>
        <ul class="cmd-liste">
            <?php foreach ($items as $item): ?>
                <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($url): ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="btn btn-primaire"><?php echo esc_html($cta); ?></a>
        <?php else: ?>
            <span class="btn btn-fantome cmd-cta-bientot">Bientôt disponible</span>
        <?php endif; ?>
    </div>
</div>

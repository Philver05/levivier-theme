<?php
/* Champs ACF : auteur, texte */
$auteur = get_field('temoignage_auteur');
$texte  = get_field('temoignage_texte');
?>

<article class="carte-temoignage">
    <p class="temoignage-texte">
        <?php echo esc_html($texte ?: get_the_excerpt()); ?>
    </p>
    <?php if ($auteur): ?>
        <p class="temoignage-auteur">&mdash; <?php echo esc_html($auteur); ?></p>
    <?php endif; ?>
</article>

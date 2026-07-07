<?php
/*
Template Name: Épicerie Africaine
*/
get_header();

if (have_posts()) the_post();

/* ----------------------------------------------------------
   Champs ACF + valeurs par défaut — tout éditable dans l'admin
---------------------------------------------------------- */
$surtitre = get_field('afr_surtitre') ?: 'Saveurs d\'Afrique · Le Vivier';
$intro    = get_field('afr_intro')    ?: "Épices, sauces, féculents et trésors culinaires venus du continent.\nUne invitation au voyage, au cœur de Matane.";

/* Présentation : $pres_texte sert à détecter un contenu réellement visible
   (get_the_content() seul renvoie souvent une chaîne "vide" du point de vue
   visuel mais non-vide en PHP, ex: <p></p> laissé par l'éditeur WP, ce qui
   affichait un bloc invisible mais quand même paddé). */
$pres_image = get_field('afr_presentation_image');
$pres_texte = trim(wp_strip_all_tags(get_the_content()));
$pres_presente = ($pres_texte !== '' || $pres_image);

/* Spécialités (3 cartes) */
$specialites = [
    [
        'titre' => get_field('afr_spec1_titre') ?: 'Épices & condiments',
        'texte' => get_field('afr_spec1_texte') ?: 'Mélanges parfumés, piments, gingembre, curcuma et condiments pour relever tous vos plats.',
        'image' => get_field('afr_spec1_image'),
    ],
    [
        'titre' => get_field('afr_spec2_titre') ?: 'Sauces & pâtes',
        'texte' => get_field('afr_spec2_texte') ?: 'Sauces tomate épicées, pâtes d\'arachide, huile de palme et bases pour vos recettes traditionnelles.',
        'image' => get_field('afr_spec2_image'),
    ],
    [
        'titre' => get_field('afr_spec3_titre') ?: 'Féculents & farines',
        'texte' => get_field('afr_spec3_texte') ?: 'Farines de manioc, igname, plantain, riz parfumé et féculents au cœur de la cuisine africaine.',
        'image' => get_field('afr_spec3_image'),
    ],
];

?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1><?php the_title(); ?></h1>
        <p><?php echo nl2br(esc_html($intro)); ?></p>
    </div>
</section>

<!-- ======================================================
     PRÉSENTATION (texte = éditeur WP) + image
====================================================== -->
<?php if ($pres_presente): ?>
<section class="section" style="padding-top:clamp(1rem,.75rem+1vw,1.5rem)">
    <div class="conteneur">
        <?php if ($pres_texte !== '' && $pres_image): ?>
        <div class="apropos-split">
            <div class="page-prose reveal" style="margin:0"><?php the_content(); ?></div>
            <div class="apropos-media reveal">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Épicerie Africaine'); ?>"></div>
            </div>
        </div>
        <?php elseif ($pres_texte !== ''): ?>
            <div class="page-prose reveal"><?php the_content(); ?></div>
        <?php elseif ($pres_image): ?>
            <div class="apropos-media reveal" style="max-width:640px;margin-inline:auto">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Épicerie Africaine'); ?>"></div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     SPÉCIALITÉS — 3 cartes
====================================================== -->
<section class="section produits"<?php echo $pres_presente ? '' : ' style="padding-top:clamp(1rem,.75rem+1vw,1.5rem)"'; ?>>
    <div class="conteneur">
        <div class="section-titre">
            <h2>Nos spécialités</h2>
            <p>Un aperçu des trésors culinaires à découvrir en boutique</p>
        </div>

        <div class="grille-prod">
            <?php $i = 0; foreach ($specialites as $spec): $delai = $i ? ' reveal-delai-' . $i : ''; $i++; ?>
            <article class="carte-prod reveal<?php echo $delai; ?>">
                <div class="photo">
                    <?php if ($spec['image']): ?>
                        <img src="<?php echo esc_url($spec['image']['sizes']['medium_large'] ?? $spec['image']['url']); ?>"
                             alt="<?php echo esc_attr($spec['image']['alt'] ?: $spec['titre']); ?>">
                    <?php else: ?>
                        <div class="carte-vide" aria-hidden="true"></div>
                    <?php endif; ?>
                </div>
                <div class="corps">
                    <h3><?php echo esc_html($spec['titre']); ?></h3>
                    <p><?php echo esc_html($spec['texte']); ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

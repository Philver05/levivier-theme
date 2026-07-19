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
        <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
        <p><?php echo nl2br(esc_html($intro)); ?></p>
    </div>
</section>

<!-- ======================================================
     TROIS ATOUTS — trio compact à icônes, même structure
     que les départements de la page Épicerie (charte épices)
====================================================== -->
<section class="section section-compacte">
    <div class="conteneur">
        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1-2-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.3 1-3a2.5 2.5 0 0 0 2.5 2.5Z"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout1_titre') ?: 'Produits authentiques'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout1_texte') ?: 'Épices, sauces et féculents choisis auprès de fournisseurs de confiance, fidèles aux saveurs du continent.'); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M8 12V9a4 4 0 0 1 8 0v3"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout2_titre') ?: 'Arrivages réguliers'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout2_texte') ?: 'Des produits secs et frais renouvelés au fil des arrivages, pour cuisiner sans compromis.'); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V10"/><path d="M12 13c0-4-3-6-7-6 0 4 3 6 7 6Z"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7Z"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout3_titre') ?: 'Conseils & recettes'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout3_texte') ?: 'Une équipe qui connaît ses produits et partage volontiers idées et techniques de préparation.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     SPÉCIALITÉS — 3 cartes (équivalent de « Nos produits »)
====================================================== -->
<section class="section produits">
    <div class="conteneur">
        <div class="section-titre">
            <h2><?php echo esc_html(get_field('afr_spec_titre_section') ?: 'Nos spécialités'); ?></h2>
            <p><?php echo esc_html(get_field('afr_spec_soustitre') ?: 'Un aperçu des trésors culinaires à découvrir en boutique'); ?></p>
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

<!-- ======================================================
     PRÉSENTATION — bande blanche finale (équivalent de la
     bande Producteurs de la page Épicerie), texte = éditeur WP
====================================================== -->
<?php if ($pres_presente): ?>
<section class="section bande-blanche">
    <div class="conteneur">
        <div class="section-titre">
            <h2><?php echo esc_html(get_field('afr_pres_titre') ?: 'La boutique africaine au Vivier'); ?></h2>
        </div>
        <?php if ($pres_texte !== '' && $pres_image): ?>
        <div class="apropos-split">
            <div class="page-prose reveal" style="margin:0"><?php the_content(); ?></div>
            <div class="apropos-media reveal">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Épicerie Africaine'); ?>"></div>
            </div>
        </div>
        <?php elseif ($pres_texte !== ''): ?>
            <div class="page-prose reveal"><?php the_content(); ?></div>
        <?php else: ?>
            <div class="apropos-media reveal" style="max-width:640px;margin-inline:auto">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Épicerie Africaine'); ?>"></div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>

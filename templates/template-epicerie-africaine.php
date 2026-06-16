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
$intro    = get_field('afr_intro')    ?: "Découvrez notre sélection de produits africains : épices parfumées, sauces authentiques, féculents et trésors culinaires venus tout droit du continent. Une invitation au voyage, au cœur de Matane.";

/* Présentation */
$pres_image = get_field('afr_presentation_image');

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

/* Bande finale */
$cta_titre = get_field('afr_cta_titre') ?: 'Venez découvrir nos saveurs';
$cta_texte = get_field('afr_cta_texte') ?: 'Passez en boutique à Matane pour explorer toute notre sélection africaine. Notre équipe se fera un plaisir de vous conseiller.';
$cta_lien  = get_field('afr_cta_lien')  ?: get_permalink(get_page_by_path('a-propos'));
$cta_label = get_field('afr_cta_label') ?: 'Nous trouver';
?>

<!-- ======================================================
     HÉROS
====================================================== -->
<section class="section-hero-direct section-hero-direct--beige">
    <div class="conteneur">
        <p class="banniere-surtitre"><?php echo esc_html($surtitre); ?></p>
        <h1 class="hero-direct-titre"><?php the_title(); ?></h1>
        <div class="hero-direct-accroche"><?php echo wpautop(esc_html($intro)); ?></div>
    </div>
</section>

<!-- ======================================================
     PRÉSENTATION (texte = éditeur WP) + image
====================================================== -->
<?php if (get_the_content() || $pres_image): ?>
<section class="section-africaine-presentation">
    <div class="conteneur">
        <div class="africaine-presentation-grille">

            <?php if (get_the_content()): ?>
            <div class="africaine-presentation-texte corps-article">
                <?php the_content(); ?>
            </div>
            <?php endif; ?>

            <?php if ($pres_image): ?>
            <div class="africaine-presentation-image">
                <img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>"
                     alt="<?php echo esc_attr($pres_image['alt'] ?: 'Épicerie Africaine'); ?>">
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     SPÉCIALITÉS — 3 cartes
====================================================== -->
<section class="section-africaine-specialites">
    <div class="conteneur">

        <h2 class="titre-section-centre">Nos spécialités</h2>
        <p class="sous-titre-section">Un aperçu des trésors culinaires à découvrir en boutique</p>

        <div class="africaine-specialites-grille">
            <?php foreach ($specialites as $spec): ?>
            <div class="africaine-carte">
                <div class="africaine-carte-image">
                    <?php if ($spec['image']): ?>
                        <img src="<?php echo esc_url($spec['image']['sizes']['medium_large'] ?? $spec['image']['url']); ?>"
                             alt="<?php echo esc_attr($spec['image']['alt'] ?: $spec['titre']); ?>">
                    <?php else: ?>
                        <div class="africaine-carte-placeholder">🌍</div>
                    <?php endif; ?>
                </div>
                <div class="africaine-carte-corps">
                    <h3><?php echo esc_html($spec['titre']); ?></h3>
                    <p><?php echo esc_html($spec['texte']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ======================================================
     BANDE FINALE — CTA
====================================================== -->
<section class="section-africaine-cta">
    <div class="conteneur">
        <div class="africaine-cta-interieur">
            <h2><?php echo esc_html($cta_titre); ?></h2>
            <p><?php echo esc_html($cta_texte); ?></p>
            <?php if ($cta_lien && $cta_label): ?>
                <a href="<?php echo esc_url($cta_lien); ?>" class="bouton-accent"><?php echo esc_html($cta_label); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

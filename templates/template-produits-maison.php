<?php
/*
Template Name: Produits Maison
*/
get_header();

if (have_posts()) the_post();

/* ----------------------------------------------------------
   Champs ACF + valeurs par défaut — tout éditable dans l'admin
---------------------------------------------------------- */
$surtitre = get_field('pm_surtitre') ?: 'Préparé & fabriqué au Vivier';
$intro    = get_field('pm_intro')    ?: "Nos produits maison, préparés sur place avec soin.\nÀ réserver par bon de commande, à récupérer en magasin.";

/* Présentation (texte = éditeur principal + image) */
$pres_image = get_field('pm_presentation_image');

/* Bon de commande / réservation */
$bon_url   = get_field('pm_bon_url');
$bon_label = get_field('pm_bon_label') ?: 'Réserver par bon de commande';
$bon_note  = get_field('pm_bon_note')  ?: "Réservez vos produits maison en ligne.\nOn les prépare, vous récupérez et payez en magasin.";

/* Bande finale */
$cta_titre = get_field('pm_cta_titre') ?: 'Une envie de produits maison ?';
$cta_texte = get_field('pm_cta_texte') ?: "Passez nous voir à Matane ou réservez à l'avance.\nNotre équipe se fera un plaisir de vous conseiller.";
$cta_lien  = get_field('pm_cta_lien')  ?: get_permalink(get_page_by_path('a-propos'));
$cta_label = get_field('pm_cta_label') ?: 'Nous trouver';

$illu = get_stylesheet_directory_uri() . '/assets/images/illustrations/';
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <span class="arche-mini am-terra"></span>
    <span class="arche-mini am-ocre"></span>
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1><?php the_title(); ?></h1>
        <p><?php echo nl2br(esc_html($intro)); ?></p>
    </div>
</section>

<!-- ======================================================
     PRÉSENTATION (texte = éditeur WP) + image
====================================================== -->
<?php if (get_the_content() || $pres_image): ?>
<section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
    <div class="conteneur">
        <?php if (get_the_content() && $pres_image): ?>
        <div class="apropos-split">
            <div class="page-prose reveal" style="margin:0"><?php the_content(); ?></div>
            <div class="apropos-media reveal">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Produits maison'); ?>"></div>
            </div>
        </div>
        <?php elseif (get_the_content()): ?>
            <div class="page-prose reveal"><?php the_content(); ?></div>
        <?php elseif ($pres_image): ?>
            <div class="apropos-media reveal" style="max-width:640px;margin-inline:auto">
                <div class="cadre"><img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Produits maison'); ?>"></div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     NOS PRODUITS MAISON — vrais produits (catégorie « Produit Maison »)
====================================================== -->
<section class="section produits" id="produits-maison">
    <div class="conteneur">

        <div class="section-titre">
            <h2>Nos produits maison</h2>
            <p>Focaccias, amaretti, salades et autres douceurs faites au Vivier</p>
        </div>

        <?php
        $maison_ids    = function_exists('lv_maison_term_ids') ? lv_maison_term_ids() : [];
        $parent_maison = get_term_by('slug', 'produit-maison', 'categorie_produit');

        /* Sous-categories (enfants de « Produit Maison ») = filtres */
        $sous_cats = ($parent_maison && !is_wp_error($parent_maison)) ? get_terms([
            'taxonomy'   => 'categorie_produit',
            'child_of'   => $parent_maison->term_id,
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]) : [];
        ?>

        <?php if ($sous_cats && !is_wp_error($sous_cats)): ?>
        <nav class="filtres" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php foreach ($sous_cats as $sc): ?>
                <a href="#" class="filtre filtre-lien" data-cat="<?php echo esc_attr($sc->slug); ?>"><?php echo esc_html($sc->name); ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php
        $maison = new WP_Query([
            'post_type'      => 'produit',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'tax_query'      => [[
                'taxonomy' => 'categorie_produit',
                'field'    => 'term_id',
                'terms'    => $maison_ids ?: [0],
            ]],
        ]);
        ?>
        <div class="grille-cartes" id="grille-maison">
            <?php if ($maison->have_posts()):
                while ($maison->have_posts()): $maison->the_post();
                    get_template_part('parts/produit', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="grille-vide">Nos produits maison arrivent bientôt, revenez nous voir !</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- ======================================================
     BON DE COMMANDE — réservation
====================================================== -->
<?php if ($bon_url): ?>
<section class="section section-compacte">
    <div class="conteneur">
        <div class="cta-panel reveal">
            <h2>Réservez vos produits maison</h2>
            <p><?php echo nl2br(esc_html($bon_note)); ?></p>
            <div class="cta-panel-actions">
                <a href="<?php echo esc_url($bon_url); ?>" target="_blank" rel="noopener" class="btn btn-clair"><?php echo esc_html($bon_label); ?></a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     BANDE FINALE — CTA
====================================================== -->
<section class="cta-finale">
    <div class="cta-finale-deco" aria-hidden="true"></div>
    <div class="conteneur cta-finale-inner reveal">
        <h2><?php echo esc_html($cta_titre); ?></h2>
        <p><?php echo nl2br(esc_html($cta_texte)); ?></p>
        <?php if ($cta_lien && $cta_label): ?>
            <div class="cta-finale-actions">
                <a href="<?php echo esc_url($cta_lien); ?>" class="btn btn-clair"><?php echo esc_html($cta_label); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>

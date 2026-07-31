<?php
/*
Template Name: Commander
*/
get_header();

if (have_posts()) the_post();

/* ----------------------------------------------------------
   ACF — modifiable dans l'admin (page Commander)
   · La description se rédige dans l'éditeur principal de la page.
   · Les bons de commande sont gérés par le type « Bons de commande ».
---------------------------------------------------------- */
/* Champs ACF avec repli (textes actuels) — tout éditable dans WP */
$cmd = function ($cle, $defaut = '') {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};
$surtitre = $cmd('cmd_surtitre', 'Bon de commande en ligne · Récupérez en magasin');
$cmd_tel      = function_exists('lv_opt') ? lv_opt('opt_telephone', '(418) 562-5230') : '(418) 562-5230';
$cmd_tel_lien = function_exists('lv_opt_tel_lien') ? lv_opt_tel_lien() : 'tel:+14185625230';
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
        <?php if (get_the_content()) the_content(); ?>
    </div>
</section>

<!-- ======================================================
     COMMENT ÇA FONCTIONNE — 4 étapes
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">
        <div class="section-titre section-titre--large">
            <h2><?php echo esc_html($cmd('cmd_etapes_titre', 'Quatre étapes, quelques minutes, zéro déplacement inutile')); ?></h2>
        </div>

        <ol class="etapes">
            <li class="etape reveal">
                <span class="etape-num">1</span>
                <h3><?php echo esc_html($cmd('cmd_etape1_titre', 'Choisissez votre bon de commande')); ?></h3>
                <p><?php echo esc_html($cmd('cmd_etape1_texte', 'Sélectionnez le bon de commande correspondant à vos besoins.')); ?></p>
            </li>
            <li class="etape reveal reveal-delai-1">
                <span class="etape-num">2</span>
                <h3><?php echo esc_html($cmd('cmd_etape2_titre', 'Sélectionnez vos produits')); ?></h3>
                <p><?php echo esc_html($cmd('cmd_etape2_texte', 'Indiquez les quantités et profitez de rabais selon les quantités choisies.')); ?></p>
            </li>
            <li class="etape reveal reveal-delai-2">
                <span class="etape-num">3</span>
                <h3><?php echo esc_html($cmd('cmd_etape3_titre', 'Envoyez votre bon de commande')); ?></h3>
                <p><?php echo esc_html($cmd('cmd_etape3_texte', 'Gagnez du temps : on s\'occupe de rassembler et de préparer soigneusement votre commande.')); ?></p>
            </li>
            <li class="etape reveal reveal-delai-3">
                <span class="etape-num">4</span>
                <h3><?php echo esc_html($cmd('cmd_etape4_titre', 'Récupérez en magasin')); ?></h3>
                <p><?php echo esc_html($cmd('cmd_etape4_texte', 'Recevez un message lorsque votre commande est prête, puis passez en magasin pour la payer et la récupérer.')); ?></p>
            </li>
        </ol>
    </div>
</section>

<!-- ======================================================
     LES BONS DE COMMANDE
====================================================== -->
<section class="section produits" id="bons-de-commande">
    <div class="conteneur">

        <div class="section-titre">
            <h2><?php echo esc_html($cmd('cmd_bons_titre', 'Nos bons de commande')); ?></h2>
        </div>

        <div class="cmd-grille">
            <?php
            /* Bons de commande manuels (CPT bon_commande) */
            $bons = new WP_Query([
                'post_type'      => 'bon_commande',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            ]);

            if ($bons->have_posts()):
                while ($bons->have_posts()): $bons->the_post();
                    get_template_part('parts/commander', 'card');
                endwhile; wp_reset_postdata();
            endif;

            /* Cartes auto-générées pour les pages PAM et Vrac (descriptions éditables ACF) */
            $templates_internes = [
                'templates/template-bon-pam.php'  => [
                    'icone'       => '🥗',
                    'description' => $cmd('cmd_desc_pam', 'Focaccias, pizzas, sandwichs, salades, sushis et plus — commandez à l\'avance et récupérez en magasin.'),
                ],
                'templates/template-bon-pm.php'   => [
                    'icone'       => '🏡',
                    'description' => $cmd('cmd_desc_pm', 'Nos préparations maison disponibles à la commande — confitures, biscuits, pâtes fraîches et autres douceurs faites au Vivier.'),
                ],
                'templates/template-bon-vrac.php' => [
                    'icone'       => '🌾',
                    'description' => $cmd('cmd_desc_vrac', 'Thés, tisanes, noix, grains, graines, légumineuses — commandez en vrac et profitez d\'escomptes selon la quantité.'),
                ],
            ];

            foreach ($templates_internes as $tpl => $meta):
                $pages = get_pages([
                    'meta_key'    => '_wp_page_template',
                    'meta_value'  => $tpl,
                    'post_status' => 'publish',
                    'number'      => 1,
                    'sort_column' => 'menu_order',
                ]);
                if (empty($pages)) continue;
                $page = $pages[0];
                $thumb = get_the_post_thumbnail_url($page->ID, 'medium_large');
            ?>
            <div class="cmd-carte reveal">
                <div class="cmd-carte-haut">
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>"
                             alt="<?php echo esc_attr($page->post_title); ?>">
                    <?php else: ?>
                        <div class="cmd-carte-icone" aria-hidden="true"><?php echo $meta['icone']; ?></div>
                    <?php endif; ?>
                    <h3 class="cmd-carte-titre-photo"><?php echo esc_html($page->post_title); ?></h3>
                </div>
                <div class="cmd-carte-corps">
                    <p><?php echo esc_html($meta['description']); ?></p>
                    <a href="<?php echo esc_url(get_permalink($page->ID)); ?>"
                       class="btn btn-primaire"><?php echo esc_html($cmd('cmd_bons_cta_texte', 'Remplir ce bon de commande')); ?></a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$bons->post_count && !get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-bon-pam.php', 'post_status' => 'publish', 'number' => 1]) && !get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-bon-vrac.php', 'post_status' => 'publish', 'number' => 1])): ?>
                <p class="grille-vide"><?php echo esc_html($cmd('cmd_bons_vide_texte', 'Les bons de commande seront disponibles ici très bientôt.')); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ======================================================
     RELANCE FINALE
====================================================== -->
<section class="section">
    <div class="conteneur">
        <div class="cta-panel reveal">
            <h2><?php echo esc_html($cmd('cmd_cta_titre', 'Prêt à gagner du temps ?')); ?></h2>
            <p><?php echo esc_html($cmd('cmd_cta_texte', 'Remplissez votre bon de commande dès maintenant. On s\'occupe du reste.')); ?> <?php echo esc_html($cmd('cmd_cta_question_texte', 'Une question ? Appelez-nous au')); ?> <a href="<?php echo esc_attr($cmd_tel_lien); ?>" class="cta-tel"><?php echo esc_html($cmd_tel); ?></a>.</p>
            <div class="cta-panel-actions">
                <a href="#bons-de-commande" class="btn btn-clair"><?php echo esc_html($cmd('cmd_cta_btn', 'Voir les bons de commande')); ?></a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

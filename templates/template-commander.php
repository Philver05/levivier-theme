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
$surtitre = get_field('cmd_surtitre') ?: 'Bon de commande en ligne · Récupérez en magasin';
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1><?php the_title(); ?></h1>
        <?php if (get_the_content()) the_content(); ?>
    </div>
</section>

<!-- ======================================================
     COMMENT ÇA FONCTIONNE — 4 étapes
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">
        <div class="section-titre section-titre--large">
            <h2>Quatre étapes, quelques minutes,<br>zéro déplacement inutile</h2>
        </div>

        <ol class="etapes">
            <li class="etape reveal">
                <span class="etape-num">1</span>
                <h3>Choisissez votre bon de commande</h3>
                <p>Sélectionnez le bon de commande correspondant à vos besoins.</p>
            </li>
            <li class="etape reveal reveal-delai-1">
                <span class="etape-num">2</span>
                <h3>Sélectionnez vos produits</h3>
                <p>Indiquez les quantités et profitez de rabais selon les quantités choisies.</p>
            </li>
            <li class="etape reveal reveal-delai-2">
                <span class="etape-num">3</span>
                <h3>Envoyez votre bon de commande</h3>
                <p>Gagnez du temps : on s'occupe de rassembler et de préparer soigneusement votre commande.</p>
            </li>
            <li class="etape reveal reveal-delai-3">
                <span class="etape-num">4</span>
                <h3>Récupérez en magasin</h3>
                <p>Recevez un message lorsque votre commande est prête, puis passez en magasin pour la payer et la récupérer.</p>
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
            <h2>Choisissez votre bon de commande</h2>
            <p>Choisissez le bon qu'il vous faut et remplissez-le en ligne</p>
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

            /* Cartes auto-générées pour les pages PAM et Vrac */
            $templates_internes = [
                'templates/template-bon-pam.php'  => [
                    'icone'       => '🥗',
                    'description' => 'Focaccias, pizzas, sandwichs, salades, sushis et plus — commandez à l\'avance et récupérez en magasin.',
                ],
                'templates/template-bon-pm.php'   => [
                    'icone'       => '🏡',
                    'description' => 'Nos préparations maison disponibles à la commande — confitures, biscuits, pâtes fraîches et autres douceurs faites au Vivier.',
                ],
                'templates/template-bon-vrac.php' => [
                    'icone'       => '🌾',
                    'description' => 'Thés, tisanes, noix, grains, graines, légumineuses — commandez en vrac et profitez d\'escomptes selon la quantité.',
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
                </div>
                <div class="cmd-carte-corps">
                    <h3><?php echo esc_html($page->post_title); ?></h3>
                    <p><?php echo esc_html($meta['description']); ?></p>
                    <a href="<?php echo esc_url(get_permalink($page->ID)); ?>"
                       class="btn btn-primaire">Remplir ce bon de commande</a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$bons->post_count && !get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-bon-pam.php', 'post_status' => 'publish', 'number' => 1]) && !get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-bon-vrac.php', 'post_status' => 'publish', 'number' => 1])): ?>
                <p class="grille-vide">Les bons de commande seront disponibles ici très bientôt.</p>
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
            <h2>Prêt à gagner du temps&nbsp;?</h2>
            <p>Remplissez votre bon de commande dès maintenant. On s'occupe du reste. Une question&nbsp;? Appelez-nous au <a href="tel:+14185625230" class="cta-tel">(418)&nbsp;562-5230</a>.</p>
            <div class="cta-panel-actions">
                <a href="#bons-de-commande" class="btn btn-clair">Voir les bons de commande</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

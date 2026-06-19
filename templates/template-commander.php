<?php
/*
Template Name: Commander
*/
get_header();

if (have_posts()) the_post();

/* ----------------------------------------------------------
   ACF + valeurs par défaut — modifiable dans l'admin (page Commander)
---------------------------------------------------------- */
$surtitre = get_field('cmd_surtitre') ?: 'Bon de commande en ligne · Récupérez en magasin';
$intro    = get_field('cmd_intro')    ?: "Commandez en ligne, on prépare votre commande, vous la récupérez et la payez en magasin.";

/* Carte Thés */
$the_titre = get_field('cmd_the_titre') ?: 'Thés & Infusions';
$the_texte = get_field('cmd_the_texte') ?: 'Plus de 50 thés et infusions à découvrir, classés par catégorie. Commandez la quantité de votre choix et économisez de 5 à 30 % selon le volume.';
$the_liste = get_field('cmd_the_liste') ?: "Thés noirs, verts et matcha\nThés fruités et chai\nMélanges oolong et rooibos\nTisanes et infusions";
$the_url   = get_field('cmd_the_url')   ?: 'https://form.jotform.com/250713809687265';

/* Carte Vrac */
$vrac_titre = get_field('cmd_vrac_titre') ?: 'Aliments en vrac';
$vrac_texte = get_field('cmd_vrac_texte') ?: "Un immense inventaire organisé en segments pour commander facilement la quantité exacte dont vous avez besoin — sans emballage superflu.";
$vrac_liste = get_field('cmd_vrac_liste') ?: "Noix, grains et graines\nLégumineuses, farines et pâtes\nGrignotines, chocolats et confiseries\nÉpices, sels, herbes séchées et cafés";
$vrac_url   = get_field('cmd_vrac_url')   ?: 'https://form.jotform.com/251905097007052';

if (!function_exists('lv_lignes')) {
    function lv_lignes($texte) {
        return array_filter(array_map('trim', explode("\n", str_replace("\r", '', $texte))));
    }
}
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
        <p><?php echo esc_html($intro); ?></p>
    </div>
</section>

<!-- ======================================================
     COMMENT ÇA FONCTIONNE — 4 étapes
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">
        <div class="section-titre">
            <h2>Quatre étapes, quelques minutes, zéro déplacement inutile</h2>
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
            else: ?>

                <!-- Repli : les bons définis dans les champs de la page -->
                <div class="cmd-carte reveal">
                    <div class="cmd-carte-haut">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h13v4a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8Z"/><path d="M17 9h2a2 2 0 0 1 0 4h-2"/><path d="M8 3c-.5 1 .5 2 0 3M12 3c-.5 1 .5 2 0 3"/></svg>
                    </div>
                    <div class="cmd-carte-corps">
                        <h3><?php echo esc_html($the_titre); ?></h3>
                        <p><?php echo esc_html($the_texte); ?></p>
                        <ul class="cmd-liste">
                            <?php foreach (lv_lignes($the_liste) as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url($the_url); ?>" target="_blank" rel="noopener" class="btn btn-primaire">Commander des thés</a>
                    </div>
                </div>

                <div class="cmd-carte reveal reveal-delai-1">
                    <div class="cmd-carte-haut">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21V9"/><path d="M12 9c0-2.2 1.6-3.5 3.5-3.5C15.5 7.7 14 9 12 9Zm0 0c0-2.2-1.6-3.5-3.5-3.5C8.5 7.7 10 9 12 9Zm0 4c0-2.2 1.6-3.5 3.5-3.5C15.5 11.7 14 13 12 13Zm0 0c0-2.2-1.6-3.5-3.5-3.5C8.5 11.7 10 13 12 13Z"/></svg>
                    </div>
                    <div class="cmd-carte-corps">
                        <h3><?php echo esc_html($vrac_titre); ?></h3>
                        <p><?php echo esc_html($vrac_texte); ?></p>
                        <ul class="cmd-liste">
                            <?php foreach (lv_lignes($vrac_liste) as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url($vrac_url); ?>" target="_blank" rel="noopener" class="btn btn-primaire">Commander en vrac</a>
                    </div>
                </div>

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
            <p>Remplissez votre bon de commande dès maintenant — on s'occupe du reste. Une question&nbsp;? Appelez-nous au <a href="tel:+14185625230">(418)&nbsp;562-5230</a>.</p>
            <div class="cta-panel-actions">
                <a href="<?php echo esc_url($the_url); ?>" target="_blank" rel="noopener" class="btn btn-clair">Commander des thés</a>
                <a href="<?php echo esc_url($vrac_url); ?>" target="_blank" rel="noopener" class="btn btn-contour">Commander en vrac</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

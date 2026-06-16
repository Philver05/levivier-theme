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
$intro    = get_field('cmd_intro')    ?: "Remplissez votre bon de commande en ligne, on prépare tout pour vous, et vous passez récupérer et payer en magasin. Simple, sans déplacement inutile.";

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
     HÉROS COMPACT — action immédiate
====================================================== -->
<section class="section-commander-hero">
    <div class="conteneur">
        <p class="banniere-surtitre"><?php echo esc_html($surtitre); ?></p>
        <h1 class="commander-hero-titre"><?php the_title(); ?></h1>
        <p class="commander-hero-accroche"><?php echo esc_html($intro); ?></p>
    
    </div>
</section>

<!-- ======================================================
     COMMENT ÇA FONCTIONNE — 4 étapes (en haut, sans hésitation)
====================================================== -->
<section class="section-commander-etapes">
    <div class="conteneur">

        <h2 class="titre-section-centre">Quatre étapes, quelques minutes, zéro déplacement inutile</h2>

        <div class="grille-etapes grille-etapes-4">

            <div class="carte-etape">
                <span class="etape-numero">1</span>
                <h3>Choisissez votre bon de commande </h3>
                <p>Sélectionner le bon de commande correspondant à vos besoins.</p>
            </div>

            <div class="carte-etape">
                <span class="etape-numero">2</span>
                <h3>Sélectionnez vos produits</h3>
                <p> Indiquez les quantités et profitez de rabais selon les quantités choisies.</p>
            </div>

            <div class="carte-etape">
                <span class="etape-numero">3</span>
                <h3>Envoyez votre bon de commande</h3>
                <p>Gagnez du temps : on s'occupe de rassembler et de préparer soigneusement votre commande.</p>
            </div>

            <div class="carte-etape">
                <span class="etape-numero">4</span>
                <h3>Récupérez en magasin</h3>
                <p>Récupérez en magasin : Recevez un message lorsque votre commande est prête, puis passez en magasin pour la payer et la récupérer.</p>
            </div>

        </div>

        <!-- <div class="section-lien-centre">
            <a href="#bons-de-commande" class="bouton-primaire">Remplir mon bon de commande</a>
        </div> -->

    </div>
</section>

<!-- ======================================================
     LES BONS DE COMMANDE — l'action
====================================================== -->
<section class="section-commander-produits" id="bons-de-commande">
    <div class="conteneur">

        <h2 class="titre-section-centre">Choisissez votre bon de commande</h2>
        <p class="sous-titre-section">Choisissez le bon qu'il vous faut et remplissez-le en ligne</p>

        <div class="commander-cartes-grille">
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
                <div class="commander-carte">
                    <div class="commander-carte-icone commander-carte-icone--terracotta">🍵</div>
                    <div class="commander-carte-corps">
                        <h3><?php echo esc_html($the_titre); ?></h3>
                        <p><?php echo esc_html($the_texte); ?></p>
                        <ul class="commander-produits-liste">
                            <?php foreach (lv_lignes($the_liste) as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url($the_url); ?>" target="_blank" rel="noopener" class="bouton-primaire commander-carte-cta">Commander des thés →</a>
                    </div>
                </div>

                <div class="commander-carte">
                    <div class="commander-carte-icone commander-carte-icone--sauge">🌾</div>
                    <div class="commander-carte-corps">
                        <h3><?php echo esc_html($vrac_titre); ?></h3>
                        <p><?php echo esc_html($vrac_texte); ?></p>
                        <ul class="commander-produits-liste">
                            <?php foreach (lv_lignes($vrac_liste) as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url($vrac_url); ?>" target="_blank" rel="noopener" class="bouton-primaire commander-carte-cta">Commander en vrac →</a>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ======================================================
     RELANCE FINALE
====================================================== -->
<section class="section-commander-relance">
    <div class="conteneur">
        <div class="commander-relance-interieur">
            <h2>Prêt à gagner du temps&nbsp;?</h2>
            <p>Remplissez votre bon de commande dès maintenant — on s'occupe du reste. Une question&nbsp;? Appelez-nous au <a href="tel:+14185625230">(418)&nbsp;562-5230</a>.</p>
            <div class="commander-hero-actions">
                <a href="<?php echo esc_url($the_url); ?>" target="_blank" rel="noopener" class="bouton-primaire">Commander des thés</a>
                <a href="<?php echo esc_url($vrac_url); ?>" target="_blank" rel="noopener" class="bouton-secondaire">Commander en vrac</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

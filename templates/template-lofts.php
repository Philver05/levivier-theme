<?php
/*
Template Name: Les Lofts de la Rivière
*/
get_header();

if (have_posts()) the_post();

$surtitre = get_field('lofts_surtitre') ?: 'Location touristique · Centre-ville de Matane';
$intro    = get_field('lofts_intro')    ?: "Deux lofts modernes avec cuisinette, pensés pour qu'il ne vous manque rien. Vous arrivez, vous déposez vos valises, et Matane est déjà à vos pieds.";

$atouts_titre = get_field('lofts_atouts_titre') ?: 'Pourquoi vous allez adorer';
$atouts_brut  = get_field('lofts_atouts') ?: "Tout à distance de marche | Restos, cafés, commerces et bord de mer à quelques minutes. Stationnez gratuitement une fois, puis oubliez la voiture.\nRien à apporter | Cuisinette complète, Wi-Fi rapide, téléviseur 65 pouces et literie soignée. Vous n'avez qu'à vous installer.\nUne épicerie sous vos pieds | Le Vivier vous attend au rez-de-chaussée : produits frais, cafés et prêt-à-manger des artisans d'ici. Le déjeuner commence dans l'escalier.\nRéservez l'esprit tranquille | Hébergement enregistré (CITQ 323422). Échanges directs, conditions claires, aucune surprise à l'arrivée.";
$atouts = [];
foreach (array_filter(array_map('trim', explode("\n", str_replace("\r", '', $atouts_brut)))) as $ligne) {
    $parts = array_map('trim', explode('|', $ligne, 2));
    if (count($parts) === 2) {
        $atouts[] = ['titre' => $parts[0], 'desc' => $parts[1]];
    }
}

$cta_titre  = get_field('lofts_cta_titre')  ?: 'Vos dates partent vite';
$cta_texte  = get_field('lofts_cta_texte')  ?: "Deux lofts seulement, un calendrier qui se remplit. Réservez les vôtres pendant qu'ils sont libres.";
$cta_bouton = get_field('lofts_cta_bouton') ?: 'Voir les lofts et réserver';
$cta_lien   = get_field('lofts_cta_lien')   ?: '#nos-lofts';

/* Coordonnées depuis les Réglages du site (mêmes helpers que Contact/À propos/footer),
   adresse remise sur une seule ligne comme dans template-contact.php (retour Philippe). */
$lofts_adresse    = function_exists('lv_opt') ? preg_replace('/\s*\R\s*/', ', ', trim(lv_opt('opt_adresse', "14 Avenue D'Amours\nMatane, QC G4W 2X4"))) : "14 Avenue D'Amours, Matane, QC G4W 2X4";
$lofts_tel        = function_exists('lv_opt') ? lv_opt('opt_telephone', '(418) 562-5230') : '(418) 562-5230';
$lofts_tel_lien   = function_exists('lv_opt_tel_lien') ? lv_opt_tel_lien() : 'tel:+14185625230';
$lofts_citq       = get_field('lofts_citq') ?: '323422';

/* Filet de sécurité si functions.php n'est pas encore à jour sur le serveur
   (même logique que dans single-loft.php) : icône cœur générique. */
if (!function_exists('lv_lofts_atout_icone')) {
    function lv_lofts_atout_icone($label) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 8.6c0-2.6-2-4.6-4.5-4.6-1.4 0-2.7.7-3.5 1.8-.8-1.1-2.1-1.8-3.5-1.8C6 4 4 6 4 8.6 4 13.5 12 20 12 20s8-6.5 8-11.4Z"/></svg>';
    }
}
?>

<div class="page-lofts">

    <!-- ======================================================
         EN-TÊTE COMPACT — pas de bannière, on va droit au but
    ====================================================== -->
    <section class="lofts-entete">
        <div class="conteneur">
            <p class="lofts-eyebrow lofts-eyebrow-centre"><?php echo esc_html($surtitre); ?></p>
            <h1 class="lofts-entete-titre"><?php the_title(); ?></h1>
            <p class="lofts-entete-lead"><?php echo esc_html($intro); ?></p>
            <p class="lofts-entete-meta">
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    <?php echo esc_html($lofts_adresse); ?>
                </span>
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    CITQ&nbsp;<?php echo esc_html($lofts_citq); ?>
                </span>
                <a href="<?php echo esc_attr($lofts_tel_lien); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L17 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg>
                    <?php echo esc_html($lofts_tel); ?>
                </a>
            </p>
        </div>
    </section>

    <!-- ======================================================
         LES LOFTS — l'action, tout de suite
    ====================================================== -->
    <section class="lofts-liste-section" id="nos-lofts">
        <div class="conteneur">

            <?php
            $lofts = new WP_Query([
                'post_type'      => 'loft',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            ]);
            ?>

            <?php if ($lofts->have_posts()): ?>
                <div class="lofts-grille">
                    <?php while ($lofts->have_posts()): $lofts->the_post();
                        get_template_part('parts/loft', 'card');
                    endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p class="lofts-vide">Nos deux lofts (Rivière Douce et Rivière Vive) seront présentés ici très bientôt. Réservez dès maintenant au <a href="<?php echo esc_attr($lofts_tel_lien); ?>"><?php echo esc_html($lofts_tel); ?></a>.</p>
            <?php endif; ?>

        </div>
    </section>

    <!-- ======================================================
         POURQUOI SÉJOURNER ICI
    ====================================================== -->
    <section class="lofts-reassurance">
        <div class="conteneur">
            <h2 class="lofts-section-titre"><?php echo esc_html($atouts_titre); ?></h2>
            <div class="lofts-reassurance-grille">
                <?php foreach ($atouts as $i => $a): ?>
                <div class="lofts-atout reveal<?php echo $i > 0 ? ' reveal-delai-' . min($i, 3) : ''; ?>">
                    <span class="lofts-atout-icone" aria-hidden="true"><?php echo lv_lofts_atout_icone($a['titre']); ?></span>
                    <h3><?php echo esc_html($a['titre']); ?></h3>
                    <p><?php echo esc_html($a['desc']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ======================================================
         CTA FINALE
    ====================================================== -->
    <section class="lofts-cta-finale">
        <div class="conteneur">
            <div class="lofts-cta-interieur reveal">
                <h2><?php echo esc_html($cta_titre); ?></h2>
                <p><?php echo esc_html($cta_texte); ?></p>
                <a href="<?php echo esc_url($cta_lien); ?>" class="lofts-cta"><?php echo esc_html($cta_bouton); ?></a>
                <p class="lofts-contact-ligne">
                    <?php echo esc_html($lofts_adresse); ?> &nbsp;·&nbsp;
                    <a href="<?php echo esc_attr($lofts_tel_lien); ?>"><?php echo esc_html($lofts_tel); ?></a> &nbsp;·&nbsp; CITQ&nbsp;<?php echo esc_html($lofts_citq); ?>
                </p>
            </div>
        </div>
        <!-- Cette bande fait office de pied de page sur /lofts/ (le pied
             habituel du site, en sauge, est retiré ici pour ne pas casser
             l'identité sarcelle en toute fin de page) -->
        <div class="conteneur lofts-cta-bas">&copy; <?php echo esc_html(wp_date('Y')); ?> Le Vivier · Matane, Québec</div>
    </section>

</div>

<?php get_footer(); ?>

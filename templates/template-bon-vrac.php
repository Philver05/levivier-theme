<?php
/*
Template Name: Bon de commande — Vrac
*/
get_header();
if (have_posts()) the_post();

$surtitre = get_field('vrac_surtitre')         ?: 'Commande en vrac · Le Vivier';
$intro    = get_field('vrac_intro');
$bandeau  = get_field('vrac_bandeau_escompte');
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1><?php the_title(); ?></h1>
        <?php if ($intro): ?>
        <div class="page-entete-intro"><?php echo wp_kses_post($intro); ?></div>
        <?php elseif (get_the_content()): the_content(); endif; ?>
    </div>
</section>

<?php if ($bandeau): ?>
<!-- ======================================================
     BANDEAU ESCOMPTE
====================================================== -->
<section class="section section-compacte vrac-bandeau-escompte">
    <div class="conteneur">
        <div class="vrac-bandeau-inner">
            <?php echo wp_kses_post($bandeau); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     FORMULAIRE
====================================================== -->
<section class="section vrac-form-section" style="padding-bottom:2rem<?php echo $bandeau ? '' : ';padding-top:.5rem'; ?>">
    <div class="conteneur">
    <div class="vrac-form-carte">

        <form id="vrac-formulaire" novalidate>

            <!-- ---- Infos client ---- -->
            <div class="pam-infos-client vrac-infos-client">
                <h2 class="pam-section-titre">Vos coordonnées</h2>

                <div class="pam-grille-form">
                    <div>
                        <div class="pam-champ">
                            <label for="vrac_prenom">Prénom <abbr title="requis">*</abbr></label>
                            <input type="text" id="vrac_prenom" name="prenom" required autocomplete="given-name">
                        </div>
                        <div class="pam-champ">
                            <label for="vrac_nom">Nom <abbr title="requis">*</abbr></label>
                            <input type="text" id="vrac_nom" name="nom" required autocomplete="family-name">
                        </div>
                    </div>
                    <div>
                        <div class="pam-champ">
                            <label for="vrac_telephone">Téléphone</label>
                            <input type="tel" id="vrac_telephone" name="telephone" autocomplete="tel">
                        </div>
                        <div class="pam-champ">
                            <label for="vrac_email">Courriel <abbr title="requis">*</abbr></label>
                            <input type="email" id="vrac_email" name="email" required autocomplete="email">
                        </div>
                    </div>
                </div>
                <div class="pam-champ">
                    <label for="vrac_commentaire">Commentaire (optionnel)</label>
                    <textarea id="vrac_commentaire" name="commentaire" rows="3" placeholder="Allergies, préférences, questions…"></textarea>
                </div>
            </div>

            <!-- ---- Grille produits ---- -->
            <div class="vrac-produits-section">
                <h2 class="pam-section-titre" style="margin-top:2.5rem;">Sélectionnez vos produits</h2>
                <p class="vrac-selection-note">Cochez un produit pour faire apparaître les formats disponibles.</p>

                <?php
                $categories = get_terms([
                    'taxonomy'   => 'vrac_categorie',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);

                $a_des_produits = false;

                if ($categories && !is_wp_error($categories)):
                    foreach ($categories as $cat):
                        $produits = new WP_Query([
                            'post_type'      => 'vrac_produit',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'tax_query'      => [[
                                'taxonomy' => 'vrac_categorie',
                                'field'    => 'term_id',
                                'terms'    => $cat->term_id,
                            ]],
                            'orderby'        => 'menu_order title',
                            'order'          => 'ASC',
                        ]);

                        if (!$produits->have_posts()) continue;
                        $a_des_produits = true;
                ?>
                <div class="pam-categorie vrac-categorie-section">
                    <h3 class="pam-categorie-titre"><?php echo esc_html($cat->name); ?></h3>
                    <div class="vrac-produits-grille">
                        <?php while ($produits->have_posts()): $produits->the_post();
                            $pid         = get_the_ID();
                            $formats     = get_field('vrac_formats')     ?: [];
                            $description = get_field('vrac_description');
                            $code        = get_field('vrac_code');
                            $thumb       = get_the_post_thumbnail_url($pid, 'medium');
                        ?>
                        <div class="vrac-produit-item" data-id="<?php echo esc_attr($pid); ?>">

                            <label class="vrac-produit-select">
                                <input type="checkbox" class="vrac-checkbox" value="<?php echo esc_attr($pid); ?>">
                                <span class="vrac-check-icon" aria-hidden="true"></span>
                                <span class="sr-only">Sélectionner <?php echo esc_attr(get_the_title()); ?></span>
                            </label>

                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="vrac-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($code): ?>
                                <p class="vrac-produit-code">Code&nbsp;: <?php echo esc_html($code); ?></p>
                                <?php endif; ?>
                                <?php if ($description): ?>
                                <p class="pam-produit-desc"><?php echo esc_html($description); ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if ($formats): ?>
                            <table class="vrac-formats-tableau" hidden>
                                <thead>
                                    <tr>
                                        <th scope="col">Format</th>
                                        <th scope="col">Qté</th>
                                        <th scope="col">Prix</th>
                                        <th scope="col">Sous-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($formats as $fmt):
                                        $fmt_label = esc_attr($fmt['format_label']);
                                        $fmt_prix  = number_format((float) $fmt['format_prix'], 2, '.', '');
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($fmt['format_label']); ?></td>
                                        <td>
                                            <input type="number"
                                                   class="vrac-qty-input"
                                                   value="0" min="0" max="99"
                                                   data-label="<?php echo $fmt_label; ?>"
                                                   data-prix="<?php echo esc_attr($fmt_prix); ?>"
                                                   aria-label="Quantité <?php echo $fmt_label; ?> de <?php echo esc_attr(get_the_title()); ?>">
                                        </td>
                                        <td><?php echo esc_html(number_format((float) $fmt['format_prix'], 2, ',', ' ')); ?>&nbsp;$</td>
                                        <td class="vrac-ligne-total">0,00&nbsp;$</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>

                        </div><!-- .vrac-produit-item -->
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div><!-- .vrac-produits-grille -->
                </div><!-- .vrac-categorie-section -->
                <?php endforeach; endif; ?>

                <?php if (!$a_des_produits): ?>
                <p class="grille-vide"><?php echo esc_html(get_field('vrac_vide_texte') ?: 'Les produits en vrac seront disponibles ici très bientôt.'); ?></p>
                <?php endif; ?>
            </div><!-- .vrac-produits-section -->

        </form>

        <div id="vrac-msg-succes" class="pam-msg-succes" role="alert" hidden></div>
        <div id="vrac-msg-erreur" class="pam-msg-erreur" role="alert" hidden></div>

    </div><!-- .vrac-form-carte -->
    </div><!-- .conteneur -->
</section>

<!-- ======================================================
     BARRE DE TOTAL (sticky bottom)
====================================================== -->
<div class="vrac-barre-total" id="vrac-barre-total" aria-live="polite">
    <div class="vrac-barre-inner conteneur">
        <div class="vrac-totaux">
            <span class="vrac-total-ligne">Sous-total&nbsp;: <strong id="vrac-sous-total">0,00&nbsp;$</strong></span>
            <span class="vrac-escompte-info" id="vrac-escompte-info" hidden></span>
            <span class="vrac-total-final-ligne">Total&nbsp;: <strong id="vrac-total-final">0,00&nbsp;$</strong></span>
        </div>
        <button type="submit" form="vrac-formulaire" class="btn btn-primaire" id="vrac-btn-soumettre">
            Envoyer la commande
        </button>
    </div>
</div>

<?php get_footer(); ?>

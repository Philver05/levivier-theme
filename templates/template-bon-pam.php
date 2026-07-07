<?php
/*
Template Name: Bon de commande — Prêt à manger
*/
get_header();
if (have_posts()) the_post();

$surtitre = get_field('pam_surtitre') ?: 'Prêt à manger · Le Vivier';
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
     FORMULAIRE
====================================================== -->
<section class="section">
    <div class="conteneur">

        <form id="pam-formulaire" class="pam-form" novalidate>

            <!-- ---- Étape 1 : filtre jour + produits ---- -->
            <div class="pam-produits-col">
                <h2 class="pam-section-titre"><span class="pam-etape" aria-hidden="true">1</span>Votre commande</h2>

                <?php
                /* Jours qui ont au moins un produit publié */
                $jours_defs = [
                    'tous_les_jours' => 'Tous les jours',
                    'lundi'          => 'Spécial lundi',
                    'mardi'          => 'Spécial mardi',
                    'mercredi'       => 'Spécial mercredi',
                    'jeudi'          => 'Spécial jeudi',
                    'vendredi'       => 'Spécial vendredi',
                    'samedi'         => 'Spécial samedi',
                    'dimanche'       => 'Spécial dimanche',
                ];

                $jours_actifs = [];
                $tous_pam = new WP_Query([
                    'post_type'      => 'pam_produit',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'no_found_rows'  => true,
                    'fields'         => 'ids',
                ]);
                foreach ($tous_pam->posts as $pid) {
                    $j = (array) (get_field('pam_jours', $pid) ?: ['tous_les_jours']);
                    foreach ($j as $slug) $jours_actifs[$slug] = true;
                }
                /* Garder l'ordre de jours_defs, afficher seulement les actifs */
                $jours_affiches = array_intersect_key($jours_defs, $jours_actifs);
                if (empty($jours_affiches)) $jours_affiches = ['tous_les_jours' => 'Tous les jours'];

                /* Messages par jour (ACF de la page) */
                $messages_jours = [];
                $msgs_raw = get_field('pam_messages_jours');
                if ($msgs_raw) {
                    foreach ($msgs_raw as $m) {
                        if (empty($m['msg_jour'])) continue;
                        if (!empty($m['msg_titre']) || !empty($m['msg_description'])) {
                            $messages_jours[$m['msg_jour']] = [
                                'titre'       => $m['msg_titre']       ?? '',
                                'description' => $m['msg_description'] ?? '',
                                'cta'         => $m['msg_cta']         ?? '',
                            ];
                        }
                    }
                }
                ?>

                <!-- Filtre par jour -->
                <div class="pam-filtre-jours" role="group" aria-labelledby="pam-filtre-label">
                    <p id="pam-filtre-label" class="pam-filtre-label">Jour de récupération</p>
                    <?php $premier = true; foreach ($jours_affiches as $slug => $label): ?>
                    <label class="pam-filtre-option">
                        <input type="radio" name="jour" value="<?php echo esc_attr($slug); ?>"
                               <?php if ($premier) { echo 'checked'; $premier = false; } ?>>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Messages par jour -->
                <?php if ($messages_jours): ?>
                <div class="pam-messages-jours" aria-live="polite">
                    <?php foreach ($messages_jours as $slug => $data): ?>
                    <div class="pam-msg-jour" data-jour="<?php echo esc_attr($slug); ?>" hidden>
                        <?php if ($data['titre']): ?>
                        <p class="pam-msg-titre"><?php echo esc_html($data['titre']); ?></p>
                        <?php endif; ?>
                        <?php if ($data['description']): ?>
                        <p class="pam-msg-desc"><?php echo nl2br(esc_html($data['description'])); ?></p>
                        <?php endif; ?>
                        <?php if ($data['cta']): ?>
                        <div class="pam-msg-cta"><?php echo nl2br(esc_html($data['cta'])); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Onglets catégorie -->
                <?php
                $categories = get_terms([
                    'taxonomy'   => 'pam_categorie',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                ?>
                <?php if ($categories && !is_wp_error($categories) && count($categories) > 1): ?>
                <div class="pam-filtre-cats" role="tablist" aria-label="Catégories">
                    <?php foreach ($categories as $cat): ?>
                    <button type="button"
                            class="pam-cat-tab"
                            role="tab"
                            data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Produits groupés par catégorie -->
                <?php
                $a_des_produits = false;

                if ($categories && !is_wp_error($categories)):
                    foreach ($categories as $cat):
                        $produits = new WP_Query([
                            'post_type'      => 'pam_produit',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'tax_query'      => [[
                                'taxonomy' => 'pam_categorie',
                                'field'    => 'term_id',
                                'terms'    => $cat->term_id,
                            ]],
                            'orderby'        => 'menu_order title',
                            'order'          => 'ASC',
                        ]);

                        if (!$produits->have_posts()) continue;
                        $a_des_produits = true;
                ?>
                <div class="pam-categorie" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <h3 class="pam-categorie-titre"><?php echo esc_html($cat->name); ?></h3>
                    <div class="pam-produits-grille">
                        <?php while ($produits->have_posts()): $produits->the_post();
                            $pid         = get_the_ID();
                            $prix        = (float) get_field('pam_prix');
                            $jours       = (array) (get_field('pam_jours') ?: ['tous_les_jours']);
                            $description = get_field('pam_description');
                            $thumb       = get_the_post_thumbnail_url($pid, 'medium');
                        ?>
                        <div class="pam-produit-item"
                             data-id="<?php echo esc_attr($pid); ?>"
                             data-prix="<?php echo esc_attr(number_format($prix, 2, '.', '')); ?>"
                             data-jours="<?php echo esc_attr(implode(' ', $jours)); ?>">

                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="pam-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($description): ?>
                                <p class="pam-produit-desc"><?php echo esc_html($description); ?></p>
                                <?php endif; ?>
                                <p class="pam-produit-prix"><?php echo esc_html(number_format($prix, 2, ',', ' ')); ?>&nbsp;$</p>

                                <div class="pam-qty-controle">
                                    <button type="button" class="pam-qty-moins" aria-label="Retirer un">-</button>
                                    <input type="number"
                                           class="pam-qty-input"
                                           name="produits[<?php echo esc_attr($pid); ?>]"
                                           value="0" min="0" max="20"
                                           aria-label="Quantité de <?php echo esc_attr(get_the_title()); ?>">
                                    <button type="button" class="pam-qty-plus" aria-label="Ajouter un">+</button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div><!-- .pam-produits-grille -->
                </div><!-- .pam-categorie -->
                <?php endforeach; endif; ?>

                <?php if (!$a_des_produits): ?>
                <p class="grille-vide">Les produits Prêt à manger seront disponibles ici très bientôt.</p>
                <?php endif; ?>
            </div><!-- .pam-produits-col -->

            <!-- ---- Étape 2 : infos client ---- -->
            <div class="pam-infos-client pam-bloc-coordonnees">
                <h2 class="pam-section-titre"><span class="pam-etape" aria-hidden="true">2</span>Vos coordonnées</h2>

                <div class="pam-grille-form">
                    <div class="pam-champ">
                        <label for="pam_prenom">Prénom <abbr title="requis">*</abbr></label>
                        <input type="text" id="pam_prenom" name="prenom" required autocomplete="given-name">
                    </div>
                    <div class="pam-champ">
                        <label for="pam_nom">Nom <abbr title="requis">*</abbr></label>
                        <input type="text" id="pam_nom" name="nom" required autocomplete="family-name">
                    </div>
                    <div class="pam-champ">
                        <label for="pam_telephone">Téléphone</label>
                        <input type="tel" id="pam_telephone" name="telephone" autocomplete="tel">
                    </div>
                    <div class="pam-champ">
                        <label for="pam_email">Courriel <abbr title="requis">*</abbr></label>
                        <input type="email" id="pam_email" name="email" required autocomplete="email">
                    </div>
                    <div class="pam-champ pam-champ--pleine">
                        <label for="pam_commentaire">Commentaire (optionnel)</label>
                        <textarea id="pam_commentaire" name="commentaire" rows="3" placeholder="Allergies, préférences, questions…"></textarea>
                    </div>
                </div>
            </div>

        </form>

        <div id="pam-msg-succes" class="pam-msg-succes" role="alert" hidden></div>
        <div id="pam-msg-erreur" class="pam-msg-erreur" role="alert" hidden></div>

    </div><!-- .conteneur -->
</section>

<!-- ======================================================
     BARRE DE TOTAL (sticky bottom)
====================================================== -->
<div class="pam-barre-total" id="pam-barre-total" aria-live="polite">
    <div class="pam-barre-inner conteneur">
        <p class="pam-total-label">Total&nbsp;: <strong id="pam-total-montant">0,00&nbsp;$</strong></p>
        <button type="submit" form="pam-formulaire" class="btn btn-primaire" id="pam-btn-soumettre">
            Envoyer le bon de commande
        </button>
    </div>
</div>

<?php get_footer(); ?>

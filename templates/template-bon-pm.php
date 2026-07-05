<?php
/*
Template Name: Bon de commande — Produits Maison
*/
get_header();
if (have_posts()) the_post();

$surtitre = get_field('pm_bon_surtitre') ?: 'Produits Maison · Le Vivier';
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
        <?php if (get_the_content()) the_content(); ?>
    </div>
</section>

<!-- ======================================================
     FORMULAIRE
====================================================== -->
<section class="section">
    <div class="conteneur">

        <form id="pm-formulaire" class="pam-grille-form" novalidate>

            <!-- ---- Colonne gauche : infos client ---- -->
            <div class="pam-infos-client">
                <h2 class="pam-section-titre">Vos coordonnées</h2>

                <div class="pam-champ">
                    <label for="pm_prenom">Prénom <abbr title="requis">*</abbr></label>
                    <input type="text" id="pm_prenom" name="prenom" required autocomplete="given-name">
                </div>
                <div class="pam-champ">
                    <label for="pm_nom">Nom <abbr title="requis">*</abbr></label>
                    <input type="text" id="pm_nom" name="nom" required autocomplete="family-name">
                </div>
                <div class="pam-champ">
                    <label for="pm_telephone">Téléphone</label>
                    <input type="tel" id="pm_telephone" name="telephone" autocomplete="tel">
                </div>
                <div class="pam-champ">
                    <label for="pm_email">Courriel <abbr title="requis">*</abbr></label>
                    <input type="email" id="pm_email" name="email" required autocomplete="email">
                </div>
                <div class="pam-champ">
                    <label for="pm_commentaire">Commentaire (optionnel)</label>
                    <textarea id="pm_commentaire" name="commentaire" rows="3" placeholder="Allergies, préférences, questions…"></textarea>
                </div>

                <!-- Barre de total (sticky) -->
                <div id="pm-barre-total" class="pam-barre-total">
                    <span>Total estimé</span>
                    <strong id="pm-total-montant">0,00 $</strong>
                </div>

                <p id="pm-msg-succes" class="pam-msg-succes" hidden></p>
                <p id="pm-msg-erreur" class="pam-msg-erreur" hidden></p>

                <button type="submit" id="pm-btn-soumettre" class="btn btn-primaire" style="width:100%;margin-top:.5rem">
                    Envoyer le bon de commande
                </button>
            </div>

            <!-- ---- Colonne droite : produits ---- -->
            <div class="pam-produits-col">
                <h2 class="pam-section-titre">Votre commande</h2>

                <?php
                $parent_maison = get_term_by('slug', 'produit-maison', 'categorie_produit');
                $sous_cats = ($parent_maison && !is_wp_error($parent_maison)) ? get_terms([
                    'taxonomy'   => 'categorie_produit',
                    'child_of'   => $parent_maison->term_id,
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]) : [];

                /* Onglets catégorie (si plus d'une sous-catégorie) */
                if ($sous_cats && !is_wp_error($sous_cats) && count($sous_cats) > 1): ?>
                <div class="pam-filtre-cats" role="tablist" aria-label="Catégories">
                    <?php foreach ($sous_cats as $cat): ?>
                    <button type="button"
                            class="pam-cat-tab"
                            role="tab"
                            data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Produits groupés par sous-catégorie -->
                <?php
                $a_des_produits = false;

                if ($sous_cats && !is_wp_error($sous_cats)):
                    foreach ($sous_cats as $cat):
                        $produits = new WP_Query([
                            'post_type'      => 'produit',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'orderby'        => 'menu_order title',
                            'order'          => 'ASC',
                            'tax_query'      => [[
                                'taxonomy' => 'categorie_produit',
                                'field'    => 'term_id',
                                'terms'    => $cat->term_id,
                            ]],
                            'meta_query'     => [[
                                'key'   => 'pm_commandable',
                                'value' => '1',
                            ]],
                        ]);

                        if (!$produits->have_posts()) continue;
                        $a_des_produits = true;
                ?>
                <div class="pam-categorie" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <h3 class="pam-categorie-titre"><?php echo esc_html($cat->name); ?></h3>
                    <div class="pam-carousel">
                        <button type="button" class="pam-fleche pam-fleche-prev" aria-label="Produits précédents">
                            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <div class="pam-produits-grille">
                        <?php while ($produits->have_posts()): $produits->the_post();
                            $pid   = get_the_ID();
                            $prix  = (float) get_field('pm_prix');
                            $thumb = get_the_post_thumbnail_url($pid, 'medium');
                            $desc  = get_field('pm_description');
                        ?>
                        <div class="pam-produit-item"
                             data-id="<?php echo esc_attr($pid); ?>"
                             data-prix="<?php echo esc_attr(number_format($prix, 2, '.', '')); ?>">

                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="pam-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($desc): ?>
                                <p class="pam-produit-desc"><?php echo esc_html($desc); ?></p>
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
                        <button type="button" class="pam-fleche pam-fleche-next" aria-label="Produits suivants">
                            <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg>
                        </button>
                    </div><!-- .pam-carousel -->
                </div><!-- .pam-categorie -->
                <?php endforeach;
                endif;

                /* Fallback : pas de sous-catégories — afficher tous les produits commandables */
                if (!$a_des_produits && $parent_maison && !is_wp_error($parent_maison)):
                    $maison_ids = get_terms([
                        'taxonomy'   => 'categorie_produit',
                        'child_of'   => $parent_maison->term_id,
                        'fields'     => 'ids',
                        'hide_empty' => false,
                    ]);
                    $maison_ids[] = $parent_maison->term_id;

                    $produits = new WP_Query([
                        'post_type'      => 'produit',
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                        'orderby'        => 'menu_order title',
                        'order'          => 'ASC',
                        'tax_query'      => [[
                            'taxonomy' => 'categorie_produit',
                            'field'    => 'term_id',
                            'terms'    => $maison_ids,
                        ]],
                        'meta_query'     => [[
                            'key'   => 'pm_commandable',
                            'value' => '1',
                        ]],
                    ]);

                    if ($produits->have_posts()):
                        $a_des_produits = true;
                ?>
                <div class="pam-categorie" data-cat="tout">
                    <div class="pam-carousel">
                        <button type="button" class="pam-fleche pam-fleche-prev" aria-label="Produits précédents">
                            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <div class="pam-produits-grille">
                        <?php while ($produits->have_posts()): $produits->the_post();
                            $pid   = get_the_ID();
                            $prix  = (float) get_field('pm_prix');
                            $thumb = get_the_post_thumbnail_url($pid, 'medium');
                            $desc  = get_field('pm_description');
                        ?>
                        <div class="pam-produit-item"
                             data-id="<?php echo esc_attr($pid); ?>"
                             data-prix="<?php echo esc_attr(number_format($prix, 2, '.', '')); ?>">

                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="pam-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($desc): ?>
                                <p class="pam-produit-desc"><?php echo esc_html($desc); ?></p>
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
                        <button type="button" class="pam-fleche pam-fleche-next" aria-label="Produits suivants">
                            <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg>
                        </button>
                    </div><!-- .pam-carousel -->
                </div><!-- .pam-categorie -->
                <?php endif;
                endif; ?>

                <?php if (!$a_des_produits): ?>
                <p class="grille-vide">Les produits maison disponibles à la commande seront affichés ici.</p>
                <?php endif; ?>

            </div><!-- .pam-produits-col -->

        </form>
    </div>
</section>

<?php get_footer(); ?>

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
<section class="section" style="padding-top:.5rem;padding-bottom:2rem">
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

                /* Messages par jour (ACF de la page) : calculé AVANT le filtre de jours
                   pour qu'un message (ex: partenaire du jeudi) puisse afficher son jour
                   dans le filtre même si aucun produit n'est encore rattaché à ce jour-là.
                   Repli de démonstration (sushis du jeudi) tant que rien n'est saisi dans
                   WP, pour visualiser le rendu sans avoir à remplir le champ. */
                $messages_jours = [];
                $msgs_raw = get_field('pam_messages_jours') ?: [[
                    'msg_jour'        => 'jeudi',
                    'msg_titre'       => 'Les jeudis sushis au Vivier',
                    'msg_description' => "Le Vivier accueille chaque jeudi Le P'tit Béret, un traiteur passionné et authentique qui vous propose de généreux sushis maison préparés avec soin.",
                    'msg_cta'         => "Commandez avant 10h le mercredi\nRécupérez au Vivier à partir de 12h le jeudi",
                    'msg_note_titre'     => 'Fraîcheur et conservation des sushis',
                    'msg_note_texte'     => "Les sushis se dégustent idéalement le jour même pour une expérience optimale. Ils peuvent se conserver jusqu'à 24 heures au réfrigérateur, bien scellés et au frais.\n\nSi vous les consommez le lendemain, les laisser reposer 10 à 15 minutes à température ambiante avant dégustation, afin que le riz retrouve sa texture moelleuse et les saveurs, leur équilibre.",
                    'msg_categorie_slug' => 'sushis',
                ]];
                foreach ($msgs_raw as $m) {
                    if (empty($m['msg_jour'])) continue;
                    if (!empty($m['msg_titre']) || !empty($m['msg_description'])) {
                        /* Catégorie associée (optionnel) : soit un terme réel choisi dans
                           WP (ID), soit le slug de démo ci-dessus tant que rien n'est saisi. */
                        $cat_slug = $m['msg_categorie_slug'] ?? '';
                        if (!$cat_slug && !empty($m['msg_categorie'])) {
                            $terme = get_term($m['msg_categorie'], 'pam_categorie');
                            if ($terme && !is_wp_error($terme)) $cat_slug = $terme->slug;
                        }
                        $messages_jours[$m['msg_jour']] = [
                            'titre'       => $m['msg_titre']       ?? '',
                            'description' => $m['msg_description'] ?? '',
                            'cta'         => $m['msg_cta']         ?? '',
                            'image'       => $m['msg_image']       ?? null,
                            'note_titre'  => $m['msg_note_titre']  ?? '',
                            'note_texte'  => $m['msg_note_texte']  ?? '',
                            'categorie'   => $cat_slug,
                        ];
                    }
                }

                $jours_actifs = [];
                foreach (array_keys($messages_jours) as $slug) $jours_actifs[$slug] = true;
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
                    <div class="pam-msg-jour<?php echo $data['image'] ? ' pam-msg-jour--avec-image' : ''; ?>" data-jour="<?php echo esc_attr($slug); ?>" data-categorie="<?php echo esc_attr($data['categorie']); ?>" hidden>
                        <?php if ($data['image']): ?>
                        <img class="pam-msg-image" src="<?php echo esc_url($data['image']['sizes']['medium'] ?? $data['image']['url']); ?>" alt="<?php echo esc_attr($data['image']['alt'] ?: $data['titre']); ?>">
                        <?php endif; ?>
                        <div class="pam-msg-corps">
                            <?php if ($data['titre']): ?>
                            <p class="pam-msg-titre"><?php echo esc_html($data['titre']); ?></p>
                            <?php endif; ?>
                            <?php if ($data['description']): ?>
                            <p class="pam-msg-desc"><?php echo nl2br(esc_html($data['description'])); ?></p>
                            <?php endif; ?>
                            <?php if ($data['cta']): ?>
                            <div class="pam-msg-cta">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                <span><?php echo esc_html(implode('  ·  ', array_filter(array_map('trim', explode("\n", $data['cta']))))); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($data['note_titre'] || $data['note_texte']): ?>
                            <div class="pam-msg-note">
                                <?php if ($data['note_titre']): ?>
                                <p class="pam-msg-note-titre"><?php echo esc_html($data['note_titre']); ?></p>
                                <?php endif; ?>
                                <?php if ($data['note_texte']): ?>
                                <p class="pam-msg-note-texte"><?php echo nl2br(esc_html($data['note_texte'])); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Onglets catégorie (2 niveaux : catégories principales + sous-catégories) -->
                <?php
                /* Catégories principales = termes sans parent (regroupements comme
                   Pâtisseries/Pains, et catégories autonomes comme Sushis) */
                $categories_principales = get_terms([
                    'taxonomy'   => 'pam_categorie',
                    'parent'     => 0,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                if (is_wp_error($categories_principales)) $categories_principales = [];

                /* Ne garder que celles qui ont au moins un produit, direct ou via une sous-catégorie */
                $categories_principales = array_values(array_filter($categories_principales, function ($cat) {
                    $q = new WP_Query([
                        'post_type'      => 'pam_produit',
                        'post_status'    => 'publish',
                        'posts_per_page' => 1,
                        'no_found_rows'  => true,
                        'fields'         => 'ids',
                        'tax_query'      => [['taxonomy' => 'pam_categorie', 'field' => 'term_id', 'terms' => $cat->term_id]],
                    ]);
                    return $q->have_posts();
                }));

                /* Ordre personnalisé demandé par Philippe (17 juillet), pas alphabétique.
                   Toute catégorie non listée ici (ex: nouvelle catégorie pas encore ajoutée
                   à cette liste) se retrouve à la fin, par ordre alphabétique. */
                $ordre_pam_categories = [
                    'Pains', 'Pâtisseries', 'Pâtés et Quiches', 'Mets préparés',
                    'Divers prêt-à-manger', 'Sushis',
                ];
                usort($categories_principales, function ($a, $b) use ($ordre_pam_categories) {
                    $pos_a = array_search($a->name, $ordre_pam_categories, true);
                    $pos_b = array_search($b->name, $ordre_pam_categories, true);
                    if ($pos_a === false) $pos_a = 999;
                    if ($pos_b === false) $pos_b = 999;
                    if ($pos_a === $pos_b) return strcasecmp($a->name, $b->name);
                    return $pos_a <=> $pos_b;
                });
                ?>
                <?php if (count($categories_principales) > 1): ?>
                <div class="pam-filtre-cats" role="tablist" aria-label="Catégories">
                    <?php foreach ($categories_principales as $cat): ?>
                    <button type="button"
                            class="pam-cat-tab"
                            role="tab"
                            data-cat="<?php echo esc_attr($cat->slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Produits groupés par catégorie principale, avec sous-onglets si elle a des enfants -->
                <?php
                $a_des_produits = false;

                foreach ($categories_principales as $cat):
                    $enfants = get_terms([
                        'taxonomy'   => 'pam_categorie',
                        'parent'     => $cat->term_id,
                        'hide_empty' => true,
                    ]);
                    if (is_wp_error($enfants)) $enfants = [];

                    $produits = new WP_Query([
                        'post_type'      => 'pam_produit',
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                        'tax_query'      => [[
                            'taxonomy' => 'pam_categorie',
                            'field'    => 'term_id',
                            'terms'    => $cat->term_id,
                        ]],
                        'meta_key'       => 'pam_prix',
                        'orderby'        => ['meta_value_num' => 'ASC', 'title' => 'ASC'],
                    ]);

                    if (!$produits->have_posts()) continue;
                    $a_des_produits = true;
                ?>
                <div class="pam-categorie" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <h3 class="pam-categorie-titre"><?php echo esc_html($cat->name); ?></h3>

                    <?php if ($enfants): ?>
                    <div class="pam-filtre-souscats" role="tablist" aria-label="Sous-catégories de <?php echo esc_attr($cat->name); ?>">
                        <button type="button" class="pam-souscat-tab pam-souscat-tab--actif" data-souscat="">Tout voir</button>
                        <?php foreach ($enfants as $enfant): ?>
                        <button type="button" class="pam-souscat-tab" data-souscat="<?php echo esc_attr($enfant->slug); ?>">
                            <?php echo esc_html($enfant->name); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="pam-produits-grille">
                        <?php while ($produits->have_posts()): $produits->the_post();
                            $pid          = get_the_ID();
                            $prix         = (float) get_field('pam_prix');
                            $jours        = (array) (get_field('pam_jours') ?: ['tous_les_jours']);
                            $description  = get_field('pam_description');
                            $instructions = get_field('pam_instructions');
                            $poids        = get_field('pam_poids');
                            $taxable      = get_field('pam_taxable');
                            $ingredients  = get_field('pam_ingredients');
                            $thumb        = get_the_post_thumbnail_url($pid, 'large');
                            $photo2       = get_field('pam_photo2');

                            /* Sous-catégorie de ce produit parmi les enfants de la
                               catégorie principale active, pour le filtre à 2 niveaux */
                            $souscat = '';
                            if ($enfants) {
                                $termes_produit = wp_get_post_terms($pid, 'pam_categorie', ['fields' => 'ids']);
                                foreach ($enfants as $enfant) {
                                    if (in_array($enfant->term_id, $termes_produit, true)) { $souscat = $enfant->slug; break; }
                                }
                            }
                        ?>
                        <div class="pam-produit-item"
                             data-id="<?php echo esc_attr($pid); ?>"
                             data-prix="<?php echo esc_attr(number_format($prix, 2, '.', '')); ?>"
                             data-jours="<?php echo esc_attr(implode(' ', $jours)); ?>"
                             data-souscat="<?php echo esc_attr($souscat); ?>">

                            <?php if ($thumb && $photo2): ?>
                            <div class="pam-produit-photos">
                                <img class="pam-produit-photo pam-produit-photo--1" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                                <img class="pam-produit-photo pam-produit-photo--2" src="<?php echo esc_url($photo2['sizes']['large'] ?? $photo2['url']); ?>" alt="" loading="lazy">
                            </div>
                            <?php elseif ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="pam-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($poids): ?>
                                <p class="pam-produit-poids"><?php echo esc_html($poids); ?></p>
                                <?php endif; ?>
                                <?php if ($description): ?>
                                <details class="pam-produit-details">
                                    <summary>Description</summary>
                                    <p class="pam-produit-desc"><?php echo esc_html($description); ?></p>
                                </details>
                                <?php endif; ?>
                                <?php if ($ingredients): ?>
                                <details class="pam-produit-details">
                                    <summary>Ingrédients et allergènes</summary>
                                    <p class="pam-produit-desc"><?php echo nl2br(esc_html($ingredients)); ?></p>
                                </details>
                                <?php endif; ?>
                                <p class="pam-produit-prix">
                                    <?php echo esc_html(number_format($prix, 2, ',', ' ')); ?>&nbsp;$
                                    <?php if ($taxable): ?><span class="pam-produit-taxes">+ taxes</span><?php endif; ?>
                                </p>
                                <?php if ($instructions): ?>
                                <p class="pam-produit-instructions">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                                    <span><?php echo nl2br(esc_html($instructions)); ?></span>
                                </p>
                                <?php endif; ?>

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
                <?php endforeach; ?>

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
                    <div class="pam-champ">
                        <label for="pam_date">Date de récupération <abbr title="requis">*</abbr></label>
                        <input type="date" id="pam_date" name="date_recuperation" required
                               min="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
                    </div>
                    <div class="pam-champ">
                        <label for="pam_heure">Heure de récupération <abbr title="requis">*</abbr></label>
                        <select id="pam_heure" name="heure_recuperation" required>
                            <option value="">Choisir une plage</option>
                            <option value="8h30 – 10h">8h30 – 10h</option>
                            <option value="10h – 12h">10h – 12h</option>
                            <option value="12h – 14h">12h – 14h</option>
                            <option value="14h – 16h">14h – 16h</option>
                            <option value="16h – 18h">16h – 18h</option>
                        </select>
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
    <!-- Récapitulatif de la sélection (déplié au-dessus de la barre) -->
    <div class="pam-recap conteneur" id="pam-recap" hidden>
        <ul class="pam-recap-liste" id="pam-recap-liste"></ul>
    </div>
    <div class="pam-barre-inner conteneur">
        <button type="button" class="pam-recap-toggle" id="pam-recap-toggle"
                aria-expanded="false" aria-controls="pam-recap" hidden>
            <span id="pam-recap-count">0 article</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 15 12 9 18 15"/></svg>
        </button>
        <p class="pam-total-label">Total&nbsp;: <strong id="pam-total-montant">0,00&nbsp;$</strong></p>
        <button type="submit" form="pam-formulaire" class="btn btn-primaire" id="pam-btn-soumettre">
            Envoyer le bon de commande
        </button>
    </div>
</div>

<?php get_footer(); ?>

<?php
/*
Template Name: Bon de commande — Prêt à manger
*/
get_header();
if (have_posts()) the_post();

$surtitre = get_field('pam_surtitre') ?: 'Commandez en ligne - Récupérez en magasin';
$intro    = get_field('pam_intro');
if (!$intro && !trim(wp_strip_all_tags(get_the_content()))) {
    $intro = 'Faites le plein de saveurs maison! Explorez nos différentes catégories de produits. Certains sont offerts tous les jours, tandis que d\'autres sont préparés à des journées précises ou en quantité limitée. Choisissez votre journée de récupération pour découvrir les produits disponibles. Une fois votre commande transmise, nous la validerons et vous enverrons une confirmation lorsqu\'elle sera prête à être payée et récupérée en magasin.';
}
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
        <?php if ($intro): ?>
            <p class="page-entete-intro"><?php echo nl2br(esc_html($intro)); ?></p>
        <?php elseif (get_the_content()): ?>
            <?php the_content(); ?>
        <?php endif; ?>
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

                /* Messages par jour et/ou par catégorie (ACF de la page) : calculé AVANT
                   le filtre de jours pour qu'un message (ex: partenaire du jeudi) puisse
                   afficher son jour dans le filtre même si aucun produit n'est encore
                   rattaché à ce jour-là. Liste simple (pas indexée par jour) : un message
                   peut n'avoir AUCUN jour (déclenché seulement par sa catégorie), et
                   plusieurs messages peuvent partager le même jour ou la même catégorie.
                   Repli de démonstration (sushis du jeudi) tant que rien n'est saisi dans
                   WP, pour visualiser le rendu sans avoir à remplir le champ. */
                $messages = [];
                $messages_par_produit = [];
                $msgs_raw = get_field('pam_messages_jours') ?: [[
                    'msg_jour'        => ['jeudi'],
                    'msg_titre'       => 'Les jeudis sushis au Vivier',
                    'msg_description' => "Le Vivier accueille chaque jeudi Le P'tit Béret, un traiteur passionné et authentique qui vous propose de généreux sushis maison préparés avec soin.",
                    'msg_cta'         => "Commandez avant 10h le mercredi\nRécupérez au Vivier à partir de 12h le jeudi",
                    'msg_note_titre'     => 'Fraîcheur et conservation des sushis',
                    'msg_note_texte'     => "Les sushis se dégustent idéalement le jour même pour une expérience optimale. Ils peuvent se conserver jusqu'à 24 heures au réfrigérateur, bien scellés et au frais.\n\nSi vous les consommez le lendemain, les laisser reposer 10 à 15 minutes à température ambiante avant dégustation, afin que le riz retrouve sa texture moelleuse et les saveurs, leur équilibre.",
                    'msg_categorie_slug' => 'sushis',
                ]];
                foreach ($msgs_raw as $m) {
                    /* Catégorie associée (optionnel) : soit un terme réel choisi dans
                       WP (ID), soit le slug de démo ci-dessus tant que rien n'est saisi. */
                    $cat_slug = $m['msg_categorie_slug'] ?? '';
                    if (!$cat_slug && !empty($m['msg_categorie'])) {
                        $terme = get_term($m['msg_categorie'], 'pam_categorie');
                        if ($terme && !is_wp_error($terme)) $cat_slug = $terme->slug;
                    }
                    /* Un ou plusieurs jours (ex : produit offert mercredi, jeudi ET
                       vendredi). Cast défensif : le champ peut être un select (valeur
                       texte) ou une case à cocher (tableau) — les deux formats sont
                       acceptés pour la compatibilité ascendante. */
                    $jours_bruts = $m['msg_jour'] ?? [];
                    if (!is_array($jours_bruts)) {
                        $jours_bruts = $jours_bruts !== '' ? [$jours_bruts] : [];
                    }
                    $jours_msg = array_values(array_filter($jours_bruts));

                    /* Produit spécifique (optionnel) : si rempli, le message se
                       concentre sur SA carte au lieu d'un bandeau au-dessus de
                       toute la grille — utile pour cibler un produit particulier
                       au sein d'une catégorie. */
                    $produit_cible = !empty($m['msg_produit']) ? (int) $m['msg_produit'] : 0;

                    /* Un message doit avoir au moins un jour, une catégorie, OU un
                       produit spécifique pour savoir quand s'afficher — sinon rien
                       ne le déclenchera jamais. */
                    if (empty($jours_msg) && empty($cat_slug) && !$produit_cible) continue;
                    if (empty($m['msg_titre']) && empty($m['msg_description'])) continue;

                    $donnees_msg = [
                        'jours'       => $jours_msg,
                        'titre'       => $m['msg_titre']       ?? '',
                        'description' => $m['msg_description'] ?? '',
                        'cta'         => $m['msg_cta']         ?? '',
                        'image'       => $m['msg_image']       ?? null,
                        'note_titre'  => $m['msg_note_titre']  ?? '',
                        'note_texte'  => $m['msg_note_texte']  ?? '',
                        'categorie'   => $cat_slug,
                    ];

                    if ($produit_cible) {
                        $messages_par_produit[$produit_cible][] = $donnees_msg;
                    } else {
                        $messages[] = $donnees_msg;
                    }
                }

                $jours_actifs = [];
                foreach ($messages as $msg) {
                    foreach ($msg['jours'] as $j) { $jours_actifs[$j] = true; }
                }
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

                <!-- Messages par jour et/ou par catégorie -->
                <?php if ($messages): ?>
                <div class="pam-messages-jours" aria-live="polite">
                    <?php foreach ($messages as $data): ?>
                    <details class="pam-msg-jour<?php echo $data['image'] ? ' pam-msg-jour--avec-image' : ''; ?>" data-jour="<?php echo esc_attr(implode(' ', $data['jours'])); ?>" data-categorie="<?php echo esc_attr($data['categorie']); ?>" hidden>
                        <summary class="pam-msg-titre"><?php echo esc_html($data['titre'] ?: 'Informations'); ?></summary>
                        <?php if ($data['image']): ?>
                        <img class="pam-msg-image" src="<?php echo esc_url($data['image']['sizes']['medium'] ?? $data['image']['url']); ?>" alt="<?php echo esc_attr($data['image']['alt'] ?: $data['titre']); ?>">
                        <?php endif; ?>
                        <div class="pam-msg-corps">
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
                    </details>
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

                /* Ne garder que celles qui ont au moins un produit, direct ou via une sous-catégorie.
                   Une seule requête batch au lieu d'une WP_Query par catégorie. */
                $ids_pam = get_posts([
                    'post_type'      => 'pam_produit',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'no_found_rows'  => true,
                ]);
                $terms_avec_produits = [];
                if ($ids_pam) {
                    foreach (wp_get_object_terms($ids_pam, 'pam_categorie', ['fields' => 'ids']) as $tid) {
                        $terms_avec_produits[$tid] = true;
                    }
                }
                $categories_principales = array_values(array_filter($categories_principales, function ($cat) use ($terms_avec_produits) {
                    if (isset($terms_avec_produits[$cat->term_id])) return true;
                    foreach (get_term_children($cat->term_id, 'pam_categorie') as $eid) {
                        if (isset($terms_avec_produits[$eid])) return true;
                    }
                    return false;
                }));

                /* Ordre des catégories — non alphabétique, défini ici.
                   Toute catégorie absente de cette liste se retrouve à la fin. */
                $ordre_pam_categories = [
                    'Pains', 'Pâtisseries', 'Pâtés et Quiches', 'Mets préparés',
                    'Divers prêt-à-manger', 'Sushis', 'Accompagnement', 'Boissons',
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
                <div class="pam-cat-select-wrap">
                    <select class="pam-cat-select" aria-label="Filtrer par catégorie">
                        <?php foreach ($categories_principales as $cat): ?>
                        <option value="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="pam-cat-select-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </div>
                <?php endif; ?>

                <!-- Produits groupés par catégorie principale, avec sous-onglets si elle a des enfants -->
                <?php
                $a_des_produits = false;

                /* Index produits par catégorie pour les suggestions automatiques Level 1 */
                $pam_index_par_cat = [];
                /* Paires de catégories : quand on ajoute X, suggérer depuis Y.
                   Logique réelle du catalogue :
                   - accompagnement = Patates sarladaises, complément assiette idéal
                   - boissons = Yesterday + Cafélimo, complément repas
                   - salades/sandwichs sont dans mets-prepares, ne pas les suggérer
                     entre eux (ce serait suggérer 2 plats principaux) */
                $pam_regles_cat = [
                    /* Catégories principales */
                    'pains'                => ['boissons', 'accompagnement'],
                    'patisseries'          => ['boissons'],
                    'pates'                => ['accompagnement', 'boissons'],
                    'mets-prepares'        => ['boissons', 'accompagnement'],
                    'divers-pret-a-manger' => ['pains', 'accompagnement'],
                    'sushis'               => ['boissons'],
                    'accompagnement'       => ['boissons', 'mets-prepares'],
                    'boissons'             => ['patisseries', 'pains'],
                    /* Sous-catégories — priorité sur la catégorie principale */
                    'focaccias'            => ['boissons', 'accompagnement'],
                    'focaccia-maison'      => ['boissons', 'accompagnement'],
                    'amaretti'             => ['boissons'],
                    'biscuit'              => ['boissons'],
                    'cake'                 => ['boissons'],
                    'tarte'                => ['boissons'],
                    'sandwichs'            => ['accompagnement', 'boissons'],
                    'salades'              => ['boissons', 'pains'],
                    'pizzas'               => ['accompagnement', 'boissons'],
                    'mets-cuisines'        => ['accompagnement', 'boissons'],
                    'sauces'               => ['pains', 'accompagnement'],
                ];

                foreach ($categories_principales as $cat):
                    $enfants = get_terms([
                        'taxonomy'   => 'pam_categorie',
                        'parent'     => $cat->term_id,
                        'hide_empty' => true,
                    ]);
                    if (is_wp_error($enfants)) $enfants = [];

                    /* Ordre personnalisé des sous-catégories par catégorie parente */
                    $ordre_souscats_par_cat = [
                        'Mets préparés' => ['Pizzas', 'Mets cuisinés', 'Sandwichs', 'Salades'],
                    ];
                    if (!empty($ordre_souscats_par_cat[$cat->name]) && !empty($enfants)) {
                        $ordre_sc = $ordre_souscats_par_cat[$cat->name];
                        usort($enfants, function ($a, $b) use ($ordre_sc) {
                            $pa = array_search($a->name, $ordre_sc, true);
                            $pb = array_search($b->name, $ordre_sc, true);
                            if ($pa === false) $pa = 999;
                            if ($pb === false) $pb = 999;
                            return $pa === $pb ? strcasecmp($a->name, $b->name) : $pa <=> $pb;
                        });
                    }

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
                        'orderby'        => ['menu_order' => 'DESC', 'meta_value_num' => 'ASC', 'title' => 'ASC'],
                    ]);

                    if (!$produits->have_posts()) continue;
                    $a_des_produits = true;
                ?>
                <div class="pam-categorie" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <h3 class="pam-categorie-titre"><?php echo esc_html($cat->name); ?></h3>

                    <?php if ($enfants): ?>
                    <div class="pam-filtre-souscats" role="tablist" aria-label="Sous-catégories de <?php echo esc_attr($cat->name); ?>">
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
                            $suggestions_raw = get_field('pam_suggestions') ?: [];
                            $sugg_data = [];
                            foreach ($suggestions_raw as $_s) {
                                $_sid = is_object($_s) ? $_s->ID : (int)$_s;
                                if (!$_sid) continue;
                                $sugg_data[] = [
                                    'id'      => $_sid,
                                    'titre'   => get_the_title($_sid),
                                    'prix'    => (float)(get_field('pam_prix', $_sid) ?: 0),
                                    'taxable' => (bool)get_field('pam_taxable', $_sid),
                                    'photo'   => get_the_post_thumbnail_url($_sid, 'thumbnail') ?: '',
                                ];
                            }
                            $chaud        = get_field('pam_chaud');

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
                        <?php
                        /* Alimenter l'index pour les suggestions automatiques */
                        $thumb_sugg = get_the_post_thumbnail_url($pid, 'thumbnail') ?: $thumb ?: '';
                        $pam_index_par_cat[$cat->slug][] = ['id' => $pid, 'titre' => get_the_title(), 'photo' => $thumb_sugg, 'prix' => $prix, 'taxable' => (bool)$taxable];
                        if ($souscat) $pam_index_par_cat[$souscat][] = ['id' => $pid, 'titre' => get_the_title(), 'photo' => $thumb_sugg, 'prix' => $prix, 'taxable' => (bool)$taxable];
                        ?>
                        <div class="pam-produit-item"
                             data-id="<?php echo esc_attr($pid); ?>"
                             data-prix="<?php echo esc_attr(number_format($prix, 2, '.', '')); ?>"
                             data-taxable="<?php echo esc_attr($taxable ? '1' : '0'); ?>"
                             data-jours="<?php echo esc_attr(implode(' ', $jours)); ?>"
                             data-souscat="<?php echo esc_attr($souscat); ?>"
                             data-suggestions="<?php echo esc_attr(json_encode($sugg_data)); ?>"
                             data-chaud="<?php echo $chaud ? '1' : '0'; ?>">

                            <?php if ($thumb && $photo2): ?>
                            <div class="pam-produit-photos">
                                <img class="pam-produit-photo pam-produit-photo--1 pm-lightbox-trigger" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" tabindex="0" role="button" aria-label="Agrandir la photo">
                                <img class="pam-produit-photo pam-produit-photo--2 pm-lightbox-trigger" src="<?php echo esc_url($photo2['sizes']['large'] ?? $photo2['url']); ?>" alt="" loading="lazy" tabindex="0" role="button" aria-label="Agrandir la photo">
                            </div>
                            <?php elseif ($thumb): ?>
                            <img class="pm-lightbox-trigger" src="<?php echo esc_url($thumb); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy" tabindex="0" role="button" aria-label="Agrandir la photo">
                            <?php else: ?>
                            <div class="pam-produit-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>

                            <div class="pam-produit-corps">
                                <p class="pam-produit-nom"><?php the_title(); ?></p>
                                <?php if ($poids): ?>
                                <p class="pam-produit-poids"><?php echo esc_html($poids); ?></p>
                                <?php endif; ?>
                                <?php foreach (($messages_par_produit[$pid] ?? []) as $data): ?>
                                <div class="pam-msg-jour pam-msg-jour--carte<?php echo $data['image'] ? ' pam-msg-jour--avec-image' : ''; ?>">
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
                                <?php if ($instructions): ?>
                                <p class="pam-produit-instructions">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                                    <span><?php echo nl2br(esc_html($instructions)); ?></span>
                                </p>
                                <?php endif; ?>

                                <div class="pam-produit-pied">
                                <p class="pam-produit-prix">
                                    <?php echo esc_html(number_format($prix, 2, ',', ' ')); ?>&nbsp;$
                                    <?php if ($taxable): ?><span class="pam-produit-taxes">+ taxes</span><?php endif; ?>
                                </p>
                                <div class="pam-qty-controle">
                                    <button type="button" class="pam-qty-moins" aria-label="Retirer un">-</button>
                                    <input type="number"
                                           class="pam-qty-input"
                                           name="produits[<?php echo esc_attr($pid); ?>]"
                                           value="0" min="0" max="20"
                                           aria-label="Quantité de <?php echo esc_attr(get_the_title()); ?>">
                                    <button type="button" class="pam-qty-plus" aria-label="Ajouter un">+</button>
                                </div>
                                </div><!-- .pam-produit-pied -->
                                <?php if ($chaud): ?>
                                <label class="pam-option-chaud" hidden>
                                    <input type="checkbox" name="chaud[<?php echo esc_attr($pid); ?>]" value="1" class="pam-chaud-input">
                                    Je le veux réchauffé
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div><!-- .pam-produits-grille -->
                </div><!-- .pam-categorie -->
                <?php endforeach; ?>

                <?php if (!$a_des_produits): ?>
                <p class="grille-vide"><?php echo esc_html(get_field('pam_vide_texte') ?: 'Les produits Prêt à manger seront disponibles ici très bientôt.'); ?></p>
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
                               min="<?php echo esc_attr(wp_date('Y-m-d', strtotime('+1 day'))); ?>">
                    </div>
                    <div class="pam-champ">
                        <label for="pam_heure">Heure de récupération <abbr title="requis">*</abbr></label>
                        <select id="pam_heure" name="heure_recuperation" required>
                            <option value="">-- Choisir une plage --</option>
                            <option value="8h30 - 10h00">8h30 - 10h00</option>
                            <option value="10h00 - 12h00">10h00 - 12h00</option>
                            <option value="12h00 - 14h00">12h00 - 14h00</option>
                            <option value="14h00 - 16h00">14h00 - 16h00</option>
                            <option value="16h00 - 18h00">16h00 - 18h00</option>
                        </select>
                    </div>
                    <div class="pam-champ pam-champ--pleine">
                        <label for="pam_commentaire">Commentaire (optionnel)</label>
                        <textarea id="pam_commentaire" name="commentaire" rows="3" placeholder="Allergies, préférences, questions…"></textarea>
                    </div>
                </div>
                <div id="pam-rechauffage-info" class="pam-rechauffage-info" hidden>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                    Réchauffage demandé pour : <strong id="pam-rechauffage-liste"></strong>
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

<!-- ======================================================
     LIGHTBOX — même composant que le bon Produits Maison,
     voir pm-lightbox.js
====================================================== -->
<div class="pm-lightbox" id="pm-lightbox" hidden>
    <button type="button" class="pm-lightbox-fermer" id="pm-lightbox-fermer" aria-label="Fermer l'aperçu">
        <span></span><span></span>
    </button>
    <img id="pm-lightbox-img" src="" alt="">
</div>

<script>
window.pamReglesCategories = <?php echo wp_json_encode($pam_regles_cat); ?>;
window.pamProduitsParCategorie = <?php echo wp_json_encode($pam_index_par_cat); ?>;
</script>
<?php get_footer(); ?>

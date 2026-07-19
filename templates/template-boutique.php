<?php
/*
Template Name: La Boutique
*/
get_header();

/* Champs ACF avec repli (textes actuels) — tout éditable dans WP */
$bout = function ($cle, $defaut = '') {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <?php if (have_posts()): the_post(); ?>
            <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
            <?php if (get_the_content()): the_content(); else: ?>
                <p>Des objets beaux et utiles pour un quotidien plus durable : zéro déchet, naturels, et fièrement fabriqués au Québec.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     VALEURS — 3 atouts au trait
====================================================== -->
<section class="section section-compacte engagements">
    <div class="conteneur">
        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 13-6l2 2M20 12a8 8 0 0 1-13 6l-2-2"/><path d="M19 4v4h-4M5 20v-4h4"/></svg>
                </span>
                <h3><?php echo esc_html($bout('bout_val1_titre', 'Zéro déchet')); ?></h3>
                <p><?php echo esc_html($bout('bout_val1_texte', 'Des alternatives durables pour réduire l\'empreinte au quotidien.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 21c0-7 5-13 14-14-1 9-7 14-14 14Z"/><path d="M5 21c2-5 6-8 10-9"/></svg>
                </span>
                <h3><?php echo esc_html($bout('bout_val2_titre', 'Naturel & Éco')); ?></h3>
                <p><?php echo esc_html($bout('bout_val2_texte', 'Cosmétiques, produits ménagers et corporels respectueux de l\'environnement.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </span>
                <h3><?php echo esc_html($bout('bout_val3_titre', 'Fait au Québec')); ?></h3>
                <p><?php echo esc_html($bout('bout_val3_texte', 'Des artisans locaux et des produits fabriqués ici, pour consommer autrement.')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     ARTICLES BOUTIQUE — sans titre de section (l'en-tête de
     page joue déjà ce rôle) ; filtres seulement s'il existe
     des catégories (un « Tout voir » seul n'apporte rien)
====================================================== -->
<section class="section produits" id="articles">
    <div class="conteneur">

        <?php
        $categories_boutique = get_terms([
            'taxonomy'   => 'categorie_boutique',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        ?>
        <?php if ($categories_boutique && !is_wp_error($categories_boutique)): ?>
        <nav class="filtres" aria-label="Filtrer par catégorie">
            <a href="#" class="filtre filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php foreach ($categories_boutique as $cat): ?>
                <a href="#" class="filtre filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <!-- Grille articles -->
        <?php
        $articles = new WP_Query([
            'post_type'      => 'article_boutique',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="grille-cartes" id="grille-boutique">
            <?php if ($articles->have_posts()):
                while ($articles->have_posts()): $articles->the_post();
                    get_template_part('parts/boutique', 'card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="grille-vide"><?php echo esc_html($bout('bout_vide_texte', 'Les articles arrivent bientôt, revenez nous voir !')); ?></p>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- ======================================================
     LA BOUTIQUE EN PHOTOS — bande de présentation juste
     avant le pied de page (demande de Philippe) ; n'apparaît
     que si photos ou texte présents
====================================================== -->
<?php
$pres_photos = $bout('bout_photos', []);
$pres_texte  = trim($bout('bout_pres_texte', ''));
if (!is_array($pres_photos)) $pres_photos = [];
if ($pres_photos || $pres_texte !== ''):
    $nb_photos = count($pres_photos);
?>
<section class="section bout-pres">
    <div class="conteneur">
        <?php if ($pres_texte !== ''): ?>
        <div class="bout-pres-grille">
            <div class="bout-pres-texte reveal">
                <span class="eyebrow"><?php echo esc_html($bout('bout_pres_surtitre', 'Sur place à Matane')); ?></span>
                <h2><?php echo esc_html($bout('bout_pres_titre', 'La boutique du Vivier')); ?></h2>
                <?php foreach (preg_split('/\n\s*\n/', $pres_texte) as $par): if (trim($par) === '') continue; ?>
                    <p><?php echo nl2br(esc_html(trim($par))); ?></p>
                <?php endforeach; ?>
            </div>
            <?php if ($pres_photos): ?>
            <div class="bout-galerie reveal reveal-delai-1" data-nb="<?php echo esc_attr(min($nb_photos, 5)); ?>">
                <?php foreach ($pres_photos as $photo): ?>
                    <figure><img src="<?php echo esc_url($photo['sizes']['large'] ?? $photo['url']); ?>"
                                 alt="<?php echo esc_attr($photo['alt'] ?: 'La boutique du Vivier'); ?>" loading="lazy"></figure>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="section-titre reveal">
            <p class="eyebrow"><?php echo esc_html($bout('bout_pres_surtitre', 'Sur place à Matane')); ?></p>
            <h2><?php echo esc_html($bout('bout_pres_titre', 'La boutique du Vivier')); ?></h2>
        </div>
        <div class="bout-galerie bout-galerie--pleine reveal" data-nb="<?php echo esc_attr(min($nb_photos, 5)); ?>">
            <?php foreach ($pres_photos as $photo): ?>
                <figure><img src="<?php echo esc_url($photo['sizes']['large'] ?? $photo['url']); ?>"
                             alt="<?php echo esc_attr($photo['alt'] ?: 'La boutique du Vivier'); ?>" loading="lazy"></figure>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>

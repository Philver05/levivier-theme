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
     FILTRE PAR CATÉGORIE — même bande que Produits Maison,
     visible seulement s'il existe des catégories boutique
====================================================== -->
<?php
$categories_boutique = get_terms([
    'taxonomy'   => 'categorie_boutique',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);
?>
<?php if ($categories_boutique && !is_wp_error($categories_boutique)): ?>
<div class="pm-filtres-wrap">
    <div class="conteneur">
        <nav class="pm-filtres" aria-label="Filtrer par catégorie">
            <a href="#articles" class="pm-filtre filtre-lien actif" data-cat="tout">Tout voir</a>
            <?php foreach ($categories_boutique as $cat): ?>
                <a href="#articles" class="pm-filtre filtre-lien" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
<?php endif; ?>

<!-- ======================================================
     ARTICLES BOUTIQUE — sans titre de section (l'en-tête de
     page joue déjà ce rôle)
====================================================== -->
<section class="section produits" id="articles">
    <div class="conteneur">

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
     LA BOUTIQUE EN PHOTOS — carrousel animé, avant le trio
     Valeurs (demande de Philippe) ; n'apparaît que si des
     photos sont présentes
====================================================== -->
<?php
$carr_photos = $bout('bout_photos', []);
if (!is_array($carr_photos)) $carr_photos = [];
$carr_texte  = trim($bout('bout_pres_texte', ''));
if ($carr_photos):
?>
<section class="section">
    <div class="conteneur">
        <div class="section-titre reveal">
            <p class="eyebrow"><?php echo esc_html($bout('bout_pres_surtitre', 'Sur place à Matane')); ?></p>
            <h2><?php echo esc_html($bout('bout_pres_titre', 'La boutique du Vivier')); ?></h2>
            <?php if ($carr_texte !== ''): ?>
                <p><?php echo nl2br(esc_html($carr_texte)); ?></p>
            <?php endif; ?>
        </div>

        <div class="carrousel reveal" data-autoplay="5000">
            <div class="carrousel-piste">
                <?php foreach ($carr_photos as $i => $photo): ?>
                    <figure class="carrousel-slide<?php echo $i === 0 ? ' actif' : ''; ?>">
                        <img src="<?php echo esc_url($photo['sizes']['large'] ?? $photo['url']); ?>"
                             alt="<?php echo esc_attr($photo['alt'] ?: 'La boutique du Vivier'); ?>"
                             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                    </figure>
                <?php endforeach; ?>
            </div>
            <?php if (count($carr_photos) > 1): ?>
            <button type="button" class="carrousel-fleche carrousel-precedent" aria-label="Photo précédente">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </button>
            <button type="button" class="carrousel-fleche carrousel-suivant" aria-label="Photo suivante">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
            </button>
            <div class="carrousel-dots" role="tablist" aria-label="Photos de la boutique">
                <?php foreach ($carr_photos as $i => $photo): ?>
                    <button type="button" class="carrousel-dot<?php echo $i === 0 ? ' actif' : ''; ?>"
                            role="tab" aria-label="Photo <?php echo esc_attr($i + 1); ?>"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     VALEURS — trio à icônes, après le contenu et avant la
     bande de clôture (demande de Philippe)
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

<?php get_footer(); ?>

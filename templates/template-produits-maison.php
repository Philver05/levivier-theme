<?php
/*
Template Name: Produits Maison
*/
get_header();

if (have_posts()) the_post();

/* URL de la page Commander */
$pages_commander = get_pages([
    'meta_key'    => '_wp_page_template',
    'meta_value'  => 'templates/template-commander.php',
    'post_status' => 'publish',
    'number'      => 1,
]);
$url_commander = !empty($pages_commander) ? get_permalink($pages_commander[0]->ID) : '#';

/* URL du bon de commande Produits Maison (cible des boutons par famille) */
$pages_bon_pm = get_pages([
    'meta_key'    => '_wp_page_template',
    'meta_value'  => 'templates/template-bon-pm.php',
    'post_status' => 'publish',
    'number'      => 1,
]);
$url_bon_pm = !empty($pages_bon_pm) ? get_permalink($pages_bon_pm[0]->ID) : '';

/* Familles de produits maison */
$familles = new WP_Query([
    'post_type'      => 'famille_maison',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
]);

/* Catégories utilisées (pour les boutons de filtre) */
$cats_utilisees = [];
if ($familles->have_posts()) {
    foreach ($familles->posts as $famille) {
        $terms = get_the_terms($famille->ID, 'categorie_famille');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $t) {
                $cats_utilisees[$t->term_id] = $t;
            }
        }
    }
}
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
        <?php if (get_the_content()) the_content(); ?>
    </div>
</section>

<!-- ======================================================
     FILTRE PAR CATÉGORIE
====================================================== -->
<?php if (!empty($cats_utilisees)): ?>
<div class="pm-filtres-wrap">
    <div class="conteneur">
        <div class="pm-filtres" role="group" aria-label="Filtrer par catégorie">
            <?php /* Pas de pilule « Tout voir » (demande de Philippe) : la
                     première catégorie est active par défaut, et filtre
                     déjà la page au chargement (voir le script plus bas). */
            $premiere_cat = true;
            foreach ($cats_utilisees as $cat): ?>
                <button class="pm-filtre<?php echo $premiere_cat ? ' actif' : ''; ?>" data-filtre="<?php echo esc_attr($cat->slug); ?>">
                    <?php echo esc_html($cat->name); ?>
                </button>
            <?php $premiere_cat = false; endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ======================================================
     ARTICLES PAR FAMILLE
====================================================== -->
<?php if ($familles->have_posts()):
    $i = 0;
    while ($familles->have_posts()): $familles->the_post();
        $cta_label = (function_exists('get_field') ? get_field('famille_cta_label') : '') ?: 'Commander';

        /* Cible du bouton, par ordre de priorité :
           1. Lien personnalisé saisi par Marie (famille_cta_url) — total contrôle.
           2. Bon de commande Produits Maison filtré sur la catégorie choisie,
              si cette page est publiée dans WP (pas encore le cas actuellement).
           3. Repli par défaut : le bon Prêt à manger, où les focaccias/amarettis
              et futures familles sont en pratique déjà commandables (demande
              de Philippe, 22 juillet). */
        $cta_url_perso = function_exists('get_field') ? get_field('famille_cta_url') : '';
        $cta_cat       = function_exists('get_field') ? get_field('famille_cta_categorie') : null;
        if ($cta_url_perso) {
            $url_cta = $cta_url_perso;
        } elseif ($url_bon_pm) {
            $url_cta = ($cta_cat && !is_wp_error($cta_cat) && !empty($cta_cat->slug))
                ? add_query_arg('cat', $cta_cat->slug, $url_bon_pm)
                : $url_bon_pm;
        } else {
            $url_cta = home_url('/pret-a-manger/');
        }
        $thumb_id  = get_post_thumbnail_id();
        $thumb     = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'large') : null;
        $inverse   = ($i % 2 === 1) ? 'pm-article-inverse' : '';
        $fond      = ($i % 2 === 1) ? 'pm-section-pair' : '';

        $post_cats = get_the_terms(get_the_ID(), 'categorie_famille');
        $data_cats = '';
        if ($post_cats && !is_wp_error($post_cats)) {
            $data_cats = implode(' ', array_column((array) $post_cats, 'slug'));
        }

        $i++;
?>
<section class="section pm-article-section <?php echo esc_attr($fond); ?>"
         id="famille-<?php echo esc_attr(get_post_field('post_name')); ?>"
         data-categories="<?php echo esc_attr($data_cats); ?>">
    <div class="conteneur">
        <div class="pm-article <?php echo esc_attr($inverse); ?> reveal">

            <div class="pm-article-visuel">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb[0]); ?>"
                         alt="<?php echo esc_attr(get_the_title()); ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="pm-article-placeholder" aria-hidden="true"></div>
                <?php endif; ?>
            </div>

            <div class="pm-article-corps">
                <?php if ($post_cats && !is_wp_error($post_cats)): ?>
                    <span class="pm-article-eyebrow"><?php echo esc_html($post_cats[0]->name); ?></span>
                <?php else: ?>
                    <span class="pm-article-eyebrow">Produits maison</span>
                <?php endif; ?>
                <h2 class="pm-article-titre"><?php the_title(); ?></h2>

                <?php if (get_the_content()): ?>
                    <div class="pm-article-texte">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

                <a href="<?php echo esc_url($url_cta); ?>"
                   class="btn btn-primaire pm-article-cta">
                    <?php echo esc_html($cta_label); ?>
                </a>
            </div>

        </div>
    </div>
</section>
<?php endwhile;
    wp_reset_postdata();

else:
?>
<section class="section pm-article-section">
    <div class="conteneur">
        <div class="pm-article reveal">
            <div class="pm-article-placeholder pm-article-visuel" aria-hidden="true"></div>
            <div class="pm-article-corps">
                <span class="pm-article-eyebrow">Produits maison</span>
                <h2 class="pm-article-titre">Nos produits maison</h2>
                <div class="pm-article-texte">
                    <p>Focaccias, amaretti, confitures et autres douceurs préparées sur place au Vivier.</p>
                    <p><em>Pour ajouter une famille, allez dans <strong>Produits Maison</strong> dans le menu WP et créez votre premier article.</em></p>
                </div>
                <a href="<?php echo esc_url($url_commander); ?>" class="btn btn-primaire">Commander</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     CTA GLOBAL
====================================================== -->
<section class="section">
    <div class="conteneur">
        <?php
        /* Textes du panneau CTA éditables (repli = textes actuels) */
        $pm_champ = function ($cle, $defaut) {
            $valeur = function_exists('get_field') ? get_field($cle) : '';
            return $valeur ?: $defaut;
        };
        ?>
        <div class="cta-panel reveal">
            <h2><?php echo esc_html($pm_champ('pm_cta_titre', 'Prêt à commander ?')); ?></h2>
            <p><?php echo esc_html($pm_champ('pm_cta_texte', 'Remplissez votre bon de commande en ligne, on prépare tout pour vous. Récupérez et payez en magasin.')); ?> Une question&nbsp;? Appelez-nous au <a href="<?php echo esc_attr(lv_opt_tel_lien()); ?>" class="cta-tel"><?php echo esc_html(lv_opt('opt_telephone', '(418) 562-5230')); ?></a>.</p>
            <div class="cta-panel-actions">
                <a href="<?php echo esc_url($url_commander); ?>" class="btn btn-clair"><?php echo esc_html($pm_champ('pm_cta_btn', 'Voir tous les bons de commande')); ?></a>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var btns = document.querySelectorAll('.pm-filtre');
    var sections = document.querySelectorAll('.pm-article-section[data-categories]');
    if (!btns.length) return;
    function appliquerFiltre(filtre) {
        sections.forEach(function (s) {
            s.style.display = (filtre === 'tout' || s.dataset.categories.split(' ').includes(filtre)) ? '' : 'none';
        });
    }
    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { b.classList.remove('actif'); });
            btn.classList.add('actif');
            appliquerFiltre(btn.dataset.filtre);
        });
    });
    /* Pas de pilule « Tout voir » : au chargement, la page reflète déjà la
       première catégorie active plutôt que d'afficher toutes les familles. */
    var actif = document.querySelector('.pm-filtre.actif');
    if (actif) appliquerFiltre(actif.dataset.filtre);
})();
</script>

<?php get_footer(); ?>

<?php
/*
Template Name: Épicerie Africaine
*/
get_header();

if (have_posts()) the_post();

/* ----------------------------------------------------------
   Champs ACF + valeurs par défaut — tout éditable dans l'admin
---------------------------------------------------------- */
$intro = get_field('afr_intro') ?: "Au sous-sol du Vivier, Okavi vous propose épices, sauces et trésors culinaires venus d'Afrique.\nUne épicerie de quartier pensée et animée par Viviane, pour faire voyager Matane.";

/* Logo Okavi du héros : même chaîne de priorité que le logo hero de
   l'accueil (front-page.php) — champ ACF > fichier fourni par Philippe
   le 22 juillet > rien (pas de repli logo du header, trop petit/inadapté
   à côté du texte). */
$logo_afr_champ = get_field('afr_logo');
if ($logo_afr_champ && !empty($logo_afr_champ['url'])) {
    $logo_afr_url = $logo_afr_champ['url'];
} else {
    $logo_afr_fichier = get_stylesheet_directory() . '/assets/images/logo-okavi.png';
    $logo_afr_url = file_exists($logo_afr_fichier) ? get_stylesheet_directory_uri() . '/assets/images/logo-okavi.png' : '';
}

/* Portrait de Viviane (bande blanche en bas de page) : le texte principal
   vit dans l'éditeur WP de la page (comme le reste du site). Tant que
   Marie n'y a rien écrit, on affiche un portrait par défaut documenté
   (recherché en ligne le 22 juillet : Radio-Canada, Le Soir Matanie,
   bienvenuedansmamatanie.com, SANAM) plutôt que de cacher la section. */
$pres_image = get_field('afr_presentation_image');
$pres_texte = trim(wp_strip_all_tags(get_the_content()));
$pres_defaut = "Derrière l'épicerie africaine du Vivier se trouve Viviane Oueko Kamga, arrivée à Matane en 2010. Passionnée de mode et de cuisine, elle fonde en 2015 le groupe Okavi pour faire découvrir le meilleur de l'Afrique à sa région d'adoption : épices, saveurs, coiffure et créations textiles. Une partie des profits de l'épicerie soutient des orphelinats au Cameroun. Viviane est aussi de celles qui font vivre la communauté à Matane, fidèle à un proverbe qui lui tient à cœur : il faut tout un village pour élever un enfant.";
$portrait_fb = get_field('afr_portrait_facebook');
$portrait_fb_label = get_field('afr_portrait_facebook_label') ?: 'Suivre Okavi sur Facebook';

/* Spécialités (4 cartes, classées par catégorie de produits). Une
   spécialité sans photo n'est pas affichée (demande de Philippe,
   29 juillet : éviter une carte avec un placeholder vide). */
$specialites = array_values(array_filter([
    [
        'titre' => get_field('afr_spec1_titre') ?: 'Épices & condiments',
        'texte' => get_field('afr_spec1_texte') ?: 'Mélanges parfumés, piments, gingembre, curcuma et condiments pour relever tous vos plats.',
        'image' => get_field('afr_spec1_image'),
    ],
    [
        'titre' => get_field('afr_spec2_titre') ?: 'Sauces & pâtes',
        'texte' => get_field('afr_spec2_texte') ?: 'Sauces tomate épicées, pâtes d\'arachide, huile de palme et bases pour vos recettes traditionnelles.',
        'image' => get_field('afr_spec2_image'),
    ],
    [
        'titre' => get_field('afr_spec3_titre') ?: 'Féculents & farines',
        'texte' => get_field('afr_spec3_texte') ?: 'Farines de manioc, igname, plantain, riz parfumé et féculents au cœur de la cuisine africaine.',
        'image' => get_field('afr_spec3_image'),
    ],
    [
        'titre' => get_field('afr_spec4_titre') ?: 'Beauté & soins',
        'texte' => get_field('afr_spec4_texte') ?: 'Produits capillaires, cosmétiques et soins traditionnels inspirés du continent.',
        'image' => get_field('afr_spec4_image'),
    ],
], function ($spec) { return !empty($spec['image']); }));

?>

<!-- ======================================================
     EN-TÊTE — logo Okavi à côté du texte de présentation
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <div class="hero-grille">
            <div class="hero-texte reveal">
                <h1 class="page-entete-titre-script"><?php the_title(); ?></h1>
                <p><?php echo nl2br(esc_html($intro)); ?></p>
            </div>
            <?php if ($logo_afr_url): ?>
            <div class="hero-logo reveal">
                <img src="<?php echo esc_url($logo_afr_url); ?>" alt="Les saveurs d'Afrique &amp; d'ailleurs by Okavi">
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
/* ----------------------------------------------------------
   PRODUITS AFRICAINS (dormant) : dès qu'une catégorie parente
   « Épicerie africaine » (slug epicerie-africaine) existe dans
   les catégories de produits et contient des produits, la page
   gagne la même bande de filtres que Produits Maison + une
   grille de produits filtrable. Rien à recoder ce jour-là :
   Marie crée la catégorie, ses sous-catégories et les produits.
---------------------------------------------------------- */
$afr_parent = get_term_by('slug', 'epicerie-africaine', 'categorie_produit');
if (!$afr_parent || is_wp_error($afr_parent)) {
    $afr_parent = get_term_by('name', 'Épicerie africaine', 'categorie_produit');
}
$afr_produits = null;
$afr_cats     = [];
if ($afr_parent && !is_wp_error($afr_parent)) {
    $afr_produits = new WP_Query([
        'post_type'      => 'produit',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => [[
            'taxonomy'         => 'categorie_produit',
            'field'            => 'term_id',
            'terms'            => $afr_parent->term_id,
            'include_children' => true,
        ]],
    ]);
    if ($afr_produits->have_posts()) {
        $afr_cats = get_terms([
            'taxonomy'   => 'categorie_produit',
            'parent'     => $afr_parent->term_id,
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        if (is_wp_error($afr_cats)) $afr_cats = [];
    }
}
?>
<?php if ($afr_produits && $afr_produits->have_posts()): ?>

<!-- Bande de filtres (même gabarit que Produits Maison, charte épices via CSS) -->
<?php if ($afr_cats): ?>
<div class="pm-filtres-wrap">
    <div class="conteneur">
        <nav class="pm-filtres" aria-label="Filtrer par catégorie">
            <?php $premiere_cat = true; foreach ($afr_cats as $cat): ?>
                <a href="#produits-afr" class="pm-filtre filtre-lien<?php echo $premiere_cat ? ' actif' : ''; ?>" data-cat="<?php echo esc_attr($cat->slug); ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php $premiere_cat = false; endforeach; ?>
        </nav>
    </div>
</div>
<?php endif; ?>

<!-- Grille des produits africains (id grille-produits = filtre main.js) -->
<section class="section produits" id="produits-afr">
    <div class="conteneur">
        <div class="grille-cartes" id="grille-produits">
            <?php while ($afr_produits->have_posts()): $afr_produits->the_post();
                get_template_part('parts/produit', 'card');
            endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     SPÉCIALITÉS — 4 cartes classées par catégorie (équivalent
     de « Nos produits »)
====================================================== -->
<section class="section produits">
    <div class="conteneur">
        <?php /* Pas de titre de section : l'en-tête de page joue déjà ce rôle
                 (demande de Philippe) */ ?>

        <div class="grille-prod">
            <?php $i = 0; foreach ($specialites as $spec): $delai = $i ? ' reveal-delai-' . $i : ''; $i++; ?>
            <article class="carte-prod reveal<?php echo $delai; ?>">
                <div class="photo">
                    <img src="<?php echo esc_url($spec['image']['sizes']['medium_large'] ?? $spec['image']['url']); ?>"
                         alt="<?php echo esc_attr($spec['image']['alt'] ?: $spec['titre']); ?>">
                </div>
                <div class="corps">
                    <h3><?php echo esc_html($spec['titre']); ?></h3>
                    <p><?php echo esc_html($spec['texte']); ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ======================================================
     TROIS ATOUTS — trio compact à icônes, après le contenu et
     avant la bande de clôture (demande de Philippe)
====================================================== -->
<section class="section section-compacte">
    <div class="conteneur">
        <div class="engagements-grille cols-3">
            <div class="engagement reveal">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1-2-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.3 1-3a2.5 2.5 0 0 0 2.5 2.5Z"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout1_titre') ?: 'Produits authentiques'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout1_texte') ?: 'Épices, sauces et féculents choisis auprès de fournisseurs de confiance, fidèles aux saveurs du continent.'); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M8 12V9a4 4 0 0 1 8 0v3"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout2_titre') ?: 'Arrivages réguliers'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout2_texte') ?: 'Des produits secs et frais renouvelés au fil des arrivages, pour cuisiner sans compromis.'); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V10"/><path d="M12 13c0-4-3-6-7-6 0 4 3 6 7 6Z"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7Z"/></svg>
                </span>
                <h3><?php echo esc_html(get_field('afr_atout3_titre') ?: 'Conseils & recettes'); ?></h3>
                <p><?php echo esc_html(get_field('afr_atout3_texte') ?: 'Une équipe qui connaît ses produits et partage volontiers idées et techniques de préparation.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     PORTRAIT — qui est Viviane (bande blanche finale, équivalent
     de la bande Producteurs de la page Épicerie), texte = éditeur
     WP en priorité, portrait par défaut documenté sinon
====================================================== -->
<section class="section bande-blanche">
    <div class="conteneur">
        <div class="section-titre">
            <h2><?php
                $afr_titre_portrait = esc_html(get_field('afr_pres_titre') ?: "Derrière l'épicerie : Viviane");
                echo str_replace(' :', "\u{00A0}:", $afr_titre_portrait);
            ?></h2>
        </div>
        <div class="apropos-split">
            <div class="page-prose reveal" style="margin:0">
                <?php if ($pres_texte !== ''): the_content(); else: ?>
                    <p><?php echo esc_html($pres_defaut); ?></p>
                <?php endif; ?>
                <?php if ($portrait_fb): ?>
                    <p><a class="afr-portrait-lien" href="<?php echo esc_url($portrait_fb); ?>" target="_blank" rel="noopener"><?php echo esc_html($portrait_fb_label); ?></a></p>
                <?php endif; ?>
            </div>
            <div class="apropos-media reveal">
                <div class="cadre">
                    <?php if ($pres_image): ?>
                        <img src="<?php echo esc_url($pres_image['sizes']['large'] ?? $pres_image['url']); ?>" alt="<?php echo esc_attr($pres_image['alt'] ?: 'Viviane, fondatrice d\'Okavi'); ?>">
                    <?php else: ?>
                        <div class="carte-vide" style="height:100%">
                            <span style="font-family:var(--f-script);font-size:4.5rem;color:var(--afr-rouille)">V</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

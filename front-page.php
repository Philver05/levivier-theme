<?php
/* Calcul du logo hero AVANT get_header() pour injecter un <link rel="preload">
   dans le <head> via wp_head — le logo est l'élément LCP de l'accueil.
   Sert le WebP si disponible (theme asset), PNG sinon (ACF ou repli). */
$logo_hero_champ_pre = function_exists('get_field') ? get_field('acc_hero_logo') : null;
$logo_use_webp = false;
if ($logo_hero_champ_pre && !empty($logo_hero_champ_pre['url'])) {
    $logo_url_pre  = $logo_hero_champ_pre['url'];
    $logo_url_png  = $logo_url_pre;
} else {
    $webp_fichier = get_stylesheet_directory() . '/assets/images/logo-complet.webp';
    $png_fichier  = get_stylesheet_directory() . '/assets/images/logo-complet.png';
    if (file_exists($webp_fichier)) {
        $logo_url_pre = get_stylesheet_directory_uri() . '/assets/images/logo-complet.webp';
        $logo_url_png = file_exists($png_fichier) ? get_stylesheet_directory_uri() . '/assets/images/logo-complet.png' : $logo_url_pre;
        $logo_use_webp = true;
    } elseif (file_exists($png_fichier)) {
        $logo_url_pre = get_stylesheet_directory_uri() . '/assets/images/logo-complet.png';
        $logo_url_png = $logo_url_pre;
    } else {
        $logo_url_pre = $logo_url_png = '';
    }
}
if ($logo_url_pre) {
    add_action('wp_head', function () use ($logo_url_pre, $logo_use_webp) {
        $type = $logo_use_webp ? ' type="image/webp"' : '';
        echo '<link rel="preload" href="' . esc_url($logo_url_pre) . '" as="image"' . $type . ' fetchpriority="high">' . "\n";
    }, 2);
}

$hero_bg_mob_pre     = function_exists('get_field') ? get_field('acc_hero_bg_mobile') : null;
$hero_bg_mob_url_pre = !empty($hero_bg_mob_pre['sizes']['large']) ? $hero_bg_mob_pre['sizes']['large']
                     : (!empty($hero_bg_mob_pre['url']) ? $hero_bg_mob_pre['url'] : '');
if ($hero_bg_mob_url_pre) {
    add_action('wp_head', function () use ($hero_bg_mob_url_pre) {
        $u = esc_url($hero_bg_mob_url_pre);
        echo '<style id="lv-hero-bg-m">.hero-cta-mobile{display:none!important}@media(max-width:960px){' .
            '.hero-avec-bg{background-image:linear-gradient(rgba(15,30,10,.72),rgba(15,30,10,.72)),url("' . $u . '");background-size:cover;background-position:center;min-height:65dvh}' .
            '.hero-avec-bg .hero-logo{display:none!important}' .
            '.hero-avec-bg .hero-grille{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;padding-block:3rem 2.5rem}' .
            '.hero-avec-bg h1{color:#f9f5f0!important;text-align:center}' .
            '.hero-avec-bg h1 .script{color:#f5ddd5!important}' .
            '.hero-avec-bg .hero-texte-sub{color:rgba(249,245,240,.85)!important;text-align:center}' .
            '.hero-avec-bg .hero-cta-mobile{display:flex!important;flex-direction:column;gap:.75rem;width:100%;max-width:340px;margin:0 auto}' .
            '.hero-avec-bg .hero-cta-m-btn{display:block;background:#b85c50;color:#fff;padding:.85rem 1.25rem;border-radius:8px;font-size:1.05rem;font-weight:600;text-decoration:none;text-align:center}' .
            '.hero-avec-bg .hero-cta-m-ligne{display:block;border:1.5px solid rgba(249,245,240,.55);color:#f9f5f0;padding:.75rem 1.25rem;border-radius:8px;font-size:.95rem;font-weight:500;text-decoration:none;text-align:center}' .
            '.intro-actions{display:none!important}' .
        '}</style>' . "\n";
    }, 99);
}
?>
<?php get_header(); ?>

<?php
if (have_posts()) the_post();

$slogan  = get_bloginfo('description') ?: 'Cultivons un avenir durable';
$titre   = wp_kses(str_replace('durable', '<span class="script">durable</span>', esc_html($slogan)), ['span' => ['class' => []], 'br' => []]);
$lien_ep = get_page_by_path('epicerie');
$url_ep  = $lien_ep ? get_permalink($lien_ep) : home_url('/');
$lien_boutique = get_page_by_path('boutique');
$url_boutique  = $lien_boutique ? get_permalink($lien_boutique) : home_url('/boutique/');
$lien_pm = get_page_by_path('produits-maison');
$url_pm  = $lien_pm ? get_permalink($lien_pm) : home_url('/produits-maison/');
$lien_afr = get_page_by_path('epicerie-africaine');
$url_afr  = $lien_afr ? get_permalink($lien_afr) : home_url('/epicerie-africaine/');
$lien_pam = get_page_by_path('pret-a-manger');
$url_pam  = $lien_pam ? get_permalink($lien_pam) : home_url('/pret-a-manger/');
$lien_lofts = get_page_by_path('lofts');
$url_lofts  = $lien_lofts ? get_permalink($lien_lofts) : home_url('/lofts/');

/* Champ ACF de la page d'accueil, avec repli si vide ou ACF inactif */
$acc = function ($cle, $defaut) {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};

/* Carrousel "Découvrir Le Vivier" : une diapo par section du site.
   Chaque diapo est un [titre ACF, url, image ACF]. Une diapo sans image
   n'est pas affichée pour éviter un rectangle vide. */
$carrousel_slides = array_values(array_filter([
    ['titre' => $acc('acc_carr1_titre', 'Épicerie'),                'url' => $url_ep,       'image' => function_exists('get_field') ? get_field('acc_carr1_image') : null],
    ['titre' => $acc('acc_carr2_titre', 'Boutique'),                'url' => $url_boutique, 'image' => function_exists('get_field') ? get_field('acc_carr2_image') : null],
    ['titre' => $acc('acc_carr3_titre', 'Produits Maison'),         'url' => $url_pm,       'image' => function_exists('get_field') ? get_field('acc_carr3_image') : null],
    ['titre' => $acc('acc_carr4_titre', 'Épicerie Africaine'),      'url' => $url_afr,      'image' => function_exists('get_field') ? get_field('acc_carr4_image') : null],
    ['titre' => $acc('acc_carr5_titre', 'Prêt à manger'),           'url' => $url_pam,      'image' => function_exists('get_field') ? get_field('acc_carr5_image') : null],
    ['titre' => $acc('acc_carr6_titre', 'Les lofts de la rivière'), 'url' => $url_lofts,    'image' => function_exists('get_field') ? get_field('acc_carr6_image') : null],
], function ($slide) { return !empty($slide['image']); }));

/* Logo complet du hero (roseau + « Le Vivier » + « Épicerie · Boutique »),
   distinct du logo compact du header (Réglages > Identité du site) : celui-ci
   est prévu pour un affichage large. Priorité : champ ACF > fichier
   logo-complet.png du thème > logo du header en dernier repli. */
$logo_hero_champ = function_exists('get_field') ? get_field('acc_hero_logo') : null;
$logo_url_webp   = '';
if ($logo_hero_champ && !empty($logo_hero_champ['url'])) {
    $logo_url = $logo_hero_champ['url'];
} else {
    $logo_fichier      = get_stylesheet_directory() . '/assets/images/logo-complet.png';
    $logo_fichier_webp = get_stylesheet_directory() . '/assets/images/logo-complet.webp';
    if (file_exists($logo_fichier_webp)) {
        $logo_url_webp     = get_stylesheet_directory_uri() . '/assets/images/logo-complet.webp';
        $logo_fichier_500  = get_stylesheet_directory() . '/assets/images/logo-complet-500.webp';
        $logo_url_webp_500 = file_exists($logo_fichier_500)
            ? get_stylesheet_directory_uri() . '/assets/images/logo-complet-500.webp'
            : '';
    }
    if (file_exists($logo_fichier)) {
        $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo-complet.png';
    } else {
        $logo_id  = get_theme_mod('custom_logo');
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    }
}

$hero_bg_mob     = function_exists('get_field') ? get_field('acc_hero_bg_mobile') : null;
$hero_bg_mob_url = !empty($hero_bg_mob['sizes']['large']) ? $hero_bg_mob['sizes']['large']
                 : (!empty($hero_bg_mob['url']) ? $hero_bg_mob['url'] : '');
?>

<!-- ============================ HERO ============================ -->
<section class="hero<?php echo $hero_bg_mob_url ? ' hero-avec-bg' : ''; ?>"
         id="accueil">
    <span class="arche arche-terra" aria-hidden="true"></span>
    <span class="arche arche-olive" aria-hidden="true"></span>
    <div class="conteneur hero-grille">

        <div class="hero-texte">
            <h1><?php echo $titre; ?></h1>
            <p class="hero-texte-sub"><?php echo esc_html($acc('acc_hero_texte', 'Au cœur de Matane, Le Vivier fait de l\'achat responsable un choix simple et inspirant.')); ?></p>
        </div>

        <?php if ($logo_url): ?>
        <div class="hero-logo">
            <?php if ($logo_use_webp): ?>
            <picture>
                <?php if (!empty($logo_url_webp_500)): ?>
                <source srcset="<?php echo esc_url($logo_url_webp_500); ?> 500w, <?php echo esc_url($logo_url_webp); ?> 1000w"
                        sizes="(max-width: 640px) 300px, 600px"
                        type="image/webp">
                <?php else: ?>
                <source srcset="<?php echo esc_url($logo_url_webp); ?>" type="image/webp">
                <?php endif; ?>
                <img src="<?php echo esc_url($logo_url); ?>"
                     alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                     width="1000" height="1000"
                     fetchpriority="high"
                     loading="eager">
            </picture>
            <?php else: ?>
            <img src="<?php echo esc_url($logo_url); ?>"
                 alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                 width="1000" height="1000"
                 fetchpriority="high"
                 loading="eager">
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($hero_bg_mob_url): ?>
        <div class="hero-cta-mobile">
            <a href="<?php echo esc_url($url_ep); ?>" class="hero-cta-m-btn"><?php echo esc_html($acc('acc_hero_btn1', 'Découvrir l\'épicerie')); ?></a>
            <a href="<?php echo esc_url($url_boutique); ?>" class="hero-cta-m-ligne"><?php echo esc_html($acc('acc_hero_btn2', 'Découvrir la Boutique')); ?></a>
            <a href="<?php echo esc_url($url_pm); ?>" class="hero-cta-m-ligne"><?php echo esc_html($acc('acc_hero_btn3', 'Découvrir nos produits maison')); ?></a>
        </div>
        <?php endif; ?>

    </div>
    <div class="hero-scroll" aria-hidden="true">
        <span><?php echo esc_html($acc('acc_hero_scroll', 'Découvrir')); ?></span>
        <span class="souris"></span>
    </div>
</section>

<!-- ===================== DÉCOUVRIR LE VIVIER ===================== -->
<section class="section intro" id="epicerie">
    <?php if (!empty($carrousel_slides)): ?>
    <div class="conteneur">
        <div class="carrousel carrousel--sobre reveal" data-autoplay="5000">
            <div class="carrousel-piste">
                <?php foreach ($carrousel_slides as $i => $slide): ?>
                    <figure class="carrousel-slide<?php echo $i === 0 ? ' actif' : ''; ?>">
                        <a href="<?php echo esc_url($slide['url']); ?>">
                            <img src="<?php echo esc_url($slide['image']['sizes']['large'] ?? $slide['image']['url']); ?>"
                                 alt="<?php echo esc_attr($slide['image']['alt'] ?: $slide['titre']); ?>"
                                 loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                            <span class="carrousel-legende"><?php echo esc_html($slide['titre']); ?></span>
                        </a>
                    </figure>
                <?php endforeach; ?>
            </div>
            <?php if (count($carrousel_slides) > 1): ?>
            <button type="button" class="carrousel-fleche carrousel-precedent" aria-label="Section précédente">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </button>
            <button type="button" class="carrousel-fleche carrousel-suivant" aria-label="Section suivante">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
            </button>
            <div class="carrousel-dots" role="tablist" aria-label="Sections du Vivier">
                <?php foreach ($carrousel_slides as $i => $slide): ?>
                    <button type="button" class="carrousel-dot<?php echo $i === 0 ? ' actif' : ''; ?>"
                            role="tab" aria-label="<?php echo esc_attr($slide['titre']); ?>"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- Boutons Découvrir : déplacés du hero, juste après le carrousel -->
    <div class="conteneur">
        <div class="intro-actions reveal reveal-delai-2">
            <a href="<?php echo esc_url($url_ep); ?>" class="btn btn-primaire"><?php echo esc_html($acc('acc_hero_btn1', 'Découvrir l\'épicerie')); ?></a>
            <a href="<?php echo esc_url($url_boutique); ?>" class="btn btn-ligne"><?php echo esc_html($acc('acc_hero_btn2', 'Découvrir la Boutique')); ?></a>
            <a href="<?php echo esc_url($url_pm); ?>" class="btn btn-ligne"><?php echo esc_html($acc('acc_hero_btn3', 'Découvrir nos produits maison')); ?></a>
        </div>
    </div>
</section>

<!-- ========================= ENGAGEMENTS ========================= -->
<section class="section engagements">
    <div class="conteneur">
        <div class="section-titre section-titre--grand reveal">
            <h2><?php echo esc_html($acc('acc_eng_titre', 'Nos Engagements')); ?></h2>
        </div>
        <div class="engagements-grille">
            <div class="engagement reveal">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,34 C20,22 26,12 34,8 C32,20 28,30 20,34 Z"/><path d="M20,34 C20,24 15,16 8,12 C11,22 14,30 20,34 Z"/><path d="M20,34 L20,20"/></svg></div>
                <h3><?php echo esc_html($acc('acc_eng1_titre', 'Local')); ?></h3>
                <p><?php echo esc_html($acc('acc_eng1_texte', 'Des producteurs locaux, de la Matanie à la Gaspésie et au Bas-Saint-Laurent.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,34 L20,16"/><path d="M20,20 C20,14 15,9 8,9 C8,15 13,20 20,20 Z"/><path d="M20,18 C20,11 25,6 32,6 C32,13 27,18 20,18 Z"/></svg></div>
                <h3><?php echo esc_html($acc('acc_eng2_titre', 'Biologique')); ?></h3>
                <p><?php echo esc_html($acc('acc_eng2_texte', 'Une sélection naturelle, choisie pour sa qualité et son impact positif.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M11,14 L29,14 L27,33 C27,34.5 26,35 25,35 L15,35 C14,35 13,34.5 13,33 Z"/><path d="M15,14 L15,10 C15,8 16,7 18,7 L22,7 C24,7 25,8 25,10 L25,14"/><path d="M17,21 L23,21 M17,27 L23,27"/></svg></div>
                <h3><?php echo esc_html($acc('acc_eng3_titre', 'Vrac')); ?></h3>
                <p><?php echo esc_html($acc('acc_eng3_texte', 'Une façon simple de réduire les emballages et le gaspillage.')); ?></p>
            </div>
            <div class="engagement reveal reveal-delai-3">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,33 C8,24 6,16 11,12 C15,9 19,12 20,16 C21,12 25,9 29,12 C34,16 32,24 20,33 Z"/><path d="M20,16 L20,22"/></svg></div>
                <h3><?php echo esc_html($acc('acc_eng4_titre', 'Communauté')); ?></h3>
                <p><?php echo esc_html($acc('acc_eng4_texte', 'Valoriser l\'achat local et soutenir notre économie régionale.')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ========================== PRODUITS ========================== -->
<?php
$produits_vedettes = new WP_Query([
    'post_type'      => 'produit',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'rand',
]);
if ($produits_vedettes->have_posts()): ?>
<section class="section produits" id="produits">
    <div class="conteneur">
        <div class="section-titre reveal">
            <p class="eyebrow"><?php echo esc_html($acc('acc_prod_surtitre', 'La boutique')); ?></p>
            <h2><?php echo esc_html($acc('acc_prod_titre', 'Nos produits du moment')); ?></h2>
            <p><?php echo esc_html($acc('acc_prod_texte', 'Une sélection fraîche de nos rayons.')); ?></p>
        </div>
        <div class="grille-cartes">
            <?php while ($produits_vedettes->have_posts()): $produits_vedettes->the_post();
                get_template_part('parts/produit', 'card');
            endwhile; wp_reset_postdata(); ?>
        </div>
        <div class="section-cta reveal"><a href="<?php echo esc_url($url_ep); ?>" class="btn btn-fantome"><?php echo esc_html($acc('acc_prod_btn', 'Voir tous les produits')); ?></a></div>
    </div>
</section>
<?php endif; ?>

<!-- ========================= NEWSLETTER ========================= -->
<section class="section newsletter" id="newsletter">
    <div class="conteneur">
        <div class="newsletter-carte reveal">
            <p class="eyebrow"><?php echo esc_html($acc('acc_news_surtitre', 'Infolettre')); ?></p>
            <h2><?php echo esc_html($acc('acc_news_titre', 'Restez dans la boucle')); ?></h2>
            <p><?php echo esc_html($acc('acc_news_texte', 'Promotions, arrivages et nouvelles de nos producteurs, directement dans votre boîte de courriel.')); ?></p>
            <form class="newsletter-form" action="#" method="post">
                <?php wp_nonce_field('newsletter', 'newsletter_nonce'); ?>
                <input type="email" name="courriel" placeholder="votre@courriel.com" aria-label="Votre courriel" required>
                <button type="submit"><?php echo esc_html($acc('acc_news_btn', 'S\'abonner')); ?></button>
            </form>
        </div>
    </div>
</section>

<?php get_footer();

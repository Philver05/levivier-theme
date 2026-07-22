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

/* Champ ACF de la page d'accueil, avec repli si vide ou ACF inactif */
$acc = function ($cle, $defaut) {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};

/* Logo complet du hero (roseau + « Le Vivier » + « Épicerie · Boutique »),
   distinct du logo compact du header (Réglages > Identité du site) : celui-ci
   est prévu pour un affichage large. Priorité : champ ACF (si Marie en
   téléverse un nouveau) > fichier fourni par Philippe le 21 juillet > logo
   du header en dernier repli, pour ne jamais afficher un hero vide. */
$logo_hero_champ = function_exists('get_field') ? get_field('acc_hero_logo') : null;
if ($logo_hero_champ && !empty($logo_hero_champ['url'])) {
    $logo_url = $logo_hero_champ['url'];
} else {
    $logo_fichier = get_stylesheet_directory() . '/assets/images/logo-complet.png';
    if (file_exists($logo_fichier)) {
        $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo-complet.png';
    } else {
        $logo_id  = get_theme_mod('custom_logo');
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    }
}
?>

<!-- ============================ HERO ============================ -->
<section class="hero" id="accueil">
    <span class="arche arche-terra" aria-hidden="true"></span>
    <span class="arche arche-olive" aria-hidden="true"></span>
    <div class="conteneur hero-grille">

        <div class="hero-texte reveal">
            <h1><?php echo $titre; ?></h1>
            <p class="hero-texte-sub"><?php echo esc_html($acc('acc_hero_texte', 'Au cœur de Matane, Le Vivier fait de l\'achat responsable un choix simple et inspirant.')); ?></p>
        </div>

        <?php if ($logo_url): ?>
        <div class="hero-logo reveal reveal-delai-1">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        </div>
        <?php endif; ?>

    </div>
    <div class="hero-scroll" aria-hidden="true">
        <span><?php echo esc_html($acc('acc_hero_scroll', 'Découvrir')); ?></span>
        <span class="souris"></span>
    </div>
</section>

<!-- ============================ INTRO ============================ -->
<section class="section intro" id="epicerie">
    <div class="conteneur intro-grille">
        <div class="intro-texte reveal">

            <?php if (trim(get_the_content())): the_content(); else: ?>
                <p>Au cœur de Matane, Le Vivier rassemble le meilleur du Bas-Saint-Laurent : fruits et légumes de saison, vrac, thés, produits fins et créations d'artisans d'ici. Chaque tablette raconte une rencontre, un savoir-faire, un terroir.</p>
            <?php endif; ?>
        </div>
        <div class="intro-media reveal reveal-delai-1">
            <div class="cadre">
                <?php if (has_post_thumbnail()):
                    the_post_thumbnail('large', ['alt' => get_bloginfo('name')]);
                else: ?>
                    <div aria-hidden="true" style="width:100%;height:100%;min-height:320px;background:var(--sauge-pale)"></div>
                <?php endif; ?>
            </div>
            <?php $badge_intro = $acc('acc_intro_badge', ''); ?>
            <?php if ($badge_intro): ?>
            <span class="intro-badge"><?php echo esc_html($badge_intro); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Boutons Découvrir : déplacés du hero, juste après l'intro
         (à la place du futur carrousel, pas encore prêt) -->
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

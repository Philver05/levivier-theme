<?php get_header(); ?>

<?php
if (have_posts()) the_post();

/* Image de bannière : image d'en-tête WP (Apparence → En-tête), sinon image mise en avant */
$banniere = get_header_image();
if (!$banniere && has_post_thumbnail()) {
    $banniere = get_the_post_thumbnail_url(get_the_ID(), 'full');
}
$slogan = get_bloginfo('description') ?: 'Cultivons un avenir durable, un achat à la fois !';
?>

<!-- ======================================================
     BANNIÈRE PLEINE LARGEUR
====================================================== -->
<section class="banniere-accueil"<?php if ($banniere): ?> style="--banniere-img:url('<?php echo esc_url($banniere); ?>');"<?php endif; ?>>
    <div class="banniere-overlay"></div>
    <div class="banniere-contenu">
        <p class="banniere-eyebrow">Épicerie boutique · Matane</p>
        <h1 class="banniere-titre"><?php echo wp_kses(str_replace('avenir durable', 'avenir<br>durable', esc_html($slogan)), ['br' => []]); ?></h1>
        <div class="hero-actions banniere-actions">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('epicerie'))); ?>" class="bouton-primaire">Découvrir l'épicerie</a>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('commandez'))); ?>" class="bouton-accent">Commander</a>
        </div>
    </div>
    <a href="#contenu-accueil" class="banniere-scroll" aria-label="Défiler vers le bas">
        <span class="banniere-scroll-texte">Découvrir</span>
        <span class="banniere-scroll-souris"></span>
    </a>
</section>

<span id="contenu-accueil"></span>

<!-- ======================================================
     INTRO — hero split (texte + image)
====================================================== -->
<section class="section-hero section-accueil-intro">
    <div class="intro-split">

        <div class="intro-texte">
            <?php the_content(); ?>
            <ul class="intro-trust">
                <li><span>🌱</span> Producteurs locaux</li>
                <li><span>🌿</span> Bio &amp; naturel</li>
                <li><span>♻️</span> Vrac &amp; zéro déchet</li>
            </ul>
        </div>

        <div class="intro-image-cadre">
            <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('large', ['alt' => get_bloginfo('name')]); ?>
            <?php else: ?>
                <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/hero.jpg" alt="Épicerie Le Vivier">
            <?php endif; ?>
            <span class="intro-badge">100% local</span>
        </div>

    </div>
</section>

<!-- ======================================================
     NOS ENGAGEMENTS
====================================================== -->
<section class="section-engagements">
    <div class="engagements-grille">

        <div class="engagement-item">
            <span class="engagement-icone">🌱</span>
            <h3>Local</h3>
            <p>13 producteurs de la région de Matane, Bas-Saint-Laurent</p>
        </div>

        <div class="engagement-item">
            <span class="engagement-icone">🌿</span>
            <h3>Biologique</h3>
            <p>Une sélection naturelle, choisie pour sa qualité et son impact positif</p>
        </div>

        <div class="engagement-item">
            <span class="engagement-icone">♻️</span>
            <h3>En vrac</h3>
            <p>Achetez ce dont vous avez besoin, réduisez vos emballages</p>
        </div>

        <div class="engagement-item">
            <span class="engagement-icone">🤝</span>
            <h3>Communauté</h3>
            <p>Soutenir l'économie locale, un achat à la fois</p>
        </div>

    </div>
</section>

<!-- ======================================================
     PRODUITS VEDETTES (s'affiche si des produits existent)
====================================================== -->
<?php
$produits_vedettes = new WP_Query([
    'post_type'      => 'produit',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'rand',
]);

if ($produits_vedettes->have_posts()): ?>
<section class="section-produits-vedettes">
    <h2 class="titre-section-centre">Nos produits du moment</h2>
    <p class="sous-titre-section">Une sélection fraîche de nos rayons</p>

    <div class="grille-cartes-4">
        <?php while ($produits_vedettes->have_posts()): $produits_vedettes->the_post();
            get_template_part('parts/produit', 'card');
        endwhile;
        wp_reset_postdata(); ?>
    </div>

    <div class="section-lien-centre">
        <a href="<?php echo esc_url(get_permalink(get_page_by_path('epicerie'))); ?>" class="bouton-secondaire">Voir tous les produits</a>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     NOS PRODUCTEURS (s'affiche si des producteurs existent)
====================================================== -->
<?php
$producteurs = new WP_Query([
    'post_type'      => 'producteur',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'orderby'        => 'rand',
]);

if ($producteurs->have_posts()): ?>
<section class="section-producteurs-accueil">
    <div class="producteurs-entete">
        <div>
            <h2>Nos producteurs locaux</h2>
            <p>Ils cultivent, récoltent et créent pour vous, à deux pas d'ici.</p>
        </div>
        <a href="<?php echo esc_url(get_post_type_archive_link('producteur')); ?>" class="bouton-secondaire">Voir tous les producteurs</a>
    </div>

    <div class="grille-cartes">
        <?php while ($producteurs->have_posts()): $producteurs->the_post();
            get_template_part('parts/producteur', 'card');
        endwhile;
        wp_reset_postdata(); ?>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================
     NEWSLETTER
====================================================== -->
<section class="section-newsletter">
    <div class="newsletter-interieur">
        <h2>Restez dans la boucle</h2>
        <p>Promotions, arrivages et nouvelles de nos producteurs — directement dans votre boîte de courriel.</p>
        <form class="newsletter-formulaire" action="#" method="post">
            <?php wp_nonce_field('newsletter', 'newsletter_nonce'); ?>
            <input type="email" name="courriel" placeholder="votre@courriel.com" required>
            <button type="submit">S'abonner</button>
        </form>
    </div>
</section>

<?php get_footer();

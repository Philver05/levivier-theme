<?php get_header(); ?>

<?php
if (have_posts()) the_post();

$illu    = get_stylesheet_directory_uri() . '/assets/images/illustrations/';
$slogan  = get_bloginfo('description') ?: 'Cultivons un avenir durable';
$titre   = wp_kses(str_replace('durable', '<span class="script">durable</span>', esc_html($slogan)), ['span' => ['class' => []], 'br' => []]);
$lien_ep = get_page_by_path('epicerie');
$url_ep  = $lien_ep ? get_permalink($lien_ep) : home_url('/');
$url_prod = get_post_type_archive_link('producteur') ?: home_url('/');
?>

<!-- ============================ HERO ============================ -->
<section class="hero" id="accueil">
    <span class="arche arche-terra" aria-hidden="true"></span>
    <span class="arche arche-olive" aria-hidden="true"></span>
    <div class="conteneur hero-grille">

        <div class="hero-texte reveal">
            <h1><?php echo $titre; ?></h1>
            <p>Produits locaux, vrac et zéro déchet, choisis avec soin auprès de nos producteurs du Bas-Saint-Laurent.</p>
            <div class="hero-actions">
                <a href="<?php echo esc_url($url_ep); ?>" class="btn btn-primaire">Découvrir l'épicerie</a>
                <a href="<?php echo esc_url($url_prod); ?>" class="btn btn-ligne">Nos producteurs</a>
            </div>
        </div>

        <div class="hero-fleur" aria-hidden="true">
            <span class="cercle-ocre"></span>
            <div class="fleur-parallax">
                <img class="bq bq-rose" src="<?php echo esc_url($illu); ?>fleur%2001.svg" alt="">
            </div>
        </div>

    </div>
    <div class="hero-scroll" aria-hidden="true">
        <span>Découvrir</span>
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
            <ul class="intro-liste">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21c0-7 4-12 9-14-1 8-4 13-9 14Z"/><path d="M12 21c0-6-3-10-7-12 1 7 3 11 7 12Z"/></svg> Producteurs locaux</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21V10"/><path d="M12 13c0-4-3-6-7-6 0 4 3 6 7 6Z"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7Z"/></svg> Bio &amp; naturel</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 8h10l-1 11a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1Z"/><path d="M9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg> Vrac &amp; zéro déchet</li>
            </ul>
            <div style="margin-top:1.8rem">
                <a href="<?php echo esc_url($url_ep); ?>" class="btn btn-fantome">Voir la boutique</a>
            </div>
        </div>
        <div class="intro-media reveal reveal-delai-1">
            <div class="cadre">
                <?php if (has_post_thumbnail()):
                    the_post_thumbnail('large', ['alt' => get_bloginfo('name')]);
                else: ?>
                    <div aria-hidden="true" style="width:100%;height:100%;min-height:320px;background:var(--sauge-pale)"></div>
                <?php endif; ?>
            </div>
            <span class="intro-badge">100 % local</span>
        </div>
    </div>
</section>

<!-- ========================= ENGAGEMENTS ========================= -->
<section class="section engagements">
    <div class="conteneur">
        <div class="section-titre reveal">
            <p class="eyebrow">Nos engagements</p>
            <h2>Le bon goût, en conscience</h2>
        </div>
        <div class="engagements-grille">
            <div class="engagement reveal">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,34 C20,22 26,12 34,8 C32,20 28,30 20,34 Z"/><path d="M20,34 C20,24 15,16 8,12 C11,22 14,30 20,34 Z"/><path d="M20,34 L20,20"/></svg></div>
                <h3>Local</h3>
                <p>13 producteurs de la région de Matane, Bas-Saint-Laurent.</p>
            </div>
            <div class="engagement reveal reveal-delai-1">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,34 L20,16"/><path d="M20,20 C20,14 15,9 8,9 C8,15 13,20 20,20 Z"/><path d="M20,18 C20,11 25,6 32,6 C32,13 27,18 20,18 Z"/></svg></div>
                <h3>Biologique</h3>
                <p>Une sélection naturelle, choisie pour sa qualité et son impact positif.</p>
            </div>
            <div class="engagement reveal reveal-delai-2">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M11,14 L29,14 L27,33 C27,34.5 26,35 25,35 L15,35 C14,35 13,34.5 13,33 Z"/><path d="M15,14 L15,10 C15,8 16,7 18,7 L22,7 C24,7 25,8 25,10 L25,14"/><path d="M17,21 L23,21 M17,27 L23,27"/></svg></div>
                <h3>En vrac</h3>
                <p>Achetez ce dont vous avez besoin, réduisez vos emballages.</p>
            </div>
            <div class="engagement reveal reveal-delai-3">
                <div class="ico"><svg viewBox="0 0 40 40"><path d="M20,33 C8,24 6,16 11,12 C15,9 19,12 20,16 C21,12 25,9 29,12 C34,16 32,24 20,33 Z"/><path d="M20,16 L20,22"/></svg></div>
                <h3>Communauté</h3>
                <p>Soutenir l'économie locale, un achat à la fois.</p>
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
            <p class="eyebrow">La boutique</p>
            <h2>Nos produits du moment</h2>
            <p>Une sélection fraîche de nos rayons.</p>
        </div>
        <div class="grille-cartes">
            <?php while ($produits_vedettes->have_posts()): $produits_vedettes->the_post();
                get_template_part('parts/produit', 'card');
            endwhile; wp_reset_postdata(); ?>
        </div>
        <div class="section-cta reveal"><a href="<?php echo esc_url($url_ep); ?>" class="btn btn-fantome">Voir tous les produits</a></div>
    </div>
</section>
<?php endif; ?>

<!-- ========================= NEWSLETTER ========================= -->
<section class="section newsletter" id="newsletter">
    <div class="conteneur">
        <div class="newsletter-carte reveal">
            <p class="eyebrow">Infolettre</p>
            <h2>Restez dans la boucle</h2>
            <p>Promotions, arrivages et nouvelles de nos producteurs, directement dans votre boîte de courriel.</p>
            <form class="newsletter-form" action="#" method="post">
                <?php wp_nonce_field('newsletter', 'newsletter_nonce'); ?>
                <input type="email" name="courriel" placeholder="votre@courriel.com" aria-label="Votre courriel" required>
                <button type="submit">S'abonner</button>
            </form>
        </div>
    </div>
</section>

<?php get_footer();

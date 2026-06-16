<?php
/*
 * Template pour un loft individuel — mise en page inspirée d'Airbnb (charte sarcelle)
 */
get_header();

if (!have_posts()) { get_footer(); exit; }
the_post();

$prix       = get_field('loft_prix');
$prix_sub   = get_field('loft_prix_sub')   ?: 'Tout inclus';
$features   = get_field('loft_features')   ?: "Cuisinette équipée\nWi-Fi gratuit\nTéléviseur intelligent 65\"\nSalle de bain privée\nStationnement gratuit\nLiterie de qualité\nCafetière filtre\nArrivée autonome";

$galerie = array_filter([
    get_field('loft_img1'), get_field('loft_img2'), get_field('loft_img3'),
    get_field('loft_img4'), get_field('loft_img5'), get_field('loft_img6'),
]);

$booking   = get_field('loft_booking_url');
$cta_label = get_field('loft_cta_label') ?: 'Réserver sur Airbnb';
$adresse   = get_field('loft_adresse')   ?: '14, avenue D\'Amours';
$ville     = get_field('loft_ville')     ?: 'Matane, Québec · Centre-ville';
$telephone = get_field('loft_telephone') ?: '418 562-5230';

/* Carte : embed personnalisé (Maps → Partager → Intégrer) sinon généré depuis l'adresse */
$map_q     = trim($adresse) . ', Matane, Québec';
$map_embed = get_field('loft_map_embed') ?: 'https://maps.google.com/maps?q=' . rawurlencode($map_q) . '&z=16&hl=fr&output=embed';
$map_lien  = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($map_q);

$type      = get_field('loft_type')      ?: 'Logement de location en entier';
$voyageurs = get_field('loft_voyageurs') ?: '2';
$chambres  = get_field('loft_chambres')  ?: '1';
$lits      = get_field('loft_lits')      ?: '1';
$sdb       = get_field('loft_sdb')       ?: '1';
$highlights = get_field('loft_highlights') ?: "🚪 | Arrivée autonome | Entrez à votre rythme grâce à la serrure intelligente.\n🅿️ | Stationnement gratuit | Un des rares logements de la région avec stationnement gratuit.\n☕ | Café maison | Commencez la journée du bon pied avec la cafetière filtre.";

$lien_resa  = $booking ?: 'tel:' . preg_replace('/\D/', '', $telephone);
$resa_attrs = $booking ? ' target="_blank" rel="noopener"' : '';

/* Liste des commodités */
$amenites = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $features))));

/* Highlights : "emoji | titre | description" par ligne */
$highlights_list = [];
foreach (array_filter(array_map('trim', explode("\n", str_replace("\r", '', $highlights)))) as $ligne) {
    $parts = array_map('trim', explode('|', $ligne));
    if (count($parts) >= 3) $highlights_list[] = $parts;
}

/* Photos : image mise en avant + galerie */
$photos = [];
if (has_post_thumbnail()) {
    $photos[] = ['full' => get_the_post_thumbnail_url(null, 'full'), 'thumb' => get_the_post_thumbnail_url(null, 'large'), 'alt' => get_the_title()];
}
foreach ($galerie as $img) {
    $photos[] = ['full' => $img['url'], 'thumb' => $img['sizes']['large'] ?? $img['url'], 'alt' => $img['alt'] ?: get_the_title()];
}

/* Icône selon le mot-clé de la commodité */
if (!function_exists('lv_amenite_icone')) {
    function lv_amenite_icone($label) {
        $l = mb_strtolower($label);
        $map = [
            'wi-fi' => '📶', 'wifi' => '📶', 'internet' => '📶',
            'cuisin' => '🍳', 'télé' => '📺', 'tele' => '📺', ' tv' => '📺',
            'stationnement' => '🅿️', 'parking' => '🅿️',
            'bain' => '🚿', 'douche' => '🚿',
            'lit' => '🛏️', 'chambre' => '🛏️', 'literie' => '🛏️',
            'café' => '☕', 'cafe' => '☕', 'cafetière' => '☕',
            'laveuse' => '🧺', 'sécheuse' => '🧺', 'buanderie' => '🧺', 'lavage' => '🧺',
            'arrivée' => '🚪', 'serrure' => '🚪', 'autonome' => '🚪',
            'eau' => '🌊', 'rivière' => '🌊', 'fleuve' => '🌊',
            'climatis' => '❄️', 'chauffage' => '🔥', 'corporel' => '🧴', 'produits' => '🧴',
        ];
        foreach ($map as $mot => $emoji) {
            if (strpos($l, $mot) !== false) return $emoji;
        }
        return '✓';
    }
}
?>

<div class="page-lofts loft-detail">
    <div class="conteneur loft-d-conteneur">

        <a href="<?php echo esc_url(get_permalink(get_page_by_path('location-loft'))); ?>" class="loft-d-retour">← Tous les lofts</a>

        <div class="loft-d-titre-row">
            <h1 class="loft-d-titre"><?php the_title(); ?></h1>
            <?php if ($booking): ?>
                <a href="<?php echo esc_url($booking); ?>" target="_blank" rel="noopener" class="loft-d-voir-airbnb">Voir sur Airbnb ↗</a>
            <?php endif; ?>
        </div>

        <?php if ($photos): ?>
        <div class="loft-d-galerie" id="loftGalerie">
            <button type="button" class="loft-d-photo loft-d-photo-big" data-index="0" style="background-image:url('<?php echo esc_url($photos[0]['thumb']); ?>')" aria-label="<?php echo esc_attr($photos[0]['alt']); ?>"></button>
            <?php foreach (array_slice($photos, 1, 4) as $i => $p): ?>
                <button type="button" class="loft-d-photo" data-index="<?php echo $i + 1; ?>" style="background-image:url('<?php echo esc_url($p['thumb']); ?>')" aria-label="<?php echo esc_attr($p['alt']); ?>"></button>
            <?php endforeach; ?>
            <?php if (count($photos) > 1): ?>
                <button type="button" class="loft-d-voir-photos" id="loftVoirPhotos">▦&nbsp; Afficher toutes les photos</button>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="loft-d-galerie-vide">🏠 Photos à venir</div>
        <?php endif; ?>

        <div class="loft-d-corps">

            <div class="loft-d-main">
                <p class="loft-d-soustitre"><?php echo esc_html($type); ?> · <?php echo esc_html($ville); ?></p>
                <p class="loft-d-meta"><?php echo esc_html($voyageurs); ?>&nbsp;voyageurs · <?php echo esc_html($chambres); ?>&nbsp;chambre · <?php echo esc_html($lits); ?>&nbsp;lit · <?php echo esc_html($sdb); ?>&nbsp;salle de bain</p>

                <?php if ($highlights_list): ?>
                <ul class="loft-d-highlights">
                    <?php foreach ($highlights_list as $h): ?>
                    <li>
                        <span class="loft-d-hl-icone" aria-hidden="true"><?php echo esc_html($h[0]); ?></span>
                        <span class="loft-d-hl-texte">
                            <strong><?php echo esc_html($h[1]); ?></strong>
                            <span class="loft-d-hl-desc"><?php echo esc_html($h[2]); ?></span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if (get_the_content()): ?>
                <div class="loft-d-desc-bloc">
                    <div class="loft-d-desc" id="loftDesc"><?php the_content(); ?></div>
                    <button type="button" class="loft-d-desc-plus" id="loftDescPlus">Afficher plus ›</button>
                </div>
                <?php endif; ?>

                <?php if ($amenites): ?>
                <div class="loft-d-amenites-bloc">
                    <h2 class="loft-d-h2">Pour votre confort</h2>
                    <ul class="loft-d-amenites">
                        <?php foreach ($amenites as $a): ?>
                            <li><span class="loft-d-am-icone" aria-hidden="true"><?php echo lv_amenite_icone($a); ?></span><?php echo esc_html($a); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <aside class="loft-d-resa-col">
                <div class="loft-d-resa-carte">
                    <?php if ($prix): ?>
                    <div class="loft-d-resa-prix">
                        <span class="loft-d-resa-montant"><?php echo esc_html($prix); ?></span>
                        <span class="loft-d-resa-nuit">/ nuit</span>
                    </div>
                    <?php if ($prix_sub): ?><p class="loft-d-resa-sub"><?php echo esc_html($prix_sub); ?></p><?php endif; ?>
                    <?php endif; ?>

                    <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $resa_attrs; ?> class="loft-d-resa-btn"><?php echo esc_html($cta_label); ?></a>

                    <p class="loft-d-resa-note"><?php echo $booking ? 'Disponibilités et paiement sécurisé sur Airbnb' : 'Réservez par téléphone, on s\'occupe du reste'; ?></p>

                    <?php if ($telephone): ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>" class="loft-d-resa-tel">Ou réservez au <?php echo esc_html($telephone); ?></a>
                    <?php endif; ?>
                </div>
            </aside>

        </div>

        <section class="loft-d-loc">
            <h2 class="loft-d-h2">Pour vous situer</h2>
            <p class="loft-d-loc-adresse"><?php echo esc_html($adresse); ?> · <?php echo esc_html($ville); ?></p>
            <div class="loft-d-loc-carte">
                <iframe src="<?php echo esc_url($map_embed); ?>" title="Carte — <?php echo esc_attr($adresse); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
            <a href="<?php echo esc_url($map_lien); ?>" target="_blank" rel="noopener" class="loft-d-loc-lien">Obtenir l'itinéraire ↗</a>
        </section>

    </div>

    <?php if (count($photos) > 1): ?>
    <div class="loft-d-overlay" id="loftOverlay" hidden>
        <button type="button" class="loft-d-overlay-close" id="loftOverlayClose" aria-label="Fermer la galerie">✕</button>
        <div class="loft-d-overlay-inner">
            <?php foreach ($photos as $p): ?>
                <img src="<?php echo esc_url($p['full']); ?>" alt="<?php echo esc_attr($p['alt']); ?>" loading="lazy">
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="loft-d-barre-mobile">
        <?php if ($prix): ?><span class="loft-d-barre-prix"><?php echo esc_html($prix); ?> <small>/ nuit</small></span><?php endif; ?>
        <a href="<?php echo esc_url($lien_resa); ?>"<?php echo $resa_attrs; ?> class="loft-d-resa-btn"><?php echo esc_html($cta_label); ?></a>
    </div>

</div>

<script>
(function () {
    var gal = document.getElementById('loftGalerie');
    var overlay = document.getElementById('loftOverlay');
    if (overlay) {
        var open = function () { overlay.hidden = false; document.body.style.overflow = 'hidden'; };
        var close = function () { overlay.hidden = true; document.body.style.overflow = ''; };
        if (gal) gal.addEventListener('click', function (e) {
            if (e.target.closest('.loft-d-photo') || e.target.closest('#loftVoirPhotos')) open();
        });
        var cb = document.getElementById('loftOverlayClose');
        if (cb) cb.addEventListener('click', close);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    }

    var desc = document.getElementById('loftDesc');
    var btn = document.getElementById('loftDescPlus');
    if (desc && btn) {
        if (desc.scrollHeight <= desc.clientHeight + 4) {
            btn.style.display = 'none';
            desc.classList.add('is-open');
        }
        btn.addEventListener('click', function () {
            desc.classList.add('is-open');
            btn.style.display = 'none';
        });
    }
})();
</script>

<?php get_footer(); ?>

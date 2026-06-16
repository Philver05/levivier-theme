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

$airbnb_url   = get_field('loft_airbnb_url')   ?: get_field('loft_booking_url');
$reservit_url = get_field('loft_reservit_url');
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
$highlights = get_field('loft_highlights') ?: "Arrivée autonome | Entrez à votre rythme grâce à la serrure intelligente.\nStationnement gratuit | Un des rares logements de la région avec stationnement gratuit.\nCafé maison | Commencez la journée du bon pied avec la cafetière filtre.";

/* Options de réservation : Reservit en principal, Airbnb en second, téléphone en repli */
$resa_options = [];
if ($reservit_url) $resa_options[] = ['url' => $reservit_url, 'label' => 'Réserver sur Reservit', 'ext' => true];
if ($airbnb_url)   $resa_options[] = ['url' => $airbnb_url,   'label' => 'Réserver sur Airbnb',   'ext' => true];
if (!$resa_options) $resa_options[] = ['url' => 'tel:' . preg_replace('/\D/', '', $telephone), 'label' => 'Réserver par téléphone', 'ext' => false];
$resa_principal = $resa_options[0];
$resa_en_ligne  = ($reservit_url || $airbnb_url);

/* Liste des commodités */
$amenites = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $features))));

/* Highlights : "Titre | Description" par ligne (ancien format "emoji | titre | desc" encore accepté) */
$highlights_list = [];
foreach (array_filter(array_map('trim', explode("\n", str_replace("\r", '', $highlights)))) as $ligne) {
    $parts = array_map('trim', explode('|', $ligne));
    if (count($parts) >= 3) {
        $highlights_list[] = ['titre' => $parts[1], 'desc' => $parts[2]];
    } elseif (count($parts) === 2) {
        $highlights_list[] = ['titre' => $parts[0], 'desc' => $parts[1]];
    }
}

/* Photos : image mise en avant + galerie */
$photos = [];
if (has_post_thumbnail()) {
    $photos[] = ['full' => get_the_post_thumbnail_url(null, 'full'), 'thumb' => get_the_post_thumbnail_url(null, 'large'), 'alt' => get_the_title()];
}
foreach ($galerie as $img) {
    $photos[] = ['full' => $img['url'], 'thumb' => $img['sizes']['large'] ?? $img['url'], 'alt' => $img['alt'] ?: get_the_title()];
}

/* Icône SVG (style ligne, couleur via currentColor) choisie selon un libellé */
if (!function_exists('lv_loft_icone')) {
    function lv_loft_icone($label) {
        $l = ' ' . mb_strtolower($label) . ' ';
        $mots = [
            'wifi'      => ['wi-fi', 'wifi', 'internet'],
            'sechoir'   => ['cheveux', 'sèche-cheveux'],
            'cuisine'   => ['cuisin', 'vaisselle'],
            'tele'      => ['télé', 'tele', ' tv ', 'téléviseur'],
            'parking'   => ['stationnement', 'parking'],
            'douche'    => ['bain', 'douche'],
            'lit'       => ['lit', 'chambre', 'literie', 'couchage'],
            'cafe'      => ['café', 'cafe', 'cafetière'],
            'buanderie' => ['laveuse', 'sécheuse', 'buanderie', 'lavage', 'laverie'],
            'camera'    => ['caméra', 'surveillance', 'sécurité', 'vidéo'],
            'volume'    => ['volume', 'bruit', 'silence', 'tapage'],
            'poubelle'  => ['poubelle', 'déchet', 'ordure', 'recyclage', 'tri'],
            'interdit'  => ['réservé', 'personnel', 'interdit', 'défense'],
            'porte'     => ['entrée', 'porte', 'accès'],
            'cle'       => ['arrivée', 'serrure', 'autonome', 'clé'],
            'eau'       => ['eau', 'rivière', 'fleuve', 'mer', 'vue'],
            'air'       => ['climatis', 'chauffage', 'ventil'],
            'produits'  => ['corporel', 'produit', 'savon', 'toilette'],
        ];
        $key = 'check';
        foreach ($mots as $k => $liste) {
            foreach ($liste as $m) {
                if (strpos($l, $m) !== false) { $key = $k; break 2; }
            }
        }
        $paths = [
            'wifi'      => '<path d="M4 11a12 12 0 0 1 16 0"/><path d="M7.5 14.5a7 7 0 0 1 9 0"/><path d="M10.5 18a3 3 0 0 1 3 0"/>',
            'cuisine'   => '<path d="M8 3v18"/><path d="M6 3v5a2 2 0 0 0 4 0V3"/><path d="M16 3v18"/><path d="M16 3c-2 0-3 2-3 4s1 3 3 3"/>',
            'tele'      => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8"/><path d="M12 16v4"/>',
            'parking'   => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 16V8h3a2.5 2.5 0 0 1 0 5H9"/>',
            'douche'    => '<path d="M12 3s6 6.5 6 10a6 6 0 0 1-12 0c0-3.5 6-10 6-10Z"/>',
            'lit'       => '<path d="M2 17h20"/><path d="M4 17v-4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M4 21v-4"/><path d="M20 21v-4"/><path d="M7 11V9a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2"/>',
            'cafe'      => '<path d="M5 8h11v5a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8Z"/><path d="M16 9h2a2 2 0 0 1 0 4h-2"/><path d="M8 3v2"/><path d="M11 3v2"/>',
            'buanderie' => '<rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="13" r="4"/><path d="M7 6h.01"/><path d="M10 6h.01"/>',
            'cle'       => '<circle cx="8" cy="8" r="4"/><path d="M11 11l9 9"/><path d="M20 17l-2 2"/><path d="M17 14l-2 2"/>',
            'eau'       => '<path d="M2 9q3-3 6 0t6 0 6 0"/><path d="M2 14q3-3 6 0t6 0 6 0"/><path d="M2 19q3-3 6 0t6 0 6 0"/>',
            'air'       => '<path d="M4 8h11a3 3 0 1 0-3-3"/><path d="M2 12h15a3 3 0 1 1-3 3"/><path d="M4 16h8a2.5 2.5 0 1 1-2.5 2.5"/>',
            'produits'  => '<path d="M10 3h4v3l1 2v11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V8l1-2Z"/><path d="M9 12h6"/>',
            'sechoir'   => '<path d="M3 8h9a4 4 0 0 1 0 8h-2"/><path d="M10 16l-1 4a1.5 1.5 0 0 1-3 0v-4"/><path d="M3 8v8h3"/><circle cx="8.5" cy="12" r="1.2"/>',
            'camera'    => '<path d="M2 8l15-4 1.2 4.6L3.2 12.6z"/><path d="M4.2 12.4 5 16a2 2 0 0 0 2 1.5h2.5"/><path d="M17.5 7.2 22 6"/><circle cx="9" cy="20" r="1.4"/>',
            'volume'    => '<path d="M4 9v6h4l5 4V5L8 9H4Z"/><path d="M17 9a4 4 0 0 1 0 6"/><path d="M19.5 7a7 7 0 0 1 0 10"/>',
            'porte'     => '<path d="M5 21V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v17"/><path d="M3 21h16"/><circle cx="13" cy="12" r="0.9" fill="currentColor" stroke="none"/>',
            'interdit'  => '<circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/>',
            'poubelle'  => '<path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            'check'     => '<path d="M4 12l5 5L20 6"/>',
        ];
        $inner = $paths[$key] ?? $paths['check'];
        return '<svg class="lv-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }
}
?>

<div class="page-lofts loft-detail">
    <div class="conteneur loft-d-conteneur">

        <a href="<?php echo esc_url(get_permalink(get_page_by_path('location-loft'))); ?>" class="loft-d-retour">← Tous les lofts</a>

        <div class="loft-d-titre-row">
            <h1 class="loft-d-titre"><?php the_title(); ?></h1>
            <?php if ($airbnb_url): ?>
                <a href="<?php echo esc_url($airbnb_url); ?>" target="_blank" rel="noopener" class="loft-d-voir-airbnb">Voir sur Airbnb ↗</a>
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
                        <span class="loft-d-hl-icone" aria-hidden="true"><?php echo lv_loft_icone($h['titre']); ?></span>
                        <span class="loft-d-hl-texte">
                            <strong><?php echo esc_html($h['titre']); ?></strong>
                            <span class="loft-d-hl-desc"><?php echo esc_html($h['desc']); ?></span>
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

                <?php if ($amenites): $am_visibles = 6; ?>
                <div class="loft-d-amenites-bloc">
                    <h2 class="loft-d-h2">Pour votre confort</h2>
                    <ul class="loft-d-amenites" id="loftAmenites">
                        <?php foreach ($amenites as $i => $a): ?>
                            <li<?php echo $i >= $am_visibles ? ' class="loft-d-am-cache"' : ''; ?>><span class="loft-d-am-icone" aria-hidden="true"><?php echo lv_loft_icone($a); ?></span><?php echo esc_html($a); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($amenites) > $am_visibles): ?>
                        <button type="button" class="loft-d-am-plus" id="loftAmenitesPlus" data-total="<?php echo count($amenites); ?>">Afficher les <?php echo count($amenites); ?> commodités</button>
                    <?php endif; ?>
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

                    <?php foreach ($resa_options as $i => $opt): ?>
                        <a href="<?php echo esc_url($opt['url']); ?>"<?php echo $opt['ext'] ? ' target="_blank" rel="noopener"' : ''; ?> class="loft-d-resa-btn<?php echo $i === 0 ? '' : ' loft-d-resa-btn--secondaire'; ?>"><?php echo esc_html($opt['label']); ?></a>
                    <?php endforeach; ?>

                    <p class="loft-d-resa-note"><?php echo $resa_en_ligne ? 'Disponibilités et paiement sécurisés en ligne' : 'Réservez par téléphone, on s\'occupe du reste'; ?></p>

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
        <a href="<?php echo esc_url($resa_principal['url']); ?>"<?php echo $resa_principal['ext'] ? ' target="_blank" rel="noopener"' : ''; ?> class="loft-d-resa-btn"><?php echo esc_html($resa_principal['label']); ?></a>
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

    var amPlus = document.getElementById('loftAmenitesPlus');
    var amList = document.getElementById('loftAmenites');
    if (amPlus && amList) {
        amPlus.addEventListener('click', function () {
            var ouvert = amList.classList.toggle('is-open');
            amPlus.textContent = ouvert
                ? 'Afficher moins'
                : 'Afficher les ' + amPlus.dataset.total + ' commodités';
        });
    }
})();
</script>

<?php get_footer(); ?>

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

/* Galerie par catégorie — une catégorie n'apparaît que si elle a des photos */
$categories_def = [
    'Salon'           => 'salon',
    'Cuisine'         => 'cuisine',
    'Chambre'         => 'chambre',
    'Salle de bain'   => 'bain',
    'Extérieur & vue' => 'ext',
    'Autres'          => 'autre',
];
$galerie_cats = [];
foreach ($categories_def as $nom => $slug) {
    $imgs = [];
    for ($n = 1; $n <= 4; $n++) {
        $img = get_field("loft_cat_{$slug}_{$n}");
        if ($img) {
            $imgs[] = ['full' => $img['url'], 'thumb' => $img['sizes']['large'] ?? $img['url'], 'alt' => $img['alt'] ?: get_the_title()];
        }
    }
    if ($imgs) $galerie_cats[$nom] = $imgs;
}

$airbnb_url   = get_field('loft_airbnb_url');
$reservit_url = get_field('loft_reservit_url');
$adresse   = get_field('loft_adresse')   ?: '14, avenue D\'Amours';
$ville     = get_field('loft_ville')     ?: 'Matane, Québec · Centre-ville';
$telephone = get_field('loft_telephone') ?: '418 562-5230';

/* Carte : code <iframe> complet collé depuis Google Maps, sinon généré depuis l'adresse */
$map_q       = trim($adresse) . ', Matane, Québec';
$map_lien    = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($map_q);
$map_saisie  = trim((string) get_field('loft_map_embed'));
$map_auto    = 'https://www.google.com/maps?q=' . rawurlencode($map_q) . '&z=16&hl=fr&output=embed';

if ($map_saisie !== '' && stripos($map_saisie, '<iframe') !== false) {
    /* L'utilisateur a collé le code <iframe> complet : on l'affiche tel quel (nettoyé) */
    $map_html = wp_kses($map_saisie, [
        'iframe' => [
            'src' => true, 'width' => true, 'height' => true, 'style' => true,
            'frameborder' => true, 'allowfullscreen' => true, 'loading' => true,
            'referrerpolicy' => true, 'title' => true, 'aria-label' => true,
        ],
    ]);
} else {
    /* Sinon : on n'utilise la valeur collée comme source que si c'est une vraie URL
       d'intégration (un lien de partage maps.app.goo.gl ne s'affiche pas en iframe).
       À défaut, on génère la carte depuis l'adresse. */
    $est_embed = (stripos($map_saisie, 'output=embed') !== false) || (stripos($map_saisie, '/maps/embed') !== false);
    $map_src   = ($map_saisie !== '' && $est_embed) ? $map_saisie : $map_auto;
    $map_html  = '<iframe src="' . esc_url($map_src) . '" title="Carte — ' . esc_attr($adresse) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
}

/* URL de la page liste des lofts (repérée par son template, repli sur /lofts/) */
$lofts_page = get_posts([
    'post_type'   => 'page',
    'meta_key'    => '_wp_page_template',
    'meta_value'  => 'templates/template-lofts.php',
    'numberposts' => 1,
    'fields'      => 'ids',
]);
$lofts_page_id = $lofts_page ? (int) $lofts_page[0] : 0;
$lofts_url = $lofts_page_id ? get_permalink($lofts_page_id) : home_url('/lofts/');

/* Bande finale : titre/texte repris de la page Lofts (générique, un seul
   endroit à modifier dans WP). Le bouton, lui, reste propre à CE loft
   (voir plus bas $resa_principal) : chaque appart a son propre lien de
   réservation, contrairement au titre/texte qui peuvent rester partagés. */
$cta_titre = get_field('lofts_cta_titre', $lofts_page_id) ?: 'Vos dates partent vite';
$cta_texte = get_field('lofts_cta_texte', $lofts_page_id) ?: "Deux lofts seulement, un calendrier qui se remplit. Réservez les vôtres pendant qu'ils sont libres.";

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

/* Bouton de la bande finale = même lien/texte que la réservation principale
   ci-dessus : propre à ce loft, pas partagé avec les autres fiches. */
$cta_bouton = $resa_principal['label'];
$cta_lien   = $resa_principal['url'];

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

/* Photos à plat pour la mosaïque du haut : couverture + toutes les photos catégorisées */
$photos = [];
if (has_post_thumbnail()) {
    $photos[] = ['full' => get_the_post_thumbnail_url(null, 'full'), 'thumb' => get_the_post_thumbnail_url(null, 'large'), 'alt' => get_the_title()];
}
foreach ($galerie_cats as $imgs) {
    foreach ($imgs as $p) $photos[] = $p;
}

/* Filet de sécurité : si lv_loft_icone() n'est pas encore disponible,
   la fiche ne plante pas en fatale. */
if (!function_exists('lv_loft_icone')) {
    function lv_loft_icone($label) {
        return '<svg class="lv-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12l5 5L20 6"/></svg>';
    }
}
?>

<div class="page-lofts loft-detail">
    <div class="conteneur loft-d-conteneur">

        <a href="<?php echo esc_url($lofts_url); ?>" class="loft-d-retour">Tous les lofts</a>

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
                <?php echo $map_html; ?>
            </div>
            <a href="<?php echo esc_url($map_lien); ?>" target="_blank" rel="noopener" class="loft-d-loc-lien">Obtenir l'itinéraire ↗</a>
        </section>

    </div>

    <!-- Le pied habituel du site (en sauge) est retiré sur les fiches loft :
         cette bande CTA (même contenu et design que le bas de /lofts/) en
         tient lieu. Titre/texte/bouton/lien modifiables dans WP, sur la
         page Lofts (Contenu — Page Lofts > Bande finale). -->
    <section class="lofts-cta-finale">
        <div class="conteneur">
            <div class="lofts-cta-interieur reveal">
                <h2><?php echo esc_html($cta_titre); ?></h2>
                <p><?php echo esc_html($cta_texte); ?></p>
                <a href="<?php echo esc_url($cta_lien); ?>" class="lofts-cta"><?php echo esc_html($cta_bouton); ?></a>
                <p class="lofts-contact-ligne">
                    <?php echo esc_html($adresse); ?>, Matane (QC) &nbsp;·&nbsp;
                    <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>"><?php echo esc_html($telephone); ?></a> &nbsp;·&nbsp; CITQ&nbsp;323422
                </p>
            </div>
        </div>
        <div class="conteneur lofts-cta-bas">&copy; <?php echo esc_html(wp_date('Y')); ?> Le Vivier · Matane, Québec</div>
    </section>

    <?php if (count($photos) > 1): ?>
    <div class="loft-d-overlay" id="loftOverlay" hidden>
        <button type="button" class="loft-d-overlay-close" id="loftOverlayClose" aria-label="Fermer la galerie">✕</button>
        <div class="loft-d-overlay-inner">
            <h2 class="loft-d-overlay-titre">Visite en photos</h2>

            <?php if ($galerie_cats): ?>
                <nav class="loft-d-overlay-nav">
                    <?php $ci = 0; foreach ($galerie_cats as $nom => $imgs): ?>
                        <a href="#loft-cat-<?php echo $ci; ?>"><?php echo esc_html($nom); ?> <span><?php echo count($imgs); ?></span></a>
                    <?php $ci++; endforeach; ?>
                </nav>
                <?php $ci = 0; foreach ($galerie_cats as $nom => $imgs): ?>
                    <section class="loft-d-overlay-cat" id="loft-cat-<?php echo $ci; ?>">
                        <h3><?php echo esc_html($nom); ?></h3>
                        <div class="loft-d-overlay-grille">
                            <?php foreach ($imgs as $p): ?>
                                <img src="<?php echo esc_url($p['full']); ?>" alt="<?php echo esc_attr($p['alt']); ?>" loading="lazy">
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php $ci++; endforeach; ?>
            <?php else: ?>
                <div class="loft-d-overlay-grille">
                    <?php foreach ($photos as $p): ?>
                        <img src="<?php echo esc_url($p['full']); ?>" alt="<?php echo esc_attr($p['alt']); ?>" loading="lazy">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
